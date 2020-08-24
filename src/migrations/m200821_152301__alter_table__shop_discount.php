<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200821_152301__alter_table__shop_discount extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_discount";

        $this->addColumn($tableName, "is_active", $this->integer(1)->notNull()->defaultValue(1));
        $this->update($tableName, ['is_active' => 0], ['active' => 'N']);
        $this->dropColumn($tableName, "active");

        $this->addColumn($tableName, "is_last", $this->integer(1)->notNull()->defaultValue(1));
        $this->update($tableName, ['is_last' => 0], ['last_discount' => 'N']);
        $this->dropColumn($tableName, "last_discount");
    }

    public function safeDown()
    {
        echo "m200820_142301__alter_table__shop_discount cannot be reverted.\n";
        return false;
    }
}