<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m210221_140601__alter_table__shop_store extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_store";

        $this->addColumn($tableName, "is_supplier", $this->integer(1)->notNull()->defaultValue(0));
        $this->createIndex($tableName.'__is_supplier', $tableName, ['is_supplier']);

    }

    public function safeDown()
    {
        echo "m210123_130601__alter_table__shop_product_price cannot be reverted.\n";
        return false;
    }
}