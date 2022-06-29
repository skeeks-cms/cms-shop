<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m220630_132301__alter_table__shop_delivery extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_delivery";

        $this->addColumn($tableName, "free_price_from", $this->decimal(18, 2)->notNull()->defaultValue(0)->comment('Бесплатная доставка от'));

        $this->createIndex($tableName.'__free_price_from', $tableName, ['free_price_from']);
    }

    public function safeDown()
    {
        echo "m220610_132301__alter_table__shop_site cannot be reverted.\n";
        return false;
    }
}