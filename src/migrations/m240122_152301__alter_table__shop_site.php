<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m240122_152301__alter_table__shop_site extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_site";

        $this->addColumn($tableName, "required_product_fields", $this->string(255)->null()->comment("Обязательные поля для товаров"));
        $this->addColumn($tableName, "required_brand_fields", $this->string(255)->null()->comment("Обязательные поля для брендов"));
        $this->addColumn($tableName, "required_collection_fields", $this->string(255)->null()->comment("Обязательные поля для коллекций"));
    }

    public function safeDown()
    {
        echo "m200717_132301__alter_table__shop_site cannot be reverted.\n";
        return false;
    }
}