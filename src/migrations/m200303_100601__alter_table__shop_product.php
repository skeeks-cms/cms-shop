<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200303_100601__alter_table__shop_product extends Migration
{
    public function safeUp()
    {
        $tableName = 'shop_product';
        $this->addColumn($tableName, "measure_matches_jsondata", $this->text()->comment("Соответствие единиц измерения"));
    }

    public function safeDown()
    {
        echo "m200303_100601__alter_table__shop_product cannot be reverted.\n";
        return false;
    }
}