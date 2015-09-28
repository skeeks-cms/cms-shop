<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */
use yii\db\Schema;
use yii\db\Migration;

class m150927_120558_alter_table__shop_fuser extends Migration
{
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%shop_fuser}} ADD `pay_system_id` INT NULL ;");

        $this->addForeignKey(
            'shop_fuser__pay_system_id', "{{%shop_fuser}}",
            'pay_system_id', '{{%shop_pay_system}}', 'id', 'SET NULL', 'SET NULL'
        );

    }


    public function safeDown()
    {
    }
}