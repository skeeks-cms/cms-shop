<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 10.03.2015
 */
use yii\db\Schema;
use yii\db\Migration;

class m150925_190601_create_table__shop_buyer_property extends Migration
{
    public function up()
    {
        $tableExist = $this->db->getTableSchema("{{%shop_buyer_property}}", true);
        if ($tableExist)
        {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable("{{%shop_buyer_property}}", [
            'id'                    => Schema::TYPE_PK,

            'created_by'            => Schema::TYPE_INTEGER . ' NULL',
            'updated_by'            => Schema::TYPE_INTEGER . ' NULL',

            'created_at'            => Schema::TYPE_INTEGER . ' NULL',
            'updated_at'            => Schema::TYPE_INTEGER . ' NULL',

            'property_id'           => Schema::TYPE_INTEGER . ' NULL',
            'element_id'            => Schema::TYPE_INTEGER . ' NULL',

            'value'                 => Schema::TYPE_STRING . '(255) NOT NULL',

            'value_enum'            => Schema::TYPE_INTEGER . '(11) NULL',
            'value_num'             => 'decimal(18,4) NULL',
            'description'           => Schema::TYPE_STRING . '(255) NULL',

        ], $tableOptions);

        $this->execute("ALTER TABLE {{%shop_buyer_property}} ADD INDEX(updated_by);");
        $this->execute("ALTER TABLE {{%shop_buyer_property}} ADD INDEX(created_by);");

        $this->execute("ALTER TABLE {{%shop_buyer_property}} ADD INDEX(created_at);");
        $this->execute("ALTER TABLE {{%shop_buyer_property}} ADD INDEX(updated_at);");

        $this->execute("ALTER TABLE {{%shop_buyer_property}} ADD INDEX(property_id);");
        $this->execute("ALTER TABLE {{%shop_buyer_property}} ADD INDEX(element_id);");

        $this->execute("ALTER TABLE {{%shop_buyer_property}} ADD INDEX(value);");
        $this->execute("ALTER TABLE {{%shop_buyer_property}} ADD INDEX(value_enum);");
        $this->execute("ALTER TABLE {{%shop_buyer_property}} ADD INDEX(value_num);");
        $this->execute("ALTER TABLE {{%shop_buyer_property}} ADD INDEX(description);");

        $this->execute("ALTER TABLE {{%shop_buyer_property}} COMMENT = 'Связь свойства и значения';");

        $this->addForeignKey(
            'shop_buyer_property_created_by', "{{%shop_buyer_property}}",
            'created_by', '{{%cms_user}}', 'id', 'RESTRICT', 'RESTRICT'
        );

        $this->addForeignKey(
            'shop_buyer_property_updated_by', "{{%shop_buyer_property}}",
            'updated_by', '{{%cms_user}}', 'id', 'RESTRICT', 'RESTRICT'
        );

        $this->addForeignKey(
            'shop_buyer_property_element_id', "{{%shop_buyer_property}}",
            'element_id', '{{%shop_buyer}}', 'id', 'CASCADE', 'CASCADE'
        );

        $this->addForeignKey(
            'shop_buyer_property_property_id', "{{%shop_buyer_property}}",
            'property_id', '{{%shop_person_type_property}}', 'id', 'CASCADE', 'CASCADE'
        );
    }

    public function down()
    {
        $this->dropForeignKey("shop_buyer_property_created_by", "{{%shop_buyer_property}}");
        $this->dropForeignKey("shop_buyer_property_updated_by", "{{%shop_buyer_property}}");

        $this->dropForeignKey("shop_buyer_property_element_id", "{{%shop_buyer_property}}");
        $this->dropForeignKey("shop_buyer_property_property_id", "{{%shop_buyer_property}}");

        $this->dropTable("{{%shop_buyer_property}}");
    }
}