<?php
namespace skeeks\cms\shop\gpd;

use yii\db\Query;

/** Wake the native writer only after receipt has committed. No publisher/API-server logic. */
final class CatalogApplyDispatch
{
    public static function forSite(int $site): void
    {
        $app=\Yii::$app;
        if(!$app->has('jobs')||!$app->has('gpd')||!$app->gpd->forSite($site)->enabled||$app->db->getTransaction())return;
        $pending=(new Query())->from(['s'=>'{{%shop_gpd_catalog_state}}'])
            ->innerJoin(['c'=>'{{%shop_gpd_connection}}'],'c.id=s.connection_id')
            ->where(['c.cms_site_id'=>$site,'s.needs_resolution'=>0])
            ->andWhere(['in','s.operation',['upsert','revoke']])->andWhere('s.revision>s.applied_revision')->exists($app->db);
        if(!$pending)return;
        try {
            $app->jobs->push('shop.gpd.catalog.apply',[],['siteId'=>$site,'priority'=>30,
                'triggerType'=>'event','triggerRef'=>'gpd:catalog-received']);
        }catch(\Throwable $e){
            // The committed receipt is safe. The regular apply schedule retries wakeup.
            \Yii::error('GPD apply wakeup failed: '.get_class($e),__METHOD__);
        }
    }
}
