<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m180726_171001__alter_table__shop_order extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_order";

        $this->dropForeignKey('shop_order__user_id', $tableName);
        $this->dropForeignKey('shop_order__affiliate_id', $tableName);

        $this->dropColumn($tableName, 'user_id');
        $this->renameColumn($tableName, "affiliate_id", 'shop_affiliate_id');

        $this->addForeignKey(
            "{$tableName}__shop_affiliate_id", $tableName,
            'shop_affiliate_id', '{{%shop_affiliate}}', 'id', 'SET NULL', 'SET NULL'
        );
    }

    public function safeDown()
    {
        echo "m180726_171001__alter_table__shop_order cannot be reverted.\n";
        return false;
    }
}