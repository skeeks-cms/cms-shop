<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m201222_120601__alter_table__shop_import_cms_site extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_import_cms_site";

        $this->addColumn($tableName, "sender_purchasing_shop_type_price_id", $this->integer());
        $this->addColumn($tableName, "purchasing_extra_charge", $this->integer()->notNull()->defaultValue(100));

        $this->addForeignKey(
            "{$tableName}__sender_purchasing_shop_type_price_id", $tableName,
            'sender_purchasing_shop_type_price_id', '{{%shop_type_price}}', 'id', 'RESTRICT', 'RESTRICT'
        );


    }

    public function safeDown()
    {
        echo "m201222_120601__alter_table__shop_import_cms_site cannot be reverted.\n";
        return false;
    }
}