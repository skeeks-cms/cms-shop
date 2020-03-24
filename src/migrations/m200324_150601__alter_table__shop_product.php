<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200324_150601__alter_table__shop_product extends Migration
{
    public function safeUp()
    {
        $this->createIndex('shop_product_no_uniq__main_pid', "shop_product", 'main_pid');
        $this->dropIndex('shop_product__main_pid', "shop_product");
    }

    public function safeDown()
    {
        echo "m200324_110601__create_table__shop_favorite_product cannot be reverted.\n";
        return false;
    }
}