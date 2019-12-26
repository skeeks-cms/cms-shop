<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m191212_150601__alter_table__shop_product extends Migration
{
    public function safeUp()
    {
        $tableName = 'shop_product';
        /*$tableExist = $this->db->getTableSchema($tableName, true);
        if (!$tableExist) {
            return true;
        }*/
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->addColumn($tableName, "shop_supplier_id", $this->integer()->comment("Поставщик"));
        $this->addColumn($tableName, "supplier_external_id", $this->string(255)->comment("ID в системе поставщика"));
        $this->addColumn($tableName, "supplier_external_jsondata", $this->text()->comment("Данные по товару от поставщика"));

        $this->createIndex($tableName.'__supplier_external', $tableName, ['shop_supplier_id', 'supplier_external_id'], true);

        $this->addForeignKey(
            "{$tableName}__shop_supplier_id", $tableName,
            'shop_supplier_id', '{{%shop_supplier}}', 'id', 'RESTRICT', 'RESTRICT'
        );

    }

    public function safeDown()
    {
        echo "m191118_150601__alter_table__shop_product cannot be reverted.\n";
        return false;
    }
}