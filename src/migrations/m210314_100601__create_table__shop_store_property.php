<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m210314_100601__create_table__shop_store_property extends Migration
{
    public function safeUp()
    {
        $tableName = 'shop_store_property';
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

            'shop_store_id' => $this->integer()->notNull(),
            'external_code' => $this->string(255)->notNull(),

            'name'      => $this->string(255),

            'is_visible'      => $this->integer(1)->notNull()->defaultValue(1),

            'cms_content_property_id' => $this->integer(),
            'priority' => $this->integer()->defaultValue(500),

            'property_type' => $this->string(255),
            'import_delimetr' => $this->string(255),

        ], $tableOptions);

        $this->createIndex($tableName.'__is_visible', $tableName, 'is_visible');
        $this->createIndex($tableName.'__priority', $tableName, 'priority');
        $this->createIndex($tableName.'__property_type', $tableName, 'property_type');
        $this->createIndex($tableName.'__cms_content_property_id', $tableName, 'cms_content_property_id');

        $this->createIndex($tableName.'__shop_store2external_code', $tableName, ['shop_store_id', 'external_code'], true);

        $this->addForeignKey(
            "{$tableName}__shop_store_id", $tableName,
            'shop_store_id', '{{%shop_store}}', 'id', 'CASCADE', 'CASCADE'
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