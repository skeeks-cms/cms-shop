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
        $this->dropColumn('{{%shop_fuser}}', 'delivery_code');
        $this->addColumn('{{%shop_fuser}}', 'delivery_id', $this->integer(11));

        $this->addForeignKey(
            'shop_fuser__delivery_id', "{{%shop_fuser}}",
            'delivery_id', '{{%shop_delivery}}', 'id', 'CASCADE', 'CASCADE'
        );
    }

    public function safeDown()
    {
    }
}