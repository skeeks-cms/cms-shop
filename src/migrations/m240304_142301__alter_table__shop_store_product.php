<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m240304_142301__alter_table__shop_store_product extends Migration
{
    public function safeUp()
    {
        $tableName = 'shop_store_product';

        $this->dropIndex("{$tableName}__store_external_uniq", $tableName);
        $this->createIndex("{$tableName}__store_external_uniq", $tableName, ["external_id", "shop_store_id"], true);
    }

    public function safeDown()
    {
        echo "m200717_132301__alter_table__shop_site cannot be reverted.\n";
        return false;
    }
}