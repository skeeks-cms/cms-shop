<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m220708_132301__alter_table__shop_store_product extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_store_product";

        $this->addColumn($tableName, "is_active", $this->integer(1)->defaultValue(1)->comment("Активность"));
        $this->createIndex($tableName.'__is_active', $tableName, ['is_active']);
    }

    public function safeDown()
    {
        echo "m220610_132301__alter_table__shop_site cannot be reverted.\n";
        return false;
    }
}