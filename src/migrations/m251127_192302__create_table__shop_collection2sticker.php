<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m251127_192302__create_table__shop_collection2sticker extends Migration
{
    public function safeUp()
    {
        $tableName = 'shop_collection2sticker';
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

            'created_by' => $this->integer()->null(),
            'created_at' => $this->integer()->null(),

            'shop_collection_id' => $this->integer()->notNull()->comment("Счет"),
            'shop_collection_sticker_id' => $this->integer()->notNull()->comment("Платеж"),

        ], $tableOptions);

        $this->addCommentOnTable($tableName, "Связь коллекций со стикерами");

        $this->createIndex($tableName.'__created_by', $tableName, 'created_by');
        $this->createIndex($tableName.'__created_at', $tableName, 'created_at');

        $this->createIndex($tableName.'__unique', $tableName, ['shop_collection_id', 'shop_collection_sticker_id'], true);


        $this->addForeignKey(
            "{$tableName}__created_by", $tableName,
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            "{$tableName}__shop_collection_id", $tableName,
            'shop_collection_id', '{{%shop_collection}}', 'id', 'CASCADE', 'CASCADE'
        );

        $this->addForeignKey(
            "{$tableName}__shop_collection_sticker_id", $tableName,
            'shop_collection_sticker_id', '{{%shop_collection_sticker}}', 'id', 'CASCADE', 'CASCADE'
        );

    }

    public function safeDown()
    {
        echo "m200212_130601__create_table__shop_supplier_property_option cannot be reverted.\n";
        return false;
    }
}