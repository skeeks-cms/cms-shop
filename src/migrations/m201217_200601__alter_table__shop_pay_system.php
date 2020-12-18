<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m201217_200601__alter_table__shop_pay_system extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_pay_system";
        $this->renameColumn($tableName, "component_settings", "component_config");
    }

    public function safeDown()
    {
        echo "m201217_200601__alter_table__shop_pay_system cannot be reverted.\n";
        return false;
    }
}