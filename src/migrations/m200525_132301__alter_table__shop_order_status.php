<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200525_132301__alter_table__shop_order_status extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_order_status";

        $this->alterColumn($tableName, "description", $this->string(255)->comment("Короткая расшифровка статуса заказа"));

        $this->alterColumn($tableName, "color", $this->string(32)->comment("Цвет текста статуса"));
        $this->addColumn($tableName, "bg_color", $this->string(32)->comment("Цвет фона подложки статуса"));

        $this->addColumn($tableName, "order_page_description", $this->text()->comment("Описание выводится на детальной странице заказа"));
        $this->addColumn($tableName, "email_notify_description", $this->text()->comment("Текст добавляется в email уведомление о заказе"));
    }

    public function safeDown()
    {
        echo "m200525_132301__alter_table__shop_order_status cannot be reverted.\n";
        return false;
    }
}