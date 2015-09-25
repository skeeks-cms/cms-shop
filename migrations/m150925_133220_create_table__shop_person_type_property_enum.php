<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 10.03.2015
 */
use yii\db\Schema;
use yii\db\Migration;

class m150925_133220_create_table__shop_person_type_property_enum extends Migration
{
    public function up()
    {
        $tableExist = $this->db->getTableSchema("{{%shop_person_type_property_enum}}", true);
        if ($tableExist)
        {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable("{{%shop_person_type_property_enum}}", [
            'id'                    => Schema::TYPE_PK,

            'created_by'            => Schema::TYPE_INTEGER . ' NULL',
            'updated_by'            => Schema::TYPE_INTEGER . ' NULL',

            'created_at'            => Schema::TYPE_INTEGER . ' NULL',
            'updated_at'            => Schema::TYPE_INTEGER . ' NULL',

            'property_id'           => Schema::TYPE_INTEGER . ' NULL',

            'value'                 => Schema::TYPE_STRING . '(255) NOT NULL',
            'def'                   => "CHAR(1) NOT NULL DEFAULT 'N'",
            'code'                  => Schema::TYPE_STRING. '(32) NOT NULL',
            'priority'              => Schema::TYPE_INTEGER. "(11) NOT NULL DEFAULT '500'",

        ], $tableOptions);

        $this->execute("ALTER TABLE {{%shop_person_type_property_enum}} ADD INDEX(updated_by);");
        $this->execute("ALTER TABLE {{%shop_person_type_property_enum}} ADD INDEX(created_by);");

        $this->execute("ALTER TABLE {{%shop_person_type_property_enum}} ADD INDEX(created_at);");
        $this->execute("ALTER TABLE {{%shop_person_type_property_enum}} ADD INDEX(updated_at);");

        $this->execute("ALTER TABLE {{%shop_person_type_property_enum}} ADD INDEX(property_id);");
        $this->execute("ALTER TABLE {{%shop_person_type_property_enum}} ADD INDEX(def);");
        $this->execute("ALTER TABLE {{%shop_person_type_property_enum}} ADD INDEX(code);");
        $this->execute("ALTER TABLE {{%shop_person_type_property_enum}} ADD INDEX(priority);");

        $this->execute("ALTER TABLE {{%shop_person_type_property_enum}} ADD INDEX(value);");

        $this->execute("ALTER TABLE {{%shop_person_type_property_enum}} COMMENT = 'Справочник значений свойств типа список';");

        $this->addForeignKey(
            'shop_person_type_property_enum_created_by', "{{%shop_person_type_property_enum}}",
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_person_type_property_enum_updated_by', "{{%shop_person_type_property_enum}}",
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_person_type_property_enum_property_id', "{{%shop_person_type_property_enum}}",
            'property_id', '{{%shop_person_type_property}}', 'id', 'CASCADE', 'CASCADE'
        );
    }

    public function down()
    {
        $this->dropForeignKey("shop_person_type_property_enum_created_by", "{{%shop_person_type_property_enum}}");
        $this->dropForeignKey("shop_person_type_property_enum_updated_by", "{{%shop_person_type_property_enum}}");

        $this->dropForeignKey("shop_person_type_property_enum_property_id", "{{%shop_person_type_property_enum}}");

        $this->dropTable("{{%shop_person_type_property_enum}}");
    }
}