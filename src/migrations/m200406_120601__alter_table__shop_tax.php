<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200406_120601__alter_table__shop_tax extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_tax";

        $this->dropForeignKey("shop_tax_site_code", $tableName);
        $this->dropColumn($tableName, "site_code");
    }

    public function safeDown()
    {
        echo "m200406_080601__alter_table__shop_order cannot be reverted.\n";
        return false;
    }
}