<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 17.09.2015
 */
use yii\db\Schema;
use yii\db\Migration;

class m150927_141201_create_table__shop_delivery extends Migration
{
    public function safeUp()
    {
        $tableExist = $this->db->getTableSchema("{{%shop_delivery}}", true);
        if ($tableExist)
        {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable("{{%shop_delivery}}", [
            'id'                        => $this->primaryKey(),

            'created_by'                => $this->integer(),
            'updated_by'                => $this->integer(),

            'created_at'                => $this->integer(),
            'updated_at'                => $this->integer(),

            'name'                      => $this->string(255)->notNull(),

            'site_id'                   => $this->integer()->notNull(),

            'period_from'               => $this->integer(),
            'period_to'                 => $this->integer(),
            'period_type'               => $this->string(1),

            'weight_from'               => $this->integer(),
            'weight_to'                 => $this->integer(),

            'order_price_from'          => $this->decimal(18, 2),
            'order_price_to'            => $this->decimal(18, 2),
            'order_currency_code'       => $this->string(3),
            'active'                    => $this->string(1)->notNull()->defaultValue('Y'),

            'price'                     => $this->decimal(18, 2)->notNull(),
            'currency_code'             => $this->string(3)->notNull(),

            'priority'                  => $this->integer()->notNull()->defaultValue(100),
            'description'               => $this->text(),
            'logo_id'                   => $this->integer(),
            'store'                     => $this->text(),

        ], $tableOptions);


        $this->createIndex('updated_by', '{{%shop_delivery}}', 'updated_by');
        $this->createIndex('created_by', '{{%shop_delivery}}', 'created_by');
        $this->createIndex('created_at', '{{%shop_delivery}}', 'created_at');
        $this->createIndex('updated_at', '{{%shop_delivery}}', 'updated_at');

        $this->createIndex('name', '{{%shop_delivery}}', 'name');

        $this->createIndex('site_id', '{{%shop_delivery}}', 'site_id');
        $this->createIndex('period_from', '{{%shop_delivery}}', 'period_from');
        $this->createIndex('period_to', '{{%shop_delivery}}', 'period_to');
        $this->createIndex('period_type', '{{%shop_delivery}}', 'period_type');

        $this->createIndex('weight_from', '{{%shop_delivery}}', 'weight_from');
        $this->createIndex('weight_to', '{{%shop_delivery}}', 'weight_to');

        $this->createIndex('order_price_from', '{{%shop_delivery}}', 'order_price_from');
        $this->createIndex('order_price_to', '{{%shop_delivery}}', 'order_price_to');
        $this->createIndex('order_currency_code', '{{%shop_delivery}}', 'order_currency_code');
        $this->createIndex('active', '{{%shop_delivery}}', 'active');
        $this->createIndex('price', '{{%shop_delivery}}', 'price');
        $this->createIndex('currency_code', '{{%shop_delivery}}', 'currency_code');
        $this->createIndex('priority', '{{%shop_delivery}}', 'priority');
        $this->createIndex('logo_id', '{{%shop_delivery}}', 'logo_id');

        $this->execute("ALTER TABLE {{%shop_delivery}} COMMENT = 'Службы доставки';");

        $this->addForeignKey(
            'shop_delivery_created_by', "{{%shop_delivery}}",
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_delivery_updated_by', "{{%shop_delivery}}",
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );


        $this->addForeignKey(
            'shop_delivery__logo_id', "{{%shop_delivery}}",
            'logo_id', '{{%cms_storage_file}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_delivery__order_currency_code', "{{%shop_delivery}}",
            'order_currency_code', '{{%money_currency}}', 'code', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_delivery__currency_code', "{{%shop_delivery}}",
            'currency_code', '{{%money_currency}}', 'code', 'RESTRICT', 'RESTRICT'
        );

        $this->addForeignKey(
            'shop_delivery__site_id', "{{%shop_delivery}}",
            'site_id', '{{%cms_site}}', 'id', 'CASCADE', 'CASCADE'
        );

        $this->addForeignKey(
            'shop_order__delivery_id', "{{%shop_order}}",
            'delivery_id', '{{%shop_delivery}}', 'id', 'SET NULL', 'SET NULL'
        );
    }

    public function safeDown()
    {
        $this->dropTable("{{%shop_delivery}}");
    }
}