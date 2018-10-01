<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m180726_190601__alter_table__shop_order_item extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_order_item";

        $this->dropForeignKey('shop_basket__site_id', $tableName);
        $this->dropForeignKey('shop_basket__fuser_id', $tableName);
        $this->dropForeignKey('shop_basket__order_id', $tableName);
        $this->dropForeignKey('shop_basket__product_id', $tableName);
        $this->dropForeignKey('shop_basket__product_price_id', $tableName);

        $this->dropColumn($tableName, "fuser_id");
        $this->dropColumn($tableName, "site_id");

        $this->renameColumn($tableName, "order_id", "shop_order_id");
        $this->renameColumn($tableName, "product_id", "shop_product_id");
        $this->renameColumn($tableName, "product_price_id", "shop_product_price_id");

        $this->addForeignKey(
            "{$tableName}__shop_order_id", $tableName,
            'shop_order_id', '{{%shop_order}}', 'id', 'CASCADE', 'CASCADE'
        );

        $this->addForeignKey(
            "{$tableName}__shop_product_id", $tableName,
            'shop_product_id', '{{%shop_product}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            "{$tableName}__shop_product_price_id", $tableName,
            'shop_product_price_id', '{{%shop_product_price}}', 'id', 'SET NULL', 'SET NULL'
        );
    }

    public function safeDown()
    {
        echo "m180726_190601__alter_table__shop_order_item cannot be reverted.\n";
        return false;
    }
}