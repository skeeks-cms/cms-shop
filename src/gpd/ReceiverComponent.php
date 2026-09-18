<?php
namespace skeeks\cms\shop\gpd;

/** One catalog per site. Credentials remain on the existing API component. */
class ReceiverComponent extends \yii\base\Component
{
    /** null follows the site GPD settings; an explicit boolean keeps legacy configuration. */
    public $enabled = null;
    public $referencesEnabled = true;
    public $offersEnabled = true;
    public $siteId = 0;
    public $url = 'https://gpd-api.skeeks.com/v2';
    public $apiComponent = 'skeeksSuppliersApi';
    /** Compatibility with the first receipt-only pilot configuration. */
    public $connections = [];

    private function enabledForSite(int $site): bool
    {
        if ($site < 1 || $this->enabled === false) return false;
        $configuredSite = (int)$this->siteId;
        if (!$configuredSite && \Yii::$app->has('skeeks')) {
            $configuredSite = (int)(\Yii::$app->skeeks->site->id ?? 0);
        }
        if ($configuredSite !== $site) return false;
        if ($this->enabled === true) return true;
        return \Yii::$app->has('gpd') && (bool)\Yii::$app->gpd->forSite($site)->enabled;
    }
    public function connectionIdForSite(int $site): string
    {
        if ($site < 1) throw new ProtocolException('connection_disabled_or_wrong_site');
        $legacy=[];
        foreach ($this->connections as $id=>$config) {
            if (is_array($config) && ($config['enabled']??false)===true && (int)($config['siteId']??0)===$site) $legacy[]=(string)$id;
        }
        if (count($legacy)>1 || ($legacy && $this->enabled)) throw new ProtocolException('ambiguous_site_connection');
        if ($legacy) return $legacy[0];
        if (!$this->enabledForSite($site)) throw new ProtocolException('connection_disabled_or_wrong_site');
        // Keep the pilot row identity, its receipt state and cursors when flattening configuration.
        $ids=(new \yii\db\Query())->select('id')->from('{{%shop_gpd_connection}}')
            ->where(['cms_site_id'=>$site])->limit(2)->column(\Yii::$app->db);
        if (count($ids)>1) throw new ProtocolException('ambiguous_site_connection');
        return $ids ? (string)$ids[0] : 'site-'.$site;
    }

    public function receiverForSite(int $site, ?string $legacyId=null): CatalogReceiver
    {
        $id=$this->connectionIdForSite($site);
        if ($legacyId!==null && $legacyId!==$id) throw new ProtocolException('connection_disabled_or_wrong_site');
        return $this->receiver($id,$site);
    }

    public function receiver(string $id, int $site): CatalogReceiver
    {
        $config=$this->connections[$id]??null;
        if ($config===null && $this->enabledForSite($site) && $id===$this->connectionIdForSite($site)) {
            $config=['enabled'=>true,'siteId'=>$site,'url'=>$this->url,'apiComponent'=>$this->apiComponent];
        }
        if (!is_array($config) || ($config['enabled']??false)!==true || (int)($config['siteId']??0)!==$site || $site<1) {
            throw new ProtocolException('connection_disabled_or_wrong_site');
        }
        $api=\Yii::$app->get($config['apiComponent']??'skeeksSuppliersApi');
        $url=(string)($config['url']??'https://gpd-api.skeeks.com/v2');
        $key=(string)$api->api_key;
        $receiver=new CatalogReceiver(\Yii::$app->db,$id,new CatalogTransport($url,$key));
        $receiver->register($site,$url,hash('sha256',$key));
        return $receiver;
    }
}
