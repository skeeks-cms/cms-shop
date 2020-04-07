<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200406_090601__drop_tables extends Migration
{
    public function safeUp()
    {
        $this->dropTable('shop_affiliate_tier');
        $this->dropTable('shop_affiliate');
        $this->dropTable('shop_affiliate_plan');
    }

    public function safeDown()
    {
        echo "m200406_090601__drop_tables cannot be reverted.\n";
        return false;
    }
}