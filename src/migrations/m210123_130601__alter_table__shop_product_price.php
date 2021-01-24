<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m210123_130601__alter_table__shop_product_price extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_product_price";

        $this->addColumn($tableName, "is_fixed", $this->integer(1)->defaultValue(0)->notNull()->comment("Цена зафиксирована?"));

        $this->createIndex("is_fixed", $tableName, ["is_fixed"]);
    }

    public function safeDown()
    {
        echo "m210123_130601__alter_table__shop_product_price cannot be reverted.\n";
        return false;
    }
}