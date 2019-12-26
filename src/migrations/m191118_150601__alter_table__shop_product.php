<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m191118_150601__alter_table__shop_product extends Migration
{
    public function safeUp()
    {
        $tableName = 'shop_product';
        /*$tableExist = $this->db->getTableSchema($tableName, true);
        if (!$tableExist) {
            return true;
        }*/
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->dropForeignKey("shop_product_measure", $tableName);

        $this->renameColumn($tableName, "measure_id", "measure_code");
        $this->alterColumn($tableName, "measure_code", $this->string(3));

        $this->update($tableName, ["measure_code" => "796"]);

    }

    public function safeDown()
    {
        echo "m191118_150601__alter_table__shop_product cannot be reverted.\n";
        return false;
    }
}