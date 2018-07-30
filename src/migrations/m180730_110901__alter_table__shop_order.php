<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m180730_110901__alter_table__shop_order extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_order";

        $this->addColumn($tableName, "shop_order_status_id", $this->integer());
        $this->createIndex("shop_order_status_id", $tableName, "shop_order_status_id");
        $this->addForeignKey(
            "{$tableName}__shop_order_status_id", $tableName,
            'shop_order_status_id', '{{%shop_order_status}}', 'id', 'RESTRICT', 'RESTRICT'
        );
    }

    public function safeDown()
    {
        echo "m180730_110901__alter_table__shop_order cannot be reverted.\n";
        return false;
    }
}