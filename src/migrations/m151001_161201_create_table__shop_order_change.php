<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 17.09.2015
 */

use yii\db\Schema;
use yii\db\Migration;

class m151001_161201_create_table__shop_order_change extends Migration
{
    public function safeUp()
    {
        $tableExist = $this->db->getTableSchema("{{%shop_order_change}}", true);
        if ($tableExist) {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable("{{%shop_order_change}}", [
            'id' => $this->primaryKey(),

            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),

            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),

            'shop_order_id' => $this->integer()->notNull(),

            'type' => $this->string(255)->notNull(),
            'data' => $this->text(),

        ], $tableOptions);


        $this->createIndex('updated_by', '{{%shop_order_change}}', 'updated_by');
        $this->createIndex('created_by', '{{%shop_order_change}}', 'created_by');
        $this->createIndex('created_at', '{{%shop_order_change}}', 'created_at');
        $this->createIndex('updated_at', '{{%shop_order_change}}', 'updated_at');

        $this->createIndex('shop_order_id', '{{%shop_order_change}}', 'shop_order_id');
        $this->createIndex('type', '{{%shop_order_change}}', 'type');


        $this->execute("ALTER TABLE {{%shop_order_change}} COMMENT = 'История по заказу';");

        $this->addForeignKey(
            'shop_order_change_created_by', "{{%shop_order_change}}",
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_order_change_updated_by', "{{%shop_order_change}}",
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );


        $this->addForeignKey(
            'shop_order_change__shop_order_id', "{{%shop_order_change}}",
            'shop_order_id', '{{%shop_order}}', 'id', 'CASCADE', 'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropTable("{{%shop_order_change}}");
    }
}