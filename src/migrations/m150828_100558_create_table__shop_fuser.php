<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Schema;
use yii\db\Migration;

class m150828_100558_create_table__shop_fuser extends Migration
{
    public function safeUp()
    {
        $tableExist = $this->db->getTableSchema("{{%shop_fuser}}", true);
        if ($tableExist) {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable("{{%shop_fuser}}", [
            'id' => $this->primaryKey(),

            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),

            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),

            'user_id' => $this->integer()->unique(),

        ], $tableOptions);

        $this->createIndex('shop_fuser__updated_by', '{{%shop_fuser}}', 'updated_by');
        $this->createIndex('shop_fuser__created_by', '{{%shop_fuser}}', 'created_by');
        $this->createIndex('shop_fuser__created_at', '{{%shop_fuser}}', 'created_at');
        $this->createIndex('shop_fuser__updated_at', '{{%shop_fuser}}', 'updated_at');

        $this->addForeignKey(
            'shop_fuser_created_by', "{{%shop_fuser}}",
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_fuser_updated_by', "{{%shop_fuser}}",
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_fuser_user_id', "{{%shop_fuser}}",
            'user_id', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

    }

    public function safeDown()
    {
        $this->dropForeignKey("shop_fuser_updated_by", "{{%shop_fuser}}");
        $this->dropForeignKey("shop_fuser_updated_by", "{{%shop_fuser}}");
        $this->dropForeignKey("shop_fuser_user_id", "{{%shop_fuser}}");

        $this->dropTable("{{%shop_fuser}}");
    }
}