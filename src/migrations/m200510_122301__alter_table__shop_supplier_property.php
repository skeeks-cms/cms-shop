<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200510_122301__alter_table__shop_supplier_property extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_supplier_property";

        $this->dropForeignKey("shop_supplier_property__shop_supplier_id", $tableName);
        $this->dropIndex("shop_supplier_property__shop_supplier2external_code", $tableName);

        $this->addColumn($tableName, "cms_site_id", $this->integer());

        $this->addForeignKey(
            "{$tableName}__cms_site_id", $tableName,
            'cms_site_id', '{{%cms_site}}', 'id', 'SET NULL', 'SET NULL'
        );

    }

    public function safeDown()
    {
        echo "m200505_101201__rename_table__shop_cart cannot be reverted.\n";
        return false;
    }
}