<?php

use yii\db\Migration;

/**
 * Adds a required business explanation for canceled documents.
 */
class m260710_135501__alter_table__shop_document__add_canceled_reason extends Migration
{
    public function safeUp()
    {
        $tableName = 'shop_document';
        $tableSchema = $this->db->getTableSchema($tableName, true);

        if ($tableSchema && !$tableSchema->getColumn('canceled_reason')) {
            $this->addColumn(
                $tableName,
                'canceled_reason',
                $this->text()->null()->comment('Причина отмены документа')
            );
        }
    }

    public function safeDown()
    {
        $tableName = 'shop_document';
        $tableSchema = $this->db->getTableSchema($tableName, true);

        if ($tableSchema && $tableSchema->getColumn('canceled_reason')) {
            $this->dropColumn($tableName, 'canceled_reason');
        }
    }
}
