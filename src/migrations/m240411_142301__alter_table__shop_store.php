<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m240411_142301__alter_table__shop_store extends Migration
{
    public function safeUp()
    {
        $tableName = 'shop_store';

        $this->addColumn($tableName, "display_name", $this->string(255)->null()->comment("Отображаемо название"));
        $this->createIndex("{$tableName}__display_name", $tableName, "display_name");

        $this->addColumn($tableName, "delivery_info", $this->text()->null()->comment("Информация о доставке"));
        $this->addColumn($tableName, "delivery_time", $this->integer()->null()->comment("Примерное Время доставки в днях с этого склада"));

        $this->createIndex("{$tableName}__delivery_time", $tableName, "delivery_time");
    }

    public function safeDown()
    {
        echo "m240411_142301__alter_table__shop_store cannot be reverted.\n";
        return false;
    }
}