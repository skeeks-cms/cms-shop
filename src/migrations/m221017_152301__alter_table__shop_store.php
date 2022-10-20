<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m221017_152301__alter_table__shop_store extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_store";

        $this->addColumn($tableName, "is_allow_no_check", $this->integer(1)->defaultValue(0)->comment("Разрешить продажу без чека?"));
        $this->createIndex($tableName.'__is_allow_no_check', $tableName, ['is_allow_no_check']);

    }

    public function safeDown()
    {
        echo "m200717_132301__alter_table__shop_site cannot be reverted.\n";
        return false;
    }
}