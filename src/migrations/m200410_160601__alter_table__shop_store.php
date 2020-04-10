<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200410_160601__alter_table__shop_store extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_store";

        $this->addColumn($tableName, "cms_site_id", $this->integer());
        $this->createIndex("cms_site_id", $tableName, "cms_site_id");

        $this->createIndex("shop_store_external_id_unique", $tableName, ["cms_site_id", "external_id"], true);

        $this->addForeignKey(
            "{$tableName}__cms_site_id", $tableName,
            'cms_site_id', "cms_site", 'id', 'RESTRICT', 'CASCADE'
        );
    }

    public function safeDown()
    {
        echo "m200406_080601__alter_table__shop_order cannot be reverted.\n";
        return false;
    }
}