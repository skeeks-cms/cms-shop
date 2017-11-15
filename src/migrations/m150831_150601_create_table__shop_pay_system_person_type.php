<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 31.08.2015
 */

use yii\db\Schema;
use yii\db\Migration;

class m150831_150601_create_table__shop_pay_system_person_type extends Migration
{
    public function safeUp()
    {
        $tableExist = $this->db->getTableSchema("{{%shop_pay_system_person_type}}", true);
        if ($tableExist) {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable("{{%shop_pay_system_person_type}}", [

            'pay_system_id' => $this->integer()->notNull(),
            'person_type_id' => $this->integer()->notNull(),

        ], $tableOptions);


        $this->createIndex('pay_system_id__person_type_id', '{{%shop_pay_system_person_type}}',
            ['pay_system_id', 'person_type_id'], true);

        $this->execute("ALTER TABLE {{%shop_pay_system_person_type}} COMMENT = 'Связь платежных систем с плательщиками';");

        $this->addForeignKey(
            'shop_pay_system_person_type_person_type_id', "{{%shop_pay_system_person_type}}",
            'person_type_id', '{{%shop_person_type}}', 'id', 'CASCADE', 'CASCADE'
        );

        $this->addForeignKey(
            'shop_pay_system_person_type_shop_pay_system', "{{%shop_pay_system_person_type}}",
            'pay_system_id', '{{%shop_pay_system}}', 'id', 'CASCADE', 'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey("shop_pay_system_person_type_site_code", "{{%shop_pay_system_person_type}}");
        $this->dropForeignKey("shop_pay_system_person_type_person_type_id", "{{%shop_pay_system_person_type}}");

        $this->dropTable("{{%shop_pay_system_person_type}}");
    }
}