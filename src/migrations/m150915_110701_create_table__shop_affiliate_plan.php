<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 17.09.2015
 */

use yii\db\Schema;
use yii\db\Migration;

class m150915_110701_create_table__shop_affiliate_plan extends Migration
{
    public function safeUp()
    {
        $tableExist = $this->db->getTableSchema("{{%shop_affiliate_plan}}", true);
        if ($tableExist) {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable("{{%shop_affiliate_plan}}", [
            'id' => $this->primaryKey(),

            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),

            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),

            'site_code' => $this->string(15)->notNull(),
            'name' => $this->string(255)->notNull(),
            'description' => $this->text(),
            'active' => $this->string(1)->notNull()->defaultValue("Y"),

            'base_rate' => $this->decimal(18, 4)->notNull()->defaultValue(0),
            'base_rate_type' => $this->string(1)->notNull()->defaultValue("P"),
            'base_rate_currency_code' => $this->string(3),

            'min_pay' => $this->decimal(18, 4)->notNull()->defaultValue(0),
            'min_plan_value' => $this->decimal(18, 4),
            'value_currency_code' => $this->string(3),


        ], $tableOptions);


        $this->createIndex('updated_by', '{{%shop_affiliate_plan}}', 'updated_by');
        $this->createIndex('created_by', '{{%shop_affiliate_plan}}', 'created_by');
        $this->createIndex('created_at', '{{%shop_affiliate_plan}}', 'created_at');
        $this->createIndex('updated_at', '{{%shop_affiliate_plan}}', 'updated_at');


        $this->createIndex('name', '{{%shop_affiliate_plan}}', 'name');
        $this->createIndex('active', '{{%shop_affiliate_plan}}', 'active');
        $this->createIndex('base_rate', '{{%shop_affiliate_plan}}', 'base_rate');
        $this->createIndex('base_rate_type', '{{%shop_affiliate_plan}}', ['base_rate_type']);
        $this->createIndex('min_pay', '{{%shop_affiliate_plan}}', ['min_pay']);
        $this->createIndex('min_plan_value', '{{%shop_affiliate_plan}}', ['min_plan_value']);
        $this->createIndex('site_code', '{{%shop_affiliate_plan}}', ['site_code']);
        $this->createIndex('base_rate_currency_code', '{{%shop_affiliate_plan}}', ['base_rate_currency_code']);
        $this->createIndex('value_currency_code', '{{%shop_affiliate_plan}}', ['value_currency_code']);

        $this->execute("ALTER TABLE {{%shop_affiliate_plan}} COMMENT = 'Планы для аффилиатов';");

        $this->addForeignKey(
            'shop_affiliate_plan_created_by', "{{%shop_affiliate_plan}}",
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_affiliate_plan_updated_by', "{{%shop_affiliate_plan}}",
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );


        $this->addForeignKey(
            'shop_affiliate_plan_base_rate_currency_code', "{{%shop_affiliate_plan}}",
            'base_rate_currency_code', '{{%money_currency}}', 'code', 'RESTRICT', 'RESTRICT'
        );

        $this->addForeignKey(
            'shop_affiliate_plan_value_currency_code', "{{%shop_affiliate_plan}}",
            'value_currency_code', '{{%money_currency}}', 'code', 'RESTRICT', 'RESTRICT'
        );

        $this->addForeignKey(
            'shop_affiliate_plan__site_code', "{{%shop_affiliate_plan}}",
            'site_code', '{{%cms_site}}', 'code', 'RESTRICT', 'CASCADE'
        );

    }

    public function safeDown()
    {
        $this->dropForeignKey("shop_affiliate_plan_updated_by", "{{%shop_affiliate_plan}}");
        $this->dropForeignKey("shop_affiliate_plan_updated_by", "{{%shop_affiliate_plan}}");
        $this->dropForeignKey("shop_affiliate_plan_site_code", "{{%shop_affiliate_plan}}");
        $this->dropForeignKey("shop_affiliate_plan_base_rate_currency_code", "{{%shop_affiliate_plan}}");
        $this->dropForeignKey("shop_affiliate_plan_value_currency_code", "{{%shop_affiliate_plan}}");

        $this->dropTable("{{%shop_affiliate_plan}}");
    }
}