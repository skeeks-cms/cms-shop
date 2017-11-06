<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 17.09.2015
 */
use yii\db\Schema;
use yii\db\Migration;

class m150926_131201_create_table__shop_basket extends Migration
{
    public function safeUp()
    {
        $tableExist = $this->db->getTableSchema("{{%shop_basket}}", true);
        if ($tableExist)
        {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable("{{%shop_basket}}", [
            'id'                        => $this->primaryKey(),

            'created_by'                => $this->integer(),
            'updated_by'                => $this->integer(),

            'created_at'                => $this->integer(),
            'updated_at'                => $this->integer(),

            'fuser_id'                  => $this->integer(),
            'order_id'                  => $this->integer(),
            'product_id'                => $this->integer(),
            'product_price_id'          => $this->integer(),

            'price'                     => $this->decimal(18,2)->notNull(),
            'currency_code'             => $this->string(3)->notNull(),

            'weight'                    => $this->decimal(18,2),
            'quantity'                  => $this->decimal(18,2)->notNull()->defaultValue(0),

            'site_id'                   => $this->integer(),

            'delay'                     => $this->string(1)->notNull()->defaultValue('N'),
            'name'                      => $this->string(255)->notNull(),

            'can_buy'                   => $this->string(1)->notNull()->defaultValue('Y'),
            'callback_func'             => $this->string(255),
            'notes'                     => $this->string(255),
            'order_callback_func'       => $this->string(255),
            'detail_page_url'           => $this->string(255),

            'discount_price'            => $this->decimal(18,2)->notNull()->defaultValue(0),
            'cancel_callback_func'      => $this->string(255),
            'pay_callback_func'         => $this->string(255),

            'catalog_xml_id'            => $this->string(100),
            'product_xml_id'            => $this->string(100),

            'discount_name'             => $this->string(255),
            'discount_value'            => $this->string(32),
            'discount_coupon'           => $this->string(32),

            'vat_rate'                  => $this->decimal(18,2)->notNull()->defaultValue(0),
            'subscribe'                 => $this->string(1)->notNull()->defaultValue('N'),
            'barcode_multi'             => $this->string(1)->notNull()->defaultValue('N'),
            'reserved'                  => $this->string(1)->notNull()->defaultValue('N'),
            'reserve_quantity'          => $this->double(),

            'deducted'                  => $this->string(1)->notNull()->defaultValue('N'),
            'custom_price'              => $this->string(1)->notNull()->defaultValue('N'),
            'dimensions'                => $this->string(255),
            'type'                      => $this->integer(),
            'set_parent_id'             => $this->integer(),

            'measure_name'              => $this->string(50),
            'measure_code'              => $this->integer(),

            'recommendation'            => $this->string(255),

        ], $tableOptions);


        $this->createIndex('updated_by', '{{%shop_basket}}', 'updated_by');
        $this->createIndex('created_by', '{{%shop_basket}}', 'created_by');
        $this->createIndex('created_at', '{{%shop_basket}}', 'created_at');
        $this->createIndex('updated_at', '{{%shop_basket}}', 'updated_at');


        $this->createIndex('site_id', '{{%shop_basket}}', ['site_id']);
        $this->createIndex('fuser_id', '{{%shop_basket}}', ['fuser_id']);
        $this->createIndex('order_id', '{{%shop_basket}}', ['order_id']);
        $this->createIndex('product_id', '{{%shop_basket}}', ['product_id']);
        $this->createIndex('product_price_id', '{{%shop_basket}}', ['product_price_id']);
        $this->createIndex('currency_code', '{{%shop_basket}}', ['currency_code']);

        $this->createIndex('price', '{{%shop_basket}}', ['price']);
        $this->createIndex('delay', '{{%shop_basket}}', ['delay']);
        $this->createIndex('name', '{{%shop_basket}}', ['name']);
        $this->createIndex('measure_name', '{{%shop_basket}}', ['measure_name']);
        $this->createIndex('set_parent_id', '{{%shop_basket}}', ['set_parent_id']);
        $this->createIndex('measure_code', '{{%shop_basket}}', ['measure_code']);

        $this->createIndex('catalog_product_xml_id', '{{%shop_basket}}', ['catalog_xml_id', 'product_xml_id']);

        $this->execute("ALTER TABLE {{%shop_basket}} COMMENT = 'Позиции в корзине';");

        $this->addForeignKey(
            'shop_basket_created_by', "{{%shop_basket}}",
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_basket_updated_by', "{{%shop_basket}}",
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_basket__site_id', "{{%shop_basket}}",
            'site_id', '{{%cms_site}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_basket__fuser_id', "{{%shop_basket}}",
            'fuser_id', '{{%shop_fuser}}', 'id', 'CASCADE', 'CASCADE'
        );

        $this->addForeignKey(
            'shop_basket__order_id', "{{%shop_basket}}",
            'order_id', '{{%shop_order}}', 'id', 'CASCADE', 'CASCADE'
        );

        $this->addForeignKey(
            'shop_basket__product_id', "{{%shop_basket}}",
            'product_id', '{{%shop_product}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_basket__product_price_id', "{{%shop_basket}}",
            'product_price_id', '{{%shop_product_price}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_basket__currency_code', "{{%shop_basket}}",
            'currency_code', '{{%money_currency}}', 'code', 'RESTRICT', 'RESTRICT'
        );

    }

    public function safeDown()
    {
        $this->dropTable("{{%shop_basket}}");
    }
}