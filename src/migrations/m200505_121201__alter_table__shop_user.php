<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200505_121201__alter_table__shop_user extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_user";

        $subQuery = $this->db->createCommand("
            UPDATE 
                `shop_user` as c
            SET 
                c.cms_site_id = (select cms_site.id from cms_site where cms_site.is_default = 1)
        ")->execute();

        $this->createIndex("{$tableName}__shop_order_id", $tableName, "shop_order_id", true);
        $this->dropIndex("shop_cart__shop_order_id", $tableName);

        $this->alterColumn($tableName, "cms_site_id", $this->integer()->notNull());

    }

    public function safeDown()
    {
        echo "m200505_101201__rename_table__shop_cart cannot be reverted.\n";
        return false;
    }
}