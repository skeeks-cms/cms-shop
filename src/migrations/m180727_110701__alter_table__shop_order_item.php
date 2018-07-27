<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m180727_110701__alter_table__shop_order_item extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_order_item";

        $this->dropColumn($tableName, "custom_price");
        $this->dropColumn($tableName, "set_parent_id");
    }

    public function safeDown()
    {
        echo "m180727_110701__alter_table__shop_order_item cannot be reverted.\n";
        return false;
    }
}