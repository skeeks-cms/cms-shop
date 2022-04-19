<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m220419_132301__alter_table__shop_site extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_site";

        $this->addColumn($tableName, "is_show_cart", $this->integer(1)->notNull()->defaultValue(1)->comment('Показывать корзину на сайте?'));
        $this->addColumn($tableName, "is_show_prices", $this->integer(1)->notNull()->defaultValue(1)->comment('Показывать цены на сайте?'));
    }

    public function safeDown()
    {
        echo "m200717_132301__alter_table__shop_site cannot be reverted.\n";
        return false;
    }
}