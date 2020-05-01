<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200430_100601__alter_table__shop_product extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_product";

        $this->addColumn($tableName, "offers_pid", $this->integer()->comment("Товар с предложениями"));
        $this->createIndex($tableName. "__offers_pid", $tableName, ["offers_pid"]);

        $this->addForeignKey(
            "{$tableName}__offers_pid", $tableName,
            'offers_pid', '{{%shop_product}}', 'id', 'SET NULL', 'SET NULL'
        );
    }

    public function safeDown()
    {
        echo "m200430_100601__alter_table__shop_product cannot be reverted.\n";
        return false;
    }
}