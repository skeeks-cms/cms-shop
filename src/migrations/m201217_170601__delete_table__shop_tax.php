<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m201217_170601__delete_table__shop_tax extends Migration
{
    public function safeUp()
    {
        $this->dropTable("shop_tax_rate");
        $this->dropTable("shop_tax");
    }

    public function safeDown()
    {
        echo "m200406_080601__alter_table__shop_order cannot be reverted.\n";
        return false;
    }
}