<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Schema;
use yii\db\Migration;

class m180726_180601__alter_table__shop_cart extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_cart";

        $this->dropForeignKey('shop_fuser_user_id', $tableName);
        $this->renameColumn($tableName, "user_id", "cms_user_id");
        $this->addForeignKey(
            "{$tableName}__cms_user_id", $tableName,
            'cms_user_id', '{{%cms_user}}', 'id', 'CASCADE', 'CASCADE'
        );
    }

    public function safeDown()
    {
        echo "m180726_180601__alter_table__shop_cart cannot be reverted.\n";
        return false;
    }
}