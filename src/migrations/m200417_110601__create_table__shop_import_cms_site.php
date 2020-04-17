<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200417_110601__create_table__shop_import_cms_site extends Migration
{
    public function safeUp()
    {
        $tableName = 'shop_import_cms_site';
        $tableExist = $this->db->getTableSchema($tableName, true);

        if ($tableExist) {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHAShopImportCmsSiteRACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable($tableName, [

            'id' => $this->primaryKey(),

            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),

            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),

            'receiver_cms_site_id'        => $this->integer()->notNull()->comment("Сайт получатель"),
            'receiver_shop_type_price_id' => $this->integer()->notNull()->comment("Цена на сайте получателе"),
            'receiver_cms_tree_id'        => $this->integer()->comment("Раздел на сайте получателе"),

            'sender_cms_site_id'        => $this->integer()->notNull()->comment("Сайт отправитель"),
            'sender_shop_type_price_id' => $this->integer()->notNull()->comment("Цена на сайте отправителе"),

            'extra_charge' => $this->integer()->notNull()->defaultValue(100)->comment("Наценка/Уценка"),

        ], $tableOptions);

        $this->createIndex($tableName.'__updated_by', $tableName, 'updated_by');
        $this->createIndex($tableName.'__created_by', $tableName, 'created_by');
        $this->createIndex($tableName.'__created_at', $tableName, 'created_at');
        $this->createIndex($tableName.'__updated_at', $tableName, 'updated_at');

        $this->createIndex($tableName.'__receiver_cms_site_id', $tableName, 'receiver_cms_site_id');
        $this->createIndex($tableName.'__receiver_shop_type_price_id', $tableName, 'receiver_shop_type_price_id');
        $this->createIndex($tableName.'__receiver_cms_tree_id', $tableName, 'receiver_cms_tree_id');

        $this->createIndex($tableName.'__sender_cms_site_id', $tableName, 'sender_cms_site_id');
        $this->createIndex($tableName.'__sender_shop_type_price_id', $tableName, 'sender_shop_type_price_id');

        //На один сайт одно задание на импорт с другого сайта
        $this->createIndex($tableName.'__uniq', $tableName, ["receiver_cms_site_id", "sender_cms_site_id"]);

        $this->createIndex($tableName.'__extra_charge', $tableName, 'extra_charge');

        $this->addCommentOnTable($tableName, 'Настройки импорта товаров с других сайтов');

        $this->addForeignKey(
            "{$tableName}__created_by", $tableName,
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );
        $this->addForeignKey(
            "{$tableName}__updated_by", $tableName,
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        //Удаляя сайт - удаляются и все его задания
        $this->addForeignKey(
            "{$tableName}__receiver_cms_site_id", $tableName,
            'receiver_cms_site_id', '{{%cms_site}}', 'id', 'CASCADE', 'CASCADE'
        );
        //Нельзя удалить цену на сайте если с ней связаны задания импорта
        $this->addForeignKey(
            "{$tableName}__receiver_shop_type_price_id", $tableName,
            'receiver_shop_type_price_id', '{{%shop_type_price}}', 'id', 'RESTRICT', 'RESTRICT'
        );
        //Удаляя раздел, в импорте сбрасывается таке
        $this->addForeignKey(
            "{$tableName}__receiver_cms_tree_id", $tableName,
            'receiver_cms_tree_id', '{{%cms_tree}}', 'id', 'CASCADE', 'CASCADE'
        );


        $this->addForeignKey(
            "{$tableName}__sender_cms_site_id", $tableName,
            'sender_cms_site_id', '{{%cms_site}}', 'id', 'CASCADE', 'CASCADE'
        );
        $this->addForeignKey(
            "{$tableName}__sender_shop_type_price_id", $tableName,
            'sender_shop_type_price_id', '{{%shop_type_price}}', 'id', 'RESTRICT', 'RESTRICT'
        );
    }

    public function safeDown()
    {
        echo "m200324_110601__create_table__shop_favorite_product cannot be reverted.\n";
        return false;
    }
}