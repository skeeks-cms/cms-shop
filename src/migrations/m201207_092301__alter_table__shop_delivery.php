<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m201207_092301__alter_table__shop_delivery extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_delivery";

        $this->addColumn($tableName, "component", $this->string(255));
        $this->addColumn($tableName, "component_config", $this->text());
    }

    public function safeDown()
    {
        echo "m200717_132301__alter_table__shop_site cannot be reverted.\n";
        return false;
    }
}