<?php

use yii\db\Migration;

class m260717_143100__alter_table__shop_document__add_registration_dates extends Migration
{
    public function safeUp()
    {
        $tableName = 'shop_document';
        $schema = $this->db->getTableSchema($tableName, true);
        if (!$schema) {
            return;
        }
        if (!isset($schema->columns['seller_contractor_registration_date'])) {
            $this->addColumn($tableName, 'seller_contractor_registration_date', $this->date()->null()->comment('Дата регистрации продавца на момент создания'));
        }
        if (!isset($schema->columns['buyer_contractor_registration_date'])) {
            $this->addColumn($tableName, 'buyer_contractor_registration_date', $this->date()->null()->comment('Дата регистрации покупателя на момент создания'));
        }
    }

    public function safeDown()
    {
        $tableName = 'shop_document';
        $schema = $this->db->getTableSchema($tableName, true);
        if ($schema && isset($schema->columns['buyer_contractor_registration_date'])) {
            $this->dropColumn($tableName, 'buyer_contractor_registration_date');
        }
        if ($schema && isset($schema->columns['seller_contractor_registration_date'])) {
            $this->dropColumn($tableName, 'seller_contractor_registration_date');
        }
    }
}
