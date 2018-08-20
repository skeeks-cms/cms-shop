<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m180730_150901__alter_table__shop_order extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_order";
        $this->dropColumn($tableName, "allow_payment");
    }

    public function safeDown()
    {
        echo "m180730_150901__alter_table__shop_order cannot be reverted.\n";
        return false;
    }
}