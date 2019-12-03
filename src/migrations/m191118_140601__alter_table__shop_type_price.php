<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m191118_140601__alter_table__shop_type_price extends Migration
{
    public function safeUp()
    {
        $tableName = 'shop_type_price';
        /*$tableExist = $this->db->getTableSchema($tableName, true);
        if (!$tableExist) {
            return true;
        }*/
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB AUTO_INCREMENT=1001';
        }

        $this->dropColumn($tableName, "xml_id");
        $this->dropColumn($tableName, "code");
        $this->dropColumn($tableName, "def");
    }

    public function safeDown()
    {
        echo "m191118_140601__alter_table__shop_type_price cannot be reverted.\n";
        return false;
    }
}