<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m221103_152301__alter_table__shop_store extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_store";

        $this->addColumn($tableName, "cashier_is_show_only_inner_products", $this->integer(1)->defaultValue(0)->comment("Показывать только товары магазина?"));
        $this->createIndex($tableName.'__cashier_is_show_only_inner_products', $tableName, ['cashier_is_show_only_inner_products']);

    }

    public function safeDown()
    {
        echo "m200717_132301__alter_table__shop_site cannot be reverted.\n";
        return false;
    }
}