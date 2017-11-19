<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Schema;
use yii\db\Migration;

class m150901_170601_create_table__shop_tax_rate extends Migration
{
    public function safeUp()
    {
        $tableExist = $this->db->getTableSchema("{{%shop_tax_rate}}", true);
        if ($tableExist) {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable("{{%shop_tax_rate}}", [
            'id' => $this->primaryKey(),

            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),

            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),

            'tax_id' => $this->integer()->notNull(),
            'person_type_id' => $this->integer()->notNull(),

            'value' => $this->decimal(18, 4)->notNull()->defaultValue(0),

            'currency' => $this->string(3),
            'is_percent' => $this->string(1)->notNull()->defaultValue('Y'),
            'is_in_price' => $this->string(1)->notNull()->defaultValue('N'),
            'priority' => $this->integer(1)->notNull()->defaultValue(100),

            'active' => $this->string(1)->notNull()->defaultValue("Y"),

        ], $tableOptions);


        $this->createIndex('shop_tax_rate__updated_by', '{{%shop_tax_rate}}', 'updated_by');
        $this->createIndex('shop_tax_rate__created_by', '{{%shop_tax_rate}}', 'created_by');
        $this->createIndex('shop_tax_rate__created_at', '{{%shop_tax_rate}}', 'created_at');
        $this->createIndex('shop_tax_rate__updated_at', '{{%shop_tax_rate}}', 'updated_at');

        $this->createIndex('shop_tax_rate__tax_id', '{{%shop_tax_rate}}', 'tax_id');
        $this->createIndex('shop_tax_rate__person_type_id', '{{%shop_tax_rate}}', 'person_type_id');
        $this->createIndex('shop_tax_rate__value', '{{%shop_tax_rate}}', 'value');
        $this->createIndex('shop_tax_rate__currency', '{{%shop_tax_rate}}', 'currency');
        $this->createIndex('shop_tax_rate__is_percent', '{{%shop_tax_rate}}', 'is_percent');
        $this->createIndex('shop_tax_rate__is_in_price', '{{%shop_tax_rate}}', 'is_in_price');
        $this->createIndex('shop_tax_rate__priority', '{{%shop_tax_rate}}', 'priority');
        $this->createIndex('shop_tax_rate__active', '{{%shop_tax_rate}}', 'active');

        $this->addForeignKey(
            'shop_tax_rate_created_by', "{{%shop_tax_rate}}",
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_tax_rate_updated_by', "{{%shop_tax_rate}}",
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );


        $this->addForeignKey(
            'shop_tax_rate_person_type_id', "{{%shop_tax_rate}}",
            'person_type_id', '{{%shop_person_type}}', 'id', 'CASCADE', 'CASCADE'
        );

        $this->addForeignKey(
            'shop_tax_rate_tax_id', "{{%shop_tax_rate}}",
            'tax_id', '{{%shop_tax}}', 'id', 'CASCADE', 'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey("shop_tax_rate_updated_by", "{{%shop_tax_rate}}");
        $this->dropForeignKey("shop_tax_rate_updated_by", "{{%shop_tax_rate}}");
        $this->dropForeignKey("shop_tax_rate_person_type_id", "{{%shop_tax_rate}}");
        $this->dropForeignKey("shop_tax_rate_tax_id", "{{%shop_tax_rate}}");

        $this->dropTable("{{%shop_tax_rate}}");
    }
}