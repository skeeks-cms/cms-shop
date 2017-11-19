<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Schema;
use yii\db\Migration;

class m150915_110601_create_table__shop_user_account extends Migration
{
    public function safeUp()
    {
        $tableExist = $this->db->getTableSchema("{{%shop_user_account}}", true);
        if ($tableExist) {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable("{{%shop_user_account}}", [
            'id' => $this->primaryKey(),

            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),

            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),

            'user_id' => $this->integer()->notNull(),

            'current_budget' => $this->decimal(18, 4)->notNull()->defaultValue(0),
            'currency_code' => $this->string(3)->notNull(),

            'locked' => $this->string(1)->notNull()->defaultValue('N'),
            'locked_at' => $this->integer(),
            'notes' => $this->text(),

        ], $tableOptions);


        $this->createIndex('shop_user_account__updated_by', '{{%shop_user_account}}', 'updated_by');
        $this->createIndex('shop_user_account__created_by', '{{%shop_user_account}}', 'created_by');
        $this->createIndex('shop_user_account__created_at', '{{%shop_user_account}}', 'created_at');
        $this->createIndex('shop_user_account__updated_at', '{{%shop_user_account}}', 'updated_at');


        $this->createIndex('shop_user_account__current_budget', '{{%shop_user_account}}', 'current_budget');
        $this->createIndex('shop_user_account__locked', '{{%shop_user_account}}', 'locked');
        $this->createIndex('shop_user_account__locked_at', '{{%shop_user_account}}', 'locked_at');
        $this->createIndex('shop_user_account__currency_user', '{{%shop_user_account}}', ['currency_code', 'user_id'], true);

        $this->execute("ALTER TABLE {{%shop_user_account}} COMMENT = 'Счета покупателей';");

        $this->addForeignKey(
            'shop_user_account_created_by', "{{%shop_user_account}}",
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_user_account_updated_by', "{{%shop_user_account}}",
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_user_account_user_id', "{{%shop_user_account}}",
            'user_id', '{{%cms_user}}', 'id', 'RESTRICT', 'RESTRICT'
        );

        $this->addForeignKey(
            'shop_user_account_currency_code', "{{%shop_user_account}}",
            'currency_code', '{{%money_currency}}', 'code', 'RESTRICT', 'RESTRICT'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey("shop_user_account_updated_by", "{{%shop_user_account}}");
        $this->dropForeignKey("shop_user_account_updated_by", "{{%shop_user_account}}");
        $this->dropForeignKey("shop_user_account_user_id", "{{%shop_user_account}}");
        $this->dropForeignKey("shop_user_account_currency_code", "{{%shop_user_account}}");

        $this->dropTable("{{%shop_user_account}}");
    }
}