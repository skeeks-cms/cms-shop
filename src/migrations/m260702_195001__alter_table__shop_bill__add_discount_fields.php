<?php
/**
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS
 */

use yii\db\Migration;

class m260702_195001__alter_table__shop_bill__add_discount_fields extends Migration
{
    public function safeUp()
    {
        $billTable = 'shop_bill';
        $billItemTable = 'shop_bill_item';

        $this->addColumnIfNotExists($billTable, 'discount_amount', $this->decimal(18, 4)->notNull()->defaultValue(0)->comment('Сумма скидки'));
        $this->addColumnIfNotExists($billTable, 'discount_value', $this->string(32)->null()->comment('Значение скидки'));
        $this->addColumnIfNotExists($billTable, 'discount_name', $this->string(255)->null()->comment('Название скидки'));
        $this->addIndexIfNotExists($billTable, $billTable.'__discount_amount', ['discount_amount']);

        if ($this->db->getTableSchema($billItemTable, true)) {
            $this->addColumnIfNotExists($billItemTable, 'discount_amount', $this->decimal(18, 4)->notNull()->defaultValue(0)->comment('Сумма скидки'));
            $this->addColumnIfNotExists($billItemTable, 'discount_value', $this->string(32)->null()->comment('Значение скидки'));
            $this->addColumnIfNotExists($billItemTable, 'discount_name', $this->string(255)->null()->comment('Название скидки'));
            $this->addIndexIfNotExists($billItemTable, $billItemTable.'__discount_amount', ['discount_amount']);
        }
    }

    protected function addColumnIfNotExists($tableName, $columnName, $type)
    {
        $schema = $this->db->getTableSchema($tableName, true);
        if ($schema && !isset($schema->columns[$columnName])) {
            $this->addColumn($tableName, $columnName, $type);
        }
    }

    protected function addIndexIfNotExists($tableName, $indexName, array $columns)
    {
        if ($this->db->getTableSchema($tableName, true)) {
            try {
                $this->createIndex($indexName, $tableName, $columns);
            } catch (\Exception $e) {
                echo "Index {$indexName} already exists or cannot be created: {$e->getMessage()}\n";
            }
        }
    }

    public function safeDown()
    {
        echo "m260702_195001__alter_table__shop_bill__add_discount_fields cannot be reverted.\n";
        return false;
    }
}
