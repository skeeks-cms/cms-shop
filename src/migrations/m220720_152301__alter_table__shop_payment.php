<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m220720_152301__alter_table__shop_payment extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_payment";

        $this->addColumn($tableName, "shop_store_id", $this->integer()->comment("Оплата в магазине"));
        $this->addColumn($tableName, "shop_store_payment_type", $this->string(255)->comment("Тип оплаты в магизне"));

        $this->createIndex($tableName.'__shop_store_id', $tableName, ['shop_store_id']);
        $this->createIndex($tableName.'__shop_store_payment_type', $tableName, ['shop_store_payment_type']);

        $this->addForeignKey(
            "{$tableName}__shop_store_id", $tableName,
            'shop_store_id', '{{%shop_store}}', 'id', 'SET NULL', 'SET NULL'
        );
    }

    public function safeDown()
    {
        echo "m200717_132301__alter_table__shop_site cannot be reverted.\n";
        return false;
    }
}