<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200610_132301__alter_table__shop_order_status extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_order_status";

        $this->addColumn($tableName, "is_install_after_pay", $this->integer(1)->comment("Установить этот статус после оплаты?"));

        $this->createIndex("is_install_after_pay", $tableName, ["is_install_after_pay"], true);

    }

    public function safeDown()
    {
        echo "m200525_132301__alter_table__shop_order_status cannot be reverted.\n";
        return false;
    }
}