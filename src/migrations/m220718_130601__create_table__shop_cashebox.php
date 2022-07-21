<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m220718_130601__create_table__shop_cashebox extends Migration
{
    public function safeUp()
    {
        $tableName = 'shop_cashebox';
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

            'cms_site_id' => $this->integer()->notNull(),

            'name'          => $this->string(255)->notNull(),
            'shop_store_id' => $this->integer(),
            'is_active'     => $this->integer(1)->notNull()->defaultValue(1),
            'priority'      => $this->integer()->notNull()->defaultValue(100),

        ], $tableOptions);

        $this->addCommentOnTable($tableName, "Касса");

        $this->createIndex($tableName.'__shop_store_id', $tableName, 'shop_store_id');
        $this->createIndex($tableName.'__is_active', $tableName, 'is_active');
        $this->createIndex($tableName.'__priority', $tableName, 'priority');

        $this->addForeignKey(
            "{$tableName}__shop_store_id", $tableName,
            'shop_store_id', '{{%shop_store}}', 'id', 'RESTRICT', 'RESTRICT'
        );

        $this->addForeignKey(
            "{$tableName}__cms_site_id", $tableName,
            'cms_site_id', '{{%cms_site}}', 'id', 'CASCADE', 'CASCADE'
        );
    }

    public function safeDown()
    {
        echo "m200212_130601__create_table__shop_supplier_property_option cannot be reverted.\n";
        return false;
    }
}