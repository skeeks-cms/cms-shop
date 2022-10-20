<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m221017_172301__update_table__shop_order extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_order";

        $subQuery = $this->db->createCommand("
            UPDATE 
                `shop_order` as o
            SET 
                o.is_order = 0
            WHERE 
                o.shop_store_id is not NULL
                AND o.paid_at is not NULL
        ")->execute();

    }

    public function safeDown()
    {
        echo "m200717_132301__alter_table__shop_site cannot be reverted.\n";
        return false;
    }
}