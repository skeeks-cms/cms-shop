<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200818_132301__alter_table__shop_cms_content_property extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_cms_content_property";

        $this->addColumn($tableName, "is_vendor", $this->integer(1)->comment("Производитель"));
        $this->addColumn($tableName, "is_vendor_code", $this->integer(1)->comment("Код производителя"));

        $this->createIndex("is_vendor", $tableName, ["is_vendor"], true);
        $this->createIndex("is_vendor_code", $tableName, ["is_vendor_code"], true);
    }

    public function safeDown()
    {
        echo "m200717_132301__alter_table__shop_site cannot be reverted.\n";
        return false;
    }
}