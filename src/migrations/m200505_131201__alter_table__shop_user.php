<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200505_131201__alter_table__shop_user extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_user";

        $this->createIndex("{$tableName}__cms_user2cms_site", $tableName, ["cms_site_id", "cms_user_id"], true);
        $this->createIndex("{$tableName}__cms_user_id", $tableName, "cms_user_id");
        $this->dropIndex("user_id", $tableName);
    }

    public function safeDown()
    {
        echo "m200505_101201__rename_table__shop_cart cannot be reverted.\n";
        return false;
    }
}