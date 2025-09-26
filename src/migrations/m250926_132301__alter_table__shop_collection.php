<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m250926_132301__alter_table__shop_collection extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_collection";

        $this->addColumn($tableName, "show_counter", $this->integer()->notNull()->defaultValue(0)->comment('Количество просмотров'));

        $this->createIndex($tableName.'__show_counter', $tableName, ['show_counter']);
    }


    public function safeDown()
    {
        echo "m250926_132301__alter_table__shop_collection cannot be reverted.\n";
        return false;
    }
}