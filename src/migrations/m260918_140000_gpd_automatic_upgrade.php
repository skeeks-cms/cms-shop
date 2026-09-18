<?php
use yii\db\Migration;
use skeeks\cms\shop\components\GpdComponent;
use skeeks\cms\shop\gpd\ScheduleUpgrade;

/** Replace legacy GPD agents after package update; hosting owns worker activation. */
class m260918_140000_gpd_automatic_upgrade extends Migration
{
    public function safeUp()
    {
        $table=$this->db->schema->getTableSchema('{{%cms_agent}}');
        if(!$table || !isset($table->columns['job_type']))throw new RuntimeException('Сначала примените миграции cms-agent/cms-job.');
        (new ScheduleUpgrade($this->db))->run(static function(int $site,bool $enable){
            if(!$enable)return;
            $settings=\Yii::$app->gpd->forSite($site);
            $settings->override=GpdComponent::OVERRIDE_SITE;
            $settings->enabled=1;
            if(!$settings->save(true,['enabled']))throw new RuntimeException('Не удалось включить GPD для сайта #'.$site.'.');
        });
    }
    public function safeDown(){echo "Restore legacy schedules explicitly; received v2 cursors must be retained.\n";return false;}
}
