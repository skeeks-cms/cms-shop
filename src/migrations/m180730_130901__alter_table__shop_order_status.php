<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m180730_130901__alter_table__shop_order_status extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_order_status";
        $this->dropColumn($tableName, "code");
    }

    public function safeDown()
    {
        echo "m180730_130901__alter_table__shop_order_status cannot be reverted.\n";
        return false;
    }
}