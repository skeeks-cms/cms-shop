<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200505_132201__alter_table__shop_viewed_product extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_viewed_product";

        $this->dropForeignKey("shop_viewed_product__shop_product_id_c", $tableName);
        $this->dropForeignKey("shop_viewed_product__shop_fuser_id", $tableName);
        $this->dropForeignKey("shop_viewed_product__site_id", $tableName);

        $this->dropColumn($tableName, "site_id");

        $this->renameColumn($tableName, "shop_fuser_id", "shop_user_id");

        $this->addForeignKey(
            "{$tableName}__shop_user_id", $tableName,
            'shop_user_id', "shop_user", 'id', 'CASCADE', 'CASCADE'
        );
    }

    public function safeDown()
    {
        echo "m200505_101201__rename_table__shop_cart cannot be reverted.\n";
        return false;
    }
}