<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Schema;
use yii\db\Migration;

class m170208_120601_create_table__shop_order2discount_coupon extends Migration
{
    public function safeUp()
    {
        $tableName = 'shop_order2discount_coupon';
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

            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),

            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),

            'discount_coupon_id' => $this->integer()->notNull(),
            'order_id' => $this->integer(1)->notNull()->defaultValue(1),

        ], $tableOptions);


        $this->createIndex($tableName . '__updated_by', $tableName, 'updated_by');
        $this->createIndex($tableName . '__created_by', $tableName, 'created_by');
        $this->createIndex($tableName . '__created_at', $tableName, 'created_at');
        $this->createIndex($tableName . '__updated_at', $tableName, 'updated_at');

        $this->createIndex($tableName . '__discount_coupon_id', $tableName, 'discount_coupon_id');
        $this->createIndex($tableName . '__order_id', $tableName, 'order_id');

        $this->addCommentOnTable($tableName, 'Contact orders with discount coupons');

        $this->addForeignKey(
            "{$tableName}__created_by", $tableName,
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            "{$tableName}__updated_by", $tableName,
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            "{$tableName}__discount_coupon_id", $tableName,
            'discount_coupon_id', '{{%shop_discount_coupon}}', 'id', 'CASCADE', 'CASCADE'
        );

        $this->addForeignKey(
            "{$tableName}__order_id", $tableName,
            'order_id', '{{%shop_order}}', 'id', 'CASCADE', 'CASCADE'
        );

    }

    public function safeDown()
    {
        $this->dropForeignKey("{$tableName}__created_by", $tableName);
        $this->dropForeignKey("{$tableName}__updated_by", $tableName);
        $this->dropForeignKey("{$tableName}__discount_coupon_id", $tableName);
        $this->dropForeignKey("{$tableName}__order_id", $tableName);

        $this->dropTable($tableName);
    }
}