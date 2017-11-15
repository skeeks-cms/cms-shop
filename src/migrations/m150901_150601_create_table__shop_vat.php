<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Schema;
use yii\db\Migration;

class m150901_150601_create_table__shop_vat extends Migration
{
    public function safeUp()
    {
        $tableExist = $this->db->getTableSchema("{{%shop_vat}}", true);
        if ($tableExist) {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable("{{%shop_vat}}", [
            'id' => $this->primaryKey(),

            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),

            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),

            'name' => $this->string(255)->notNull(),
            'priority' => $this->integer()->notNull()->defaultValue(100),
            'active' => $this->string(1)->notNull()->defaultValue("Y"),

            'rate' => $this->decimal(18, 2)->notNull()->defaultValue(0),

        ], $tableOptions);


        $this->createIndex('updated_by', '{{%shop_vat}}', 'updated_by');
        $this->createIndex('created_by', '{{%shop_vat}}', 'created_by');
        $this->createIndex('created_at', '{{%shop_vat}}', 'created_at');
        $this->createIndex('updated_at', '{{%shop_vat}}', 'updated_at');

        $this->createIndex('priority', '{{%shop_vat}}', 'priority');
        $this->createIndex('active', '{{%shop_vat}}', 'active');
        $this->createIndex('name', '{{%shop_vat}}', 'name');
        $this->createIndex('rate', '{{%shop_vat}}', 'rate');

        $this->execute("ALTER TABLE {{%shop_vat}} COMMENT = 'Ставки НДС';");

        $this->addForeignKey(
            'shop_vat_created_by', "{{%shop_vat}}",
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_vat_updated_by', "{{%shop_vat}}",
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );


        $this->insert('{{%shop_vat}}', [
            'name' => 'Без НДС',
            'priority' => 100,
            'active' => 'Y',
            'rate' => 0,
        ]);

        $this->insert('{{%shop_vat}}', [
            'name' => 'НДС 18%',
            'priority' => 200,
            'active' => 'Y',
            'rate' => 18,
        ]);
    }

    public function safeDown()
    {
        $this->dropForeignKey("shop_vat_updated_by", "{{%shop_vat}}");
        $this->dropForeignKey("shop_vat_updated_by", "{{%shop_vat}}");

        $this->dropTable("{{%shop_vat}}");
    }
}