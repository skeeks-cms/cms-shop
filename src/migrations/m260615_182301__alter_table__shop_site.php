<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 15.06.2026
 */

use yii\db\Migration;

class m260615_182301__alter_table__shop_site extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_site";

        $tableSchema = $this->db->getTableSchema($tableName, true);
        if ($tableSchema && !isset($tableSchema->columns["product_default_sort"])) {
            $this->addColumn($tableName, "product_default_sort", $this->string(32)->notNull()->defaultValue("-popular")->comment("Сортировка товаров по умолчанию"));
        }
    }

    public function safeDown()
    {
        echo "m260615_182301__alter_table__shop_site cannot be reverted.\n";
        return false;
    }
}
