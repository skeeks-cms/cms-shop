<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m221019_162301__alter_table__shop_payment extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_payment";

        $this->addColumn($tableName, "shop_check_id", $this->integer()->comment("Чек"));
        $this->createIndex($tableName.'__shop_check_id', $tableName, ['shop_check_id']);

        $this->addForeignKey(
            "{$tableName}__shop_check_id", $tableName,
            'shop_check_id', '{{%shop_check}}', 'id', 'SET NULL', 'SET NULL'
        );
    }

    public function safeDown()
    {
        echo "m200717_132301__alter_table__shop_site cannot be reverted.\n";
        return false;
    }
}