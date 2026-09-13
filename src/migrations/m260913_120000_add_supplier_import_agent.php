<?php
use yii\db\Migration;

class m260913_120000_add_supplier_import_agent extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%shop_store}}', 'import_agent_id', $this->integer()->null());
        $this->createIndex('shop_store_import_agent', '{{%shop_store}}', 'import_agent_id');
        // cms-agent is optional; no FK to a table absent in standalone shop installs.
    }
    public function safeDown()
    {
        $this->dropIndex('shop_store_import_agent', '{{%shop_store}}');
        $this->dropColumn('{{%shop_store}}', 'import_agent_id');
    }
}
