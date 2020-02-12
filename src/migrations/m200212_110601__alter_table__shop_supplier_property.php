<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200212_110601__alter_table__shop_supplier_property extends Migration
{
    public function safeUp()
    {
        $tableName = 'shop_supplier_property';
        /*$tableExist = $this->db->getTableSchema($tableName, true);
        if (!$tableExist) {
            return true;
        }*/
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->addColumn($tableName, "property_type", $this->string(255));
        $this->createIndex($tableName . '__property_type', $tableName, 'property_type');
    }

    public function safeDown()
    {
        echo "m200211_100601__create_table__shop_supplier_property cannot be reverted.\n";
        return false;
    }
}