<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Schema;
use yii\db\Migration;

class m180726_170701__alter_table__shop_order extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_order";

        $this->dropColumn($tableName, "emp_payed_id");
        $this->dropColumn($tableName, "emp_canceled_id");
        $this->dropColumn($tableName, "emp_status_id");
        $this->dropColumn($tableName, "emp_allow_delivery_id");
        $this->dropColumn($tableName, "emp_deducted_id");
        $this->dropColumn($tableName, "emp_marked_id");
        $this->dropColumn($tableName, "id_1c");
        $this->dropColumn($tableName, "version_1c");
        $this->dropColumn($tableName, "version");
        $this->dropColumn($tableName, "ps_status");
        $this->dropColumn($tableName, "ps_status_code");
        $this->dropColumn($tableName, "ps_status_description");
        $this->dropColumn($tableName, "ps_status_message");
        $this->dropColumn($tableName, "ps_sum");
        $this->dropColumn($tableName, "ps_currency_code");
        $this->dropColumn($tableName, "ps_response_at");
        $this->dropColumn($tableName, "update_1c");
    }

    public function safeDown()
    {
        echo "m180726_170701__alter_table__shop_order cannot be reverted.\n";
        return false;
    }
}