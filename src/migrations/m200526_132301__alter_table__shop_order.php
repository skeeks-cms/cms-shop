<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200526_132301__alter_table__shop_order extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_order";

        $this->dropForeignKey("shop_order__locked_by", $tableName);

        $this->dropColumn($tableName, "canceled_at");
        $this->dropColumn($tableName, "reason_canceled");

        $this->dropColumn($tableName, "allow_delivery");
        $this->dropColumn($tableName, "allow_delivery_at");

        $this->dropColumn($tableName, "additional_info");
        $this->dropColumn($tableName, "user_description");

        $this->dropColumn($tableName, "locked_by");
        $this->dropColumn($tableName, "locked_at");

        $this->dropColumn($tableName, "delivery_doc_num");
        $this->dropColumn($tableName, "delivery_doc_at");

        $this->dropColumn($tableName, "tracking_number");
        $this->dropColumn($tableName, "comments");

    }

    public function safeDown()
    {
        echo "m200525_132301__alter_table__shop_order_status cannot be reverted.\n";
        return false;
    }
}