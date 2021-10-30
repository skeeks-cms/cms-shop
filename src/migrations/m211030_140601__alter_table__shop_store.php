<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m211030_140601__alter_table__shop_store extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_store";

        $this->addColumn($tableName, "source_selling_price", $this->string(255)->defaultValue("selling_price")->notNull());
        $this->createIndex($tableName.'__source_selling_price', $tableName, ['source_selling_price']);

        $this->addColumn($tableName, "selling_extra_charge", $this->decimal(18,4)->defaultValue(100)->notNull());
        $this->createIndex($tableName.'__selling_extra_charge', $tableName, ['selling_extra_charge']);


        $this->addColumn($tableName, "source_purchase_price", $this->string(255)->defaultValue("purchase_price")->notNull());
        $this->createIndex($tableName.'__source_purchase_price', $tableName, ['source_purchase_price']);

        $this->addColumn($tableName, "purchase_extra_charge", $this->decimal(18,4)->defaultValue(100)->notNull());
        $this->createIndex($tableName.'__purchase_extra_charge', $tableName, ['purchase_extra_charge']);
    }

    public function safeDown()
    {
        echo "m210123_130601__alter_table__shop_product_price cannot be reverted.\n";
        return false;
    }
}