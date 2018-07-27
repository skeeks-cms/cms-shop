<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m150914_190601_create_table__shop_product_price extends Migration
{
    public function safeUp()
    {
        $tableExist = $this->db->getTableSchema("{{%shop_product_price}}", true);
        if ($tableExist) {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable("{{%shop_product_price}}", [
            'id' => $this->primaryKey(),

            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),

            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),

            'product_id'    => $this->integer()->notNull(),
            'type_price_id' => $this->integer()->notNull(),

            'price'         => $this->decimal(18, 2)->notNull(),
            'currency_code' => $this->string(3)->notNull(),

            'quantity_from' => $this->integer(),
            'quantity_to'   => $this->integer(),

            'tmp_id' => $this->string(40),
        ], $tableOptions);


        $this->createIndex('shop_product_price__updated_by', '{{%shop_product_price}}', 'updated_by');
        $this->createIndex('shop_product_price__created_by', '{{%shop_product_price}}', 'created_by');
        $this->createIndex('shop_product_price__created_at', '{{%shop_product_price}}', 'created_at');
        $this->createIndex('shop_product_price__updated_at', '{{%shop_product_price}}', 'updated_at');


        $this->createIndex('shop_product_price__price', '{{%shop_product_price}}', 'price');
        $this->createIndex('shop_product_price__currency_code', '{{%shop_product_price}}', 'currency_code');
        $this->createIndex('shop_product_price__quantity_from', '{{%shop_product_price}}', 'quantity_from');
        $this->createIndex('shop_product_price__quantity_to', '{{%shop_product_price}}', 'quantity_to');
        $this->createIndex('shop_product_price__tmp_id', '{{%shop_product_price}}', 'tmp_id');

        $this->addForeignKey(
            'shop_product_price_created_by', "{{%shop_product_price}}",
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_product_price_updated_by', "{{%shop_product_price}}",
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_product_price_product_id', "{{%shop_product_price}}",
            'product_id', '{{%shop_product}}', 'id', 'CASCADE', 'CASCADE'
        );

        $this->addForeignKey(
            'shop_product_price_shop_type_price', "{{%shop_product_price}}",
            'type_price_id', '{{%shop_type_price}}', 'id', 'CASCADE', 'CASCADE'
        );

        $this->addForeignKey(
            'shop_product_currency_code', "{{%shop_product_price}}",
            'currency_code', '{{%money_currency}}', 'code', 'RESTRICT', 'RESTRICT'
        );

    }

    public function safeDown()
    {
        $this->dropForeignKey("shop_product_price_updated_by", "{{%shop_product_price}}");
        $this->dropForeignKey("shop_product_price_updated_by", "{{%shop_product_price}}");
        $this->dropForeignKey("shop_product_price_product_id", "{{%shop_product_price}}");
        $this->dropForeignKey("shop_product_price_shop_type_price", "{{%shop_product_price}}");
        $this->dropForeignKey("shop_product_currency_code", "{{%shop_product_price}}");

        $this->dropTable("{{%shop_product_price}}");
    }
}