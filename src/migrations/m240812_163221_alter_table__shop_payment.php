<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m240812_163221_alter_table__shop_payment extends Migration
{
    public function safeUp()
    {
        $tableName = 'shop_payment';

        $this->dropColumn($tableName, "shop_buyer_id");

        $this->addColumn($tableName, "cms_company_id", $this->integer()->null()->comment("Компания"));

        $this->addColumn($tableName, "sender_contractor_id", $this->integer()->null()->comment("Контрагент отправитель"));
        $this->addColumn($tableName, "sender_contractor_bank_id", $this->integer()->null()->comment("Банк отправитель"));

        $this->addColumn($tableName, "receiver_contractor_id", $this->integer()->null()->comment("Контрагент получатель"));
        $this->addColumn($tableName, "receiver_contractor_bank_id", $this->integer()->null()->comment("Банк получатель"));

        $this->createIndex("{$tableName}__cms_company_id", $tableName, "cms_company_id");

        $this->createIndex("{$tableName}__sender_contractor_id", $tableName, "sender_contractor_id");
        $this->createIndex("{$tableName}__receiver_contractor_id", $tableName, "receiver_contractor_id");
        $this->createIndex("{$tableName}__receiver_contractor_bank_id", $tableName, "receiver_contractor_bank_id");
        $this->createIndex("{$tableName}__sender_contractor_bank_id", $tableName, "sender_contractor_bank_id");

        $this->addForeignKey(
            "{$tableName}__cms_company_id", $tableName,
            'cms_company_id', '{{%cms_company}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            "{$tableName}__sender_contractor_id", $tableName,
            'sender_contractor_id', '{{%cms_contractor}}', 'id', 'RESTRICT', 'RESTRICT'
        );

        $this->addForeignKey(
            "{$tableName}__receiver_contractor_id", $tableName,
            'receiver_contractor_id', '{{%cms_contractor}}', 'id', 'RESTRICT', 'RESTRICT'
        );

        $this->addForeignKey(
            "{$tableName}__receiver_contractor_bank_id", $tableName,
            'receiver_contractor_bank_id', '{{%cms_contractor_bank}}', 'id', 'RESTRICT', 'RESTRICT'
        );

        $this->addForeignKey(
            "{$tableName}__sender_contractor_bank_id", $tableName,
            'sender_contractor_bank_id', '{{%cms_contractor_bank}}', 'id', 'RESTRICT', 'RESTRICT'
        );
    }

    public function safeDown()
    {
        echo "m240411_142301__alter_table__shop_store cannot be reverted.\n";
        return false;
    }
}