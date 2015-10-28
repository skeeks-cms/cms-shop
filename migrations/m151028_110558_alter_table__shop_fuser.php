<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */
use yii\db\Schema;
use yii\db\Migration;

class m151028_110558_alter_table__shop_fuser extends Migration
{
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%shop_fuser}} DROP `delivery_code`;");
        $this->execute("ALTER TABLE {{%shop_fuser}} ADD `delivery_id` INT(11) NULL;");

        $this->addForeignKey(
            'shop_fuser__delivery_id', "{{%shop_fuser}}",
            'delivery_id', '{{%shop_delivery}}', 'id', 'CASCADE', 'CASCADE'
        );
    }

    public function safeDown()
    {}
}