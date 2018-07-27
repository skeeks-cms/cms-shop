<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 23.07.2016
 */

use yii\db\Migration;

class m161102_150558_alter_table__shop_product_price extends Migration
{
    public function safeUp()
    {
        $this->createIndex('unique_product_priceType', '{{%shop_product_price}}', ['product_id', 'type_price_id'],
            true);
    }

    public function safeDown()
    {
        return true;
    }
}