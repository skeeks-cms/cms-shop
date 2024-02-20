<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m240123_152302__create_table__shop_collection extends Migration
{
    public function safeUp()
    {
        $tableName = 'shop_collection';
        $tableExist = $this->db->getTableSchema($tableName, true);

        if ($tableExist) {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable($tableName, [

            'id' => $this->primaryKey(),

            'created_at' => $this->integer()->null(),
            'updated_at' => $this->integer()->null(),
            'created_by' => $this->integer()->null(),

            //Основная информация
            'name' => $this->string(255)->notNull()->comment("Название коллекции"),

            'is_active' => $this->integer(1)->notNull()->defaultValue(1),

            'description_short' => $this->text()->null()->comment("Описание короткое"),
            'description_full' => $this->text()->null()->comment("Описание подробное"),

            'cms_image_id' => $this->integer()->null()->comment("Главное фото"),
            'shop_brand_id' => $this->integer()->comment("Бренд"),

            //Страница на сайте
            'code' => $this->string(255)->notNull()->comment("Формирование url коллекции на сайте"),
            'seo_h1' => $this->string(255)->null(),
            'meta_title' => $this->string(255)->null(),
            'meta_description' => $this->text()->null(),
            'meta_keywords' => $this->text()->null(),

            'priority' => $this->integer()->notNull()->defaultValue(500)->comment("Сортировка"),

            'external_id' => $this->string(255)->null()->unique()->comment("Внешний код"),

        ], $tableOptions);

        $this->addCommentOnTable($tableName, "Коллекции");

        $this->createIndex($tableName.'__name', $tableName, 'name');
        $this->createIndex($tableName.'__is_active', $tableName, 'is_active');
        $this->createIndex($tableName.'__cms_image_id', $tableName, 'cms_image_id');
        $this->createIndex($tableName.'__code', $tableName, 'code');
        $this->createIndex($tableName.'__created_at', $tableName, 'created_at');
        $this->createIndex($tableName.'__updated_at', $tableName, 'updated_at');
        $this->createIndex($tableName.'__created_by', $tableName, 'created_by');

        $this->addForeignKey(
            "{$tableName}__created_by", $tableName,
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            "{$tableName}__cms_image_id", $tableName,
            'cms_image_id', '{{%cms_storage_file}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            "{$tableName}__shop_brand_id", $tableName,
            'shop_brand_id', '{{%shop_brand}}', 'id', 'RESTRICT', 'RESTRICT'
        );
    }

    public function safeDown()
    {
        echo "m200212_130601__create_table__shop_supplier_property_option cannot be reverted.\n";
        return false;
    }
}