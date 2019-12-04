<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m191119_140601__create_table__shop_store extends Migration
{
    public function safeUp()
    {
        $tableName = 'shop_product_quantity_change';
        /*$tableExist = $this->db->getTableSchema($tableName, true);
        if (!$tableExist) {
            return true;
        }*/
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB AUTO_INCREMENT=1001';
        }

        $this->dropForeignKey("shop_product_quantity_change__measure_id", $tableName);

        $this->renameColumn($tableName, "measure_id", "measure_code");
        $this->alterColumn($tableName, "measure_code", $this->string(3));

        $this->delete($tableName);

    }

    public function safeDown()
    {
        echo "m191119_140601__create_table__shop_store cannot be reverted.\n";
        return false;
    }
}