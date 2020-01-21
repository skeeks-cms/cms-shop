<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200121_170601__alter_table__shop_type_price extends Migration
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

        $this->addColumn($tableName, "shop_supplier_id", $this->integer());

        $this->createIndex($tableName.'__shop_supplier_id', $tableName, 'shop_supplier_id');

        $this->addForeignKey(
            "{$tableName}__shop_supplier_id", $tableName,
            'shop_supplier_id', '{{%shop_supplier}}', 'id', 'RESTRICT', 'RESTRICT'
        );
    }

    public function safeDown()
    {
        echo "m200121_170601__alter_table__shop_type_price cannot be reverted.\n";
        return false;
    }
}