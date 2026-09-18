<?php
use yii\db\Migration;
/** Reapply metadata written before property handlers were initialized before serialization. */
class m260918_020000_gpd_property_handler_settings extends Migration
{
    public function safeUp()
    {
        if($this->db->schema->getTableSchema('{{%shop_gpd_reference_state}}',true)) {
            $this->update('{{%shop_gpd_reference_state}}',['applied_revision'=>0,'applied_at'=>null],['kind'=>'properties']);
        }
    }
    public function safeDown(){return false;}
}