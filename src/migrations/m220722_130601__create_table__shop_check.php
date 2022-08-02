<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m220722_130601__create_table__shop_check extends Migration
{
    public function safeUp()
    {
        $tableName = 'shop_check';
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

            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),

            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),

            'cms_site_id' => $this->integer()->notNull(),

            'status'   => $this->string(255)->notNull()->defaultValue("new")->comment("Статус чека (new, wait, approved, error)"),
            'doc_type' => $this->string(255)->notNull()->defaultValue("sale")->comment("Тип документа (sale, return, buy, bure_return)"),

            'shop_store_id'          => $this->integer()->comment('Магазин'),
            'shop_cashebox_id'       => $this->integer()->comment('Касса'),
            'shop_cashebox_shift_id' => $this->integer()->comment('Смена'),
            'shop_order_id'          => $this->integer()->comment('Заказ'),
            'cms_user_id'            => $this->integer()->comment('Клиент'),



            'email' => $this->string(255)->comment('Email или телефон клиента')->notNull(),

            'cashier_name'        => $this->string(255)->comment('Кассир'),
            'cashier_position'    => $this->string(255)->comment('Должность'),
            'cashier_cms_user_id' => $this->integer()->comment('Кассир - пользователь'),

            'tax_mode' => $this->string(255)->comment('Применяемая система налогообложения'),

            'amount'                        => $this->decimal(18, 2)->notNull()->defaultValue(0)->comment("Сумма чека"),
            'moneyPositions'                => $this->text()->comment("Json объект с типом оплаты и суммы платежа"),
            'inventPositions'               => $this->text()->comment("Json объект с позициями"),

            //Это уже данные из ответа кассы
            //Далее фискальные данные
            'fiscal_date_at'                => $this->integer(),
            'fiscal_date'                   => $this->string(255),
            'fiscal_kkt_number'             => $this->string(255),
            'fiscal_fn_number'              => $this->string(255),
            'fiscal_fn_doc_number'          => $this->string(255),
            'fiscal_fn_doc_mark'            => $this->string(255),
            'fiscal_shift_number'           => $this->string(255),
            'fiscal_check_number'           => $this->string(255),
            'fiscal_ecr_registration_umber' => $this->string(255),

            'qr' => $this->string(255)->comment("QR код чека"),

            'error_message' => $this->text()->comment('Сообщение об ошибке'),

            'is_print' => $this->integer(1)->defaultValue(0)->comment('Печатать бумажную версию чека?'),

            'seller_address'       => $this->string(255)->comment("Адрес торговой точки"),
            'seller_name'          => $this->string(255)->comment("Название юр. лица (ИП или ООО и т.д.)"),
            'seller_inn'           => $this->string(255)->comment("ИНН торговой точки"),
            'kkm_payments_address' => $this->string(255)->comment("Платежные адреса (сайт; Разъездная; Магазин)"),

            'provider_uid'           => $this->string(255)->comment('Уникальный ID внешней системы'),
            'provider_request_data'  => $this->text()->comment('Данные для создания объекта чека'),
            'provider_response_data' => $this->text()->comment('Все данные из внешней системы по чеку'),

        ], $tableOptions);

        $this->addCommentOnTable($tableName, "Кассовые чеки");

        $this->createIndex($tableName.'__updated_by', $tableName, 'updated_by');
        $this->createIndex($tableName.'__created_by', $tableName, 'created_by');
        $this->createIndex($tableName.'__created_at', $tableName, 'created_at');
        $this->createIndex($tableName.'__updated_at', $tableName, 'updated_at');

        $this->createIndex($tableName.'__status', $tableName, 'status');

        $this->createIndex($tableName.'__shop_store_id', $tableName, 'shop_store_id');
        $this->createIndex($tableName.'__shop_cashebox_id', $tableName, 'shop_cashebox_id');
        $this->createIndex($tableName.'__shop_cashebox_shift_id', $tableName, 'shop_cashebox_shift_id');
        $this->createIndex($tableName.'__shop_order_id', $tableName, 'shop_order_id');
        $this->createIndex($tableName.'__cms_user_id', $tableName, 'cms_user_id');

        $this->addForeignKey(
            "{$tableName}__cms_site_id", $tableName,
            'cms_site_id', '{{%cms_site}}', 'id', 'CASCADE', 'CASCADE'
        );

        $this->addForeignKey(
            "{$tableName}__created_by", $tableName,
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            "{$tableName}__updated_by", $tableName,
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            "{$tableName}__shop_store_id", $tableName,
            'shop_store_id', '{{%shop_store}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            "{$tableName}__shop_cashebox_id", $tableName,
            'shop_cashebox_id', '{{%shop_cashebox}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            "{$tableName}__shop_cashebox_shift_id", $tableName,
            'shop_cashebox_shift_id', '{{%shop_cashebox_shift}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            "{$tableName}__shop_order_id", $tableName,
            'shop_order_id', '{{%shop_order}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            "{$tableName}__cms_user_id", $tableName,
            'cms_user_id', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            "{$tableName}__cashier_cms_user_id", $tableName,
            'cashier_cms_user_id', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );
    }

    public function safeDown()
    {
        echo "m200212_130601__create_table__shop_supplier_property_option cannot be reverted.\n";
        return false;
    }
}