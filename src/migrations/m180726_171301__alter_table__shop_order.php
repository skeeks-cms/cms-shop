<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m180726_171301__alter_table__shop_order extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_order";

        $this->renameColumn($tableName, 'price', 'amount');
        $this->renameColumn($tableName, 'discount_value', 'discount_amount');
        $this->renameColumn($tableName, 'tax_value', 'tax_amount');
        $this->renameColumn($tableName, 'sum_paid', 'paid_amount');
        $this->renameColumn($tableName, 'key', 'code');
    }

    public function safeDown()
    {
        echo "m180726_171301__alter_table__shop_order cannot be reverted.\n";
        return false;
    }
}