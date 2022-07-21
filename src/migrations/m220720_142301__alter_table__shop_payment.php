<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m220720_142301__alter_table__shop_payment extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_payment";

        $this->dropForeignKey("{$tableName}__shop_buyer_id", $tableName);
        $this->dropForeignKey("{$tableName}__shop_pay_system_id", $tableName);

        $this->dropIndex("{$tableName}__shop_buyer_id", $tableName);

        $this->alterColumn($tableName, 'shop_pay_system_id', $this->integer()->comment("Платежная система"));

        $this->addForeignKey(
            "{$tableName}__shop_pay_system_id", $tableName,
            'shop_pay_system_id', '{{%shop_pay_system}}', 'id', 'SET NULL', 'SET NULL'
        );
    }

    public function safeDown()
    {
        echo "m200717_132301__alter_table__shop_site cannot be reverted.\n";
        return false;
    }
}