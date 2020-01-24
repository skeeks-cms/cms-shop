<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200124_010601__alter_table__shop_product extends Migration
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

        $this->addColumn($tableName, "main_pid", $this->integer()->comment('Главная родительская карточка товара.'));

        $this->createIndex($tableName.'__main_pid', $tableName, 'main_pid', true);


        $this->addForeignKey(
            "{$tableName}__main_pid", $tableName,
            'main_pid', '{{%shop_product}}', 'id', 'SET NULL', 'SET NULL'
        );
    }

    public function safeDown()
    {
        echo "m200124_010601__alter_table__shop_product cannot be reverted.\n";
        return false;
    }
}