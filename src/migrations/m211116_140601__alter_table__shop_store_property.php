<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m211116_140601__alter_table__shop_store_property extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_store_property";

        $this->addColumn($tableName, "is_options", $this->integer(1)->defaultValue(0)->notNull());
        $this->createIndex($tableName.'is_options', $tableName, ['is_options']);

        $this->db->createCommand("UPDATE 
            `shop_store_property` as ssp 
            INNER JOIN (
                /*Товары у которых задан главный товар*/
                SELECT 
                    inner_ssp.id as inner_ssp_id
                FROM 
                    shop_store_property inner_ssp
                WHERE 
                    inner_ssp.property_type = 'list'
            ) p2 ON p2.inner_ssp_id = ssp.id 
        SET 
            ssp.`is_options` = 1")->execute();
    }

    public function safeDown()
    {
        echo "m210123_130601__alter_table__shop_product_price cannot be reverted.\n";
        return false;
    }
}