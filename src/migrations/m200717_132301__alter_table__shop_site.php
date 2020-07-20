<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200717_132301__alter_table__shop_site extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_site";

        $this->addColumn($tableName, "notify_emails", $this->text()->comment("Email адреса для уведомлений о заказах"));

        $this->addColumn($tableName, "is_show_product_no_price", $this->integer(1)->notNull()->defaultValue(1)->comment('Показывать товары с нулевыми ценами?'));
        $this->addColumn($tableName, "is_show_button_no_price", $this->integer(1)->notNull()->defaultValue(1)->comment('Показывать кнопку «добавить в корзину» для товаров с нулевыми ценами?'));
        $this->addColumn($tableName, "is_show_product_only_quantity", $this->integer(1)->notNull()->defaultValue(1)->comment('Показывать товары только в наличии на сайте?'));
        $this->addColumn($tableName, "is_show_quantity_product", $this->integer(1)->notNull()->defaultValue(1)->comment('Показывать оставшееся количество товаров на складе?'));

        $this->addColumn($tableName, "show_filter_property_ids", $this->text()->comment('Какие фильтры разрешено показывать на сайте?'));
        $this->addColumn($tableName, "open_filter_property_ids", $this->text()->comment('Какие фильтры по умолчанию открыты на сайте?'));
    }

    public function safeDown()
    {
        echo "m200717_132301__alter_table__shop_site cannot be reverted.\n";
        return false;
    }
}