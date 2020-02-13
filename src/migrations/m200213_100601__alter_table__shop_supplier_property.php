<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200213_100601__alter_table__shop_supplier_property extends Migration
{
    public function safeUp()
    {
        $tableName = 'shop_supplier_property';
        /*$tableExist = $this->db->getTableSchema($tableName, true);
        if (!$tableExist) {
            return true;
        }*/
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->addColumn($tableName, "import_delimetr", $this->text());
        $this->addColumn($tableName, "import_replace", $this->text());
        $this->addColumn($tableName, "import_miltiple", $this->decimal(18,8));
    }

    public function safeDown()
    {
        echo "m200213_100601__alter_table__shop_supplier_property cannot be reverted.\n";
        return false;
    }
}