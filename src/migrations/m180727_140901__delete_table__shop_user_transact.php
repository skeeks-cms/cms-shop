<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m180727_140901__delete_table__shop_user_transact extends Migration
{
    public function safeUp()
    {
        $this->dropTable("shop_user_transact");
    }

    public function safeDown()
    {
        echo "m180727_140901__delete_table__shop_user_transact cannot be reverted.\n";
        return false;
    }
}