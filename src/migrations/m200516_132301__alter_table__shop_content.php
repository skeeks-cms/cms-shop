<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200516_132301__alter_table__shop_content extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_content";

        $this->dropColumn($tableName, "yandex_export");
        $this->dropColumn($tableName, "subscription");
        $this->dropForeignKey("shop_content_shop_vat", $tableName);
        $this->dropColumn($tableName, "vat_id");
    }

    public function safeDown()
    {
        echo "m200516_132301__alter_table__shop_content cannot be reverted.\n";
        return false;
    }
}