<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m151028_100558_alter_table__shop_delivery extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('{{%shop_delivery}}', 'site_id', $this->integer(11));
    }

    public function safeDown()
    {
    }
}