<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 23.07.2016
 */
use yii\db\Schema;
use yii\db\Migration;

class m161016_150558_alter_table__shop_order extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('{{%shop_order}}', 'user_id', $this->integer(11));
    }

    public function safeDown()
    {
        return true;
    }
}