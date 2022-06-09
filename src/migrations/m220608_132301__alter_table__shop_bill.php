<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m220608_132301__alter_table__shop_bill extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_bill";

        $this->dropForeignKey("shop_bill__shop_buyer_id", $tableName);

        $this->alterColumn($tableName, 'shop_buyer_id', $this->integer(11));
        $this->addColumn($tableName, "cms_user_id", $this->integer()->comment('Клиент (пользователь)'));

        $this->createIndex($tableName.'__cms_user_id', $tableName, ['cms_user_id']);

        ;
        $this->addForeignKey(
            "{$tableName}__cms_user_id", $tableName,
            'cms_user_id', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );
        $this->addForeignKey(
            "{$tableName}__shop_buyer_id", $tableName,
            'shop_buyer_id', '{{%shop_buyer}}', 'id', 'SET NULL', 'SET NULL'
        );
    }

    public function safeDown()
    {
        echo "m200717_132301__alter_table__shop_site cannot be reverted.\n";
        return false;
    }
}