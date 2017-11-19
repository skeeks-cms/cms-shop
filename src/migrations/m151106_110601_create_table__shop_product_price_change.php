<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Schema;
use yii\db\Migration;

class m151106_110601_create_table__shop_product_price_change extends Migration
{
    public function safeUp()
    {
        $tableExist = $this->db->getTableSchema("{{%shop_product_price_change}}", true);
        if ($tableExist) {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable("{{%shop_product_price_change}}", [
            'id' => $this->primaryKey(),

            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),

            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),

            'shop_product_price_id' => $this->integer(),

            'price' => $this->decimal(18, 2)->notNull(),
            'currency_code' => $this->string(3)->notNull(),

            'quantity_from' => $this->integer(),
            'quantity_to' => $this->integer(),

        ], $tableOptions);


        $this->createIndex('shop_product_price_change__updated_by', '{{%shop_product_price_change}}', 'updated_by');
        $this->createIndex('shop_product_price_change__created_by', '{{%shop_product_price_change}}', 'created_by');
        $this->createIndex('shop_product_price_change__created_at', '{{%shop_product_price_change}}', 'created_at');
        $this->createIndex('shop_product_price_change__updated_at', '{{%shop_product_price_change}}', 'updated_at');


        $this->createIndex('shop_product_price_change__price', '{{%shop_product_price_change}}', 'price');
        $this->createIndex('shop_product_price_change__currency_code', '{{%shop_product_price_change}}', 'currency_code');
        $this->createIndex('shop_product_price_change__quantity_from', '{{%shop_product_price_change}}', 'quantity_from');
        $this->createIndex('shop_product_price_change__quantity_to', '{{%shop_product_price_change}}', 'quantity_to');
        $this->createIndex('shop_product_price_change__shop_product_price_id', '{{%shop_product_price_change}}', 'shop_product_price_id');


        $this->addForeignKey(
            'shop_product_price_change_created_by', "{{%shop_product_price_change}}",
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_product_price_change_updated_by', "{{%shop_product_price_change}}",
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_product__currency_code', "{{%shop_product_price_change}}",
            'currency_code', '{{%money_currency}}', 'code', 'RESTRICT', 'RESTRICT'
        );

        $this->addForeignKey(
            'shop_product_price_change__shop_product_price_id', "{{%shop_product_price_change}}",
            'shop_product_price_id', '{{%shop_product_price}}', 'id', 'CASCADE', 'CASCADE'
        );

    }

    public function safeDown()
    {
        $this->dropForeignKey("shop_product_price_change_updated_by", "{{%shop_product_price_change}}");
        $this->dropForeignKey("shop_product_price_change_updated_by", "{{%shop_product_price_change}}");
        $this->dropForeignKey("shop_product_price_change__shop_product_price_id", "{{%shop_product_price_change}}");
        $this->dropForeignKey("shop_product__currency_code", "{{%shop_product_price_change}}");

        $this->dropTable("{{%shop_product_price_change}}");
    }
}