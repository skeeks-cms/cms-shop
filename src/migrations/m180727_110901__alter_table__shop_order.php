<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Schema;
use yii\db\Migration;

class m180727_110901__alter_table__shop_order extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_order";

        $this->renameColumn($tableName, "price_delivery", "delivery_amount");
    }

    public function safeDown()
    {
        echo "m180727_110901__alter_table__shop_order cannot be reverted.\n";
        return false;
    }
}