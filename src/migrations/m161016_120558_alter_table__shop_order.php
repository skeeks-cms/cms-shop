<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 23.07.2016
 */

use yii\db\Migration;

class m161016_120558_alter_table__shop_order extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%shop_order}}', 'key', $this->string(32)->unique());
    }

    public function safeDown()
    {
        return true;
    }
}