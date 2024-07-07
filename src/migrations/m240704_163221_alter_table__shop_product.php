<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m240704_163221_alter_table__shop_product extends Migration
{
    public function safeUp()
    {
        $tableName = 'shop_product';

        $this->addColumn($tableName, "shop_product_model_id", $this->integer()->null()->comment("Связь с моделью"));

        $this->createIndex("{$tableName}__shop_product_model_id", $tableName, "shop_product_model_id");

        $this->addForeignKey(
            "{$tableName}__shop_product_model_id", $tableName,
            'shop_product_model_id', '{{%shop_product_model}}', 'id', 'SET NULL', 'SET NULL'
        );
    }

    public function safeDown()
    {
        echo "m240411_142301__alter_table__shop_store cannot be reverted.\n";
        return false;
    }
}