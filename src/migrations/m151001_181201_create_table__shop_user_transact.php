<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 17.09.2015
 */

use yii\db\Schema;
use yii\db\Migration;

class m151001_181201_create_table__shop_user_transact extends Migration
{
    public function safeUp()
    {
        $tableExist = $this->db->getTableSchema("{{%shop_user_transact}}", true);
        if ($tableExist) {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable("{{%shop_user_transact}}", [
            'id' => $this->primaryKey(),

            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),

            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),

            'cms_user_id' => $this->integer()->notNull(),
            'shop_order_id' => $this->integer(),

            'amount' => $this->decimal(18, 4)->notNull()->defaultValue(0),
            'currency_code' => $this->string(3)->notNull(),

            'debit' => $this->string(1)->notNull()->defaultValue('N'),
            'description' => $this->string(255)->notNull(),
            'notes' => $this->text(),


        ], $tableOptions);


        $this->createIndex('updated_by', '{{%shop_user_transact}}', 'updated_by');
        $this->createIndex('created_by', '{{%shop_user_transact}}', 'created_by');
        $this->createIndex('created_at', '{{%shop_user_transact}}', 'created_at');
        $this->createIndex('updated_at', '{{%shop_user_transact}}', 'updated_at');

        $this->createIndex('shop_order_id', '{{%shop_user_transact}}', 'shop_order_id');
        $this->createIndex('cms_user_id', '{{%shop_user_transact}}', 'cms_user_id');
        $this->createIndex('amount', '{{%shop_user_transact}}', 'amount');
        $this->createIndex('currency_code', '{{%shop_user_transact}}', 'currency_code');
        $this->createIndex('debit', '{{%shop_user_transact}}', 'debit');
        $this->createIndex('description', '{{%shop_user_transact}}', 'description');


        $this->execute("ALTER TABLE {{%shop_user_transact}} COMMENT = 'Транзакции пользователя';");

        $this->addForeignKey(
            'shop_user_transact_created_by', "{{%shop_user_transact}}",
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_user_transact_updated_by', "{{%shop_user_transact}}",
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );


        $this->addForeignKey(
            'shop_user_transact__shop_order_id', "{{%shop_user_transact}}",
            'shop_order_id', '{{%shop_order}}', 'id', 'SET NULL', 'SET NULL'
        );


        $this->addForeignKey(
            'shop_user_transact__cms_user_id', "{{%shop_user_transact}}",
            'cms_user_id', '{{%cms_user}}', 'id', 'CASCADE', 'CASCADE'
        );

        $this->addForeignKey(
            'shop_user_transact__currency_code', "{{%shop_user_transact}}",
            'currency_code', '{{%money_currency}}', 'code', 'RESTRICT', 'RESTRICT'
        );
    }

    public function safeDown()
    {
        $this->dropTable("{{%shop_user_transact}}");
    }
}