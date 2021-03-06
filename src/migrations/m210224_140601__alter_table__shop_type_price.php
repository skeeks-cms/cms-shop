<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m210224_140601__alter_table__shop_type_price extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_type_price";

        $this->addColumn($tableName, "is_purchase", $this->integer(1));
        $this->createIndex($tableName.'__is_purchase', $tableName, ['is_purchase', 'cms_site_id'], true);

    }

    public function safeDown()
    {
        echo "m210123_130601__alter_table__shop_product_price cannot be reverted.\n";
        return false;
    }
}