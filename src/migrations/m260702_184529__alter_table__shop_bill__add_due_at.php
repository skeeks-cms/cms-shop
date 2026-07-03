<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS
 */

use yii\db\Migration;

class m260702_184529__alter_table__shop_bill__add_due_at extends Migration
{
    public function safeUp()
    {
        $tableName = 'shop_bill';

        if ($this->db->getTableSchema($tableName, true)->getColumn('due_at')) {
            return true;
        }

        $this->addColumn($tableName, 'due_at', $this->integer()->null()->comment('Оплатить до'));
        $this->createIndex($tableName.'__due_at', $tableName, 'due_at');
    }

    public function safeDown()
    {
        echo "m260702_184529__alter_table__shop_bill__add_due_at cannot be reverted.\n";
        return false;
    }
}
