<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m180726_171401__alter_table__shop_order extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_order";
        $this->alterColumn($tableName, "is_created", $this->integer(1)->notNull()->defaultValue(0)->comment('Заказ создан?'));
    }

    public function safeDown()
    {
        echo "m180726_171401__alter_table__shop_order cannot be reverted.\n";
        return false;
    }
}