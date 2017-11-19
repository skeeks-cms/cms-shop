<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Schema;
use yii\db\Migration;

class m160601_110558_alter_table__shop_order extends Migration
{
    public function safeUp()
    {
        $this->update('shop_fuser', [
            'store_id' => null
        ]);
        $this->update('shop_order', [
            'store_id' => null
        ]);

        $this->dropForeignKey('shop_order__store_id', "{{%shop_order}}");
        $this->dropForeignKey('shop_fuser__store_id', "{{%shop_fuser}}");

        $this->addForeignKey(
            'shop_order__store_id', "{{%shop_order}}",
            'store_id', '{{%cms_content_element}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_fuser__store_id', "{{%shop_fuser}}",
            'store_id', '{{%cms_content_element}}', 'id', 'SET NULL', 'SET NULL'
        );


    }

    public function safeDown()
    {

    }
}