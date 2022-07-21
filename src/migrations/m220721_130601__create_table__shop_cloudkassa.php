<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m220721_130601__create_table__shop_cloudkassa extends Migration
{
    public function safeUp()
    {
        $tableName = 'shop_cloudkassa';
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

            'cms_site_id' => $this->integer()->notNull(),

            'name' => $this->string()->notNull(),

            'priority' => $this->integer()->notNull()->defaultValue(100),
            'is_main'  => $this->integer(1)->defaultValue(1),

            'component'        => $this->string(255)->notNull(),
            'component_config' => $this->text(),

        ], $tableOptions);

        $this->addCommentOnTable($tableName, "Облачная касса");

        $this->createIndex($tableName.'__is_main', $tableName, 'is_main');
        $this->createIndex($tableName.'__cms_site_id', $tableName, 'cms_site_id');
        $this->createIndex($tableName.'__priority', $tableName, 'priority');
        $this->createIndex($tableName.'__name', $tableName, 'name');
        $this->createIndex($tableName.'__site_main_uniq', $tableName, ['cms_site_id', 'is_main'], true);

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