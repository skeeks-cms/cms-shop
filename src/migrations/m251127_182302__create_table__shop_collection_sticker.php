<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m251127_182302__create_table__shop_collection_sticker extends Migration
{
    public function safeUp()
    {
        $tableName = 'shop_collection_sticker';
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

            //Основная информация
            'name' => $this->string(255)->notNull()->comment("Название"),

            'description' => $this->text()->null()->comment("Описание"),
            'color' => $this->string(255)->null()->comment("Цвет"),

            'priority' => $this->integer()->notNull()->defaultValue(500)->comment("Сортировка"),

        ], $tableOptions);

        $this->addCommentOnTable($tableName, "Стикеры коллекций");

        $this->createIndex($tableName.'__name', $tableName, 'name');
        $this->createIndex($tableName.'__color', $tableName, 'color');

        $this->createIndex($tableName.'__created_by', $tableName, 'created_by');
        $this->createIndex($tableName.'__created_at', $tableName, 'created_at');

        $this->addForeignKey(
            "{$tableName}__created_by", $tableName,
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

    }

    public function safeDown()
    {
        echo "m200212_130601__create_table__shop_supplier_property_option cannot be reverted.\n";
        return false;
    }
}