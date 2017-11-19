<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Schema;
use yii\db\Migration;

class m150914_180601_create_table__shop_product extends Migration
{
    public function safeUp()
    {
        $tableExist = $this->db->getTableSchema("{{%shop_product}}", true);
        if ($tableExist) {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable("{{%shop_product}}", [
            'id' => $this->primaryKey(),

            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),

            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),

            'quantity' => $this->double(),
            'quantity_trace' => $this->string(1)->defaultValue("N")->notNull(),
            'weight' => $this->double()->defaultValue(0)->notNull(),

            'price_type' => $this->string(1)->defaultValue("S")->notNull(),

            'recur_scheme_length' => $this->integer(),
            'recur_scheme_type' => $this->string(1)->defaultValue("D")->notNull(),

            'trial_price_id' => $this->integer(),
            'without_order' => $this->string(1)->defaultValue("N")->notNull(),

            'select_best_price' => $this->string(1)->defaultValue("Y")->notNull(),

            'vat_id' => $this->integer(),
            'vat_included' => $this->string(1)->defaultValue("Y")->notNull(),

            'tmp_id' => $this->string(40),

            'can_buy_zero' => $this->string(1)->defaultValue("Y")->notNull(),
            'negative_amount_trace' => $this->string(1)->defaultValue("D")->notNull(),

            'barcode_multi' => $this->string(1)->defaultValue("N")->notNull(),

            'purchasing_price' => $this->decimal(18, 2),
            'purchasing_currency' => $this->string(3),

            'quantity_reserved' => $this->double()->defaultValue(0),

            'measure_id' => $this->integer(),
            'measure_ratio' => $this->double()->notNull()->defaultValue(1),

            'width' => $this->double(),
            'length' => $this->double(),
            'height' => $this->double(),

            'subscribe' => $this->string(1)->defaultValue("D")->notNull(),

        ], $tableOptions);


        $this->createIndex('shop_product__updated_by', '{{%shop_product}}', 'updated_by');
        $this->createIndex('shop_product__created_by', '{{%shop_product}}', 'created_by');
        $this->createIndex('shop_product__created_at', '{{%shop_product}}', 'created_at');
        $this->createIndex('shop_product__updated_at', '{{%shop_product}}', 'updated_at');


        $this->createIndex('shop_product__quantity', '{{%shop_product}}', 'quantity');
        $this->createIndex('shop_product__quantity_trace', '{{%shop_product}}', 'quantity_trace');
        $this->createIndex('shop_product__weight', '{{%shop_product}}', 'weight');
        $this->createIndex('shop_product__price_type', '{{%shop_product}}', 'price_type');
        $this->createIndex('shop_product__recur_scheme_length', '{{%shop_product}}', 'recur_scheme_length');
        $this->createIndex('shop_product__recur_scheme_type', '{{%shop_product}}', 'recur_scheme_type');
        $this->createIndex('shop_product__select_best_price', '{{%shop_product}}', 'select_best_price');
        $this->createIndex('shop_product__vat_included', '{{%shop_product}}', 'vat_included');
        $this->createIndex('shop_product__tmp_id', '{{%shop_product}}', 'tmp_id');
        $this->createIndex('shop_product__can_buy_zero', '{{%shop_product}}', 'can_buy_zero');
        $this->createIndex('shop_product__negative_amount_trace', '{{%shop_product}}', 'negative_amount_trace');
        $this->createIndex('shop_product__barcode_multi', '{{%shop_product}}', 'barcode_multi');
        $this->createIndex('shop_product__purchasing_price', '{{%shop_product}}', 'purchasing_price');
        $this->createIndex('shop_product__purchasing_currency', '{{%shop_product}}', 'purchasing_currency');
        $this->createIndex('shop_product__quantity_reserved', '{{%shop_product}}', 'quantity_reserved');
        $this->createIndex('shop_product__measure_id', '{{%shop_product}}', 'measure_id');
        $this->createIndex('shop_product__width', '{{%shop_product}}', 'width');
        $this->createIndex('shop_product__length', '{{%shop_product}}', 'length');
        $this->createIndex('shop_product__height', '{{%shop_product}}', 'height');
        $this->createIndex('shop_product__subscribe', '{{%shop_product}}', 'subscribe');
        $this->createIndex('shop_product__measure_ratio', '{{%shop_product}}', 'measure_ratio');


        $this->addForeignKey(
            'shop_product_created_by', "{{%shop_product}}",
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_product_updated_by', "{{%shop_product}}",
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_product_cms_content_element', "{{%shop_product}}",
            'id', '{{%cms_content_element}}', 'id', 'CASCADE', 'CASCADE'
        );

        $this->addForeignKey(
            'shop_product_shop_type_price', "{{%shop_product}}",
            'trial_price_id', '{{%shop_type_price}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_product_shop_vat', "{{%shop_product}}",
            'vat_id', '{{%shop_vat}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_product_measure', "{{%shop_product}}",
            'measure_id', '{{%measure}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_product_money_currency', "{{%shop_product}}",
            'purchasing_currency', '{{%money_currency}}', 'code', 'SET NULL', 'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey("shop_product_updated_by", "{{%shop_product}}");
        $this->dropForeignKey("shop_product_updated_by", "{{%shop_product}}");
        $this->dropForeignKey("shop_product_cms_content_element", "{{%shop_product}}");
        $this->dropForeignKey("shop_product_shop_type_price", "{{%shop_product}}");
        $this->dropForeignKey("shop_product_shop_vat", "{{%shop_product}}");
        $this->dropForeignKey("shop_product_measure", "{{%shop_product}}");
        $this->dropForeignKey("shop_product_money_currency", "{{%shop_product}}");

        $this->dropTable("{{%shop_product}}");
    }
}