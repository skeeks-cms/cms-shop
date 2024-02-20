<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m240123_152305__alter_table__shop_product extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_product";

        $this->addColumn($tableName, "brand_id", $this->integer()->null()->comment("Бренд"));
        $this->addColumn($tableName, "brand_sku", $this->string(255)->null()->comment("Артикул бренда"));

        $this->createIndex("brand_id", $tableName, "brand_id");
        $this->createIndex("brand_sku", $tableName, "brand_sku");

        $this->addForeignKey(
            "{$tableName}__brand_id", $tableName,
            'brand_id', '{{%shop_brand}}', 'id', 'RESTRICT', 'RESTRICT'
        );

    }

    public function safeDown()
    {
        echo "m200717_132301__alter_table__shop_site cannot be reverted.\n";
        return false;
    }
}