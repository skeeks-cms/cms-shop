<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200406_100601__alter_table__shop_person_type_site extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_person_type_site";

        $this->addColumn($tableName, "cms_site_id", $this->integer());

        $result = \Yii::$app->db->createCommand(<<<SQL
            UPDATE 
                `shop_person_type_site` as spts 
                LEFT JOIN cms_site site on site.code = spts.site_code 
            SET 
                spts.`cms_site_id` = site.id
SQL
        )->execute();

        $this->dropForeignKey("shop_person_type_site_site_code", $tableName);
        $this->dropIndex("site_code__person_type_id", $tableName);

        $this->dropColumn($tableName, "site_code");


        $this->alterColumn($tableName, "cms_site_id", $this->integer()->notNull());

        $this->createIndex($tableName . "__uniq", $tableName, ['cms_site_id', 'person_type_id'], true);

        $this->addForeignKey(
            "{$tableName}__cms_site_id", $tableName,
            'cms_site_id', '{{%cms_site}}', 'id', 'CASCADE', 'CASCADE'
        );
    }

    public function safeDown()
    {
        echo "m200406_080601__alter_table__shop_order cannot be reverted.\n";
        return false;
    }
}