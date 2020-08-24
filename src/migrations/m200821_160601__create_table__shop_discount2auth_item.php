<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200821_160601__create_table__shop_discount2auth_item extends Migration
{
    public function safeUp()
    {
        $tableName = 'shop_discount2auth_item';
        $tableExist = $this->db->getTableSchema($tableName, true);

        if ($tableExist) {
            //return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable($tableName, [

            'id' => $this->primaryKey(),

            'shop_discount_id' => $this->integer()->notNull(),
            'auth_item_name' => $this->string(64)->notNull(),

        ], $tableOptions);

        $this->createIndex($tableName . '__unique', $tableName, ['shop_discount_id', 'auth_item_name'], true);

        $this->addCommentOnTable($tableName, 'Связь скидки с ролью пользователя');

        $this->addForeignKey(
            "{$tableName}__shop_discount_id", $tableName,
            'shop_discount_id', '{{%shop_discount}}', 'id', 'CASCADE', 'CASCADE'
        );

        $this->addForeignKey(
            "{$tableName}__auth_item_name", $tableName,
            'auth_item_name', '{{%auth_item}}', 'name', 'CASCADE', 'CASCADE'
        );

    }

    public function safeDown()
    {
        echo "m200507_110601__create_table__shop_product_relation cannot be reverted.\n";
        return false;
    }
}