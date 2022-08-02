<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m220731_130601__create_table__shop_store_product_move extends Migration
{
    public function safeUp()
    {
        $tableName = 'shop_store_product_move';
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

            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),

            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),

            'is_active' => $this->integer(1)->defaultValue(1)->notNull()->comment("Документ проведен?"),

            'shop_store_doc_move_id' => $this->integer()->notNull()->comment("Документ"),

            'product_name' => $this->string(255)->notNull()->comment("Название товара"),
            'shop_store_product_id' => $this->integer()->comment("Товар"),
            
            'quantity'   => $this->decimal(18, 4)->notNull()->defaultValue(0)->comment("Количество"),

            'price' => $this->decimal(18, 2)->notNull()->defaultValue(0)->comment("Цена"),

        ], $tableOptions);

        $this->addCommentOnTable($tableName, "Движение товара");

        $this->createIndex($tableName.'__updated_by', $tableName, 'updated_by');
        $this->createIndex($tableName.'__created_by', $tableName, 'created_by');
        $this->createIndex($tableName.'__created_at', $tableName, 'created_at');
        $this->createIndex($tableName.'__updated_at', $tableName, 'updated_at');

        $this->createIndex($tableName.'__is_active', $tableName, 'is_active');
        
        $this->createIndex($tableName.'__shop_store_doc_move_id', $tableName, 'shop_store_doc_move_id');
        $this->createIndex($tableName.'__shop_store_product_id', $tableName, 'shop_store_product_id');
        
        $this->createIndex($tableName.'__quantity', $tableName, 'quantity');

        $this->createIndex($tableName.'__price', $tableName, 'price');


        $this->addForeignKey(
            "{$tableName}__created_by", $tableName,
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            "{$tableName}__updated_by", $tableName,
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        //Удалив документ все позиции удаляются
        $this->addForeignKey(
            "{$tableName}__shop_store_doc_move_id", $tableName,
            'shop_store_doc_move_id', '{{%shop_store_doc_move}}', 'id', 'CASCADE', 'CASCADE'
        );
        
        //Удалив товар, позиция в этом документе остается
        $this->addForeignKey(
            "{$tableName}__shop_store_product_id", $tableName,
            'shop_store_product_id', '{{%shop_store_product}}', 'id', 'SET NULL', 'CASCADE'
        );

    }

    public function safeDown()
    {
        echo "m200212_130601__create_table__shop_supplier_property_option cannot be reverted.\n";
        return false;
    }
}