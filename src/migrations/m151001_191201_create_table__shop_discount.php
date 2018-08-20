<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 17.09.2015
 */

use yii\db\Migration;

class m151001_191201_create_table__shop_discount extends Migration
{
    public function safeUp()
    {
        $tableExist = $this->db->getTableSchema("{{%shop_discount}}", true);
        if ($tableExist) {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable("{{%shop_discount}}", [
            'id' => $this->primaryKey(),

            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),

            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),


            'site_id'       => $this->integer(),
            'active'        => $this->string(1)->notNull()->defaultValue('Y'),
            'active_from'   => $this->integer(),
            'active_to'     => $this->integer(),
            'renewal'       => $this->string(1)->notNull()->defaultValue('N'),
            'name'          => $this->string(255)->notNull(),
            'max_uses'      => $this->integer()->notNull()->defaultValue(0),
            'count_uses'    => $this->integer()->notNull()->defaultValue(0),
            'coupon'        => $this->string(20),
            'max_discount'  => $this->decimal(18, 4),
            'value_type'    => $this->string(1)->notNull()->defaultValue('P'),
            'value'         => $this->decimal(18, 4)->notNull()->defaultValue(0),
            'currency_code' => $this->string(3)->notNull(),
            'min_order_sum' => $this->decimal(18, 4)->notNull()->defaultValue(0),
            'notes'         => $this->string(255),
            'type'          => $this->integer()->notNull()->defaultValue(0),
            'xml_id'        => $this->string(255),
            'count_period'  => $this->string(1)->notNull()->defaultValue("U"),
            'count_size'    => $this->integer()->notNull()->defaultValue(0),
            'count_type'    => $this->string(1)->notNull()->defaultValue("Y"),
            'count_from'    => $this->integer(),
            'count_to'      => $this->integer(),
            'action_size'   => $this->integer()->notNull()->defaultValue(0),
            'action_type'   => $this->string(1)->notNull()->defaultValue('Y'),
            'priority'      => $this->integer()->notNull()->defaultValue(1),
            'last_discount' => $this->string(1)->notNull()->defaultValue('Y'),
            'conditions'    => $this->text(),
            'unpack'        => $this->text(),
            'version'       => $this->integer()->notNull()->defaultValue(1),
        ], $tableOptions);


        $this->createIndex('shop_discount__updated_by', '{{%shop_discount}}', 'updated_by');
        $this->createIndex('shop_discount__created_by', '{{%shop_discount}}', 'created_by');
        $this->createIndex('shop_discount__created_at', '{{%shop_discount}}', 'created_at');
        $this->createIndex('shop_discount__updated_at', '{{%shop_discount}}', 'updated_at');

        $this->createIndex('shop_discount__site_id', '{{%shop_discount}}', 'site_id');
        $this->createIndex('shop_discount__active', '{{%shop_discount}}', 'active');
        $this->createIndex('shop_discount__active_from', '{{%shop_discount}}', 'active_from');
        $this->createIndex('shop_discount__active_to', '{{%shop_discount}}', 'active_to');
        $this->createIndex('shop_discount__renewal', '{{%shop_discount}}', 'renewal');
        $this->createIndex('shop_discount__name', '{{%shop_discount}}', 'name');
        $this->createIndex('shop_discount__max_uses', '{{%shop_discount}}', 'max_uses');
        $this->createIndex('shop_discount__count_uses', '{{%shop_discount}}', 'count_uses');
        $this->createIndex('shop_discount__coupon', '{{%shop_discount}}', 'coupon');
        $this->createIndex('shop_discount__priority', '{{%shop_discount}}', 'priority');
        $this->createIndex('shop_discount__max_discount', '{{%shop_discount}}', 'max_discount');
        $this->createIndex('shop_discount__value_type', '{{%shop_discount}}', 'value_type');
        $this->createIndex('shop_discount__value', '{{%shop_discount}}', 'value');
        $this->createIndex('shop_discount__currency_code', '{{%shop_discount}}', 'currency_code');
        $this->createIndex('shop_discount__min_order_sum', '{{%shop_discount}}', 'min_order_sum');
        $this->createIndex('shop_discount__type', '{{%shop_discount}}', 'type');
        $this->createIndex('shop_discount__count_period', '{{%shop_discount}}', 'count_period');
        $this->createIndex('shop_discount__count_size', '{{%shop_discount}}', 'count_size');
        $this->createIndex('shop_discount__count_type', '{{%shop_discount}}', 'count_type');
        $this->createIndex('shop_discount__count_from', '{{%shop_discount}}', 'count_from');
        $this->createIndex('shop_discount__count_to', '{{%shop_discount}}', 'count_to');
        $this->createIndex('shop_discount__action_size', '{{%shop_discount}}', 'action_size');
        $this->createIndex('shop_discount__action_type', '{{%shop_discount}}', 'action_type');
        $this->createIndex('shop_discount__last_discount', '{{%shop_discount}}', 'last_discount');
        $this->createIndex('shop_discount__version', '{{%shop_discount}}', 'version');

        $this->addForeignKey(
            'shop_discount_created_by', "{{%shop_discount}}",
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_discount_updated_by', "{{%shop_discount}}",
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_discount__currency_code', "{{%shop_discount}}",
            'currency_code', '{{%money_currency}}', 'code', 'RESTRICT', 'RESTRICT'
        );

        $this->addForeignKey(
            'shop_discount__site_id', "{{%shop_discount}}",
            'site_id', '{{%cms_site}}', 'id', 'SET NULL', 'SET NULL'
        );
    }

    public function safeDown()
    {
        $this->dropTable("{{%shop_discount}}");
    }
}