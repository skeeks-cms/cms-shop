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
        $this->execute("ALTER TABLE {{%shop_fuser}} ADD `additional` TEXT NULL ;");

        $this->execute("ALTER TABLE {{%shop_fuser}} ADD `person_type_id` INT NULL ;");
        $this->execute("ALTER TABLE {{%shop_fuser}} ADD `site_id` INT NULL ;");
        $this->execute("ALTER TABLE {{%shop_fuser}} ADD `delivery_code` VARCHAR(50) NULL ;");
        $this->execute("ALTER TABLE {{%shop_fuser}} ADD `buyer_id` INT NULL ;");

        $this->addForeignKey(
            'shop_fuser__person_type_id', "{{%shop_fuser}}",
            'person_type_id', '{{%shop_person_type}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_fuser__site_code', "{{%shop_fuser}}",
            'site_id', '{{%cms_site}}', 'id', 'SET NULL', 'SET NULL'
        );

    }

    public function safeDown()
    {
    }
}