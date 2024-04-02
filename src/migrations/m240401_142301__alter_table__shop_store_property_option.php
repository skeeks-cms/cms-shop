<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m240401_142301__alter_table__shop_store_property_option extends Migration
{
    public function safeUp()
    {
        $tableName = 'shop_store_property_option';

        $this->addColumn($tableName, "shop_brand_id", $this->integer()->null());
        $this->createIndex("{$tableName}__shop_brand_id", $tableName, "shop_brand_id");

        $this->addForeignKey(
            "{$tableName}__shop_brand_id", $tableName,
            'shop_brand_id', '{{%shop_brand}}', 'id', 'SET NULL', 'SET NULL'
        );

    }

    public function safeDown()
    {
        echo "m200717_132301__alter_table__shop_site cannot be reverted.\n";
        return false;
    }
}