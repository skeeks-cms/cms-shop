<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m240120_152310__delete_table__shop_content extends Migration
{
    public function safeUp()
    {
        $tableName = 'shop_content';
        $tableExist = $this->db->getTableSchema($tableName, true);

        if ($tableExist) {
            return true;
        }

        $this->delete($tableName);
    }

    public function safeDown()
    {
        echo "m200212_130601__create_table__shop_supplier_property_option cannot be reverted.\n";
        return false;
    }
}