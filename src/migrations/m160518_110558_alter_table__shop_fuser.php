<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */
use yii\db\Schema;
use yii\db\Migration;

class m160518_110558_alter_table__shop_fuser extends Migration
{
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%shop_fuser}} ADD `store_id` INT(11) NULL;");

        $this->addForeignKey(
            'shop_fuser__store_id', "{{%shop_fuser}}",
            'store_id', '{{%shop_store}}', 'id', 'SET NULL', 'SET NULL'
        );
    }

    public function safeDown()
    {}
}