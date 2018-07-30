<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m180730_120901__alter_table__shop_order extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_order";

        $this->dropForeignKey("shop_order__status_code", $tableName);
        $this->dropColumn($tableName, "status_code");
    }

    public function safeDown()
    {
        echo "m180730_120901__alter_table__shop_order cannot be reverted.\n";
        return false;
    }
}