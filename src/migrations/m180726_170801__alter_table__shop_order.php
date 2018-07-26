<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Schema;
use yii\db\Migration;

class m180726_170801__alter_table__shop_order extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_order";

        $this->alterColumn($tableName, "person_type_id", $this->integer());
        $this->alterColumn($tableName, "buyer_id", $this->integer());
    }

    public function safeDown()
    {
        echo "m180726_170801__alter_table__shop_order cannot be reverted.\n";
        return false;
    }
}