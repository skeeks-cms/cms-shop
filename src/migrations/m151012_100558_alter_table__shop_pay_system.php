<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Schema;
use yii\db\Migration;

class m151012_100558_alter_table__shop_pay_system extends Migration
{
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%shop_pay_system}} ADD `component` VARCHAR(255) NULL;");
        $this->execute("ALTER TABLE {{%shop_pay_system}} ADD `component_settings` TEXT NULL;");
    }

    public function safeDown()
    {
    }
}