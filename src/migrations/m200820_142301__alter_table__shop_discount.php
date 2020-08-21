<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200820_142301__alter_table__shop_discount extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_discount";

        $this->dropIndex("site_id", $tableName);
        $this->renameColumn($tableName, "site_id", 'cms_site_id');
        $this->createIndex("cms_site_id", $tableName, ["cms_site_id"]);
        $this->addForeignKey(
            "{$tableName}__cms_site_id", $tableName,
            'cms_site_id', '{{%cms_site}}', 'id', 'CASCADE', 'CASCADE'
        );
    }

    public function safeDown()
    {
        echo "m200820_142301__alter_table__shop_discount cannot be reverted.\n";
        return false;
    }
}