<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Schema;
use yii\db\Migration;

class m150901_160601_create_table__shop_tax extends Migration
{
    public function safeUp()
    {
        $tableExist = $this->db->getTableSchema("{{%shop_tax}}", true);
        if ($tableExist) {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable("{{%shop_tax}}", [
            'id' => $this->primaryKey(),

            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),

            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),

            'name' => $this->string(255)->notNull(),
            'description' => $this->text(),
            'code' => $this->string(50)->notNull(),

            'site_code' => $this->string(15),

        ], $tableOptions);


        $this->createIndex('shop_tax__updated_by', '{{%shop_tax}}', 'updated_by');
        $this->createIndex('shop_tax__created_by', '{{%shop_tax}}', 'created_by');
        $this->createIndex('shop_tax__created_at', '{{%shop_tax}}', 'created_at');
        $this->createIndex('shop_tax__updated_at', '{{%shop_tax}}', 'updated_at');

        $this->createIndex('shop_tax__name', '{{%shop_tax}}', 'name');
        $this->createIndex('shop_tax__code', '{{%shop_tax}}', 'code');
        $this->createIndex('shop_tax__site_code', '{{%shop_tax}}', 'site_code');

        $this->addForeignKey(
            'shop_tax_created_by', "{{%shop_tax}}",
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_tax_updated_by', "{{%shop_tax}}",
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );


        $this->addForeignKey(
            'shop_tax_site_code', "{{%shop_tax}}",
            'site_code', '{{%cms_site}}', 'code', 'SET NULL', 'SET NULL'
        );


        $site_code = null;
        $site = \skeeks\cms\models\CmsSite::find()->def()->one();

        if ($site) {
            $site_code = $site->code;
        }

        $this->insert('{{%shop_tax}}', [
            'name' => 'НДС',
            'code' => 'NDS',
            'site_code' => $site_code,
        ]);

    }

    public function safeDown()
    {
        $this->dropForeignKey("shop_tax_updated_by", "{{%shop_tax}}");
        $this->dropForeignKey("shop_tax_updated_by", "{{%shop_tax}}");
        $this->dropForeignKey("shop_tax_site_code", "{{%shop_tax}}");

        $this->dropTable("{{%shop_tax}}");
    }
}