<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m180726_171201__alter_table__shop_order extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_order";

        $this->dropForeignKey("shop_order__store_id", $tableName);
        $this->dropColumn($tableName, 'store_id');

        $this->dropColumn($tableName, 'payed');
        $this->dropColumn($tableName, 'canceled');
        $this->dropColumn($tableName, 'account_id');
        $this->dropColumn($tableName, 'xml_id');
    }

    public function safeDown()
    {
        echo "m180726_171201__alter_table__shop_order cannot be reverted.\n";
        return false;
    }
}