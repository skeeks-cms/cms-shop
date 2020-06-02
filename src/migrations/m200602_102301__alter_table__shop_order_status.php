<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200602_102301__alter_table__shop_order_status extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_order_status";

        $this->addColumn($tableName, "btn_name", $this->string(255)->comment("Название на кнопке смены статуса"));
    }

    public function safeDown()
    {
        echo "m200525_132301__alter_table__shop_order_status cannot be reverted.\n";
        return false;
    }
}