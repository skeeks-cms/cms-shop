<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m201201_092301__alter_table__shop_product extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_product";

        $this->dropForeignKey("shop_product__main_pid", $tableName);
        $this->dropForeignKey("shop_product__main_pid_by", $tableName);

        $this->dropColumn($tableName, 'main_pid');
        $this->dropColumn($tableName, 'main_pid_at');
        $this->dropColumn($tableName, 'main_pid_by');
    }

    public function safeDown()
    {
        echo "m200717_132301__alter_table__shop_site cannot be reverted.\n";
        return false;
    }
}