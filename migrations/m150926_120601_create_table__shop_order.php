<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 17.09.2015
 */
use yii\db\Schema;
use yii\db\Migration;

class m150926_120601_create_table__shop_order extends Migration
{
    public function safeUp()
    {
        $tableExist = $this->db->getTableSchema("{{%shop_order}}", true);
        if ($tableExist)
        {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable("{{%shop_order}}", [
            'id'                    => $this->primaryKey(),

            'created_by'            => $this->integer(),
            'updated_by'            => $this->integer(),

            'created_at'            => $this->integer(),
            'updated_at'            => $this->integer(),

            'site_code'             => $this->string(15)->notNull(),

            'person_type_id'        => $this->integer()->notNull(),
            'buyer_id'              => $this->integer()->notNull(),

            'payed'                 => $this->string(1)->notNull()->defaultValue('N'),
            'payed_at'              => $this->integer(),
            'emp_payed_id'          => $this->integer(),

            'canceled'              => $this->string(1)->notNull()->defaultValue('N'),
            'canceled_at'           => $this->integer(),
            'emp_canceled_id'       => $this->integer(),
            'reason_canceled'       => $this->string(255),

            'status_code'           => $this->string(1)->notNull()->defaultValue('N'),
            'status_at'             => $this->integer()->notNull(),
            'emp_status_id'         => $this->integer(),

            'price_delivery'        => $this->decimal(18,2)->notNull()->defaultValue(0),
            'allow_delivery'        => $this->string(1)->notNull()->defaultValue('N'),
            'allow_delivery_at'     => $this->integer(),
            'emp_allow_delivery_id' => $this->integer(),

            'price'                 => $this->decimal(18,2)->notNull()->defaultValue(0),
            'currency_code'         => $this->string(3)->notNull(),

            'discount_value'        => $this->decimal(18,2)->notNull()->defaultValue(0),

            'user_id'               => $this->integer()->notNull(),

            'pay_system_id'         => $this->integer(),

            'delivery_code'         => $this->string(50),
            'user_description'      => $this->string(255),
            'additional_info'       => $this->string(255),

            'ps_status'             => $this->string(1),
            'ps_status_code'        => $this->string(5),
            'ps_status_description' => $this->string(255),
            'ps_status_message'     => $this->string(255),
            'ps_sum'                => $this->decimal(18,2),
            'ps_currency_code'      => $this->string(3),
            'ps_response_at'        => $this->integer(),

            'comments'              => $this->text(),

            'tax_value'             => $this->decimal(18,2)->notNull()->defaultValue(0),
            'stat_gid'              => $this->string(255),
            'sum_paid'              => $this->decimal(18,2)->notNull()->defaultValue(0),

            'recuring_id'           => $this->integer(),

            'pay_voucher_num'       => $this->string(20),
            'pay_voucher_at'        => $this->integer(),

            'locked_by'             => $this->integer(),
            'locked_at'             => $this->integer(),

            'recount_flag'          => $this->string(1)->notNull()->defaultValue("Y"),
            'affiliate_id'          => $this->integer(),

            'delivery_doc_num'      => $this->string(20),
            'delivery_doc_at'       => $this->integer(),

            'update_1c'             => $this->string(1)->notNull()->defaultValue("N"),

            'deducted'              => $this->string(1)->notNull()->defaultValue("N"),
            'deducted_at'           => $this->integer(),
            'emp_deducted_id'       => $this->integer(),
            'reason_undo_deducted'  => $this->string(255),

            'marked'                => $this->string(1)->notNull()->defaultValue("N"),
            'marked_at'             => $this->integer(),
            'emp_marked_id'         => $this->integer(),
            'reason_marked'         => $this->string(255),

            'reserved'              => $this->string(1)->notNull()->defaultValue("N"),
            'store_id'              => $this->integer(),

            'order_topic'           => $this->string(255),
            'responsible_id'        => $this->integer(),

            'pay_before_at'         => $this->integer(),
            'account_id'            => $this->integer(),

            'bill_at'               => $this->integer(),
            'tracking_number'       => $this->string(100),
            'xml_id'                => $this->string(255),
            'id_1c'                 => $this->string(15),
            'version_1c'            => $this->string(15),
            'version'               => $this->integer(),

            'external_order'        => $this->string(1)->notNull()->defaultValue('N'),

        ], $tableOptions);


        $this->createIndex('updated_by', '{{%shop_order}}', 'updated_by');
        $this->createIndex('created_by', '{{%shop_order}}', 'created_by');
        $this->createIndex('created_at', '{{%shop_order}}', 'created_at');
        $this->createIndex('updated_at', '{{%shop_order}}', 'updated_at');

        $this->createIndex('site_code', '{{%shop_order}}', 'site_code');
        $this->createIndex('person_type_id', '{{%shop_order}}', 'person_type_id');
        $this->createIndex('status_code', '{{%shop_order}}', 'status_code');
        $this->createIndex('currency_code', '{{%shop_order}}', 'currency_code');
        $this->createIndex('user_id', '{{%shop_order}}', 'user_id');
        $this->createIndex('pay_system_id', '{{%shop_order}}', 'pay_system_id');
        $this->createIndex('locked_by', '{{%shop_order}}', 'locked_by');
        $this->createIndex('affiliate_id', '{{%shop_order}}', 'affiliate_id');
        $this->createIndex('store_id', '{{%shop_order}}', 'store_id');


        $this->createIndex('payed', '{{%shop_order}}', 'payed');
        $this->createIndex('payed_at', '{{%shop_order}}', 'payed_at');


        $this->execute("ALTER TABLE {{%shop_order}} COMMENT = 'Заказы';");

        $this->addForeignKey(
            'shop_order_created_by', "{{%shop_order}}",
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_order_updated_by', "{{%shop_order}}",
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_order__site_code', "{{%shop_order}}",
            'site_code', '{{%cms_site}}', 'code', 'RESTRICT', 'CASCADE'
        );

        $this->addForeignKey(
            'shop_order__person_type_id', "{{%shop_order}}",
            'person_type_id', '{{%shop_person_type}}', 'id', 'RESTRICT', 'RESTRICT'
        );

        $this->addForeignKey(
            'shop_order__status_code', "{{%shop_order}}",
            'status_code', '{{%shop_order_status}}', 'code', 'RESTRICT', 'CASCADE'
        );

        $this->addForeignKey(
            'shop_order__currency_code', "{{%shop_order}}",
            'currency_code', '{{%money_currency}}', 'code', 'RESTRICT', 'RESTRICT'
        );

        $this->addForeignKey(
            'shop_order__user_id', "{{%shop_order}}",
            'user_id', '{{%cms_user}}', 'id', 'RESTRICT', 'RESTRICT'
        );

        $this->addForeignKey(
            'shop_order__locked_by', "{{%shop_order}}",
            'locked_by', '{{%cms_user}}', 'id', 'RESTRICT', 'RESTRICT'
        );

        $this->addForeignKey(
            'shop_order__buyer_id', "{{%shop_order}}",
            'buyer_id', '{{%shop_buyer}}', 'id', 'RESTRICT', 'RESTRICT'
        );

        $this->addForeignKey(
            'shop_order__pay_system_id', "{{%shop_order}}",
            'pay_system_id', '{{%shop_pay_system}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_order__affiliate_id', "{{%shop_order}}",
            'affiliate_id', '{{%shop_affiliate}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_order__store_id', "{{%shop_order}}",
            'store_id', '{{%shop_store}}', 'id', 'SET NULL', 'SET NULL'
        );

    }

    public function safeDown()
    {
        $this->dropTable("{{%shop_order}}");
    }
}