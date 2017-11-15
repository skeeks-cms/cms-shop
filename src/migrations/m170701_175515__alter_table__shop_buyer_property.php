<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Schema;
use yii\db\Migration;

class m170701_175515__alter_table__shop_buyer_property extends Migration
{

    public function safeUp()
    {
        $this->addColumn("{{%shop_buyer_property}}", "value_num2", $this->decimal(18, 4));
        $this->createIndex("value_num2", "{{%shop_buyer_property}}", "value_num2");

        $this->addColumn("{{%shop_buyer_property}}", "value_int2", $this->integer());
        $this->createIndex("value_int2", "{{%shop_buyer_property}}", "value_int2");

        $this->addColumn("{{%shop_buyer_property}}", "value_string", $this->string(255));
        $this->createIndex("value_string", "{{%shop_buyer_property}}", "value_string");

    }

    public function safeDown()
    {
        echo "m170701_173515__alter_table__shop_buyer_property cannot be reverted.\n";
        return false;
    }
}