<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */
use yii\db\Migration;

class m240305_153220_create_table__shop_feedback extends Migration
{
    public function safeUp()
    {
        $tableName = 'shop_feedback';
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

            'created_by'     => $this->integer()->notNull(),
            'shop_product_id' => $this->integer()->notNull(),

            'status' => $this->string(255)->notNull()->defaultValue("approved"),

            'message' => $this->text()->null(),

            'seller_message' => $this->text()->null(),
            'seller_cms_user_id' => $this->integer()->null(),

            'rate'    => $this->integer()->notNull(),

        ], $tableOptions);

        $this->createIndex($tableName.'__created_at', $tableName, 'created_at');
        $this->createIndex($tableName.'__updated_at', $tableName, 'updated_at');

        $this->createIndex($tableName.'__created_by', $tableName, 'created_by');
        $this->createIndex($tableName.'__shop_product_id', $tableName, 'shop_product_id');

        $this->createIndex($tableName.'__rate', $tableName, 'rate');

        $this->createIndex($tableName.'__unique', $tableName, ['created_by', 'shop_product_id'], true);

        $this->addForeignKey(
            "{$tableName}__created_by", $tableName,
            'created_by', '{{%cms_user}}', 'id', 'RESTRICT', 'RESTRICT'
        );

        $this->addForeignKey(
            "{$tableName}__seller_cms_user_id", $tableName,
            'seller_cms_user_id', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            "{$tableName}__shop_product_id", $tableName,
            'shop_product_id', '{{%shop_product}}', 'id', 'RESTRICT', 'RESTRICT'
        );

    }

    public function safeDown()
    {
    }
}