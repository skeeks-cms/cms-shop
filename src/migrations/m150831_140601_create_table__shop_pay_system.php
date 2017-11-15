<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Schema;
use yii\db\Migration;

class m150831_140601_create_table__shop_pay_system extends Migration
{
    public function safeUp()
    {
        $tableExist = $this->db->getTableSchema("{{%shop_pay_system}}", true);
        if ($tableExist) {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable("{{%shop_pay_system}}", [
            'id' => $this->primaryKey(),

            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),

            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),

            'name' => $this->string(255)->notNull()->unique(),
            'priority' => $this->integer()->notNull()->defaultValue(100),
            'active' => $this->string(1)->notNull()->defaultValue("Y"),

            'description' => $this->text(),

        ], $tableOptions);


        $this->createIndex('updated_by', '{{%shop_pay_system}}', 'updated_by');
        $this->createIndex('created_by', '{{%shop_pay_system}}', 'created_by');
        $this->createIndex('created_at', '{{%shop_pay_system}}', 'created_at');
        $this->createIndex('updated_at', '{{%shop_pay_system}}', 'updated_at');

        $this->createIndex('priority', '{{%shop_pay_system}}', 'priority');
        $this->createIndex('active', '{{%shop_pay_system}}', 'active');

        $this->execute("ALTER TABLE {{%shop_pay_system}} COMMENT = 'Платежные системы';");

        $this->addForeignKey(
            'shop_pay_system_created_by', "{{%shop_pay_system}}",
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_pay_system_updated_by', "{{%shop_pay_system}}",
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );


        $this->insert('{{%shop_pay_system}}', [
            'name' => 'Наличный расчет',
            'priority' => '50',
            'active' => 'Y',
        ]);

        $this->insert('{{%shop_pay_system}}', [
            'name' => 'Кредитная карта',
            'priority' => '60',
            'active' => 'N',
        ]);

        $this->insert('{{%shop_pay_system}}', [
            'name' => 'Оплата в платежной системе Web Money',
            'priority' => '70',
            'active' => 'N',
        ]);

        $this->insert('{{%shop_pay_system}}', [
            'name' => 'Оплата в платежной системе Яндекс.Деньги',
            'priority' => '80',
            'active' => 'N',
        ]);

        $this->insert('{{%shop_pay_system}}', [
            'name' => 'Сбербанк',
            'priority' => '90',
            'active' => 'N',
        ]);

        $this->insert('{{%shop_pay_system}}', [
            'name' => 'Счет',
            'priority' => '100',
            'active' => 'N',
        ]);

    }

    public function safeDown()
    {
        $this->dropForeignKey("shop_pay_system_updated_by", "{{%shop_pay_system}}");
        $this->dropForeignKey("shop_pay_system_updated_by", "{{%shop_pay_system}}");

        $this->dropTable("{{%shop_pay_system}}");
    }
}