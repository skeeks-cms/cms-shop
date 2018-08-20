<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m180730_111001__update_table__shop_order extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_order";

        $this->execute("
            UPDATE shop_order SET shop_order_status_id = (SELECT id FROM shop_order_status WHERE code = shop_order.status_code)
        ");

    }

    public function safeDown()
    {
        echo "m180730_111001__update_table__shop_order cannot be reverted.\n";
        return false;
    }
}