<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m151012_100558_alter_table__shop_pay_system extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%shop_pay_system}}', 'component', $this->string(255));
        $this->addColumn('{{%shop_pay_system}}', 'component_settings', $this->text());
    }

    public function safeDown()
    {
    }
}