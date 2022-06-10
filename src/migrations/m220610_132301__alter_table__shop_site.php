<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m220610_132301__alter_table__shop_site extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_site";

        $this->addColumn($tableName, "order_free_shipping_from_price", $this->decimal(18, 2)->notNull()->defaultValue(0)->comment('Бесплатная доставка от'));

        $this->createIndex($tableName.'__order_free_shipping_from_price', $tableName, ['order_free_shipping_from_price']);
    }

    public function safeDown()
    {
        echo "m220610_132301__alter_table__shop_site cannot be reverted.\n";
        return false;
    }
}