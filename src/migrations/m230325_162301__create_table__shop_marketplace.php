<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m230325_162301__create_table__shop_marketplace extends Migration
{
    public function safeUp()
    {
        $tableName = 'shop_marketplace';
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

            'cms_site_id' => $this->integer()->notNull(),

            'name' => $this->string(255)->notNull()->comment("Название"),
            'is_active' => $this->integer(1)->notNull()->defaultValue(1)->comment('Активен?'),

            'marketplace' => $this->string(255)->notNull()->comment("Маркетплейс (oz, wb, ym)"),

            'wb_key_stat' => $this->string(255)->comment("Ключ «Статистика»"),
            'wb_key_standart' => $this->string(255)->comment("Ключ «Стандартный»"),

            'oz_client_id' => $this->integer(11)->comment("Client ID"),
            'oz_api_key' => $this->string(255)->comment("API Key"),

            'ym_company_id' => $this->integer(11)->comment("Кампания №"),

            'priority' => $this->integer()->notNull()->defaultValue(100),

        ], $tableOptions);

        $this->addCommentOnTable($tableName, "Маркетплейс");

        $this->createIndex($tableName.'__name', $tableName, 'name');
        $this->createIndex($tableName.'__component', $tableName, 'component');
        $this->createIndex($tableName.'__is_active', $tableName, 'is_active');
        $this->createIndex($tableName.'__priority', $tableName, 'priority');


        $this->addForeignKey(
            "{$tableName}__cms_site_id", $tableName,
            'cms_site_id', '{{%cms_site}}', 'id', 'CASCADE', 'CASCADE'
        );
    }

    public function safeDown()
    {
        echo "m230325_162301__create_table__shop_marketplace cannot be reverted.\n";
        return false;
    }
}