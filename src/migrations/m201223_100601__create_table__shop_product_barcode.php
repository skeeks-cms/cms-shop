<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m201223_100601__create_table__shop_product_barcode extends Migration
{
    public function safeUp()
    {
        $tableName = 'shop_product_barcode';
        $tableExist = $this->db->getTableSchema($tableName, true);

        if ($tableExist) {
            //return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable($tableName, [

            'id' => $this->primaryKey(),

            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),

            'shop_product_id' => $this->integer()->notNull(),
            'barcode_type' => $this->string(12)->notNull()->defaultValue("ean13"),

            'value' => $this->string(128)->notNull(),

        ], $tableOptions);

        $this->createIndex($tableName . '__unique', $tableName, ['shop_product_id', 'value'], true);

        $this->addCommentOnTable($tableName, 'Штрихкод товара');

        $this->addForeignKey(
            "{$tableName}__shop_product_id", $tableName,
            'shop_product_id', '{{%shop_product}}', 'id', 'CASCADE', 'CASCADE'
        );
    }

    public function safeDown()
    {
        echo "m200507_110601__create_table__shop_product_relation cannot be reverted.\n";
        return false;
    }
}