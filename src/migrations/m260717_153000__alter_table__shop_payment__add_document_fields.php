<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 */

use yii\db\Migration;

class m260717_153000__alter_table__shop_payment__add_document_fields extends Migration
{
    public function safeUp()
    {
        $tableName = '{{%shop_payment}}';

        $this->addColumn($tableName, 'document_number', $this->string(100)->null()->comment('Номер платежного документа'));
        $this->addColumn($tableName, 'document_date', $this->date()->null()->comment('Дата платежного документа'));
        $this->addColumn($tableName, 'operation_at', $this->integer()->null()->comment('Дата и время операции во внешней системе'));
        $this->addColumn($tableName, 'external_status', $this->string(64)->null()->comment('Статус операции во внешней системе'));
    }

    public function safeDown()
    {
        $tableName = '{{%shop_payment}}';

        $this->dropColumn($tableName, 'external_status');
        $this->dropColumn($tableName, 'operation_at');
        $this->dropColumn($tableName, 'document_date');
        $this->dropColumn($tableName, 'document_number');
    }
}
