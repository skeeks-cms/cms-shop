<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m180730_140901__alter_table__shop_order extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_order";
        $this->addColumn($tableName, "is_allowed_payment", $this->integer(1)->notNull()->defaultValue(0));
    }

    public function safeDown()
    {
        echo "m180730_140901__alter_table__shop_order cannot be reverted.\n";
        return false;
    }
}