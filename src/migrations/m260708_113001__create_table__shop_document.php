<?php
/**
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS
 */

use yii\db\Migration;

class m260708_113001__create_table__shop_document extends Migration
{
    public function safeUp()
    {
        $this->createDocumentTable();
        $this->createDocumentItemTable();
        $this->createDocument2billTable();
        $this->createDocument2dealTable();
    }

    protected function createDocumentTable()
    {
        $tableName = 'shop_document';
        if ($this->db->getTableSchema($tableName, true)) {
            return;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable($tableName, [
            'id'                                    => $this->primaryKey(),
            'created_by'                            => $this->integer(),
            'updated_by'                            => $this->integer(),
            'created_at'                            => $this->integer(),
            'updated_at'                            => $this->integer(),

            'type'                                  => $this->string(32)->notNull()->defaultValue('act')->comment('Тип документа'),
            'status'                                => $this->string(32)->notNull()->defaultValue('issued')->comment('Статус'),
            'number'                                => $this->string(64)->null()->comment('Номер документа'),
            'issued_at'                             => $this->integer()->null()->comment('Дата документа'),

            'cms_company_id'                        => $this->integer()->null()->comment('Компания'),
            'cms_user_id'                           => $this->integer()->null()->comment('Клиент'),
            'seller_contractor_id'                  => $this->integer()->null()->comment('Продавец / исполнитель'),
            'buyer_contractor_id'                   => $this->integer()->null()->comment('Покупатель / заказчик'),

            'seller_contractor_type'                => $this->string(32)->null()->comment('Тип продавца на момент создания'),
            'seller_contractor_name'                => $this->string(255)->null()->comment('Продавец на момент создания'),
            'seller_contractor_full_name'           => $this->string(255)->null()->comment('Полное имя продавца на момент создания'),
            'seller_contractor_inn'                 => $this->string(32)->null()->comment('ИНН продавца на момент создания'),
            'seller_contractor_kpp'                 => $this->string(32)->null()->comment('КПП продавца на момент создания'),
            'seller_contractor_ogrn'                => $this->string(32)->null()->comment('ОГРН продавца на момент создания'),
            'seller_contractor_address'             => $this->string(255)->null()->comment('Адрес продавца на момент создания'),
            'seller_contractor_mailing_postcode'    => $this->string(32)->null()->comment('Индекс продавца на момент создания'),

            'buyer_contractor_type'                 => $this->string(32)->null()->comment('Тип покупателя на момент создания'),
            'buyer_contractor_name'                 => $this->string(255)->null()->comment('Покупатель на момент создания'),
            'buyer_contractor_full_name'            => $this->string(255)->null()->comment('Полное имя покупателя на момент создания'),
            'buyer_contractor_inn'                  => $this->string(32)->null()->comment('ИНН покупателя на момент создания'),
            'buyer_contractor_kpp'                  => $this->string(32)->null()->comment('КПП покупателя на момент создания'),
            'buyer_contractor_ogrn'                 => $this->string(32)->null()->comment('ОГРН покупателя на момент создания'),
            'buyer_contractor_address'              => $this->string(255)->null()->comment('Адрес покупателя на момент создания'),
            'buyer_contractor_mailing_postcode'     => $this->string(32)->null()->comment('Индекс покупателя на момент создания'),

            'amount'                                => $this->decimal(18, 4)->notNull()->defaultValue(0)->comment('Сумма'),
            'discount_amount'                       => $this->decimal(18, 4)->notNull()->defaultValue(0)->comment('Скидка'),
            'currency_code'                         => $this->string(3)->notNull()->defaultValue('RUB')->comment('Валюта'),
            'description'                           => $this->text()->null()->comment('Основание или комментарий'),
            'comment_before'                        => $this->text()->null()->comment('Комментарий перед таблицей'),
            'comment_after'                         => $this->text()->null()->comment('Комментарий после таблицы'),
            'document_data'                         => $this->text()->null()->comment('Специфичные данные документа'),

            'code'                                  => $this->string(255)->notNull()->unique()->comment('Уникальный код документа'),
            'external_id'                           => $this->string(255)->null()->comment('Идентификатор внешней системы'),
            'external_name'                         => $this->string(255)->null()->comment('Внешняя система'),
            'external_data'                         => $this->text()->null()->comment('Данные внешней системы'),
        ], $tableOptions);

        $this->createIndex($tableName.'__created_by', $tableName, 'created_by');
        $this->createIndex($tableName.'__updated_by', $tableName, 'updated_by');
        $this->createIndex($tableName.'__created_at', $tableName, 'created_at');
        $this->createIndex($tableName.'__updated_at', $tableName, 'updated_at');
        $this->createIndex($tableName.'__type', $tableName, 'type');
        $this->createIndex($tableName.'__status', $tableName, 'status');
        $this->createIndex($tableName.'__number', $tableName, 'number');
        $this->createIndex($tableName.'__issued_at', $tableName, 'issued_at');
        $this->createIndex($tableName.'__cms_company_id', $tableName, 'cms_company_id');
        $this->createIndex($tableName.'__cms_user_id', $tableName, 'cms_user_id');
        $this->createIndex($tableName.'__seller_contractor_id', $tableName, 'seller_contractor_id');
        $this->createIndex($tableName.'__buyer_contractor_id', $tableName, 'buyer_contractor_id');
        $this->createIndex($tableName.'__currency_code', $tableName, 'currency_code');
        $this->createIndex($tableName.'__amount', $tableName, 'amount');

        $this->addCommentOnTable($tableName, 'Бухгалтерские документы: акты, УПД и другие первичные документы');

        $this->addForeignKey($tableName.'__created_by', $tableName, 'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL');
        $this->addForeignKey($tableName.'__updated_by', $tableName, 'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL');
        $this->addForeignKey($tableName.'__cms_company_id', $tableName, 'cms_company_id', '{{%cms_company}}', 'id', 'SET NULL', 'SET NULL');
        $this->addForeignKey($tableName.'__cms_user_id', $tableName, 'cms_user_id', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL');
        $this->addForeignKey($tableName.'__seller_contractor_id', $tableName, 'seller_contractor_id', '{{%cms_contractor}}', 'id', 'SET NULL', 'SET NULL');
        $this->addForeignKey($tableName.'__buyer_contractor_id', $tableName, 'buyer_contractor_id', '{{%cms_contractor}}', 'id', 'SET NULL', 'SET NULL');
        $this->addForeignKey($tableName.'__currency_code', $tableName, 'currency_code', '{{%money_currency}}', 'code', 'RESTRICT', 'RESTRICT');
    }

    protected function createDocumentItemTable()
    {
        $tableName = 'shop_document_item';
        if ($this->db->getTableSchema($tableName, true)) {
            return;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable($tableName, [
            'id'               => $this->primaryKey(),
            'created_by'       => $this->integer(),
            'updated_by'       => $this->integer(),
            'created_at'       => $this->integer(),
            'updated_at'       => $this->integer(),
            'shop_document_id' => $this->integer()->notNull()->comment('Документ'),
            'shop_product_id'  => $this->integer()->null()->comment('Товар или услуга'),
            'source_shop_bill_id'      => $this->integer()->null()->comment('Исходный счет'),
            'source_shop_bill_item_id' => $this->integer()->null()->comment('Исходная позиция счета'),
            'name'             => $this->string(255)->notNull()->comment('Наименование'),
            'measure_name'     => $this->string(50)->notNull()->defaultValue('шт')->comment('Ед. изм.'),
            'quantity'         => $this->decimal(18, 4)->notNull()->defaultValue(1)->comment('Количество'),
            'price'            => $this->decimal(18, 4)->notNull()->defaultValue(0)->comment('Цена'),
            'amount'           => $this->decimal(18, 4)->notNull()->defaultValue(0)->comment('Сумма'),
            'discount_amount'  => $this->decimal(18, 4)->notNull()->defaultValue(0)->comment('Сумма скидки'),
            'discount_value'   => $this->string(32)->null()->comment('Значение скидки'),
            'discount_name'    => $this->string(255)->null()->comment('Название скидки'),
            'currency_code'    => $this->string(3)->notNull()->defaultValue('RUB')->comment('Валюта'),
            'vat_name'         => $this->string(32)->notNull()->defaultValue('Без НДС')->comment('НДС'),
            'extra_data'       => $this->text()->null()->comment('Специфичные данные позиции'),
            'sort'             => $this->integer()->notNull()->defaultValue(100)->comment('Сортировка'),
        ], $tableOptions);

        $this->createIndex($tableName.'__created_by', $tableName, 'created_by');
        $this->createIndex($tableName.'__updated_by', $tableName, 'updated_by');
        $this->createIndex($tableName.'__shop_document_id', $tableName, 'shop_document_id');
        $this->createIndex($tableName.'__shop_product_id', $tableName, 'shop_product_id');
        $this->createIndex($tableName.'__source_shop_bill_id', $tableName, 'source_shop_bill_id');
        $this->createIndex($tableName.'__source_shop_bill_item_id', $tableName, 'source_shop_bill_item_id');
        $this->createIndex($tableName.'__currency_code', $tableName, 'currency_code');
        $this->createIndex($tableName.'__sort', $tableName, 'sort');

        $this->addForeignKey($tableName.'__created_by', $tableName, 'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL');
        $this->addForeignKey($tableName.'__updated_by', $tableName, 'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL');
        $this->addForeignKey($tableName.'__shop_document_id', $tableName, 'shop_document_id', '{{%shop_document}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey($tableName.'__shop_product_id', $tableName, 'shop_product_id', '{{%shop_product}}', 'id', 'SET NULL', 'SET NULL');
        $this->addForeignKey($tableName.'__source_shop_bill_id', $tableName, 'source_shop_bill_id', '{{%shop_bill}}', 'id', 'SET NULL', 'SET NULL');
        $this->addForeignKey($tableName.'__source_shop_bill_item_id', $tableName, 'source_shop_bill_item_id', '{{%shop_bill_item}}', 'id', 'SET NULL', 'SET NULL');
        $this->addForeignKey($tableName.'__currency_code', $tableName, 'currency_code', '{{%money_currency}}', 'code', 'RESTRICT', 'RESTRICT');

        $this->addCommentOnTable($tableName, 'Позиции бухгалтерских документов');
    }

    protected function createDocument2billTable()
    {
        $tableName = 'shop_document2bill';
        if ($this->db->getTableSchema($tableName, true)) {
            return;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable($tableName, [
            'id'               => $this->primaryKey(),
            'created_by'       => $this->integer(),
            'created_at'       => $this->integer(),
            'shop_document_id' => $this->integer()->notNull()->comment('Документ'),
            'shop_bill_id'     => $this->integer()->notNull()->comment('Счет'),
        ], $tableOptions);

        $this->createIndex($tableName.'__unique', $tableName, ['shop_document_id', 'shop_bill_id'], true);
        $this->createIndex($tableName.'__shop_document_id', $tableName, 'shop_document_id');
        $this->createIndex($tableName.'__shop_bill_id', $tableName, 'shop_bill_id');

        $this->addForeignKey($tableName.'__created_by', $tableName, 'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL');
        $this->addForeignKey($tableName.'__shop_document_id', $tableName, 'shop_document_id', '{{%shop_document}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey($tableName.'__shop_bill_id', $tableName, 'shop_bill_id', '{{%shop_bill}}', 'id', 'CASCADE', 'CASCADE');

        $this->addCommentOnTable($tableName, 'Связь бухгалтерских документов и счетов');
    }

    protected function createDocument2dealTable()
    {
        $tableName = 'shop_document2deal';
        if ($this->db->getTableSchema($tableName, true)) {
            return;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable($tableName, [
            'id'               => $this->primaryKey(),
            'created_by'       => $this->integer(),
            'created_at'       => $this->integer(),
            'shop_document_id' => $this->integer()->notNull()->comment('Документ'),
            'cms_deal_id'      => $this->integer()->notNull()->comment('Сделка'),
        ], $tableOptions);

        $this->createIndex($tableName.'__unique', $tableName, ['shop_document_id', 'cms_deal_id'], true);
        $this->createIndex($tableName.'__shop_document_id', $tableName, 'shop_document_id');
        $this->createIndex($tableName.'__cms_deal_id', $tableName, 'cms_deal_id');

        $this->addForeignKey($tableName.'__created_by', $tableName, 'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL');
        $this->addForeignKey($tableName.'__shop_document_id', $tableName, 'shop_document_id', '{{%shop_document}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey($tableName.'__cms_deal_id', $tableName, 'cms_deal_id', '{{%cms_deal}}', 'id', 'CASCADE', 'CASCADE');

        $this->addCommentOnTable($tableName, 'Связь бухгалтерских документов и сделок');
    }

    public function safeDown()
    {
        echo "m260708_113001__create_table__shop_document cannot be reverted.\n";
        return false;
    }
}
