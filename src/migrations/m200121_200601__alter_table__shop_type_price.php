<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200121_200601__alter_table__shop_type_price extends Migration
{
    public function safeUp()
    {
        $tableName = 'shop_type_price';
        /*$tableExist = $this->db->getTableSchema($tableName, true);
        if (!$tableExist) {
            return true;
        }*/
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->addColumn($tableName, "external_id", $this->string(255));

        $this->createIndex($tableName.'__external_id', $tableName, ['shop_supplier_id', 'external_id'], true);
    }

    public function safeDown()
    {
        echo "m200121_200601__alter_table__shop_type_price cannot be reverted.\n";
        return false;
    }
}