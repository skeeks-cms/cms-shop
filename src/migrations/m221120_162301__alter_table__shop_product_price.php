<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m221120_162301__alter_table__shop_product_price extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_product_price";

        $this->dropForeignKey("shop_product_price_created_by", $tableName);
        $this->dropForeignKey("shop_product_price_updated_by", $tableName);

        $this->dropIndex("updated_by", $tableName);
        $this->dropIndex("created_by", $tableName);
        $this->dropIndex("created_at", $tableName);
        $this->dropIndex("updated_at", $tableName);

        $this->dropColumn($tableName, "created_by");
        $this->dropColumn($tableName, "updated_by");
        $this->dropColumn($tableName, "created_at");
        $this->dropColumn($tableName, "updated_at");

    }

    public function safeDown()
    {
        echo "m200717_132301__alter_table__shop_site cannot be reverted.\n";
        return false;
    }
}