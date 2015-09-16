<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 17.09.2015
 */
use yii\db\Schema;
use yii\db\Migration;

class m150915_120601_create_table__shop_order extends Migration
{
    public function safeUp()
    {
        $tableExist = $this->db->getTableSchema("{{%shop_order}}", true);
        if ($tableExist)
        {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable("{{%shop_order}}", [
            'id'                    => $this->primaryKey(),

            'created_by'            => $this->integer(),
            'updated_by'            => $this->integer(),

            'created_at'            => $this->integer(),
            'updated_at'            => $this->integer(),

            'site_code'             => $this->string(15)->notNull(),

            'person_type_id'        => $this->integer()->notNull(),

            'payed'                 => $this->string(1)->notNull()->defaultValue('N'),
            'payed_at'              => $this->integer(),
            'emp_payed_id'          => $this->integer(),

            'canceled'              => $this->string(1)->notNull()->defaultValue('N'),
            'canceled_at'           => $this->integer(),
            'emp_canceled_id'       => $this->integer(),
            'reason_canceled'       => $this->string(255),

            'status_code'           => $this->string(1)->notNull()->defaultValue('N'),
            'status_at'             => $this->integer()->notNull(),
            'emp_status_id'          => $this->integer(),

        ], $tableOptions);


        $this->createIndex('updated_by', '{{%shop_order}}', 'updated_by');
        $this->createIndex('created_by', '{{%shop_order}}', 'created_by');
        $this->createIndex('created_at', '{{%shop_order}}', 'created_at');
        $this->createIndex('updated_at', '{{%shop_order}}', 'updated_at');


        $this->createIndex('current_budget', '{{%shop_order}}', 'current_budget');
        $this->createIndex('locked', '{{%shop_order}}', 'locked');
        $this->createIndex('locked_at', '{{%shop_order}}', 'locked_at');
        $this->createIndex('currency_user', '{{%shop_order}}', ['currency_code', 'user_id'], true);

        $this->execute("ALTER TABLE {{%shop_order}} COMMENT = 'Счета покупателей';");

        $this->addForeignKey(
            'shop_order_created_by', "{{%shop_order}}",
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_order_updated_by', "{{%shop_order}}",
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_order_user_id', "{{%shop_order}}",
            'user_id', '{{%cms_user}}', 'id', 'RESTRICT', 'RESTRICT'
        );

        $this->addForeignKey(
            'shop_order_currency_code', "{{%shop_order}}",
            'currency_code', '{{%money_currency}}', 'code', 'RESTRICT', 'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey("shop_order_updated_by", "{{%shop_order}}");
        $this->dropForeignKey("shop_order_updated_by", "{{%shop_order}}");
        $this->dropForeignKey("shop_order_user_id", "{{%shop_order}}");
        $this->dropForeignKey("shop_order_currency_code", "{{%shop_order}}");

        $this->dropTable("{{%shop_order}}");
    }
}