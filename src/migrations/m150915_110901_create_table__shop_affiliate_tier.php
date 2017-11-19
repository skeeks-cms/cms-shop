<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 17.09.2015
 */

use yii\db\Schema;
use yii\db\Migration;

class m150915_110901_create_table__shop_affiliate_tier extends Migration
{
    public function safeUp()
    {
        $tableExist = $this->db->getTableSchema("{{%shop_affiliate_tier}}", true);
        if ($tableExist) {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable("{{%shop_affiliate_tier}}", [
            'id' => $this->primaryKey(),

            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),

            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),

            'site_code' => $this->string(15)->notNull()->unique(),
            'rate1' => $this->decimal(18, 4)->notNull()->defaultValue(0),
            'rate2' => $this->decimal(18, 4)->notNull()->defaultValue(0),
            'rate3' => $this->decimal(18, 4)->notNull()->defaultValue(0),
            'rate4' => $this->decimal(18, 4)->notNull()->defaultValue(0),
            'rate5' => $this->decimal(18, 4)->notNull()->defaultValue(0),


        ], $tableOptions);


        $this->createIndex('shop_affiliate_tier__updated_by', '{{%shop_affiliate_tier}}', 'updated_by');
        $this->createIndex('shop_affiliate_tier__created_by', '{{%shop_affiliate_tier}}', 'created_by');
        $this->createIndex('shop_affiliate_tier__created_at', '{{%shop_affiliate_tier}}', 'created_at');
        $this->createIndex('shop_affiliate_tier__updated_at', '{{%shop_affiliate_tier}}', 'updated_at');


        $this->createIndex('shop_affiliate_tier__rate1', '{{%shop_affiliate_tier}}', 'rate1');
        $this->createIndex('shop_affiliate_tier__rate2', '{{%shop_affiliate_tier}}', 'rate2');
        $this->createIndex('shop_affiliate_tier__rate3', '{{%shop_affiliate_tier}}', ['rate3']);
        $this->createIndex('shop_affiliate_tier__rate4', '{{%shop_affiliate_tier}}', ['rate4']);
        $this->createIndex('shop_affiliate_tier__rate5', '{{%shop_affiliate_tier}}', ['rate5']);

        $this->addForeignKey(
            'shop_affiliate_tier_created_by', "{{%shop_affiliate_tier}}",
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_affiliate_tier_updated_by', "{{%shop_affiliate_tier}}",
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_affiliate_tier__site_code', "{{%shop_affiliate_tier}}",
            'site_code', '{{%cms_site}}', 'code', 'RESTRICT', 'CASCADE'
        );


    }

    public function safeDown()
    {
        $this->dropForeignKey("shop_affiliate_tier_updated_by", "{{%shop_affiliate_tier}}");
        $this->dropForeignKey("shop_affiliate_tier_updated_by", "{{%shop_affiliate_tier}}");
        $this->dropForeignKey("shop_affiliate_tier_site_code", "{{%shop_affiliate_tier}}");

        $this->dropTable("{{%shop_affiliate_tier}}");
    }
}