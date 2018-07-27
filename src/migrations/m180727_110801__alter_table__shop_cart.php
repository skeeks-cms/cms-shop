<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Schema;
use yii\db\Migration;

class m180727_110801__alter_table__shop_cart extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_cart";

        $this->dropForeignKey("shop_fuser__delivery_id", $tableName);
        $this->dropForeignKey("shop_fuser__pay_system_id", $tableName);
        $this->dropForeignKey("shop_fuser__person_type_id", $tableName);
        $this->dropForeignKey("shop_fuser__shop_buyer", $tableName);
        $this->dropForeignKey("shop_fuser__site_id", $tableName);
        $this->dropForeignKey("shop_fuser__store_id", $tableName);

        $this->dropColumn($tableName, "additional");
        $this->dropColumn($tableName, "person_type_id");
        $this->dropColumn($tableName, "site_id");
        $this->dropColumn($tableName, "buyer_id");
        $this->dropColumn($tableName, "pay_system_id");
        $this->dropColumn($tableName, "delivery_id");
        $this->dropColumn($tableName, "store_id");
        $this->dropColumn($tableName, "discount_coupons");
    }

    public function safeDown()
    {
        echo "m180727_110801__alter_table__shop_cart cannot be reverted.\n";
        return false;
    }
}