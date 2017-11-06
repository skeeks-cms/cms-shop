<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */
use yii\db\Schema;
use yii\db\Migration;

class m150828_130601_create_table__shop_person_type extends Migration
{
    public function safeUp()
    {
        $tableExist = $this->db->getTableSchema("{{%shop_person_type}}", true);
        if ($tableExist)
        {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable("{{%shop_person_type}}", [
            'id'                    => $this->primaryKey(),

            'created_by'            => $this->integer(),
            'updated_by'            => $this->integer(),

            'created_at'            => $this->integer(),
            'updated_at'            => $this->integer(),

            'name'                  => $this->string(255)->notNull()->unique(),
            'priority'              => $this->integer()->notNull()->defaultValue(100),
            'active'                => $this->string(1)->notNull()->defaultValue("Y"),

        ], $tableOptions);


        $this->createIndex('updated_by', '{{%shop_person_type}}', 'updated_by');
        $this->createIndex('created_by', '{{%shop_person_type}}', 'created_by');
        $this->createIndex('created_at', '{{%shop_person_type}}', 'created_at');
        $this->createIndex('updated_at', '{{%shop_person_type}}', 'updated_at');

        $this->createIndex('priority', '{{%shop_person_type}}', 'priority');
        $this->createIndex('active', '{{%shop_person_type}}', 'active');

        $this->execute("ALTER TABLE {{%shop_person_type}} COMMENT = 'Типы плательщиков';");

        $this->addForeignKey(
            'shop_person_type_created_by', "{{%shop_person_type}}",
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_person_type_updated_by', "{{%shop_person_type}}",
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );


        $this->insert('{{%shop_person_type}}', [
            'name'          => 'Физическое лицо',
            'priority'      => '100',
            'active'        => 'Y',
        ]);

        $this->insert('{{%shop_person_type}}', [
            'name'          => 'Юридическое лицо',
            'priority'      => '200',
            'active'        => 'Y',
        ]);
    }

    public function safeDown()
    {
        $this->dropForeignKey("shop_person_type_updated_by", "{{%shop_person_type}}");
        $this->dropForeignKey("shop_person_type_updated_by", "{{%shop_person_type}}");

        $this->dropTable("{{%shop_person_type}}");
    }
}