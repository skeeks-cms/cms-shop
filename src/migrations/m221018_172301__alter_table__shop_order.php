<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m221018_172301__alter_table__shop_order extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_order";

        $this->addColumn($tableName, "shop_cashebox_shift_id", $this->integer()->comment("Смена"));
        $this->createIndex($tableName.'__shop_cashebox_shift_id', $tableName, ['shop_cashebox_shift_id']);

        $this->addColumn($tableName, "shop_cashebox_id", $this->integer()->comment("Касса"));
        $this->createIndex($tableName.'__shop_cashebox_id', $tableName, ['shop_cashebox_id']);

        $this->addForeignKey(
            "{$tableName}__shop_cashebox_shift_id", $tableName,
            'shop_cashebox_shift_id', '{{%shop_cashebox_shift}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            "{$tableName}__shop_cashebox_id", $tableName,
            'shop_cashebox_id', '{{%shop_cashebox}}', 'id', 'SET NULL', 'SET NULL'
        );
    }

    public function safeDown()
    {
        echo "m200717_132301__alter_table__shop_site cannot be reverted.\n";
        return false;
    }
}