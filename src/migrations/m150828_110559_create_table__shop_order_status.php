<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Schema;
use yii\db\Migration;

class m150828_110559_create_table__shop_order_status extends Migration
{
    public function safeUp()
    {
        $tableExist = $this->db->getTableSchema("{{%shop_order_status}}", true);
        if ($tableExist) {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable("{{%shop_order_status}}", [
            'id' => $this->primaryKey(),

            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),

            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),

            'code' => $this->string(2)->notNull()->unique(),

            'name' => $this->string(255)->notNull(),
            'description' => $this->text(),
            'priority' => $this->integer()->notNull()->defaultValue(100),

            'color' => $this->string(32),

        ], $tableOptions);


        $this->createIndex('shop_order_status__updated_by', '{{%shop_order_status}}', 'updated_by');
        $this->createIndex('shop_order_status__created_by', '{{%shop_order_status}}', 'created_by');
        $this->createIndex('shop_order_status__created_at', '{{%shop_order_status}}', 'created_at');
        $this->createIndex('shop_order_status__updated_at', '{{%shop_order_status}}', 'updated_at');

        $this->createIndex('shop_order_status__name', '{{%shop_order_status}}', 'name');
        $this->createIndex('shop_order_status__priority', '{{%shop_order_status}}', 'priority');
        $this->createIndex('shop_order_status__color', '{{%shop_order_status}}', 'color');

        $this->addForeignKey(
            'shop_order_status_created_by', "{{%shop_order_status}}",
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_order_status_updated_by', "{{%shop_order_status}}",
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );


        $this->insert('{{%shop_order_status}}', [
            'code' => 'F',
            'name' => 'Выполнен',
            'description' => 'Заказ доставлен и оплачен',
            'priority' => '100',
            'color' => 'green',
        ]);

        $this->insert('{{%shop_order_status}}', [
            'code' => 'N',
            'name' => 'Принят',
            'description' => 'Заказ принят, но пока не обрабатывается (например, заказ только что создан или ожидается оплата заказа)',
            'priority' => '200',
            'color' => 'orange',
        ]);

    }

    public function safeDown()
    {
        $this->dropForeignKey("shop_order_status_updated_by", "{{%shop_order_status}}");
        $this->dropForeignKey("shop_order_status_updated_by", "{{%shop_order_status}}");

        $this->dropTable("{{%shop_order_status}}");
    }
}