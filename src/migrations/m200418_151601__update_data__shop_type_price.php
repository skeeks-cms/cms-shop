<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200418_151601__update_data__shop_type_price extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_type_price";

        $this->db->createCommand("UPDATE 
            `shop_type_price` as price 
            INNER JOIN (
                
                /*Товары у которых задан главный товар*/
                SELECT 
                    inner_price.id as inner_price_id 
                FROM 
                    shop_type_price inner_price 
                GROUP BY 
                    inner_price.cms_site_id 
                ORDER BY 
                    inner_price.priority ASC
            ) group_price ON group_price.inner_price_id = price.id 
        SET 
            price.`is_default` = 1")->execute();
    }

    public function safeDown()
    {
        echo "m200406_080601__alter_table__shop_order cannot be reverted.\n";
        return false;
    }
}