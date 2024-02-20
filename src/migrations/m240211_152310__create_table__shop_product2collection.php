<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m240211_152310__create_table__shop_product2collection extends Migration
{
    public function safeUp()
    {
        $tableName = 'shop_product2collection';
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

            'shop_product_id'      => $this->integer()->notNull()->comment("Товар"),
            'shop_collection_id'      => $this->integer()->notNull()->comment("Коллекция"),

        ], $tableOptions);

        $this->addCommentOnTable($tableName, "Связь товара с Коллекцией");

        $this->createIndex($tableName.'__uniq', $tableName, ['shop_product_id', 'shop_collection_id'], true);

        $this->addForeignKey(
            "{$tableName}__shop_product_id", $tableName,
            'shop_product_id', '{{%shop_product}}', 'id', 'CASCADE', 'CASCADE'
        );

        $this->addForeignKey(
            "{$tableName}__shop_collection_id", $tableName,
            'shop_collection_id', '{{%shop_collection}}', 'id', 'CASCADE', 'CASCADE'
        );
    }

    public function safeDown()
    {
        echo "m200212_130601__create_table__shop_supplier_property_option cannot be reverted.\n";
        return false;
    }
}