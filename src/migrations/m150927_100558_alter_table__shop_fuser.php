<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Schema;
use yii\db\Migration;

class m150927_100558_alter_table__shop_fuser extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('{{%shop_fuser}}', 'additional', $this->text());
        $this->alterColumn('{{%shop_fuser}}', 'person_type_id', $this->integer());
        $this->alterColumn('{{%shop_fuser}}', 'site_id', $this->integer());
        $this->alterColumn('{{%shop_fuser}}', 'delivery_code', $this->string(50));
        $this->alterColumn('{{%shop_fuser}}', 'buyer_id', $this->integer());
        $this->alterColumn('{{%shop_fuser}}', 'pay_system_id', $this->integer());
       
        $this->addForeignKey(
            'shop_fuser__pay_system_id', "{{%shop_fuser}}",
            'pay_system_id', '{{%shop_pay_system}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_fuser__person_type_id', "{{%shop_fuser}}",
            'person_type_id', '{{%shop_person_type}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_fuser__site_id', "{{%shop_fuser}}",
            'site_id', '{{%cms_site}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_fuser__shop_buyer', "{{%shop_fuser}}",
            'buyer_id', '{{%shop_buyer}}', 'id', 'SET NULL', 'SET NULL'
        );

    }

    public function safeDown()
    {
    }
}