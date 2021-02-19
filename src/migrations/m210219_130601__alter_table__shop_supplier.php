<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m210219_130601__alter_table__shop_supplier extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_supplier";

        $this->db->createCommand("DELETE FROM `shop_supplier`")
            ->execute();

        $this->dropIndex("shop_supplier__external_id", $tableName);
        $this->dropColumn($tableName, "is_main");

        $this->addColumn($tableName, "cms_site_id", $this->integer()->notNull());
        $this->createIndex("cms_site_id", $tableName, "cms_site_id");

        $this->createIndex("shop_supplier_external_id_unique", $tableName, ["cms_site_id", "external_id"], true);

        $this->addForeignKey(
            "{$tableName}__cms_site_id", $tableName,
            'cms_site_id', '{{%cms_site}}', 'id', 'CASCADE', 'CASCADE'
        );

    }

    public function safeDown()
    {
        echo "m210123_130601__alter_table__shop_product_price cannot be reverted.\n";
        return false;
    }
}