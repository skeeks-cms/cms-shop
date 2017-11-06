<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */
use yii\db\Schema;
use yii\db\Migration;

class m170103_120601_create_table__shop_discount_coupon extends Migration
{
    public function safeUp()
    {
        $tableName  = 'shop_discount_coupon';
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

            'shop_discount_id'      => $this->integer()->notNull(),
            'is_active'             => $this->integer(1)->notNull()->defaultValue(1),

            'active_from'           => $this->integer(),
            'active_to'             => $this->integer(),

            'coupon'                => $this->string(32)->notNull(),
            'max_use'               => $this->integer()->notNull()->defaultValue(1),
            'use_count'             => $this->integer()->notNull()->defaultValue(0),

            'cms_user_id'           => $this->integer(),
            'description'           => $this->string(255),

        ], $tableOptions);


        $this->createIndex('updated_by', $tableName, 'updated_by');
        $this->createIndex('created_by', $tableName, 'created_by');
        $this->createIndex('created_at', $tableName, 'created_at');
        $this->createIndex('updated_at', $tableName, 'updated_at');

        $this->createIndex('shop_discount_id', $tableName, 'shop_discount_id');
        $this->createIndex('is_active', $tableName, 'is_active');
        $this->createIndex('active_from', $tableName, 'active_from');
        $this->createIndex('active_to', $tableName, 'active_to');
        $this->createIndex('coupon', $tableName, 'coupon');
        $this->createIndex('max_use', $tableName, 'max_use');
        $this->createIndex('use_count', $tableName, 'use_count');
        $this->createIndex('cms_user_id', $tableName, 'cms_user_id');

        $this->addCommentOnTable($tableName, '');

        $this->addForeignKey(
            "{$tableName}__created_by", $tableName,
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            "{$tableName}__updated_by", $tableName,
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            "{$tableName}__shop_discount_id", $tableName,
            'shop_discount_id', '{{%shop_discount}}', 'id', 'CASCADE', 'CASCADE'
        );

        $this->addForeignKey(
            "{$tableName}__cms_user_id", $tableName,
            'cms_user_id', '{{%cms_user}}', 'id', 'CASCADE', 'CASCADE'
        );

    }

    public function safeDown()
    {
        $this->dropForeignKey("{$tableName}__created_by", $tableName);
        $this->dropForeignKey("{$tableName}__updated_by", $tableName);
        $this->dropForeignKey("{$tableName}__shop_discount_id", $tableName);
        $this->dropForeignKey("{$tableName}__cms_user_id", $tableName);

        $this->dropTable($tableName);
    }
}