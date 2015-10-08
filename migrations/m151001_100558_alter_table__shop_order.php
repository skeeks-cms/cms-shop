<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */
use yii\db\Schema;
use yii\db\Migration;

class m151001_100558_alter_table__shop_order extends Migration
{
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%shop_order}} ADD `allow_payment` VARCHAR(1) NOT NULL DEFAULT 'N';");
    }

    public function safeDown()
    {}
}