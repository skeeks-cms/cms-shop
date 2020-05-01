<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200430_101001__update_data__shop_product extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_product";

        $this->db->createCommand("UPDATE 
            `shop_product` as product 
            INNER JOIN (
                /*Товары у которых задан главный товар*/
                SELECT 
                    inner_product.id as inner_product_id,
                    inner_ce.parent_content_element_id as parent_content_element_id
                FROM 
                    shop_product inner_product
                    LEFT JOIN cms_content_element as inner_ce on inner_ce.id = inner_product.id
                WHERE 
                    inner_ce.parent_content_element_id is not null
            ) p2 ON p2.inner_product_id = product.id 
        SET 
            product.`offers_pid` = p2.parent_content_element_id")->execute();

    }

    public function safeDown()
    {
        echo "m200430_100601__alter_table__shop_product cannot be reverted.\n";
        return false;
    }
}