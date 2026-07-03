<?php
/**
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS
 */

use yii\db\Migration;

class m260703_082001__alter_table__shop_bill__add_snapshot_fields extends Migration
{
    public function safeUp()
    {
        $table = 'shop_bill';

        $this->addColumnIfNotExists($table, 'company_name', $this->string(255)->null()->comment('Company name snapshot'));
        $this->addColumnIfNotExists($table, 'shop_pay_system_name', $this->string(255)->null()->comment('Pay system name snapshot'));

        $this->addContractorSnapshotColumns($table, 'sender', 'Sender');
        $this->addContractorSnapshotColumns($table, 'receiver', 'Receiver');

        $this->addColumnIfNotExists($table, 'receiver_bank_name', $this->string(255)->null()->comment('Receiver bank name snapshot'));
        $this->addColumnIfNotExists($table, 'receiver_bank_bic', $this->string(12)->null()->comment('Receiver bank BIC snapshot'));
        $this->addColumnIfNotExists($table, 'receiver_bank_correspondent_account', $this->string(20)->null()->comment('Receiver bank correspondent account snapshot'));
        $this->addColumnIfNotExists($table, 'receiver_bank_checking_account', $this->string(20)->null()->comment('Receiver bank checking account snapshot'));
        $this->addColumnIfNotExists($table, 'receiver_bank_address', $this->string(255)->null()->comment('Receiver bank address snapshot'));

        $this->fillExistingBillsSnapshot($table);
    }

    protected function addContractorSnapshotColumns($table, $prefix, $title)
    {
        $this->addColumnIfNotExists($table, $prefix.'_contractor_type', $this->string(32)->null()->comment($title.' contractor type snapshot'));
        $this->addColumnIfNotExists($table, $prefix.'_contractor_name', $this->string(255)->null()->comment($title.' contractor short name snapshot'));
        $this->addColumnIfNotExists($table, $prefix.'_contractor_full_name', $this->string(255)->null()->comment($title.' contractor full name snapshot'));
        $this->addColumnIfNotExists($table, $prefix.'_contractor_inn', $this->string(32)->null()->comment($title.' contractor INN snapshot'));
        $this->addColumnIfNotExists($table, $prefix.'_contractor_kpp', $this->string(32)->null()->comment($title.' contractor KPP snapshot'));
        $this->addColumnIfNotExists($table, $prefix.'_contractor_ogrn', $this->string(32)->null()->comment($title.' contractor OGRN snapshot'));
        $this->addColumnIfNotExists($table, $prefix.'_contractor_address', $this->string(255)->null()->comment($title.' contractor address snapshot'));
        $this->addColumnIfNotExists($table, $prefix.'_contractor_mailing_postcode', $this->string(32)->null()->comment($title.' contractor postcode snapshot'));
    }

    protected function fillExistingBillsSnapshot($table)
    {
        if (!$this->db->getTableSchema($table, true)) {
            return;
        }

        $this->db->createCommand("
            UPDATE {$table} b
            LEFT JOIN cms_company c ON c.id = b.cms_company_id
            LEFT JOIN shop_pay_system ps ON ps.id = b.shop_pay_system_id
            LEFT JOIN cms_contractor sender ON sender.id = b.sender_contractor_id
            LEFT JOIN cms_contractor receiver ON receiver.id = b.receiver_contractor_id
            LEFT JOIN cms_contractor_bank bank ON bank.id = b.receiver_contractor_bank_id
            SET
                b.company_name = IF(NULLIF(b.company_name, '') IS NULL, c.name, b.company_name),
                b.shop_pay_system_name = IF(NULLIF(b.shop_pay_system_name, '') IS NULL, ps.name, b.shop_pay_system_name),

                b.sender_contractor_type = IF(NULLIF(b.sender_contractor_type, '') IS NULL, sender.contractor_type, b.sender_contractor_type),
                b.sender_contractor_name = IF(NULLIF(b.sender_contractor_name, '') IS NULL, COALESCE(NULLIF(sender.name, ''), NULLIF(TRIM(CONCAT_WS(' ', sender.last_name, sender.first_name)), ''), sender.full_name), b.sender_contractor_name),
                b.sender_contractor_full_name = IF(NULLIF(b.sender_contractor_full_name, '') IS NULL, COALESCE(NULLIF(sender.full_name, ''), NULLIF(sender.name, ''), NULLIF(TRIM(CONCAT_WS(' ', sender.last_name, sender.first_name)), '')), b.sender_contractor_full_name),
                b.sender_contractor_inn = IF(NULLIF(b.sender_contractor_inn, '') IS NULL, sender.inn, b.sender_contractor_inn),
                b.sender_contractor_kpp = IF(NULLIF(b.sender_contractor_kpp, '') IS NULL, sender.kpp, b.sender_contractor_kpp),
                b.sender_contractor_ogrn = IF(NULLIF(b.sender_contractor_ogrn, '') IS NULL, sender.ogrn, b.sender_contractor_ogrn),
                b.sender_contractor_address = IF(NULLIF(b.sender_contractor_address, '') IS NULL, sender.address, b.sender_contractor_address),
                b.sender_contractor_mailing_postcode = IF(NULLIF(b.sender_contractor_mailing_postcode, '') IS NULL, sender.mailing_postcode, b.sender_contractor_mailing_postcode),

                b.receiver_contractor_type = IF(NULLIF(b.receiver_contractor_type, '') IS NULL, receiver.contractor_type, b.receiver_contractor_type),
                b.receiver_contractor_name = IF(NULLIF(b.receiver_contractor_name, '') IS NULL, COALESCE(NULLIF(receiver.name, ''), NULLIF(TRIM(CONCAT_WS(' ', receiver.last_name, receiver.first_name)), ''), receiver.full_name), b.receiver_contractor_name),
                b.receiver_contractor_full_name = IF(NULLIF(b.receiver_contractor_full_name, '') IS NULL, COALESCE(NULLIF(receiver.full_name, ''), NULLIF(receiver.name, ''), NULLIF(TRIM(CONCAT_WS(' ', receiver.last_name, receiver.first_name)), '')), b.receiver_contractor_full_name),
                b.receiver_contractor_inn = IF(NULLIF(b.receiver_contractor_inn, '') IS NULL, receiver.inn, b.receiver_contractor_inn),
                b.receiver_contractor_kpp = IF(NULLIF(b.receiver_contractor_kpp, '') IS NULL, receiver.kpp, b.receiver_contractor_kpp),
                b.receiver_contractor_ogrn = IF(NULLIF(b.receiver_contractor_ogrn, '') IS NULL, receiver.ogrn, b.receiver_contractor_ogrn),
                b.receiver_contractor_address = IF(NULLIF(b.receiver_contractor_address, '') IS NULL, receiver.address, b.receiver_contractor_address),
                b.receiver_contractor_mailing_postcode = IF(NULLIF(b.receiver_contractor_mailing_postcode, '') IS NULL, receiver.mailing_postcode, b.receiver_contractor_mailing_postcode),

                b.receiver_bank_name = IF(NULLIF(b.receiver_bank_name, '') IS NULL, bank.bank_name, b.receiver_bank_name),
                b.receiver_bank_bic = IF(NULLIF(b.receiver_bank_bic, '') IS NULL, bank.bic, b.receiver_bank_bic),
                b.receiver_bank_correspondent_account = IF(NULLIF(b.receiver_bank_correspondent_account, '') IS NULL, bank.correspondent_account, b.receiver_bank_correspondent_account),
                b.receiver_bank_checking_account = IF(NULLIF(b.receiver_bank_checking_account, '') IS NULL, bank.checking_account, b.receiver_bank_checking_account),
                b.receiver_bank_address = IF(NULLIF(b.receiver_bank_address, '') IS NULL, bank.bank_address, b.receiver_bank_address)
        ")->execute();
    }

    protected function addColumnIfNotExists($tableName, $columnName, $type)
    {
        $schema = $this->db->getTableSchema($tableName, true);
        if ($schema && !isset($schema->columns[$columnName])) {
            $this->addColumn($tableName, $columnName, $type);
        }
    }

    public function safeDown()
    {
        echo "m260703_082001__alter_table__shop_bill__add_snapshot_fields cannot be reverted.\n";
        return false;
    }
}
