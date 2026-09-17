<?php
namespace skeeks\cms\shop\gpd;

/** Explicit opt-in connections. API credentials remain on the existing component. */
class ReceiverComponent extends \yii\base\Component
{
    public $connections = [];
    public function receiver(string $id, int $site): CatalogReceiver
    {
        $config=$this->connections[$id]??null;
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
