<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m201222_110601__alter_table__shop_payment extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_payment";

        $this->dropColumn($tableName, "paid_at");
    }

    public function safeDown()
    {
        echo "m201217_200601__alter_table__shop_pay_system cannot be reverted.\n";
        return false;
    }
}