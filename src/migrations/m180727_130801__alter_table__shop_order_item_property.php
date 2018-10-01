<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m180727_130801__alter_table__shop_order_item_property extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_order_item_property";

        $this->dropForeignKey("shop_basket_props__shop_basket_id", $tableName);

        $this->renameColumn($tableName, "shop_basket_id", "shop_order_item_id");

        $this->addForeignKey(
            "{$tableName}__shop_order_item_id", $tableName,
            'shop_order_item_id', '{{%shop_order_item}}', 'id', 'CASCADE', 'CASCADE'
        );
    }

    public function safeDown()
    {
        echo "m180727_130801__alter_table__shop_order_item_property cannot be reverted.\n";
        return false;
    }
}