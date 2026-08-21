<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 */

use yii\db\Migration;

class m260817_200000__create_table__shop_partner_payout extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $tableName = '{{%shop_partner_payout}}';
        $this->createTable($tableName, [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'cms_site_id' => $this->integer()->notNull()->comment('Сайт'),
            'cms_user_id' => $this->integer()->notNull()->comment('Партнёр'),
            'value' => $this->decimal(18, 2)->notNull()->comment('Сумма к выводу, бонусов'),
            'requisites' => $this->text()->notNull()->comment('Реквизиты для выплаты'),
            'status' => $this->string(32)->notNull()->defaultValue('new')->comment('Статус заявки'),
            'reject_reason' => $this->text()->comment('Причина отмены'),
            'manager_comment' => $this->text()->comment('Сообщение партнёру о выплате'),
            'shop_bonus_transaction_id' => $this->integer()->comment('Транзакция списания'),
            'paid_at' => $this->integer()->comment('Дата выплаты'),
            'lock_version' => $this->integer()->notNull()->defaultValue(0)->comment('Версия записи'),
        ], $tableOptions);

        $this->addCommentOnTable($tableName, 'Партнёрская программа: заявки на вывод бонусов');
        $this->createIndex('shop_partner_payout__created_by', $tableName, 'created_by');
        $this->createIndex('shop_partner_payout__updated_by', $tableName, 'updated_by');
        $this->createIndex('shop_partner_payout__site_user_status', $tableName, ['cms_site_id', 'cms_user_id', 'status']);
        $this->createIndex('shop_partner_payout__site_created_at', $tableName, ['cms_site_id', 'created_at']);
        $this->createIndex('shop_partner_payout__bonus', $tableName, 'shop_bonus_transaction_id', true);
        $this->addForeignKey('shop_partner_payout__created_by_fk', $tableName, 'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL');
        $this->addForeignKey('shop_partner_payout__updated_by_fk', $tableName, 'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL');
        $this->addForeignKey('shop_partner_payout__site_fk', $tableName, 'cms_site_id', '{{%cms_site}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('shop_partner_payout__user_fk', $tableName, 'cms_user_id', '{{%cms_user}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('shop_partner_payout__bonus_fk', $tableName, 'shop_bonus_transaction_id', '{{%shop_bonus_transaction}}', 'id', 'RESTRICT', 'CASCADE');

        return true;
    }

    public function safeDown()
    {
        $tableName = '{{%shop_partner_payout}}';
        if (!$this->db->getTableSchema($tableName, true)) {
            return true;
        }
        foreach (['created_by', 'updated_by', 'site', 'user', 'bonus'] as $suffix) {
            $this->dropForeignKey('shop_partner_payout__'.$suffix.'_fk', $tableName);
        }
        $this->dropTable($tableName);

        return true;
    }
}
