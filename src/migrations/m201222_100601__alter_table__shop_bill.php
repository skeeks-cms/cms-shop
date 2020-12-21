<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m201222_100601__alter_table__shop_bill extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_bill";

        $this->addColumn($tableName, "external_id", $this->string(255)->null());
        $this->addColumn($tableName, "external_name", $this->string(255)->null());

        $this->createIndex("external_unique", $tableName, ["shop_pay_system_id", "external_id"], true);
    }

    public function safeDown()
    {
        echo "m201217_200601__alter_table__shop_pay_system cannot be reverted.\n";
        return false;
    }
}