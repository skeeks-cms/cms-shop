<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m240208_152301__alter_table__shop_product extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_product";

        $this->addColumn($tableName, "country_alpha2", $this->string(2)->null()->comment("Страна производитель"));

        $this->createIndex($tableName."__country_alpha2", $tableName, "country_alpha2");

        $this->addForeignKey(
            "{$tableName}__country_alpha2", $tableName,
            'country_alpha2', '{{%cms_country}}', 'alpha2', 'RESTRICT', 'RESTRICT'
        );

    }

    public function safeDown()
    {
        echo "m200717_132301__alter_table__shop_site cannot be reverted.\n";
        return false;
    }
}