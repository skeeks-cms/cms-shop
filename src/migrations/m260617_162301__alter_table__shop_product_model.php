<?php

use yii\db\Migration;

class m260617_162301__alter_table__shop_product_model extends Migration
{
    public function safeUp()
    {
        $tableName = 'shop_product_model';

        $this->addColumn($tableName, 'sx_id', $this->integer()->null());
        $this->createIndex($tableName.'__sx_id', $tableName, 'sx_id', true);
    }

    public function safeDown()
    {
        return false;
    }
}
