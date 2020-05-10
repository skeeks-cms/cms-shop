<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200510_132301__alter_table__shop_supplier_property extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_supplier_property";

        $this->dropColumn($tableName, "shop_supplier_id");
        $this->createIndex($tableName . "__external_site_uniq", $tableName, ["cms_site_id", "external_code"], true);
    }

    public function safeDown()
    {
        echo "m200505_101201__rename_table__shop_cart cannot be reverted.\n";
        return false;
    }
}