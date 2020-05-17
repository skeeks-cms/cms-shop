<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200516_150602__create_table__shop_cms_content_property extends Migration
{
    public function safeUp()
    {
        $tableName = 'shop_cms_content_property';
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

            'cms_content_property_id'        => $this->integer()->notNull(),
            'is_offer_property'        => $this->integer(1)->defaultValue(0)->notNull(),

        ], $tableOptions);

        $this->createIndex($tableName.'__updated_by', $tableName, 'updated_by');
        $this->createIndex($tableName.'__created_by', $tableName, 'created_by');
        $this->createIndex($tableName.'__created_at', $tableName, 'created_at');
        $this->createIndex($tableName.'__updated_at', $tableName, 'updated_at');

        //На один сайт одно задание на импорт с другого сайта
        $this->createIndex($tableName.'__cms_content_property_id', $tableName, ["cms_content_property_id"], true);
        $this->createIndex($tableName.'__is_offer_property', $tableName, ["is_offer_property"]);


        $this->addForeignKey(
            "{$tableName}__created_by", $tableName,
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );
        $this->addForeignKey(
            "{$tableName}__updated_by", $tableName,
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        //Удаляя сайт - удаляются и все его задания
        $this->addForeignKey(
            "{$tableName}__cms_content_property_id", $tableName,
            'cms_content_property_id', '{{%cms_content_property}}', 'id', 'CASCADE', 'CASCADE'
        );
    }

    public function safeDown()
    {
        echo "m200516_140601__create_table__shop_offers_property cannot be reverted.\n";
        return false;
    }
}