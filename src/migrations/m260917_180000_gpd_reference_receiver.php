<?php
use yii\db\Migration;

class m260917_180000_gpd_reference_receiver extends Migration
{
    public function up()
    {
        // Explicit independent stream tables; catalog connection selection and cursors stay unchanged.
        $this->execute('CREATE TABLE {{%shop_gpd_reference_connection}} LIKE {{%shop_gpd_connection}}');
        $this->execute('CREATE TABLE {{%shop_gpd_reference_state}} LIKE {{%shop_gpd_catalog_state}}');
        $this->addColumn('{{%shop_gpd_reference_state}}','kind',$this->string(20));
        $this->addColumn('{{%shop_gpd_reference_state}}','source_id',$this->integer());
        $this->createIndex('gpd_reference_identity','{{%shop_gpd_reference_state}}',['connection_id','kind','source_id'],true);
    }
    public function down(){return false;}
}
