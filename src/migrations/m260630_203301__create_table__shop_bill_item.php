<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS
 */

use yii\db\Migration;

class m260630_203301__create_table__shop_bill_item extends Migration
{
    public function safeUp()
    {
        $tableName = 'shop_bill_item';
        if ($this->db->getTableSchema($tableName, true)) {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable($tableName, [
            'id'              => $this->primaryKey(),
            'created_by'      => $this->integer(),
            'updated_by'      => $this->integer(),
            'created_at'      => $this->integer(),
            'updated_at'      => $this->integer(),
            'shop_bill_id'    => $this->integer()->notNull()->comment('Счет'),
            'shop_product_id' => $this->integer()->null()->comment('Товар или услуга'),
            'name'            => $this->string(255)->notNull()->comment('Наименование'),
            'measure_name'    => $this->string(50)->notNull()->defaultValue('шт')->comment('Ед. изм.'),
            'quantity'        => $this->decimal(18, 4)->notNull()->defaultValue(1)->comment('Количество'),
            'price'           => $this->decimal(18, 4)->notNull()->defaultValue(0)->comment('Цена'),
            'amount'          => $this->decimal(18, 4)->notNull()->defaultValue(0)->comment('Сумма'),
            'currency_code'   => $this->string(3)->notNull()->defaultValue('RUB')->comment('Валюта'),
            'vat_name'        => $this->string(32)->notNull()->defaultValue('Без НДС')->comment('НДС'),
            'sort'            => $this->integer()->notNull()->defaultValue(100)->comment('Сортировка'),
        ], $tableOptions);

        $this->createIndex($tableName.'__created_by', $tableName, 'created_by');
        $this->createIndex($tableName.'__updated_by', $tableName, 'updated_by');
        $this->createIndex($tableName.'__shop_bill_id', $tableName, 'shop_bill_id');
        $this->createIndex($tableName.'__shop_product_id', $tableName, 'shop_product_id');
        $this->createIndex($tableName.'__currency_code', $tableName, 'currency_code');
        $this->createIndex($tableName.'__sort', $tableName, 'sort');

        $this->addForeignKey($tableName.'__created_by', $tableName, 'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL');
        $this->addForeignKey($tableName.'__updated_by', $tableName, 'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL');
        $this->addForeignKey($tableName.'__shop_bill_id', $tableName, 'shop_bill_id', '{{%shop_bill}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey($tableName.'__shop_product_id', $tableName, 'shop_product_id', '{{%shop_product}}', 'id', 'SET NULL', 'SET NULL');
        $this->addForeignKey($tableName.'__currency_code', $tableName, 'currency_code', '{{%money_currency}}', 'code', 'RESTRICT', 'RESTRICT');

        $this->addCommentOnTable($tableName, 'Позиции счетов на оплату');
    }

    public function safeDown()
    {
        echo "m260630_203301__create_table__shop_bill_item cannot be reverted.\n";
        return false;
    }
}
