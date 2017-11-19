<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Schema;
use yii\db\Migration;

class m161220_110601_create_table__shop_product_quantity_change extends Migration
{
    public function safeUp()
    {
        $tableName = 'shop_product_quantity_change';
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

            'shop_product_id' => $this->integer()->notNull(),

            'quantity' => $this->double()->notNull()->defaultValue(0),
            'quantity_reserved' => $this->double()->notNull()->defaultValue(0),
            'measure_id' => $this->integer(),
            'measure_ratio' => $this->double()->notNull()->defaultValue(1),

        ], $tableOptions);


        $this->createIndex($tableName . '__updated_by', $tableName, 'updated_by');
        $this->createIndex($tableName . '__created_by', $tableName, 'created_by');
        $this->createIndex($tableName . '__created_at', $tableName, 'created_at');
        $this->createIndex($tableName . '__updated_at', $tableName, 'updated_at');
        $this->createIndex($tableName . '__quantity', $tableName, 'quantity');
        $this->createIndex($tableName . '__quantity_reserved', $tableName, 'quantity_reserved');
        $this->createIndex($tableName . '__measure_ratio', $tableName, 'measure_ratio');
        $this->createIndex($tableName . '__measure_id', $tableName, 'measure_id');

        $this->addCommentOnTable($tableName, 'Changes in the quantity of products');

        $this->addForeignKey(
            "{$tableName}__created_by", $tableName,
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            "{$tableName}__updated_by", $tableName,
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            "{$tableName}__measure_id", $tableName,
            'measure_id', '{{%measure}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            "{$tableName}__shop_product_id", $tableName,
            'shop_product_id', '{{%shop_product}}', 'id', 'CASCADE', 'CASCADE'
        );

    }

    public function safeDown()
    {
        $this->dropForeignKey("{$tableName}__created_by", $tableName);
        $this->dropForeignKey("{$tableName}__updated_by", $tableName);
        $this->dropForeignKey("{$tableName}__measure_id", $tableName);
        $this->dropForeignKey("{$tableName}__shop_product_id", $tableName);

        $this->dropTable($tableName);
    }
}