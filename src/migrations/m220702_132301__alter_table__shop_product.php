<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m220702_132301__alter_table__shop_product extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_product";

        $this->addColumn($tableName, "rating_value", $this->decimal(18, 4)->defaultValue(0)->comment("Рейтинг товара"));
        $this->addColumn($tableName, "rating_count", $this->integer()->defaultValue(0)->comment("Количество голосов"));

        $this->createIndex($tableName.'__rating_value', $tableName, ['rating_value']);
        $this->createIndex($tableName.'__rating_count', $tableName, ['rating_count']);
    }

    public function safeDown()
    {
        echo "m220610_132301__alter_table__shop_site cannot be reverted.\n";
        return false;
    }
}