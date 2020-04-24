<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200424_150601__alter_table__shop_site extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_site";

        $this->addColumn($tableName, "is_receiver", $this->integer(1)->unsigned()->notNull()->defaultValue(0)->comment("Сайт получает товары от поставщиков?"));
        $this->addColumn($tableName, "catalog_cms_tree_id", $this->integer()->comment("Главный раздел для товаров"));

        $this->createIndex($tableName. "__catalog_cms_tree_id", $tableName, ["catalog_cms_tree_id"]);

        $this->addForeignKey(
            "{$tableName}__catalog_cms_tree_id", $tableName,
            'catalog_cms_tree_id', '{{%cms_tree}}', 'id', 'SET NULL', 'SET NULL'
        );
    }

    public function safeDown()
    {
        echo "m200406_080601__alter_table__shop_order cannot be reverted.\n";
        return false;
    }
}