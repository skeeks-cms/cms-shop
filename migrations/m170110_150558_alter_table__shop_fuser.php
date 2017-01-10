<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 23.07.2016
 */
use yii\db\Schema;
use yii\db\Migration;

class m170110_150558_alter_table__shop_fuser extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%shop_fuser}}', 'discount_coupons', $this->text());
    }

    public function safeDown()
    {
        return true;
    }
}