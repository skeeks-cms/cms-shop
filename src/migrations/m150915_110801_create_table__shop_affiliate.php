<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 17.09.2015
 */
use yii\db\Schema;
use yii\db\Migration;

class m150915_110801_create_table__shop_affiliate extends Migration
{
    public function safeUp()
    {
        $tableExist = $this->db->getTableSchema("{{%shop_affiliate}}", true);
        if ($tableExist)
        {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable("{{%shop_affiliate}}", [
            'id'                        => $this->primaryKey(),

            'created_by'                => $this->integer(),
            'updated_by'                => $this->integer(),

            'created_at'                => $this->integer(),
            'updated_at'                => $this->integer(),

            'site_code'                 => $this->string(15)->notNull(),
            'user_id'                   => $this->integer()->notNull(),
            'affiliate_id'              => $this->integer(),
            'plan_id'                   => $this->integer()->notNull(),

            'active'                    => $this->string(1)->notNull()->defaultValue("Y"),

            'paid_sum'                  => $this->decimal(18,4)->notNull()->defaultValue(0),
            'approved_sum'              => $this->decimal(18,4)->notNull()->defaultValue(0),
            'pending_sum'               => $this->decimal(18,4)->notNull()->defaultValue(0),

            'items_number'              => $this->integer()->notNull()->defaultValue(0),
            'items_sum'                 => $this->decimal(18,4)->notNull()->defaultValue(0),

            'last_calculate_at'         => $this->integer(),
            'aff_site'                  => $this->string(255),
            'aff_description'           => $this->text(),
            'fix_plan'                  => $this->string(1)->notNull()->defaultValue('N'),


        ], $tableOptions);


        $this->createIndex('updated_by', '{{%shop_affiliate}}', 'updated_by');
        $this->createIndex('created_by', '{{%shop_affiliate}}', 'created_by');
        $this->createIndex('created_at', '{{%shop_affiliate}}', 'created_at');
        $this->createIndex('updated_at', '{{%shop_affiliate}}', 'updated_at');

        $this->createIndex('affiliate_id', '{{%shop_affiliate}}', 'affiliate_id');


        $this->createIndex('site_code', '{{%shop_affiliate}}', 'site_code');
        $this->createIndex('active', '{{%shop_affiliate}}', 'active');
        $this->createIndex('paid_sum', '{{%shop_affiliate}}', 'paid_sum');
        $this->createIndex('approved_sum', '{{%shop_affiliate}}', ['approved_sum']);
        $this->createIndex('items_number', '{{%shop_affiliate}}', ['items_number']);
        $this->createIndex('items_sum', '{{%shop_affiliate}}', ['items_sum']);
        $this->createIndex('last_calculate_at', '{{%shop_affiliate}}', ['last_calculate_at']);
        $this->createIndex('aff_site', '{{%shop_affiliate}}', ['aff_site']);
        $this->createIndex('fix_plan', '{{%shop_affiliate}}', ['fix_plan']);
        $this->createIndex('user_id', '{{%shop_affiliate}}', ['user_id']);
        $this->createIndex('plan_id', '{{%shop_affiliate}}', ['plan_id']);
        $this->createIndex('user_id__site_code', '{{%shop_affiliate}}', ['user_id', 'site_code'], true);

        $this->execute("ALTER TABLE {{%shop_affiliate}} COMMENT = 'Аффилиаты';");

        $this->addForeignKey(
            'shop_affiliate_created_by', "{{%shop_affiliate}}",
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_affiliate_updated_by', "{{%shop_affiliate}}",
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_affiliate__site_code', "{{%shop_affiliate}}",
            'site_code', '{{%cms_site}}', 'code', 'RESTRICT', 'CASCADE'
        );

        $this->addForeignKey(
            'shop_affiliate__user_id', "{{%shop_affiliate}}",
            'user_id', '{{%cms_user}}', 'id', 'RESTRICT', 'RESTRICT'
        );


        $this->addForeignKey(
            'shop_affiliate__plan_id', "{{%shop_affiliate}}",
            'plan_id', '{{%shop_affiliate_plan}}', 'id', 'RESTRICT', 'RESTRICT'
        );

        $this->addForeignKey(
            'shop_affiliate__affiliate_id', "{{%shop_affiliate}}",
            'affiliate_id', '{{%shop_affiliate}}', 'id', 'SET NULL', 'SET NULL'
        );

    }

    public function safeDown()
    {
        $this->dropForeignKey("shop_affiliate_updated_by", "{{%shop_affiliate}}");
        $this->dropForeignKey("shop_affiliate_updated_by", "{{%shop_affiliate}}");
        $this->dropForeignKey("shop_affiliate_site_code", "{{%shop_affiliate}}");
        $this->dropForeignKey("shop_affiliate_user_id", "{{%shop_affiliate}}");
        $this->dropForeignKey("shop_affiliate_plan_id", "{{%shop_affiliate}}");
        $this->dropForeignKey("shop_affiliate__affiliate_id", "{{%shop_affiliate}}");

        $this->dropTable("{{%shop_affiliate}}");
    }
}