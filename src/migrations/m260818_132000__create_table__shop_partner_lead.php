<?php

use yii\db\Migration;

class m260818_132000__create_table__shop_partner_lead extends Migration
{
    public function safeUp()
    {
        $tableName = '{{%shop_partner_lead}}';
        $tableOptions = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB'
            : null;
        $this->createTable($tableName, [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'cms_lead_id' => $this->integer()->notNull()->comment('Лид'),
            'reward_value' => $this->decimal(18, 2)->notNull()->comment('Вознаграждение, бонусов'),
            'shop_bonus_transaction_id' => $this->integer()->notNull()->comment('Транзакция начисления'),
        ], $tableOptions);

        $this->addCommentOnTable($tableName, 'Финансовое расширение партнёрского лида');
        $this->createIndex('shop_partner_lead__created_by', $tableName, 'created_by');
        $this->createIndex('shop_partner_lead__updated_by', $tableName, 'updated_by');
        $this->createIndex('shop_partner_lead__lead', $tableName, 'cms_lead_id', true);
        $this->createIndex('shop_partner_lead__bonus', $tableName, 'shop_bonus_transaction_id', true);
        $this->addForeignKey('shop_partner_lead__created_by_fk', $tableName, 'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL');
        $this->addForeignKey('shop_partner_lead__updated_by_fk', $tableName, 'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL');
        $this->addForeignKey('shop_partner_lead__lead_fk', $tableName, 'cms_lead_id', '{{%cms_lead}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('shop_partner_lead__bonus_fk', $tableName, 'shop_bonus_transaction_id', '{{%shop_bonus_transaction}}', 'id', 'RESTRICT', 'CASCADE');

        return true;
    }

    public function safeDown()
    {
        if (!$this->db->getTableSchema('{{%shop_partner_lead}}', true)) {
            return true;
        }
        foreach (['created_by', 'updated_by', 'lead', 'bonus'] as $suffix) {
            $this->dropForeignKey('shop_partner_lead__'.$suffix.'_fk', '{{%shop_partner_lead}}');
        }
        $this->dropTable('{{%shop_partner_lead}}');

        return true;
    }
}
