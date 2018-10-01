<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m150910_170601_create_table__kladr_location extends Migration
{
    public function safeUp()
    {
        $tableExist = $this->db->getTableSchema("{{%kladr_location}}", true);
        if ($tableExist) {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable("{{%kladr_location}}", [
            'id' => $this->primaryKey(),

            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),

            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),

            'name'       => $this->string(255)->notNull(),
            'name_short' => $this->string(255),
            'name_full'  => $this->string(255),

            'zip' => $this->string(20),

            'okato' => $this->string(20),
            'type'  => $this->string(10)->notNull(),

            'kladr_api_id' => $this->string(20),

            'active' => $this->string(1)->notNull()->defaultValue("Y"),

            'parent_id' => $this->integer(),
            'sort'      => $this->integer(),

        ], $tableOptions);


        $this->createIndex('kladr_location__updated_by', '{{%kladr_location}}', 'updated_by');
        $this->createIndex('kladr_location__created_by', '{{%kladr_location}}', 'created_by');
        $this->createIndex('kladr_location__created_at', '{{%kladr_location}}', 'created_at');
        $this->createIndex('kladr_location__updated_at', '{{%kladr_location}}', 'updated_at');

        $this->createIndex('kladr_location__name', '{{%kladr_location}}', 'name');
        $this->createIndex('kladr_location__name_short', '{{%kladr_location}}', 'name_short');
        $this->createIndex('kladr_location__name_full', '{{%kladr_location}}', 'name_full');
        $this->createIndex('kladr_location__zip', '{{%kladr_location}}', 'zip');
        $this->createIndex('kladr_location__okato', '{{%kladr_location}}', 'okato');
        $this->createIndex('kladr_location__type', '{{%kladr_location}}', 'type');
        $this->createIndex('kladr_location__active', '{{%kladr_location}}', 'active');

        $this->createIndex('kladr_location__parent_sort', '{{%kladr_location}}', ['parent_id', 'sort']);

        $this->addForeignKey(
            'kladr_location_created_by', "{{%kladr_location}}",
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'kladr_location_updated_by', "{{%kladr_location}}",
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->insert("{{%kladr_location}}", [
            'name' => 'Россия',
            'type' => 'country',
        ]);
    }

    public function safeDown()
    {
        $this->dropForeignKey("kladr_location_updated_by", "{{%kladr_location}}");
        $this->dropForeignKey("kladr_location_updated_by", "{{%kladr_location}}");

        $this->dropTable("{{%kladr_location}}");
    }
}