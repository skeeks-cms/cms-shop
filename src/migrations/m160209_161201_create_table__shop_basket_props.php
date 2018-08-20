<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 17.09.2015
 */

use yii\db\Migration;

class m160209_161201_create_table__shop_basket_props extends Migration
{
    public function safeUp()
    {
        $tableExist = $this->db->getTableSchema("{{%shop_basket_props}}", true);
        if ($tableExist) {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable("{{%shop_basket_props}}", [
            'id' => $this->primaryKey(),

            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),

            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),

            'shop_basket_id' => $this->integer()->notNull(),

            'name'     => $this->string(255)->notNull(),
            'value'    => $this->string(255),
            'code'     => $this->string(255),
            'priority' => $this->integer()->notNull()->defaultValue(100),

        ], $tableOptions);


        $this->createIndex('shop_basket_props__updated_by', '{{%shop_basket_props}}', 'updated_by');
        $this->createIndex('shop_basket_props__created_by', '{{%shop_basket_props}}', 'created_by');
        $this->createIndex('shop_basket_props__created_at', '{{%shop_basket_props}}', 'created_at');
        $this->createIndex('shop_basket_props__updated_at', '{{%shop_basket_props}}', 'updated_at');

        $this->createIndex('shop_basket_props__shop_basket_id', '{{%shop_basket_props}}', 'shop_basket_id');
        $this->createIndex('shop_basket_props__name', '{{%shop_basket_props}}', 'name');
        $this->createIndex('shop_basket_props__value', '{{%shop_basket_props}}', 'value');
        $this->createIndex('shop_basket_props__code', '{{%shop_basket_props}}', 'code');
        $this->createIndex('shop_basket_props__priority', '{{%shop_basket_props}}', 'priority');

        $this->addForeignKey(
            'shop_basket_props_created_by', "{{%shop_basket_props}}",
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_basket_props_updated_by', "{{%shop_basket_props}}",
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );


        $this->addForeignKey(
            'shop_basket_props__shop_basket_id', "{{%shop_basket_props}}",
            'shop_basket_id', '{{%shop_basket}}', 'id', 'CASCADE', 'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey("shop_basket_props_created_by", "{{%shop_basket_props}}");
        $this->dropForeignKey("shop_basket_props_updated_by", "{{%shop_basket_props}}");
        $this->dropForeignKey("shop_basket_props__shop_basket_id", "{{%shop_basket_props}}");

        $this->dropTable("{{%shop_basket_props}}");
    }
}