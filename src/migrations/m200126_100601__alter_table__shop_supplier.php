<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200126_100601__alter_table__shop_supplier extends Migration
{
    public function safeUp()
    {
        $tableName = 'shop_supplier';
        /*$tableExist = $this->db->getTableSchema($tableName, true);
        if (!$tableExist) {
            return true;
        }*/
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->addColumn($tableName, "is_main", $this->integer(1)->notNull()->defaultValue(0));

        $this->createIndex($tableName.'__is_main', $tableName, 'is_main');
    }

    public function safeDown()
    {
        echo "m200126_100601__alter_table__shop_supplier cannot be reverted.\n";
        return false;
    }
}