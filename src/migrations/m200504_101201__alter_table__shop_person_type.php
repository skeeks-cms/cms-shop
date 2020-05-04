<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200504_101201__alter_table__shop_person_type extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_person_type";

        $this->addColumn($tableName, "is_active", $this->integer(1)->notNull()->defaultValue(1));
        $this->update($tableName, ['is_active' => 0], ['active' => 'N']);
    }

    public function safeDown()
    {
        echo "m200504_101201__alter_table__shop_person_type cannot be reverted.\n";
        return false;
    }
}