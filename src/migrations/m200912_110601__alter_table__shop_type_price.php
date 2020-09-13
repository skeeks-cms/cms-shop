<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200912_110601__alter_table__shop_type_price extends Migration
{
    public function safeUp()
    {
        $tableName = 'shop_type_price';

        $this->addColumn($tableName, "is_auto", $this->integer(1)->notNull()->defaultValue(0)->comment("Цена обновляется автоматически?"));
        $this->addColumn($tableName, "base_auto_shop_type_price_id", $this->integer()->comment("Базовая цена для автообновления."));
        $this->addColumn($tableName, "auto_extra_charge", $this->integer()->defaultValue(100)->comment("Наценка на базовую цену автообновления."));

        $this->createIndex($tableName.'__extra_charge', $tableName, 'auto_extra_charge');
        $this->createIndex($tableName.'__is_auto', $tableName, 'is_auto');
        $this->createIndex($tableName.'__base_auto_shop_type_price_id', $tableName, 'base_auto_shop_type_price_id');

        $this->addForeignKey(
            "{$tableName}__base_auto_shop_type_price_id", $tableName,
            'base_auto_shop_type_price_id', '{{%shop_type_price}}', 'id', 'RESTRICT', 'RESTRICT'
        );
    }

    public function safeDown()
    {
        echo "m200507_110601__create_table__shop_product_relation cannot be reverted.\n";
        return false;
    }
}