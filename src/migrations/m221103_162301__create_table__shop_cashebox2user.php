<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m221103_162301__create_table__shop_cashebox2user extends Migration
{
    public function safeUp()
    {
        $tableName = 'shop_cashebox2user';
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
            'created_by' => $this->integer(),

            'cms_user_id'      => $this->integer()->comment("Сотрудник"),
            'shop_cashebox_id' => $this->integer()->comment("Касса"),

            'is_active' => $this->integer(1)->notNull()->defaultValue(1)->comment('Активен?'),

            'cashiers_name' => $this->string(255)->comment('Имя кассира на чеке'),

            'comment' => $this->text()->comment("Комментарий"),

        ], $tableOptions);

        $this->addCommentOnTable($tableName, "Кассиры");

        $this->createIndex($tableName.'__created_by', $tableName, 'created_by');
        $this->createIndex($tableName.'__created_at', $tableName, 'created_at');
        $this->createIndex($tableName.'__cms_user_id', $tableName, 'cms_user_id');
        $this->createIndex($tableName.'__shop_cashebox_id', $tableName, 'shop_cashebox_id');
        $this->createIndex($tableName.'__is_active', $tableName, 'is_active');

        $this->addForeignKey(
            "{$tableName}__created_by", $tableName,
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            "{$tableName}__cms_user_id", $tableName,
            'cms_user_id', '{{%cms_user}}', 'id', 'CASCADE', 'CASCADE'
        );

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