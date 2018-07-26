<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m180726_171101__alter_table__shop_order extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_order";

        $this->dropColumn($tableName, 'external_order');
        $this->dropColumn($tableName, 'recuring_id');
        $this->dropColumn($tableName, 'stat_gid');
        $this->dropColumn($tableName, 'pay_voucher_num');
        $this->dropColumn($tableName, 'pay_voucher_at');
        $this->dropColumn($tableName, 'recount_flag');
        $this->dropColumn($tableName, 'deducted');
        $this->dropColumn($tableName, 'deducted_at');
        $this->dropColumn($tableName, 'reason_undo_deducted');
        $this->dropColumn($tableName, 'marked');
        $this->dropColumn($tableName, 'marked_at');
        $this->dropColumn($tableName, 'reason_marked');
        $this->dropColumn($tableName, 'reserved');
        $this->dropColumn($tableName, 'order_topic');
        $this->dropColumn($tableName, 'responsible_id');
        $this->dropColumn($tableName, 'pay_before_at');
        $this->dropColumn($tableName, 'bill_at');
    }

    public function safeDown()
    {
        echo "m180726_171101__alter_table__shop_order cannot be reverted.\n";
        return false;
    }
}