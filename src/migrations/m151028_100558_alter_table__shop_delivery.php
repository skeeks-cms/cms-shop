<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */
use yii\db\Schema;
use yii\db\Migration;

class m151028_100558_alter_table__shop_delivery extends Migration
{
    public function safeUp()
    {
        $this->execute("ALTER TABLE `shop_delivery` CHANGE `site_id` `site_id` INT(11) NULL;");
    }

    public function safeDown()
    {}
}