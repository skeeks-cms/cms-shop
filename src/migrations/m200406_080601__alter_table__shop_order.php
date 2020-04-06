<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200406_080601__alter_table__shop_order extends Migration
{
    public function safeUp()
    {
        $this->dropForeignKey("shop_order__shop_affiliate_id", "shop_order");
        $this->dropColumn("shop_order", "shop_affiliate_id");
    }

    public function safeDown()
    {
        echo "m200406_080601__alter_table__shop_order cannot be reverted.\n";
        return false;
    }
}