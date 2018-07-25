<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Schema;
use yii\db\Migration;

class m180725_130601__alter_table__shop_bill extends Migration
{

    public function safeUp()
    {
        $this->addColumn("{{%shop_bill}}", "external_data", $this->text());
    }

    public function safeDown()
    {
        echo "m180725_130601__alter_table__shop_bill cannot be reverted.\n";
        return false;
    }
}