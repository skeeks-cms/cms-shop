<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m260213_100601__alter_table__shop_supplier_property_option extends Migration
{
    public function safeUp()
    {
        $tableName = 'shop_supplier_property_option';
        $this->addColumn($tableName, "cms_tree_id", $this->integer());
        $this->createIndex($tableName . '__cms_tree_id', $tableName, 'cms_tree_id');

        $this->addForeignKey(
            "{$tableName}__cms_tree_id", $tableName,
            'cms_tree_id', '{{%cms_tree}}', 'id', 'SET NULL', 'SET NULL'
        );
    }

    public function safeDown()
    {
        echo "m200213_100601__alter_table__shop_supplier_property cannot be reverted.\n";
        return false;
    }
}