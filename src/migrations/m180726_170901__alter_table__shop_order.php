<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m180726_170901__alter_table__shop_order extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_order";

        $this->dropForeignKey('shop_order__buyer_id', $tableName);
        $this->dropForeignKey('shop_order__delivery_id', $tableName);
        $this->dropForeignKey('shop_order__pay_system_id', $tableName);
        $this->dropForeignKey('shop_order__person_type_id', $tableName);
        $this->dropForeignKey('shop_order__site_id', $tableName);

        $this->renameColumn($tableName, "buyer_id", 'shop_buyer_id');
        $this->renameColumn($tableName, "delivery_id", 'shop_delivery_id');
        $this->renameColumn($tableName, "pay_system_id", 'shop_pay_system_id');
        $this->renameColumn($tableName, "person_type_id", 'shop_person_type_id');
        $this->renameColumn($tableName, "site_id", 'cms_site_id');

        $this->addForeignKey(
            "{$tableName}__shop_buyer_id", $tableName,
            'shop_buyer_id', '{{%shop_buyer}}', 'id', 'RESTRICT', 'RESTRICT'
        );
        $this->addForeignKey(
            "{$tableName}__shop_delivery_id", $tableName,
            'shop_delivery_id', '{{%shop_delivery}}', 'id', 'RESTRICT', 'RESTRICT'
        );
        $this->addForeignKey(
            "{$tableName}__shop_pay_system_id", $tableName,
            'shop_pay_system_id', '{{%shop_pay_system}}', 'id', 'RESTRICT', 'RESTRICT'
        );
        $this->addForeignKey(
            "{$tableName}__shop_person_type_id", $tableName,
            'shop_person_type_id', '{{%shop_person_type}}', 'id', 'RESTRICT', 'RESTRICT'
        );
        $this->addForeignKey(
            "{$tableName}__cms_site_id", $tableName,
            'cms_site_id', '{{%cms_site}}', 'id', 'SET NULL', 'SET NULL'
        );
    }

    public function safeDown()
    {
        echo "m180726_170901__alter_table__shop_order cannot be reverted.\n";
        return false;
    }
}