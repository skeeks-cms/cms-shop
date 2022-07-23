<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m220721_152301__alter_table__shop_store extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_store";

        $this->addColumn($tableName, "cashier_is_allow_sell_out_of_stock", $this->integer(1)->defaultValue(0)->comment("Разрешить продажу товаров не в наличии?"));
        $this->addColumn($tableName, "cashier_is_show_out_of_stock", $this->integer(1)->defaultValue(1)->comment("Показывать товары не в наличии?"));

        $this->createIndex($tableName.'__cashier_is_allow_sell_out_of_stock', $tableName, ['cashier_is_allow_sell_out_of_stock']);
        $this->createIndex($tableName.'__cashier_is_show_out_of_stock', $tableName, ['cashier_is_show_out_of_stock']);

    }

    public function safeDown()
    {
        echo "m200717_132301__alter_table__shop_site cannot be reverted.\n";
        return false;
    }
}