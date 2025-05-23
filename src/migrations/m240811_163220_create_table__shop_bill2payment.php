<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */
use yii\db\Migration;

class m240811_163220_create_table__shop_bill2payment extends Migration
{
    public function safeUp()
    {
        $tableName = 'shop_bill2payment';
        $tableExist = $this->db->getTableSchema($tableName, true);
        if ($tableExist) {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable($tableName, [
            'id' => $this->primaryKey(),

            'created_by' => $this->integer()->null(),
            'created_at' => $this->integer()->null(),

            'shop_bill_id' => $this->integer()->notNull()->comment("Счет"),
            'shop_payment_id' => $this->integer()->notNull()->comment("Платеж"),

        ], $tableOptions);

        $this->createIndex($tableName.'__created_by', $tableName, 'created_by');
        $this->createIndex($tableName.'__created_at', $tableName, 'created_at');

        $this->createIndex($tableName.'__unique', $tableName, ['shop_bill_id', 'shop_payment_id'], true);

        $this->addCommentOnTable($tableName, 'Связь счетов и платежей');

        $this->addForeignKey(
            "{$tableName}__created_by", $tableName,
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            "{$tableName}__shop_payment_id", $tableName,
            'shop_payment_id', '{{%shop_payment}}', 'id', 'CASCADE', 'CASCADE'
        );

        $this->addForeignKey(
            "{$tableName}__shop_bill_id", $tableName,
            'shop_bill_id', '{{%shop_bill}}', 'id', 'CASCADE', 'CASCADE'
        );
    }

    public function safeDown()
    {}
}