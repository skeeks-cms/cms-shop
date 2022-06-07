<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m220607_132301__alter_table__shop_order extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_order";

        $this->addColumn($tableName, "delivery_address", $this->string(255)->comment('Адрес доставки'));
        $this->addColumn($tableName, "delivery_latitude", $this->double()->comment("Широта"));
        $this->addColumn($tableName, "delivery_longitude", $this->double()->comment("Долгота"));
        $this->addColumn($tableName, "delivery_entrance", $this->string(255)->comment("Подъезд"));
        $this->addColumn($tableName, "delivery_floor", $this->string(255)->notNull()->comment("Этаж"));
        $this->addColumn($tableName, "delivery_apartment_number", $this->string(255)->notNull()->comment("Номер квартиры"));
        $this->addColumn($tableName, "delivery_comment", $this->text()->notNull()->comment("Коментарий к адресу"));

        $this->createIndex($tableName.'__delivery_coordinates', $tableName, ['delivery_latitude', 'delivery_longitude']);
        $this->createIndex($tableName.'__delivery_address', $tableName, ['delivery_address']);
    }

    public function safeDown()
    {
        echo "m200717_132301__alter_table__shop_site cannot be reverted.\n";
        return false;
    }
}