<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m220722_142301__alter_table__shop_store extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_store";

        $this->addColumn($tableName, "cashier_default_cms_user_id", $this->integer()->comment("Клиент по умолчанию"));
        $this->createIndex($tableName.'__cashier_default_cms_user_id', $tableName, ['cashier_default_cms_user_id']);

        $this->addForeignKey(
            "{$tableName}__cashier_default_cms_user_id", $tableName,
            'cashier_default_cms_user_id', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );
    }

    public function safeDown()
    {
        echo "m200717_132301__alter_table__shop_site cannot be reverted.\n";
        return false;
    }
}