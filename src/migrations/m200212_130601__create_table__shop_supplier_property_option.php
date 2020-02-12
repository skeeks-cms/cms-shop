<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200212_130601__create_table__shop_supplier_property_option extends Migration
{
    public function safeUp()
    {
        $tableName = 'shop_supplier_property_option';
        $tableExist = $this->db->getTableSchema($tableName, true);

        if ($tableExist) {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable($tableName, [

            'id' => $this->primaryKey(),

            'shop_supplier_property_id' => $this->integer()->notNull(),
            'name' => $this->string(255)->notNull(),

            'cms_content_property_enum_id' => $this->integer(),
            'cms_content_element_id' => $this->integer(),

        ], $tableOptions);

        $this->createIndex($tableName.'__cms_content_property_enum_id', $tableName, 'cms_content_property_enum_id');
        $this->createIndex($tableName.'__cms_content_element_id', $tableName, 'cms_content_element_id');

        $this->createIndex($tableName.'__property2name', $tableName, ['shop_supplier_property_id', 'name'], true);

        $this->addCommentOnTable($tableName, 'Опции свойств поставщика');

        $this->addForeignKey(
            "{$tableName}__shop_supplier_property_id", $tableName,
            'shop_supplier_property_id', '{{%shop_supplier_property}}', 'id', 'CASCADE', 'CASCADE'
        );

        $this->addForeignKey(
            "{$tableName}__cms_content_property_enum_id", $tableName,
            'cms_content_property_enum_id', '{{%cms_content_property_enum}}', 'id', 'SET NULL', 'SET NULL'
        );
        $this->addForeignKey(
            "{$tableName}__cms_content_element_id", $tableName,
            'cms_content_element_id', '{{%cms_content_element}}', 'id', 'SET NULL', 'SET NULL'
        );
    }

    public function safeDown()
    {
        echo "m200212_130601__create_table__shop_supplier_property_option cannot be reverted.\n";
        return false;
    }
}