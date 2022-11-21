<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m221120_172301__alter_table__shop_store_product extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_store_product";

        $this->dropForeignKey("shop_store_product__created_by", $tableName);
        $this->dropForeignKey("shop_store_product__updated_by", $tableName);

        $this->dropIndex("shop_store_product__updated_by", $tableName);
        $this->dropIndex("shop_store_product__created_by", $tableName);

        $this->dropColumn($tableName, "created_by");
        $this->dropColumn($tableName, "updated_by");

    }

    public function safeDown()
    {
        echo "m200717_132301__alter_table__shop_site cannot be reverted.\n";
        return false;
    }
}