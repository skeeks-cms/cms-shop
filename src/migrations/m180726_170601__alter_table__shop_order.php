<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Schema;
use yii\db\Migration;

class m180726_170601__alter_table__shop_order extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_order";

        $this->addColumn($tableName, "is_created", $this->integer(1)->notNull()->defaultValue(1)->comment('Заказ создан?'));
        $this->createIndex($tableName . '__is_created', $tableName, 'is_created');
    }

    public function safeDown()
    {
        echo "m180726_170601__alter_table__shop_order cannot be reverted.\n";
        return false;
    }
}