<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m180727_120901__delete_table__shop_extra extends Migration
{
    public function safeUp()
    {
        $this->dropTable("shop_extra");
    }

    public function safeDown()
    {
        echo "m180727_120901__delete_table__shop_extra cannot be reverted.\n";
        return false;
    }
}