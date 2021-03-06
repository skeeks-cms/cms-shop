<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m210219_140601__alter_table__shop_store extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_store";

        $this->addColumn($tableName, "latitude", $this->double()->comment("Широта"),);
        $this->addColumn($tableName, "longitude", $this->double()->comment("Долгота"));
        $this->addColumn($tableName, "address", $this->string(255)->comment("Полный адрес"));
        $this->addColumn($tableName, "work_time", $this->text()->comment("Рабочее время"));
        $this->addColumn($tableName, "priority", $this->integer()->notNull()->defaultValue(100));

        $this->createIndex($tableName.'__priority', $tableName, 'priority');
        $this->createIndex($tableName.'__address_uniq', $tableName, ['cms_site_id', 'address'], true);
        $this->createIndex($tableName.'__coordinates', $tableName, ['latitude', 'longitude']);

    }

    public function safeDown()
    {
        echo "m210123_130601__alter_table__shop_product_price cannot be reverted.\n";
        return false;
    }
}