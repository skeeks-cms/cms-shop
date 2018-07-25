<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Schema;
use yii\db\Migration;

class m180724_130601__create_table__shop_bill extends Migration
{
    public function safeUp()
    {
        $tableName = 'shop_bill';
        $tableExist = $this->db->getTableSchema($tableName, true);
        if ($tableExist) {
            return true;
        }
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB AUTO_INCREMENT=1001';
        }

        $this->createTable($tableName, [
            'id' => $this->primaryKey(),

            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),

            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),

            'shop_buyer_id' => $this->integer()->notNull()->comment("Покупатель"),
            'shop_order_id' => $this->integer()->notNull()->comment("Заказ"),
            'shop_pay_system_id' => $this->integer()->notNull()->comment("Платежная система"),

            'paid_at' => $this->integer()->comment("Дата оплаты"),
            'shop_payment_id' => $this->integer()->comment("Платеж"),

            'closed_at' => $this->integer()->comment("Дата отмены"),
            'reason_closed' => $this->text()->comment("Причина отмены"),

            'amount' => $this->decimal(18, 4)->notNull()->defaultValue(0),
            'currency_code' => $this->string(3)->notNull()->defaultValue("RUB"),

            'description' => $this->text(),

            'code' => $this->string(255)->notNull()->unique()->comment('Уникальный код счета'),

        ], $tableOptions);


        $this->createIndex($tableName . '__updated_by', $tableName, 'updated_by');
        $this->createIndex($tableName . '__created_by', $tableName, 'created_by');
        $this->createIndex($tableName . '__created_at', $tableName, 'created_at');
        $this->createIndex($tableName . '__updated_at', $tableName, 'updated_at');

        $this->createIndex($tableName . '__paid_at', $tableName, 'paid_at');
        $this->createIndex($tableName . '__closed_at', $tableName, 'closed_at');

        $this->createIndex($tableName . '__shop_buyer_id', $tableName, 'shop_buyer_id');
        $this->createIndex($tableName . '__shop_order_id', $tableName, 'shop_order_id');
        $this->createIndex($tableName . '__shop_pay_system_id', $tableName, 'shop_pay_system_id');
        $this->createIndex($tableName . '__shop_payment_id', $tableName, 'shop_payment_id');

        $this->addCommentOnTable($tableName, 'Счета для оплаты покупателей');

        $this->addForeignKey(
            "{$tableName}__created_by", $tableName,
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            "{$tableName}__updated_by", $tableName,
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            "{$tableName}__shop_buyer_id", $tableName,
            'shop_buyer_id', '{{%shop_buyer}}', 'id', 'RESTRICT', 'RESTRICT'
        );

        $this->addForeignKey(
            "{$tableName}__shop_order_id", $tableName,
            'shop_order_id', '{{%shop_order}}', 'id', 'RESTRICT', 'RESTRICT'
        );
        $this->addForeignKey(
            "{$tableName}__shop_pay_system_id", $tableName,
            'shop_pay_system_id', '{{%shop_pay_system}}', 'id', 'RESTRICT', 'RESTRICT'
        );

        $this->addForeignKey(
            "{$tableName}__shop_payment_id", $tableName,
            'shop_payment_id', '{{%shop_payment}}', 'id', 'RESTRICT', 'RESTRICT'
        );

        $this->addForeignKey(
            "{$tableName}__currency_code", $tableName,
            'currency_code', '{{%money_currency}}', 'code', 'RESTRICT', 'RESTRICT'
        );

    }

    public function safeDown()
    {
        echo "m180724_130601__create_table__shop_bill cannot be reverted.\n";
        return false;
    }
}