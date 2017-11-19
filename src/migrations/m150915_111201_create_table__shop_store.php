<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 17.09.2015
 */

use yii\db\Schema;
use yii\db\Migration;

class m150915_111201_create_table__shop_store extends Migration
{
    public function safeUp()
    {
        $tableExist = $this->db->getTableSchema("{{%shop_store}}", true);
        if ($tableExist) {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable("{{%shop_store}}", [
            'id' => $this->primaryKey(),

            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),

            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),

            'name' => $this->string(255)->notNull(),

            'active' => $this->string(1)->notNull()->defaultValue("Y"),
            'address' => $this->string(255)->notNull(),
            'description' => $this->text(),
            'gps_n' => $this->string(15)->notNull()->defaultValue(0),
            'gps_s' => $this->string(15)->notNull()->defaultValue(0),

            'image_id' => $this->integer(),
            'location_id' => $this->integer(),

            'phone' => $this->string(255),
            'schedule' => $this->string(255),
            'xml_id' => $this->string(255),
            'priority' => $this->integer()->notNull()->defaultValue(100),
            'email' => $this->string(255),

            'issuing_center' => $this->string(1)->notNull()->defaultValue('Y'),
            'shipping_center' => $this->string(1)->notNull()->defaultValue('Y'),

            'site_code' => $this->string(15),


        ], $tableOptions);


        $this->createIndex('shop_store__updated_by', '{{%shop_store}}', 'updated_by');
        $this->createIndex('shop_store__created_by', '{{%shop_store}}', 'created_by');
        $this->createIndex('shop_store__created_at', '{{%shop_store}}', 'created_at');
        $this->createIndex('shop_store__updated_at', '{{%shop_store}}', 'updated_at');


        $this->createIndex('shop_store__name', '{{%shop_store}}', 'name');
        $this->createIndex('shop_store__address', '{{%shop_store}}', 'address');
        $this->createIndex('shop_store__gps_n', '{{%shop_store}}', ['gps_n']);
        $this->createIndex('shop_store__gps_s', '{{%shop_store}}', ['gps_s']);
        $this->createIndex('shop_store__image_id', '{{%shop_store}}', ['image_id']);
        $this->createIndex('shop_store__location_id', '{{%shop_store}}', ['location_id']);
        $this->createIndex('shop_store__phone', '{{%shop_store}}', ['phone']);
        $this->createIndex('shop_store__schedule', '{{%shop_store}}', ['schedule']);
        $this->createIndex('shop_store__xml_id', '{{%shop_store}}', ['xml_id']);
        $this->createIndex('shop_store__priority', '{{%shop_store}}', ['priority']);
        $this->createIndex('shop_store__email', '{{%shop_store}}', ['email']);
        $this->createIndex('shop_store__issuing_center', '{{%shop_store}}', ['issuing_center']);
        $this->createIndex('shop_store__shipping_center', '{{%shop_store}}', ['shipping_center']);
        $this->createIndex('shop_store__site_code', '{{%shop_store}}', ['site_code']);

        $this->addForeignKey(
            'shop_store_created_by', "{{%shop_store}}",
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_store_updated_by', "{{%shop_store}}",
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_store__site_code', "{{%shop_store}}",
            'site_code', '{{%cms_site}}', 'code', 'RESTRICT', 'CASCADE'
        );

        $this->addForeignKey(
            'shop_store__image_id', "{{%shop_store}}",
            'image_id', '{{%cms_storage_file}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_store__location_id', "{{%shop_store}}",
            'location_id', '{{%kladr_location}}', 'id', 'SET NULL', 'SET NULL'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey("shop_store_updated_by", "{{%shop_store}}");
        $this->dropForeignKey("shop_store_updated_by", "{{%shop_store}}");
        $this->dropForeignKey("shop_store__site_code", "{{%shop_store}}");
        $this->dropForeignKey("shop_store__image_id", "{{%shop_store}}");
        $this->dropForeignKey("shop_store__location_id", "{{%shop_store}}");

        $this->dropTable("{{%shop_store}}");
    }
}