<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */
use yii\db\Schema;
use yii\db\Migration;

class m150925_180601_create_table__shop_buyer extends Migration
{
    public function safeUp()
    {
        $tableExist = $this->db->getTableSchema("{{%shop_buyer}}", true);
        if ($tableExist)
        {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable("{{%shop_buyer}}", [
            'id'                    => $this->primaryKey(),

            'created_by'            => $this->integer(),
            'updated_by'            => $this->integer(),

            'created_at'            => $this->integer(),
            'updated_at'            => $this->integer(),

            'name'                  => $this->string()->notNull(),

            'cms_user_id'           => $this->integer()->notNull(),
            'shop_person_type_id'   => $this->integer()->notNull(),

        ], $tableOptions);


        $this->createIndex('updated_by', '{{%shop_buyer}}', 'updated_by');
        $this->createIndex('created_by', '{{%shop_buyer}}', 'created_by');
        $this->createIndex('created_at', '{{%shop_buyer}}', 'created_at');
        $this->createIndex('updated_at', '{{%shop_buyer}}', 'updated_at');

        $this->createIndex('cms_user_id', '{{%shop_buyer}}', 'cms_user_id');
        $this->createIndex('shop_person_type_id', '{{%shop_buyer}}', 'shop_person_type_id');
        $this->createIndex('name', '{{%shop_buyer}}', 'name');

        $this->execute("ALTER TABLE {{%shop_buyer}} COMMENT = 'Покупатели';");

        $this->addForeignKey(
            'shop_buyer_created_by', "{{%shop_buyer}}",
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_buyer_updated_by', "{{%shop_buyer}}",
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_buyer_cms__user_id', "{{%shop_buyer}}",
            'cms_user_id', '{{%cms_user}}', 'id', 'CASCADE', 'CASCADE'
        );

        $this->addForeignKey(
            'shop_buyer_cms__shop_person_type_id', "{{%shop_buyer}}",
            'shop_person_type_id', '{{%shop_person_type}}', 'id', 'CASCADE', 'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey("shop_buyer_updated_by", "{{%shop_buyer}}");
        $this->dropForeignKey("shop_buyer_updated_by", "{{%shop_buyer}}");
        $this->dropForeignKey("shop_buyer_cms__user_id", "{{%shop_buyer}}");
        $this->dropForeignKey("shop_buyer_cms__shop_person_type_id", "{{%shop_buyer}}");

        $this->dropTable("{{%shop_buyer}}");
    }
}