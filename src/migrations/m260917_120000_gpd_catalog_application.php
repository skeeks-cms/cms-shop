<?php
use yii\db\Migration;

class m260917_120000_gpd_catalog_application extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%shop_gpd_catalog_state}}','applied_revision',$this->bigInteger()->unsigned()->notNull()->defaultValue(0));
        $this->addColumn('{{%shop_gpd_catalog_state}}','applied_at',$this->integer());
        $this->addColumn('{{%shop_gpd_catalog_state}}','local_product_id',$this->integer());
        $this->addColumn('{{%shop_gpd_catalog_state}}','deactivated_by_gpd',$this->boolean()->notNull()->defaultValue(false));
    }
    public function safeDown() {return false;}
}
