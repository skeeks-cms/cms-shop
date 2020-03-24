<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200324_110601__create_table__shop_favorite_product extends Migration
{
    public function safeUp()
    {
        $tableName = 'shop_favorite_product';
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

            'shop_cart_id' => $this->integer()->notNull(),
            'shop_product_id' => $this->integer()->notNull(),

            'cms_site_id' => $this->integer()->notNull(),

        ], $tableOptions);

        $this->createIndex($tableName.'__cms_site_id', $tableName, 'cms_site_id');
        $this->createIndex($tableName.'__cart2product', $tableName, ['shop_cart_id', 'shop_product_id'], true);

        $this->addCommentOnTable($tableName, 'Избранные продукты');

        $this->addForeignKey(
            "{$tableName}__shop_cart_id", $tableName,
            'shop_cart_id', '{{%shop_cart}}', 'id', 'CASCADE', 'CASCADE'
        );

        $this->addForeignKey(
            "{$tableName}__shop_product_id", $tableName,
            'shop_product_id', '{{%shop_product}}', 'id', 'CASCADE', 'CASCADE'
        );
        $this->addForeignKey(
            "{$tableName}__cms_site_id", $tableName,
            'cms_site_id', '{{%cms_site}}', 'id', 'CASCADE', 'CASCADE'
        );
    }

    public function safeDown()
    {
        echo "m200324_110601__create_table__shop_favorite_product cannot be reverted.\n";
        return false;
    }
}