<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Schema;
use yii\db\Migration;

class m180726_160601__alter_table__shop_cart extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_cart";

        $this->addColumn("{{%shop_cart}}", "shop_order_id", $this->integer());
        $this->createIndex($tableName . '__shop_order_id', $tableName, 'shop_order_id');

        $this->addForeignKey(
            "{$tableName}__shop_order_id", $tableName,
            'shop_order_id', '{{%shop_order}}', 'id', 'SET NULL', 'SET NULL'
        );
    }

    public function safeDown()
    {
        echo "m180726_160601__alter_table__shop_cart cannot be reverted.\n";
        return false;
    }
}