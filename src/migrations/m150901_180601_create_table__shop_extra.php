<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m150901_180601_create_table__shop_extra extends Migration
{
    public function safeUp()
    {
        $tableExist = $this->db->getTableSchema("{{%shop_extra}}", true);
        if ($tableExist) {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable("{{%shop_extra}}", [
            'id' => $this->primaryKey(),

            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),

            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),

            'name'  => $this->string(50)->notNull(),
            'value' => $this->decimal(18, 2)->notNull(),

        ], $tableOptions);


        $this->createIndex('shop_extra__updated_by', '{{%shop_extra}}', 'updated_by');
        $this->createIndex('shop_extra__created_by', '{{%shop_extra}}', 'created_by');
        $this->createIndex('shop_extra__created_at', '{{%shop_extra}}', 'created_at');
        $this->createIndex('shop_extra__updated_at', '{{%shop_extra}}', 'updated_at');

        $this->createIndex('shop_extra__name', '{{%shop_extra}}', 'name');
        $this->createIndex('shop_extra__value', '{{%shop_extra}}', 'value');

        $this->addForeignKey(
            'shop_extra_created_by', "{{%shop_extra}}",
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_extra_updated_by', "{{%shop_extra}}",
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey("shop_extra_updated_by", "{{%shop_extra}}");
        $this->dropForeignKey("shop_extra_updated_by", "{{%shop_extra}}");

        $this->dropTable("{{%shop_extra}}");
    }
}