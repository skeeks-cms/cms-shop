<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m180819_110901__alter_table__shop_discount extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_discount";
        $this->addColumn($tableName, "assignment_type", $this->string(10)->notNull()->defaultValue("product"));
    }

    public function safeDown()
    {
        echo "m180819_110901__alter_table__shop_discount cannot be reverted.\n";
        return false;
    }
}