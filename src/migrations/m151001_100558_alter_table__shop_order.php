<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m151001_100558_alter_table__shop_order extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%shop_order}}', 'allow_payment', $this->string(1)->notNull()->defaultValue('N'));
    }

    public function safeDown()
    {
    }
}