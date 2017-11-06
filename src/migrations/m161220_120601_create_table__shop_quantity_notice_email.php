<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */
use yii\db\Schema;
use yii\db\Migration;

class m161220_120601_create_table__shop_quantity_notice_email extends Migration
{
    public function safeUp()
    {
        $tableName  = 'shop_quantity_notice_email';
        $tableExist = $this->db->getTableSchema($tableName, true);
        if ($tableExist)
        {
            return true;
        }
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable($tableName, [
            'id'                    => $this->primaryKey(),

            'created_by'            => $this->integer(),
            'updated_by'            => $this->integer(),

            'created_at'            => $this->integer(),
            'updated_at'            => $this->integer(),

            'shop_product_id'       => $this->integer()->notNull(),
            'email'                 => $this->string()->notNull(),

            'name'                  => $this->string()->null(),

            'is_notified'           => $this->integer()->notNull()->defaultValue(0),
            'notified_at'           => $this->integer(),

            'shop_fuser_id'         => $this->integer(),

        ], $tableOptions);


        $this->createIndex('updated_by', $tableName, 'updated_by');
        $this->createIndex('created_by', $tableName, 'created_by');
        $this->createIndex('created_at', $tableName, 'created_at');
        $this->createIndex('updated_at', $tableName, 'updated_at');
        $this->createIndex('shop_product_id', $tableName, 'shop_product_id');
        $this->createIndex('email', $tableName, 'email');
        $this->createIndex('name', $tableName, 'name');
        $this->createIndex('shop_fuser_id', $tableName, 'shop_fuser_id');
        $this->createIndex('is_notified', $tableName, 'is_notified');
        $this->createIndex('notified_at', $tableName, 'notified_at');

        $this->addCommentOnTable($tableName, 'Subscribers to the notice of receipt product');

        $this->addForeignKey(
            "{$tableName}__created_by", $tableName,
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            "{$tableName}__updated_by", $tableName,
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            "{$tableName}__shop_product_id", $tableName,
            'shop_product_id', '{{%shop_product}}', 'id', 'CASCADE', 'CASCADE'
        );
        $this->addForeignKey(
            "{$tableName}__shop_fuser_id", $tableName,
            'shop_fuser_id', '{{%shop_fuser}}', 'id', 'SET NULL', 'SET NULL'
        );

    }

    public function safeDown()
    {
        $this->dropForeignKey("{$tableName}__created_by", $tableName);
        $this->dropForeignKey("{$tableName}__updated_by", $tableName);
        $this->dropForeignKey("{$tableName}__shop_product_id", $tableName);
        $this->dropForeignKey("{$tableName}__shop_fuser_id", $tableName);

        $this->dropTable($tableName);
    }
}