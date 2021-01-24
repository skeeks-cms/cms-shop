<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m210123_140601__alter_table__shop_product_price_change extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_product_price_change";

        $this->dropColumn($tableName, "quantity_from");
        $this->dropColumn($tableName, "quantity_to");
    }

    public function safeDown()
    {
        echo "m201222_120601__alter_table__shop_import_cms_site cannot be reverted.\n";
        return false;
    }
}