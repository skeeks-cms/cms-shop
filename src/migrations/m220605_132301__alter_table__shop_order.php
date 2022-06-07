<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m220605_132301__alter_table__shop_order extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_order";

        $this->addColumn($tableName, "receiver_cms_user_id", $this->integer()->comment('Получатель заказа'));

        $this->addColumn($tableName, "receiver_first_name", $this->string(255)->comment('Имя получателя'));
        $this->addColumn($tableName, "receiver_last_name", $this->string(255)->comment('Фамилия получателя'));

        $this->addColumn($tableName, "receiver_phone", $this->string(255)->comment('Телефон получателя'));
        $this->addColumn($tableName, "receiver_email", $this->string(255)->comment('Email получателя'));


        $this->addColumn($tableName, "cms_user_address_id", $this->integer()->comment('Адрес пользователя'));


        $this->addForeignKey(
            "{$tableName}__receiver_cms_user_id", $tableName,
            'receiver_cms_user_id', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            "{$tableName}__cms_user_address_id", $tableName,
            'cms_user_address_id', '{{%cms_user_address}}', 'id', 'SET NULL', 'SET NULL'
        );

    }

    public function safeDown()
    {
        echo "m200717_132301__alter_table__shop_site cannot be reverted.\n";
        return false;
    }
}