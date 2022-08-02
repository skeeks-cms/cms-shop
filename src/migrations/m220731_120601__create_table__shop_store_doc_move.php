<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m220731_120601__create_table__shop_store_doc_move extends Migration
{
    public function safeUp()
    {
        $tableName = 'shop_store_doc_move';
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

            'doc_type' => $this->string()->notNull()->defaultValue("correction")->comment("Тип операции"),
            'shop_store_id'   => $this->integer()->notNull()->comment("Магазин"),

            'shop_order_id'   => $this->integer()->comment("Заказ"),
            'client_cms_user_id'   => $this->integer()->comment("Клиент"),

            'comment'   => $this->text()->comment("Комментарий"),

        ], $tableOptions);

        $this->addCommentOnTable($tableName, "Движение товара");

        $this->createIndex($tableName.'__updated_by', $tableName, 'updated_by');
        $this->createIndex($tableName.'__created_by', $tableName, 'created_by');
        $this->createIndex($tableName.'__created_at', $tableName, 'created_at');
        $this->createIndex($tableName.'__updated_at', $tableName, 'updated_at');

        $this->createIndex($tableName.'__is_active', $tableName, 'is_active');
        $this->createIndex($tableName.'__doc_type', $tableName, 'doc_type');
        $this->createIndex($tableName.'__shop_store_id', $tableName, 'shop_store_id');
        $this->createIndex($tableName.'__shop_order_id', $tableName, 'shop_order_id');
        $this->createIndex($tableName.'__client_cms_user_id', $tableName, 'client_cms_user_id');


        $this->addForeignKey(
            "{$tableName}__created_by", $tableName,
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            "{$tableName}__updated_by", $tableName,
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            "{$tableName}__shop_store_id", $tableName,
            'shop_store_id', '{{%shop_store}}', 'id', 'CASCADE', 'CASCADE'
        );


        $this->addForeignKey(
            "{$tableName}__shop_order_id", $tableName,
            'shop_order_id', '{{%shop_order}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            "{$tableName}__client_cms_user_id", $tableName,
            'client_cms_user_id', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );
    }

    public function safeDown()
    {
        echo "m200212_130601__create_table__shop_supplier_property_option cannot be reverted.\n";
        return false;
    }
}