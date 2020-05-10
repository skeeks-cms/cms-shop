<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200510_112301__alter_table__shop_store extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_store";

        $this->dropForeignKey("shop_store__shop_supplier_id", $tableName);
        $this->dropIndex("shop_store__name_supplier", $tableName);
        $this->dropIndex("shop_store__external_id", $tableName);
        $this->dropColumn($tableName, "shop_supplier_id");
        $this->createIndex("shop_store__name_supplier", $tableName, ['cms_site_id', 'name'], true);
    }

    public function safeDown()
    {
        echo "m200505_101201__rename_table__shop_cart cannot be reverted.\n";
        return false;
    }
}