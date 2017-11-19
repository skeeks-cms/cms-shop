<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 10.03.2015
 */

use yii\db\Schema;
use yii\db\Migration;

class m150925_113220_create_table__shop_person_type_property extends Migration
{
    public function safeUp()
    {
        $tableExist = $this->db->getTableSchema("{{%shop_person_type_property}}", true);
        if ($tableExist) {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable("{{%shop_person_type_property}}", [
            'id' => Schema::TYPE_PK,

            'created_by' => Schema::TYPE_INTEGER . ' NULL',
            'updated_by' => Schema::TYPE_INTEGER . ' NULL',

            'created_at' => Schema::TYPE_INTEGER . ' NULL',
            'updated_at' => Schema::TYPE_INTEGER . ' NULL',

            'name' => Schema::TYPE_STRING . '(255) NOT NULL',
            'code' => Schema::TYPE_STRING . '(64) NULL',

            'active' => "CHAR(1) NOT NULL DEFAULT 'Y'",
            'priority' => "INT NOT NULL DEFAULT '500'",
            'property_type' => "CHAR(1) NOT NULL DEFAULT 'S'",
            'list_type' => "CHAR(1) NOT NULL DEFAULT 'L'",
            'multiple' => "CHAR(1) NOT NULL DEFAULT 'N'",
            'multiple_cnt' => "INT NULL",
            'with_description' => "CHAR(1) NULL",
            'searchable' => "CHAR(1) NOT NULL DEFAULT 'N'",
            'filtrable' => "CHAR(1) NOT NULL DEFAULT 'N'",
            'is_required' => "CHAR(1) NULL",
            'version' => "INT NOT NULL DEFAULT '1'",
            'component' => "VARCHAR(255) NULL",
            'component_settings' => "TEXT NULL",
            'hint' => "VARCHAR(255) NULL",
            'smart_filtrable' => "CHAR(1) NOT NULL DEFAULT 'N'",

            'shop_person_type_id' => $this->integer()->notNull(),

            'is_order_location_delivery' => $this->string(1)->notNull()->defaultValue('N'),
            //Значение свойства будет использовано как местоположение покупателя для расчета стоимости доставки (только для свойств типа LOCATION)
            'is_order_location_tax' => $this->string(1)->notNull()->defaultValue('N'),
            //Значение свойства будет использовано как местоположение покупателя для расчета налогов (только для свойств типа LOCATION)
            'is_order_postcode' => $this->string(1)->notNull()->defaultValue('N'),
            //Значение свойства будет использовано как почтовый индекс покупателя для расчета стоимости доставки

            'is_user_email' => $this->string(1)->notNull()->defaultValue('N'),
            //Значение свойства будет использовано как E-Mail при регистрации нового пользователя
            'is_user_phone' => $this->string(1)->notNull()->defaultValue('N'),
            //Значение свойства будет использовано как E-Mail при регистрации нового пользователя
            'is_user_username' => $this->string(1)->notNull()->defaultValue('N'),
            //Значение свойства будет использовано как E-Mail при регистрации нового пользователя
            'is_user_name' => $this->string(1)->notNull()->defaultValue('N'),
            //Значение свойства будет использовано как E-Mail при регистрации нового пользователя

            'is_buyer_name' => $this->string(1)->notNull()->defaultValue('N'),
            //Значение свойства будет использовано как E-Mail при регистрации нового пользователя

        ], $tableOptions);

        $this->createIndex('shop_person_type_property__updated_by', '{{%shop_person_type_property}}', 'updated_by');
        $this->createIndex('shop_person_type_property__created_by', '{{%shop_person_type_property}}', 'created_by');
        $this->createIndex('shop_person_type_property__created_at', '{{%shop_person_type_property}}', 'created_at');
        $this->createIndex('shop_person_type_property__updated_at', '{{%shop_person_type_property}}', 'updated_at');
        $this->createIndex('shop_person_type_property__name', '{{%shop_person_type_property}}', 'name');

        $this->createIndex('shop_person_type_property__active', '{{%shop_person_type_property}}', 'active');
        $this->createIndex('shop_person_type_property__priority', '{{%shop_person_type_property}}', 'priority');
        $this->createIndex('shop_person_type_property__property_type', '{{%shop_person_type_property}}', 'property_type');
        $this->createIndex('shop_person_type_property__list_type', '{{%shop_person_type_property}}', 'list_type');
        $this->createIndex('shop_person_type_property__multiple', '{{%shop_person_type_property}}', 'multiple');
        $this->createIndex('shop_person_type_property__multiple_cnt', '{{%shop_person_type_property}}', 'multiple_cnt');
        $this->createIndex('shop_person_type_property__with_description', '{{%shop_person_type_property}}', 'with_description');
        $this->createIndex('shop_person_type_property__searchable', '{{%shop_person_type_property}}', 'searchable');
        $this->createIndex('shop_person_type_property__filtrable', '{{%shop_person_type_property}}', 'filtrable');
        $this->createIndex('shop_person_type_property__is_required', '{{%shop_person_type_property}}', 'is_required');
        $this->createIndex('shop_person_type_property__version', '{{%shop_person_type_property}}', 'version');
        $this->createIndex('shop_person_type_property__component', '{{%shop_person_type_property}}', 'component');
        $this->createIndex('shop_person_type_property__hint', '{{%shop_person_type_property}}', 'hint');
        $this->createIndex('shop_person_type_property__smart_filtrable', '{{%shop_person_type_property}}', 'smart_filtrable');

        $this->createIndex('shop_person_type_property__shop_person_type_id', '{{%shop_person_type_property}}', 'shop_person_type_id');
        $this->createIndex('shop_person_type_property__type_code', '{{%shop_person_type_property}}', ['shop_person_type_id', 'code'], true);

        $this->createIndex('shop_person_type_property__is_order_location_delivery', '{{%shop_person_type_property}}', 'is_order_location_delivery');
        $this->createIndex('shop_person_type_property__is_order_location_tax', '{{%shop_person_type_property}}', 'is_order_location_tax');
        $this->createIndex('shop_person_type_property__is_order_postcode', '{{%shop_person_type_property}}', 'is_order_postcode');
        $this->createIndex('shop_person_type_property__is_user_email', '{{%shop_person_type_property}}', 'is_user_email');
        $this->createIndex('shop_person_type_property__is_user_phone', '{{%shop_person_type_property}}', 'is_user_phone');
        $this->createIndex('shop_person_type_property__is_user_username', '{{%shop_person_type_property}}', 'is_user_username');
        $this->createIndex('shop_person_type_property__is_user_name', '{{%shop_person_type_property}}', 'is_user_name');
        $this->createIndex('shop_person_type_property__is_buyer_name', '{{%shop_person_type_property}}', 'is_buyer_name');

        $this->addForeignKey(
            'shop_person_type_property_created_by', "{{%shop_person_type_property}}",
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_person_type_property_updated_by', "{{%shop_person_type_property}}",
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_person_type_property__shop_person_type_id', "{{%shop_person_type_property}}",
            'shop_person_type_id', '{{%shop_person_type}}', 'id', 'RESTRICT', 'RESTRICT'
        );

    }

    public function down()
    {
        $this->dropForeignKey("shop_person_type_property_created_by", "{{%shop_person_type_property}}");
        $this->dropForeignKey("shop_person_type_property_updated_by", "{{%shop_person_type_property}}");

        $this->dropForeignKey("shop_person_type_property__shop_person_type_id", "{{%shop_person_type_property}}");

        $this->dropTable("{{%shop_person_type_property}}");
    }
}