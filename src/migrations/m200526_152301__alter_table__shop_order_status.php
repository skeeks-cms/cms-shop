<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200526_152301__alter_table__shop_order_status extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_order_status";

        $this->addColumn($tableName, "is_comment_required", $this->integer(1)->notNull()->defaultValue(0)->comment("При установке этого статуса комментарий обязателен?"));
        $this->addColumn($tableName, "client_available_statuses", $this->text()->comment("Доступные статусы для клиента"));
    }

    public function safeDown()
    {
        echo "m200525_132301__alter_table__shop_order_status cannot be reverted.\n";
        return false;
    }
}