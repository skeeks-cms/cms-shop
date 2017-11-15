<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Schema;
use yii\db\Migration;

class m170701_073840__alter_table__shop_person_type_property extends Migration
{
    public function safeUp()
    {
        $this->dropColumn("{{%shop_person_type_property}}", "list_type");
        $this->dropColumn("{{%shop_person_type_property}}", "multiple_cnt");
        $this->dropColumn("{{%shop_person_type_property}}", "with_description");
        $this->dropColumn("{{%shop_person_type_property}}", "searchable");
        $this->dropColumn("{{%shop_person_type_property}}", "filtrable");
        $this->dropColumn("{{%shop_person_type_property}}", "version");
        $this->dropColumn("{{%shop_person_type_property}}", "smart_filtrable");
    }

    public function safeDown()
    {
        echo "m170701_063840__alter_table__form_property cannot be reverted.\n";
        return false;
    }
}