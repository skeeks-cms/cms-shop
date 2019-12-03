<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m191118_130601__alter_table__shop_product extends Migration
{
    public function safeUp()
    {
        $tableName = 'shop_product';
        /*$tableExist = $this->db->getTableSchema($tableName, true);
        if (!$tableExist) {
            return true;
        }*/
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB AUTO_INCREMENT=1001';
        }

        $this->dropForeignKey("shop_product_shop_type_price", $tableName);
        $this->dropForeignKey("shop_product_money_currency", $tableName);

        $this->dropColumn($tableName, "recur_scheme_type");
        $this->dropColumn($tableName, "quantity_trace");
        $this->dropColumn($tableName, "price_type");
        $this->dropColumn($tableName, "trial_price_id");
        $this->dropColumn($tableName, "without_order");
        $this->dropColumn($tableName, "select_best_price");
        $this->dropColumn($tableName, "tmp_id");
        $this->dropColumn($tableName, "can_buy_zero");
        $this->dropColumn($tableName, "negative_amount_trace");
        $this->dropColumn($tableName, "barcode_multi");
        $this->dropColumn($tableName, "purchasing_price");
        $this->dropColumn($tableName, "purchasing_currency");
        $this->dropColumn($tableName, "subscribe");
        $this->dropColumn($tableName, "recur_scheme_type");


    }

    public function safeDown()
    {
        echo "m191119_130601__create_table__shop_supplier cannot be reverted.\n";
        return false;
    }
}