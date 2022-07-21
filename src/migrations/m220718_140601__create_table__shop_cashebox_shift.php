<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m220718_140601__create_table__shop_cashebox_shift extends Migration
{
    public function safeUp()
    {
        $tableName = 'shop_cashebox_shift';
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

            'shift_number' => $this->integer()->notNull()->defaultValue(1),

            'shop_cashebox_id' => $this->integer()->notNull(),

            'created_by' => $this->integer(),
            'created_at' => $this->integer(),

            'closed_at' => $this->integer(),
            'closed_by' => $this->integer(),


        ], $tableOptions);

        $this->addCommentOnTable($tableName, "Кассовая смена");

        $this->createIndex($tableName.'__shop_cashebox_id', $tableName, 'shop_cashebox_id');
        $this->createIndex($tableName.'__number_cachebox_uniq', $tableName, ['shift_number', 'shop_cashebox_id'], true);

        $this->addForeignKey(
            "{$tableName}__shop_cashebox_id", $tableName,
            'shop_cashebox_id', '{{%shop_cashebox}}', 'id', 'CASCADE', 'CASCADE'
        );
    }

    public function safeDown()
    {
        echo "m200212_130601__create_table__shop_supplier_property_option cannot be reverted.\n";
        return false;
    }
}