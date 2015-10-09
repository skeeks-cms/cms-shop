<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 17.09.2015
 */
use yii\db\Schema;
use yii\db\Migration;

class m151001_191201_create_table__shop_discount extends Migration
{
    public function safeUp()
    {
        $tableExist = $this->db->getTableSchema("{{%shop_discount}}", true);
        if ($tableExist)
        {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable("{{%shop_discount}}", [
            'id'                        => $this->primaryKey(),

            'created_by'                => $this->integer(),
            'updated_by'                => $this->integer(),

            'created_at'                => $this->integer(),
            'updated_at'                => $this->integer(),


            'site_id'                   => $this->integer(),
            'active'                    => $this->string(1)->notNull()->defaultValue('Y'),
            'active_from'               => $this->integer(),
            'active_to'                 => $this->integer(),
            'renewal'                   => $this->string(1)->notNull()->defaultValue('N'),
            'name'                      => $this->string(255),
            'max_uses'                  => $this->integer()->notNull()->defaultValue(0),
            'count_uses'                => $this->integer()->notNull()->defaultValue(0),
            'coupon'                    => $this->string(20),
            'priority'                  => $this->integer()->notNull()->defaultValue(100),
            'max_discount'              => $this->decimal(18,4),
            'value_type'                => $this->string(1)->notNull()->defaultValue('P'),
            'value'                     => $this->decimal(18,4)->notNull()->defaultValue(0),
            'currency_code'             => $this->string(3)->notNull(),


        ], $tableOptions);


        $this->createIndex('updated_by', '{{%shop_discount}}', 'updated_by');
        $this->createIndex('created_by', '{{%shop_discount}}', 'created_by');
        $this->createIndex('created_at', '{{%shop_discount}}', 'created_at');
        $this->createIndex('updated_at', '{{%shop_discount}}', 'updated_at');

        $this->createIndex('shop_order_id', '{{%shop_discount}}', 'shop_order_id');
        $this->createIndex('cms_user_id', '{{%shop_discount}}', 'cms_user_id');
        $this->createIndex('amount', '{{%shop_discount}}', 'amount');
        $this->createIndex('currency_code', '{{%shop_discount}}', 'currency_code');
        $this->createIndex('debit', '{{%shop_discount}}', 'debit');
        $this->createIndex('description', '{{%shop_discount}}', 'description');


        $this->execute("ALTER TABLE {{%shop_discount}} COMMENT = 'Транзакции пользователя';");

        $this->addForeignKey(
            'shop_discount_created_by', "{{%shop_discount}}",
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_discount_updated_by', "{{%shop_discount}}",
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );


        $this->addForeignKey(
            'shop_discount__shop_order_id', "{{%shop_discount}}",
            'shop_order_id', '{{%shop_order}}', 'id', 'SET NULL', 'SET NULL'
        );


        $this->addForeignKey(
            'shop_discount__cms_user_id', "{{%shop_discount}}",
            'cms_user_id', '{{%cms_user}}', 'id', 'CASCADE', 'CASCADE'
        );

        $this->addForeignKey(
            'shop_discount__currency_code', "{{%shop_discount}}",
            'currency_code', '{{%money_currency}}', 'code', 'RESTRICT', 'RESTRICT'
        );
    }

    public function safeDown()
    {
        $this->dropTable("{{%shop_discount}}");
    }
}