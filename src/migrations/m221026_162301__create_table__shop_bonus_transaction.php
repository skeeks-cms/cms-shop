<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m221026_162301__create_table__shop_bonus_transaction extends Migration
{
    public function safeUp()
    {
        $tableName = 'shop_bonus_transaction';
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

            'cms_site_id' => $this->integer()->notNull()->comment("Сайт"),

            'cms_user_id' => $this->integer()->comment("Пользователь"),
            'shop_order_id' => $this->integer()->comment("Заказ"),

            'is_debit' => $this->integer(1)->notNull()->defaultValue(1)->comment('Дебет? (иначе кредит)'),

            'value' => $this->decimal(18, 2)->notNull()->defaultValue(0)->comment("Количество бонусов"),

            'end_at' => $this->integer()->comment("Дата до которой действуют бонусы"),

            'comment' => $this->text()->comment("Комментарий"),

        ], $tableOptions);

        $this->addCommentOnTable($tableName, "Движение бонусов");

        $this->createIndex($tableName.'__updated_by', $tableName, 'updated_by');
        $this->createIndex($tableName.'__created_by', $tableName, 'created_by');
        $this->createIndex($tableName.'__created_at', $tableName, 'created_at');
        $this->createIndex($tableName.'__updated_at', $tableName, 'updated_at');

        $this->createIndex($tableName.'__cms_user_id', $tableName, 'cms_user_id');
        $this->createIndex($tableName.'__shop_order_id', $tableName, 'shop_order_id');
        $this->createIndex($tableName.'__is_debit', $tableName, 'is_debit');
        $this->createIndex($tableName.'__end_at', $tableName, 'end_at');
        $this->createIndex($tableName.'__value', $tableName, 'value');
        $this->createIndex($tableName.'__cms_site_id', $tableName, 'cms_site_id');


        $this->addForeignKey(
            "{$tableName}__created_by", $tableName,
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            "{$tableName}__updated_by", $tableName,
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            "{$tableName}__cms_user_id", $tableName,
            'cms_user_id', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            "{$tableName}__shop_order_id", $tableName,
            'shop_order_id', '{{%shop_order}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            "{$tableName}__cms_site_id", $tableName,
            'cms_site_id', '{{%cms_site}}', 'id', 'CASCADE', 'CASCADE'
        );


    }

    public function safeDown()
    {
        echo "m200212_130601__create_table__shop_supplier_property_option cannot be reverted.\n";
        return false;
    }
}