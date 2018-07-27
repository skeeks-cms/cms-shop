<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Schema;
use yii\db\Migration;

class m180727_110601__alter_table__shop_order_item extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_order_item";

        $this->renameColumn($tableName, "price", "amount");
        $this->renameColumn($tableName, "discount_price", "discount_amount");
        $this->dropColumn($tableName, "delay");
        $this->dropColumn($tableName, "can_buy");
        $this->dropColumn($tableName, "callback_func");
        $this->dropColumn($tableName, "order_callback_func");
        $this->dropColumn($tableName, "detail_page_url");
        $this->dropColumn($tableName, "cancel_callback_func");
        $this->dropColumn($tableName, "pay_callback_func");
        $this->dropColumn($tableName, "catalog_xml_id");
        $this->dropColumn($tableName, "product_xml_id");
        $this->dropColumn($tableName, "discount_coupon");
        $this->dropColumn($tableName, "subscribe");
        $this->dropColumn($tableName, "barcode_multi");
        $this->dropColumn($tableName, "reserved");
        $this->dropColumn($tableName, "deducted");
        $this->dropColumn($tableName, "type");
        $this->dropColumn($tableName, "recommendation");
    }

    public function safeDown()
    {
        echo "m180727_110601__alter_table__shop_order_item cannot be reverted.\n";
        return false;
    }
}