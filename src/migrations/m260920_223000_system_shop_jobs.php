<?php
use yii\db\Migration;
use skeeks\cms\shop\gpd\ScheduleUpgrade;
use skeeks\cms\shop\gpd\LegacyProcessGuard;
class m260920_223000_system_shop_jobs extends Migration
{
    public function safeUp()
    {
        $routes = ['shop/agents/update-subproducts'=>'shop.update-subproducts',
            'shop/notify/quantity-emails'=>'shop.quantity-emails'];
        $rows=(new yii\db\Query())->from('{{%cms_agent}}')->where(['name'=>array_keys($routes)])->all($this->db);
        if (array_filter($rows, static fn($row)=>(bool)$row['is_running'])) {
            LegacyProcessGuard::assertStopped(defined('ROOT_DIR') ? ROOT_DIR : Yii::getAlias('@root'), '/proc', array_keys($routes));
        }
        foreach ($rows as $row) {
            $this->update('{{%cms_agent}}', [
                'job_type'=>$routes[$row['name']], 'job_payload'=>'{}', 'is_system'=>1, 'is_running'=>0,
            ], ['id'=>$row['id']]);
        }
        $this->update('{{%cms_agent}}', ['is_system'=>1], ['job_type'=>array_keys(ScheduleUpgrade::SCHEDULES)]);
    }
    public function safeDown(){return false;}
}