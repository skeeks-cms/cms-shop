<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */
use yii\db\Migration;

class m240305_163220_create_table__shop_feedback2image extends Migration
{
    public function safeUp()
    {
        $tableName = 'shop_feedback2image';
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

            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),

            'storage_file_id'     => $this->integer()->notNull(),
            'shop_feedback_id' => $this->integer()->notNull(),

            'priority' => $this->integer()->null()->defaultValue(100),

        ], $tableOptions);

        $this->createIndex($tableName.'__created_at', $tableName, 'created_at');
        $this->createIndex($tableName.'__updated_at', $tableName, 'updated_at');
        $this->createIndex($tableName.'__priority', $tableName, 'priority');

        $this->createIndex($tableName.'__unique', $tableName, ['storage_file_id', 'shop_feedback_id'], true);

        $this->addForeignKey(
            "{$tableName}__storage_file_id", $tableName,
            'storage_file_id', '{{%cms_storage_file}}', 'id', 'CASCADE', 'CASCADE'
        );

        $this->addForeignKey(
            "{$tableName}__shop_feedback_id", $tableName,
            'shop_feedback_id', '{{%shop_feedback}}', 'id', 'CASCADE', 'CASCADE'
        );

    }

    public function safeDown()
    {}
}