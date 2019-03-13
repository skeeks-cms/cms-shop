<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m190313_165801__update_table__shop_pay_system extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_pay_system";
        $this->dropIndex('name', $tableName);
    }

    public function safeDown()
    {
        echo "m190313_165801__update_table__shop_pay_system cannot be reverted.\n";
        return false;
    }
}