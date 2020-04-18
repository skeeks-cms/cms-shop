<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200418_150601__alter_table__shop_type_price extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_type_price";

        $this->dropForeignKey("shop_type_price__shop_supplier_id", $tableName);
        $this->dropIndex("shop_type_price__external_id", $tableName);
        $this->dropColumn($tableName, "shop_supplier_id");

        $this->addColumn($tableName, "is_default", $this->integer(1)->unsigned());

        $this->createIndex($tableName. "__uniq_default", $tableName, ["cms_site_id", "is_default"], true);
    }

    public function safeDown()
    {
        echo "m200406_080601__alter_table__shop_order cannot be reverted.\n";
        return false;
    }
}