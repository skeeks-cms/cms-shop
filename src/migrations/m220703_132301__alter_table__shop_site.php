<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m220703_132301__alter_table__shop_site extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_site";

        $this->addColumn($tableName, "max_product_rating_value", $this->integer()->defaultValue(5)->comment("Максимальное значение рейтинга товаров"));

        $this->addColumn($tableName, "is_generate_product_rating", $this->integer(1)->defaultValue(1)->comment("Генерировать рейтинг товара?"));
        $this->addColumn($tableName, "generate_min_product_rating_value", $this->decimal(18, 4)->defaultValue(4)->comment("Минимальное значение рейтинга"));
        $this->addColumn($tableName, "generate_max_product_rating_value", $this->decimal(18, 4)->defaultValue(5)->comment("Максимальное значение рейтинга"));
        $this->addColumn($tableName, "generate_min_product_rating_count", $this->integer()->defaultValue(30)->comment("Минимальное количество отзывов"));
        $this->addColumn($tableName, "generate_max_product_rating_count", $this->integer()->defaultValue(100)->comment("Максимальное количество отзывов"));
        
        $this->createIndex($tableName.'__max_product_rating_value', $tableName, ['max_product_rating_value']);
        $this->createIndex($tableName.'__is_generate_product_rating', $tableName, ['is_generate_product_rating']);
        $this->createIndex($tableName.'__generate_min_product_rating_value', $tableName, ['generate_min_product_rating_value']);
        $this->createIndex($tableName.'__generate_max_product_rating_value', $tableName, ['generate_max_product_rating_value']);
        $this->createIndex($tableName.'__generate_min_product_rating_count', $tableName, ['generate_min_product_rating_count']);
        $this->createIndex($tableName.'__generate_max_product_rating_count', $tableName, ['generate_max_product_rating_count']);
        
    }

    public function safeDown()
    {
        echo "m220610_132301__alter_table__shop_site cannot be reverted.\n";
        return false;
    }
}