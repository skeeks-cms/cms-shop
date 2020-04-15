<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200414_110601__create_table__shop_site extends Migration
{
    public function safeUp()
    {
        $tableName = 'shop_site';
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

            'description' => $this->text(),
            'description_internal' => $this->text(),

            'is_supplier' => $this->integer()->notNull()->defaultValue(0),

        ], $tableOptions);

        $this->createIndex($tableName.'__is_supplier', $tableName, 'is_supplier');

        $this->addCommentOnTable($tableName, 'Настройки магазина для сайта');

        $this->addForeignKey(
            "{$tableName}__id", $tableName,
            'id', '{{%cms_site}}', 'id', 'CASCADE', 'CASCADE'
        );
    }

    public function safeDown()
    {
        echo "m200324_110601__create_table__shop_favorite_product cannot be reverted.\n";
        return false;
    }
}