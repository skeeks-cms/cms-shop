<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200430_101201__alter_table__shop_product extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_product";

        $this->dropForeignKey("shop_product__shop_supplier_id", $tableName);

        $this->dropIndex("shop_product__supplier_external", $tableName);
        $this->dropColumn($tableName, "shop_supplier_id");
        $this->dropColumn($tableName, "supplier_external_id");
    }

    public function safeDown()
    {
        echo "m200430_100601__alter_table__shop_product cannot be reverted.\n";
        return false;
    }
}