<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */
use yii\db\Schema;
use yii\db\Migration;

class m150927_110558_alter_table__shop_fuser extends Migration
{
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%shop_fuser}} ADD `buyer_id` INT NULL ;");

        $this->addForeignKey(
            'shop_fuser__shop_buyer', "{{%shop_fuser}}",
            'buyer_id', '{{%shop_buyer}}', 'id', 'SET NULL', 'SET NULL'
        );

    }


    public function safeDown()
    {
    }
}