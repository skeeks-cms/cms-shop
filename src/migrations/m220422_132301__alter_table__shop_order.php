<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m220422_132301__alter_table__shop_order extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_order";

        $this->addColumn($tableName, "cms_user_id", $this->integer()->comment('Покупатель'));

        $this->addColumn($tableName, "contact_phone", $this->string(255)->comment('Телефон'));
        $this->addColumn($tableName, "contact_email", $this->string(255)->comment('Email'));

        $this->createIndex($tableName.'__cms_user_id', $tableName, 'cms_user_id');

        $this->createIndex($tableName.'__contact_phone', $tableName, 'contact_phone');
        $this->createIndex($tableName.'__contact_email', $tableName, 'contact_email');

        $this->addForeignKey(
            "{$tableName}__cms_user_id", $tableName,
            'cms_user_id', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );
    }

    public function safeDown()
    {
        echo "m200717_132301__alter_table__shop_site cannot be reverted.\n";
        return false;
    }
}