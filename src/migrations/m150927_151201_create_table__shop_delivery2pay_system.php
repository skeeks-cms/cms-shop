<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 17.09.2015
 */

use yii\db\Schema;
use yii\db\Migration;

class m150927_151201_create_table__shop_delivery2pay_system extends Migration
{
    public function safeUp()
    {
        $tableExist = $this->db->getTableSchema("{{%shop_delivery2pay_system}}", true);
        if ($tableExist) {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable("{{%shop_delivery2pay_system}}", [
            'id' => $this->primaryKey(),

            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),

            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),

            'pay_system_id' => $this->integer()->notNull(),
            'delivery_id' => $this->integer()->notNull(),

        ], $tableOptions);


        $this->createIndex('updated_by', '{{%shop_delivery2pay_system}}', 'updated_by');
        $this->createIndex('created_by', '{{%shop_delivery2pay_system}}', 'created_by');
        $this->createIndex('created_at', '{{%shop_delivery2pay_system}}', 'created_at');
        $this->createIndex('updated_at', '{{%shop_delivery2pay_system}}', 'updated_at');


        $this->createIndex('shop_delivery2pay_system', '{{%shop_delivery2pay_system}}',
            ['pay_system_id', 'delivery_id'], true);


        $this->execute("ALTER TABLE {{%shop_delivery2pay_system}} COMMENT = 'Службы доставки с платежными системами';");

        $this->addForeignKey(
            'shop_delivery2pay_system_created_by', "{{%shop_delivery2pay_system}}",
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_delivery2pay_system_updated_by', "{{%shop_delivery2pay_system}}",
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_delivery2pay_system__shop_pay_system', "{{%shop_delivery2pay_system}}",
            'pay_system_id', '{{%shop_pay_system}}', 'id', 'CASCADE', 'CASCADE'
        );

        $this->addForeignKey(
            'shop_delivery2pay_system__shop_delivery', "{{%shop_delivery2pay_system}}",
            'delivery_id', '{{%shop_delivery}}', 'id', 'CASCADE', 'CASCADE'
        );

    }

    public function safeDown()
    {
        $this->dropTable("{{%shop_delivery2pay_system}}");
    }
}