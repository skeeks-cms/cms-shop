<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */
use yii\db\Schema;
use yii\db\Migration;

class m150915_100601_create_table__shop_content extends Migration
{
    public function safeUp()
    {
        $tableExist = $this->db->getTableSchema("{{%shop_content}}", true);
        if ($tableExist)
        {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable("{{%shop_content}}", [
            'id'                    => $this->primaryKey(),

            'created_by'            => $this->integer(),
            'updated_by'            => $this->integer(),

            'created_at'            => $this->integer(),
            'updated_at'            => $this->integer(),

            'content_id'            => $this->integer()->notNull()->unique(),

            'yandex_export'         => $this->string(1)->notNull()->defaultValue("N"),
            'subscription'          => $this->string(1)->notNull()->defaultValue("N"),

            'vat_id'                => $this->integer(),

        ], $tableOptions);


        $this->createIndex('updated_by', '{{%shop_content}}', 'updated_by');
        $this->createIndex('created_by', '{{%shop_content}}', 'created_by');
        $this->createIndex('created_at', '{{%shop_content}}', 'created_at');
        $this->createIndex('updated_at', '{{%shop_content}}', 'updated_at');


        $this->createIndex('yandex_export', '{{%shop_content}}', 'yandex_export');
        $this->createIndex('subscription', '{{%shop_content}}', 'subscription');

        $this->execute("ALTER TABLE {{%shop_content}} COMMENT = 'Связь контента с магазином';");

        $this->addForeignKey(
            'shop_content_created_by', "{{%shop_content}}",
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_content_updated_by', "{{%shop_content}}",
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_content_shop_vat', "{{%shop_content}}",
            'vat_id', '{{%shop_vat}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_content_content_id', "{{%shop_content}}",
            'content_id', '{{%cms_content}}', 'id', 'CASCADE', 'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey("shop_content_updated_by", "{{%shop_content}}");
        $this->dropForeignKey("shop_content_updated_by", "{{%shop_content}}");
        $this->dropForeignKey("shop_content_shop_vat", "{{%shop_content}}");
        $this->dropForeignKey("shop_content_content_id", "{{%shop_content}}");

        $this->dropTable("{{%shop_content}}");
    }
}