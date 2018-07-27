<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m180727_120901__rename_table__shop_order_item_props extends Migration
{
    public function safeUp()
    {
        $this->renameTable("shop_order_item_props", "shop_order_item_property");
    }

    public function safeDown()
    {
        echo "m180727_120901__rename_table__shop_order_item_props cannot be reverted.\n";
        return false;
    }
}