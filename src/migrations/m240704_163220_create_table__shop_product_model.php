<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */
use yii\db\Migration;

class m240704_163220_create_table__shop_product_model extends Migration
{
    public function safeUp()
    {
        $tableName = 'shop_product_model';
        $tableExist = $this->db->getTableSchema($tableName, true);
        if ($tableExist) {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable($tableName, [
            'id' => $this->primaryKey(),

            'created_at' => $this->integer(),

        ], $tableOptions);

        $this->createIndex($tableName.'__created_at', $tableName, 'created_at');
    }

    public function safeDown()
    {}
}