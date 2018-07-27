<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 17.09.2015
 */

use yii\db\Migration;

class m151001_201201_create_table__shop_discount2type_price extends Migration
{
    public function safeUp()
    {
        $tableExist = $this->db->getTableSchema("{{%shop_discount2type_price}}", true);
        if ($tableExist) {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable("{{%shop_discount2type_price}}", [
            'id' => $this->primaryKey(),

            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),

            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),

            'discount_id'   => $this->integer()->notNull(),
            'type_price_id' => $this->integer()->notNull(),
        ], $tableOptions);


        $this->createIndex('shop_discount2type_price__updated_by', '{{%shop_discount2type_price}}', 'updated_by');
        $this->createIndex('shop_discount2type_price__created_by', '{{%shop_discount2type_price}}', 'created_by');
        $this->createIndex('shop_discount2type_price__created_at', '{{%shop_discount2type_price}}', 'created_at');
        $this->createIndex('shop_discount2type_price__updated_at', '{{%shop_discount2type_price}}', 'updated_at');

        $this->createIndex('discount_id__type_price_id', '{{%shop_discount2type_price}}',
            ['discount_id', 'type_price_id'], true);

        $this->addForeignKey(
            'shop_discount2type_price_created_by', "{{%shop_discount2type_price}}",
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_discount2type_price_updated_by', "{{%shop_discount2type_price}}",
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_discount2type_price__type_price_id', "{{%shop_discount2type_price}}",
            'type_price_id', '{{%shop_type_price}}', 'id', 'CASCADE', 'CASCADE'
        );

        $this->addForeignKey(
            'shop_discount2type_price__discount_id', "{{%shop_discount2type_price}}",
            'discount_id', '{{%shop_discount}}', 'id', 'CASCADE', 'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropTable("{{%shop_discount2type_price}}");
    }
}