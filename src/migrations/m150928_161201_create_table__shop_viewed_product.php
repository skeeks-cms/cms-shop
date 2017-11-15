<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 17.09.2015
 */

use yii\db\Schema;
use yii\db\Migration;

class m150928_161201_create_table__shop_viewed_product extends Migration
{
    public function safeUp()
    {
        $tableExist = $this->db->getTableSchema("{{%shop_viewed_product}}", true);
        if ($tableExist) {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable("{{%shop_viewed_product}}", [
            'id' => $this->primaryKey(),

            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),

            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),

            'shop_fuser_id' => $this->integer()->notNull(),
            'shop_product_id' => $this->integer()->notNull(),

            'site_id' => $this->integer()->notNull(),

            'name' => $this->string(255),
            'url' => $this->string(255),

        ], $tableOptions);


        $this->createIndex('updated_by', '{{%shop_viewed_product}}', 'updated_by');
        $this->createIndex('created_by', '{{%shop_viewed_product}}', 'created_by');
        $this->createIndex('created_at', '{{%shop_viewed_product}}', 'created_at');
        $this->createIndex('updated_at', '{{%shop_viewed_product}}', 'updated_at');

        $this->createIndex('shop_fuser_id', '{{%shop_viewed_product}}', 'shop_fuser_id');
        $this->createIndex('shop_product_id', '{{%shop_viewed_product}}', 'shop_product_id');
        $this->createIndex('site_id', '{{%shop_viewed_product}}', 'site_id');


        $this->execute("ALTER TABLE {{%shop_viewed_product}} COMMENT = 'Ранее просмотренные товары';");

        $this->addForeignKey(
            'shop_viewed_product_created_by', "{{%shop_viewed_product}}",
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_viewed_product_updated_by', "{{%shop_viewed_product}}",
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );


        $this->addForeignKey(
            'shop_viewed_product__shop_fuser_id', "{{%shop_viewed_product}}",
            'shop_fuser_id', '{{%shop_fuser}}', 'id', 'CASCADE', 'CASCADE'
        );

        $this->addForeignKey(
            'shop_viewed_product__shop_product_id', "{{%shop_viewed_product}}",
            'shop_product_id', '{{%shop_product}}', 'id', 'CASCADE', 'CASCADE'
        );

        $this->addForeignKey(
            'shop_viewed_product__shop_product_id_c', "{{%shop_viewed_product}}",
            'shop_product_id', '{{%cms_content_element}}', 'id', 'CASCADE', 'CASCADE'
        );

        $this->addForeignKey(
            'shop_viewed_product__site_id', "{{%shop_viewed_product}}",
            'site_id', '{{%cms_site}}', 'id', 'CASCADE', 'CASCADE'
        );


    }

    public function safeDown()
    {
        $this->dropTable("{{%shop_viewed_product}}");
    }
}