<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200211_100601__create_table__shop_supplier_property extends Migration
{
    public function safeUp()
    {
        $tableName = 'shop_supplier_property';
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

            'shop_supplier_id' => $this->integer()->notNull(),
            'external_code' => $this->string(255)->notNull(),

            'name'      => $this->string(255),

            'is_visible'      => $this->integer(1)->notNull()->defaultValue(1),

            'cms_content_property_id' => $this->integer(),

        ], $tableOptions);

        $this->createIndex($tableName.'__is_visible', $tableName, 'is_visible');
        $this->createIndex($tableName.'__cms_content_property_id', $tableName, 'cms_content_property_id');

        $this->createIndex($tableName.'__shop_supplier2external_code', $tableName, ['shop_supplier_id', 'external_code'], true);

        $this->addCommentOnTable($tableName, 'Свойства поставщика');

        $this->addForeignKey(
            "{$tableName}__shop_supplier_id", $tableName,
            'shop_supplier_id', '{{%shop_supplier}}', 'id', 'CASCADE', 'CASCADE'
        );
        $this->addForeignKey(
            "{$tableName}__cms_content_property_id", $tableName,
            'cms_content_property_id', '{{%cms_content_property}}', 'id', 'SET NULL', 'SET NULL'
        );
    }

    public function safeDown()
    {
        echo "m200211_100601__create_table__shop_supplier_property cannot be reverted.\n";
        return false;
    }
}