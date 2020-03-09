<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200309_111020__alter_table__shop_person_type_property extends Migration
{
    public function safeUp()
    {
        $this->addColumn("{{%shop_person_type_property}}", "cms_measure_code", $this->string(20));

        $this->addForeignKey(
            "shop_person_type_property__measure_code", "{{%shop_person_type_property}}",
            'cms_measure_code', '{{%cms_measure}}', 'code', 'RESTRICT', 'CASCADE'
        );
    }

    public function safeDown()
    {
        echo "m200129_095515__alter_table__cms_content cannot be reverted.\n";
        return false;
    }
}