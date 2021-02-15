<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m210215_130601__alter_table__shop_product extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_product";

        $this->addColumn($tableName, "measure_ratio_min", $this->double()->notNull()->defaultValue(1));

        $this->createIndex("measure_ratio_min", $tableName, ["measure_ratio_min"]);

        $this->db->createCommand("UPDATE 
            `shop_product` as product 
            SET 
                product.`measure_ratio_min` = product.`measure_ratio`")
            ->execute();
    }

    public function safeDown()
    {
        echo "m210123_130601__alter_table__shop_product_price cannot be reverted.\n";
        return false;
    }
}