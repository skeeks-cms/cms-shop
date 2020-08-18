<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200818_142301__alter_table__shop_product extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_product";

        $this->addColumn($tableName, "main_pid_at", $this->integer()->comment("Когда создана привязка к главному"));
        $this->addColumn($tableName, "main_pid_by", $this->integer(1)->comment("Кем создана привязка к главному"));

        $this->createIndex("main_pid_at", $tableName, ["main_pid_at"]);
        $this->createIndex("main_pid_by", $tableName, ["main_pid_by"]);

        $this->addForeignKey(
            "{$tableName}__main_pid_by", $tableName,
            'main_pid_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );
    }

    public function safeDown()
    {
        echo "m200717_132301__alter_table__shop_site cannot be reverted.\n";
        return false;
    }
}