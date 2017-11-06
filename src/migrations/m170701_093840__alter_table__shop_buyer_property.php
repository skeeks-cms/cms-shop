<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */
use yii\db\Schema;
use yii\db\Migration;

class m170701_093840__alter_table__shop_buyer_property extends Migration
{
    public function safeUp()
    {
        $this->addColumn("{{%shop_buyer_property}}", "value_bool", $this->boolean());
    }

    public function safeDown()
    {
        echo "m170701_093840__alter_table__shop_buyer_property cannot be reverted.\n";
        return false;
    }
}