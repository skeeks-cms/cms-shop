<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m191119_140601__create_table__shop_store extends Migration
{
    public function safeUp()
    {
        $tableName = 'shop_store';
        $tableExist = $this->db->getTableSchema($tableName, true);
        if ($tableExist) {
            $this->dropTable($tableName);
            //return true;
        }
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable($tableName, [
            'id' => $this->primaryKey(),

            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),

            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),

            'shop_supplier_id' => $this->integer()->notNull(),
            'name'             => $this->string(255)->notNull(),
            'description'      => $this->text(),

            'cms_image_id' => $this->integer(),

            'is_active' => $this->integer(1)->notNull()->defaultValue(1),

        ], $tableOptions);


        $this->createIndex($tableName.'__updated_by', $tableName, 'updated_by');
        $this->createIndex($tableName.'__created_by', $tableName, 'created_by');
        $this->createIndex($tableName.'__created_at', $tableName, 'created_at');
        $this->createIndex($tableName.'__updated_at', $tableName, 'updated_at');

        $this->createIndex($tableName.'__name', $tableName, 'name');
        $this->createIndex($tableName.'__is_active', $tableName, 'is_active');
        $this->createIndex($tableName.'__cms_image_id', $tableName, 'cms_image_id');
        $this->createIndex($tableName.'__shop_supplier_id', $tableName, 'shop_supplier_id');

        $this->addCommentOnTable($tableName, 'Склады');

        $this->addForeignKey(
            "{$tableName}__created_by", $tableName,
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            "{$tableName}__updated_by", $tableName,
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            "{$tableName}__cms_image_id", $tableName,
            'cms_image_id', '{{%cms_storage_file}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            "{$tableName}__shop_supplier_id", $tableName,
            'shop_supplier_id', '{{%shop_supplier}}', 'id', 'RESTRICT', 'RESTRICT'
        );

    }

    public function safeDown()
    {
        echo "m191119_130601__create_table__shop_supplier cannot be reverted.\n";
        return false;
    }
}