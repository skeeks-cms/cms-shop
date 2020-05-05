<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200505_142301__alter_table__shop_quantity_notice_email extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_quantity_notice_email";

        $this->dropForeignKey("shop_quantity_notice_email__shop_fuser_id", $tableName);

        $this->renameColumn($tableName, "shop_fuser_id", "shop_user_id");

        $this->addForeignKey(
            "{$tableName}__shop_user_id", $tableName,
            'shop_user_id', "shop_user", 'id', 'SET NULL', 'SET NULL'
        );
    }

    public function safeDown()
    {
        echo "m200505_101201__rename_table__shop_cart cannot be reverted.\n";
        return false;
    }
}