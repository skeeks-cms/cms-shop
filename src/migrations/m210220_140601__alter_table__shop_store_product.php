<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m210220_140601__alter_table__shop_store_product extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_store_product";

        $this->addColumn($tableName, "name", $this->string(255));

        $this->addColumn($tableName, "external_id", $this->string(255));
        $this->addColumn($tableName, "external_data", $this->text());

        $this->createIndex("{$tableName}__name", $tableName, "name");
        $this->createIndex("{$tableName}__store_external_uniq", $tableName, ["external_id", "shop_store_id"]);


        $this->dropForeignKey("shop_store_product__shop_product_id", $tableName);
        $this->alterColumn($tableName, "shop_product_id", $this->integer());
        $this->addForeignKey(
            "{$tableName}__shop_product_id", $tableName,
            'shop_product_id', '{{%shop_product}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addColumn($tableName, "purchase_price", $this->decimal(18, 2)->notNull()->defaultValue(0));
        $this->addColumn($tableName, "selling_price", $this->decimal(18, 2)->notNull()->defaultValue(0));

        $this->createIndex("{$tableName}__purchase_price", $tableName, "purchase_price");
        $this->createIndex("{$tableName}__selling_price", $tableName, "selling_price");


    }

    public function safeDown()
    {
        echo "m210123_130601__alter_table__shop_product_price cannot be reverted.\n";
        return false;
    }
}