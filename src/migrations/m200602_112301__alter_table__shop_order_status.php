<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200602_112301__alter_table__shop_order_status extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_order_status";

        $this->addColumn($tableName, "auto_next_shop_order_status_id", $this->integer(11)->comment("Следующий статус, который будет проставлен автоматически"));
        $this->addColumn($tableName, "auto_next_status_time", $this->integer(11)->comment("Время через которое следующий статус будет проставлен"));

        $this->createIndex("auto_next_shop_order_status_id", $tableName, "auto_next_shop_order_status_id");
        $this->createIndex("auto_next_status_time", $tableName, "auto_next_status_time");

        $this->addForeignKey(
            "{$tableName}__auto_next_shop_order_status", $tableName,
            'auto_next_shop_order_status_id', '{{%shop_order_status}}', 'id', 'SET NULL', 'SET NULL'
        );


    }

    public function safeDown()
    {
        echo "m200525_132301__alter_table__shop_order_status cannot be reverted.\n";
        return false;
    }
}