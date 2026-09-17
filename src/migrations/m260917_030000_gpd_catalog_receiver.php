<?php
use yii\db\Migration;

/** Receiver metadata only: no product JSON, credentials or separate execution queue. */
class m260917_030000_gpd_catalog_receiver extends Migration
{
    public function safeUp()
    {
        $options = 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
        $this->createTable('{{%shop_gpd_connection}}', [
            'id'=>$this->string(100)->notNull(),
            'cms_site_id'=>$this->integer()->notNull(),
            'source_url'=>$this->string(512)->notNull(),
            'credential_fingerprint'=>$this->char(64)->notNull(),
            'phase'=>$this->string(20)->notNull()->defaultValue('bootstrap'),
            'cursor'=>$this->text(), 'changes_cursor'=>$this->text(),
            'generation'=>$this->bigInteger()->notNull()->defaultValue(0),
            'total'=>$this->integer()->notNull()->defaultValue(0),
            'received'=>$this->integer()->notNull()->defaultValue(0),
            'updated_at'=>$this->integer()->notNull(),
            'PRIMARY KEY ([[id]])',
        ], $options);
        $this->createTable('{{%shop_gpd_catalog_state}}', [
            'connection_id'=>$this->string(100)->notNull(),
            'product_id'=>$this->bigInteger()->notNull(),
            'revision'=>$this->bigInteger()->unsigned()->notNull()->defaultValue(0),
            'product_revision'=>$this->bigInteger()->unsigned(),
            'operation'=>$this->string(20)->notNull()->defaultValue('pending'),
            'needs_resolution'=>$this->boolean()->notNull()->defaultValue(true),
            'seen_generation'=>$this->bigInteger()->notNull()->defaultValue(0),
            'updated_at'=>$this->integer()->notNull(),
            'PRIMARY KEY ([[connection_id]], [[product_id]])',
        ], $options);
        $this->createIndex('gpd_resolve', '{{%shop_gpd_catalog_state}}', ['connection_id','needs_resolution','product_id']);
        $this->addForeignKey($this->db->tablePrefix.'gpd_receiver_connection', '{{%shop_gpd_catalog_state}}', 'connection_id', '{{%shop_gpd_connection}}', 'id', 'RESTRICT');
    }
    public function safeDown()
    {
        echo "Receiver cursors must be retained; remove explicitly only after disabling its schedules.\n";
        return false;
    }
}
