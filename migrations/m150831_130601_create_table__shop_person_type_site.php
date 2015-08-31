<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 31.08.2015
 */
use yii\db\Schema;
use yii\db\Migration;

class m150831_130601_create_table__shop_person_type_site extends Migration
{
    public function safeUp()
    {
        $tableExist = $this->db->getTableSchema("{{%shop_person_type_site}}", true);
        if ($tableExist)
        {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable("{{%shop_person_type_site}}", [

            'person_type_id'        => $this->integer()->notNull(),
            'site_code'             => "CHAR(15) NOT NULL",

        ], $tableOptions);


        $this->createIndex('site_code__person_type_id', '{{%shop_person_type_site}}', ['person_type_id', 'site_code'], true);

        $this->execute("ALTER TABLE {{%shop_person_type_site}} COMMENT = 'Связь плательщиков с сайтами';");

        $this->addForeignKey(
            'shop_person_type_site_person_type_id', "{{%shop_person_type_site}}",
            'person_type_id', '{{%shop_person_type}}', 'id', 'CASCADE', 'CASCADE'
        );

        $this->addForeignKey(
            'shop_person_type_site_site_code', "{{%shop_person_type_site}}",
            'site_code', '{{%cms_site}}', 'code', 'CASCADE', 'CASCADE'
        );

        $persons    = \skeeks\cms\shop\models\ShopPersonType::find()->all();
        $site       = \skeeks\cms\models\CmsSite::find()->def()->one();

        if ($persons && $site)
        {
            foreach ($persons as $person)
            {
                $this->insert('{{%shop_person_type_site}}', [
                    'person_type_id'            => $person->id,
                    'site_code'                 => $site->code,
                ]);
            }
        }

    }

    public function safeDown()
    {
        $this->dropForeignKey("shop_person_type_site_site_code", "{{%shop_person_type_site}}");
        $this->dropForeignKey("shop_person_type_site_person_type_id", "{{%shop_person_type_site}}");

        $this->dropTable("{{%shop_person_type_site}}");
    }
}