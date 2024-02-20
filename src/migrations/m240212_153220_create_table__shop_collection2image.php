<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */
use yii\db\Schema;
use yii\db\Migration;

class m240212_153220_create_table__shop_collection2image extends Migration
{
    public function safeUp()
    {
        $tableName = 'shop_collection2image';
        $tableExist = $this->db->getTableSchema($tableName, true);
        if ($tableExist)
        {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable($tableName, [
            'id'                    => $this->primaryKey(),

            'created_at'            => $this->integer(),

            'storage_file_id'       => $this->integer()->notNull(),
            'shop_collection_id'    => $this->integer()->notNull(),

            'priority'              => $this->integer()->notNull()->defaultValue(100),

        ], $tableOptions);

        $this->createIndex($tableName.'__priority', $tableName, 'priority');
        $this->createIndex($tableName.'__created_at', $tableName, 'created_at');
        $this->createIndex($tableName.'__storage_file_id', $tableName, 'storage_file_id');
        $this->createIndex($tableName.'__shop_collection_id', $tableName, 'shop_collection_id');
        $this->createIndex($tableName.'__unique', $tableName, ['shop_collection_id', 'storage_file_id'], true);

        $this->addForeignKey(
            "{$tableName}__storage_file_id", $tableName,
            'storage_file_id', '{{%cms_storage_file}}', 'id', 'CASCADE', 'CASCADE'
        );

        $this->addForeignKey(
            "{$tableName}__shop_collection_id", $tableName,
            'shop_collection_id', '{{%shop_collection}}', 'id', 'CASCADE', 'CASCADE'
        );

    }

    public function safeDown()
    {
    }
}