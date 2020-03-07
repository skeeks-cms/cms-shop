<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200307_181020__alter_table__shop_product extends Migration
{
    public function safeUp()
    {
        $this->alterColumn("{{%shop_product}}", "measure_code", $this->string(20));

        $this->addForeignKey(
            "shop_product__measure_code", "{{%shop_product}}",
            'measure_code', '{{%cms_measure}}', 'code', 'RESTRICT', 'CASCADE'
        );

    }

    public function safeDown()
    {
        echo "m200129_095515__alter_table__cms_content cannot be reverted.\n";
        return false;
    }
}