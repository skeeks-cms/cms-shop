<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m150828_120559_create_table__shop_typ_price extends Migration
{
    public function safeUp()
    {
        $tableExist = $this->db->getTableSchema("{{%shop_type_price}}", true);
        if ($tableExist) {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable("{{%shop_type_price}}", [
            'id' => $this->primaryKey(),

            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),

            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),

            'code'   => $this->string(32)->notNull()->unique(),
            'xml_id' => $this->string(255),

            'name'        => $this->string(255)->notNull(),
            'description' => $this->text(),
            'priority'    => $this->integer()->notNull()->defaultValue(100),

            'def' => $this->string(1)->notNull()->defaultValue('N'),

        ], $tableOptions);


        $this->createIndex('shop_type_price__updated_by', '{{%shop_type_price}}', 'updated_by');
        $this->createIndex('shop_type_price__created_by', '{{%shop_type_price}}', 'created_by');
        $this->createIndex('shop_type_price__created_at', '{{%shop_type_price}}', 'created_at');
        $this->createIndex('shop_type_price__updated_at', '{{%shop_type_price}}', 'updated_at');

        $this->createIndex('shop_type_price__name', '{{%shop_type_price}}', 'name');
        $this->createIndex('shop_type_price__priority', '{{%shop_type_price}}', 'priority');
        $this->createIndex('shop_type_price__def', '{{%shop_type_price}}', 'def');
        $this->createIndex('shop_type_price__xml_id', '{{%shop_type_price}}', 'xml_id');

        $this->addForeignKey(
            'shop_type_price_created_by', "{{%shop_type_price}}",
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_type_price_updated_by', "{{%shop_type_price}}",
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );


        $this->insert('{{%shop_type_price}}', [
            'code'        => 'BASE',
            'name'        => 'Базовая цена',
            'description' => '',
            'priority'    => '100',
            'def'         => 'Y',
        ]);


        $this->insert('{{%shop_type_price}}', [
            'code'        => 'WHOLESALE',
            'name'        => 'Розничная цена',
            'description' => '',
            'priority'    => '200',
            'def'         => 'N',
        ]);
        $this->insert('{{%shop_type_price}}', [
            'code'        => 'RETAIL',
            'name'        => 'Оптовая цена',
            'description' => '',
            'priority'    => '300',
            'def'         => 'N',
        ]);
    }

    public function safeDown()
    {
        $this->dropForeignKey("shop_type_price_updated_by", "{{%shop_type_price}}");
        $this->dropForeignKey("shop_type_price_updated_by", "{{%shop_type_price}}");

        $this->dropTable("{{%shop_type_price}}");
    }
}