<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200820_152301__alter_table__shop_discount extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_discount";

        $this->dropColumn($tableName, "count_period");
        $this->dropColumn($tableName, "count_size");
        $this->dropColumn($tableName, "count_type");
        $this->dropColumn($tableName, "count_from");
        $this->dropColumn($tableName, "count_to");
        $this->dropColumn($tableName, "action_size");
        $this->dropColumn($tableName, "action_type");
        $this->dropColumn($tableName, "unpack");
        $this->dropColumn($tableName, "version");
        $this->dropColumn($tableName, "count_uses");
        $this->dropColumn($tableName, "max_uses");
        $this->dropColumn($tableName, "xml_id");
        $this->dropColumn($tableName, "coupon");
        $this->dropColumn($tableName, "renewal");
    }

    public function safeDown()
    {
        echo "m200820_142301__alter_table__shop_discount cannot be reverted.\n";
        return false;
    }
}