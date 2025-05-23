<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */


use yii\db\Migration;

class m231021_162301__create_table__shop_wb_product extends Migration
{
    public function safeUp()
    {
        $tableName = 'shop_wb_product';
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

            'updated_at' => $this->integer()->null(),

            'wb_updated_at'        => $this->integer()->notNull(),
            'wb_updated_at_string' => $this->string(255)->notNull(),

            'shop_marketplace_id' => $this->integer()->notNull(),

            'shop_product_id' => $this->integer()->null(),
            'vendor_code'     => $this->string(255)->notNull()->comment('Артикул продавца'),

            'imt_id' => $this->integer()->null()->comment('Идентификатор карточки товара'),
            'wb_id'  => $this->integer()->notNull()->comment('Артикул WB'),

            'price'      => $this->decimal(18, 4)->notNull()->defaultValue(0)->comment('Цена'),
            'discount'   => $this->integer()->notNull()->defaultValue(0)->comment('Скидка'),
            'promo_code' => $this->decimal(18, 4)->notNull()->defaultValue(0)->comment('Промокод'),

            'brand' => $this->string(255)->null()->comment('Брэнд'),

            'wb_object'    => $this->string(255)->null()->comment('Категория для который создавалось КТ с данной НМ'),
            'wb_object_id' => $this->integer()->null()->comment('Идентификатор предмета'),

            'wb_data' => $this->text()->null()->comment('Все данные по товару'),

        ], $tableOptions);

        $this->addCommentOnTable($tableName, "Товары WB");

        $this->createIndex($tableName.'__shop_marketplace_id', $tableName, 'shop_marketplace_id');
        $this->createIndex($tableName.'__shop_product_id', $tableName, 'shop_product_id');
        $this->createIndex($tableName.'__vendor_code', $tableName, 'vendor_code');
        $this->createIndex($tableName.'__imt_id', $tableName, 'imt_id');
        $this->createIndex($tableName.'__wb_id', $tableName, 'wb_id', true);

        $this->createIndex($tableName.'__price', $tableName, 'price');
        $this->createIndex($tableName.'__discount', $tableName, 'discount');
        $this->createIndex($tableName.'__promo_code', $tableName, 'promo_code');

        $this->addForeignKey(
            "{$tableName}__shop_marketplace_id", $tableName,
            'shop_marketplace_id', '{{%shop_marketplace}}', 'id', 'CASCADE', 'CASCADE'
        );

        $this->addForeignKey(
            "{$tableName}__shop_product_id", $tableName,
            'shop_product_id', '{{%shop_product}}', 'id', 'SET NULL', 'SET NULL'
        );
    }

    public function safeDown()
    {
        echo "m230325_162301__create_table__shop_marketplace cannot be reverted.\n";
        return false;
    }
}