<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m220701_132301__alter_table__shop_site extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_site";

        $this->addColumn($tableName, "order_required_fields", $this->string(500)->defaultValue("phone")->comment("Обязательные поля при оформлении заказа"));
    }

    public function safeDown()
    {
        echo "m220610_132301__alter_table__shop_site cannot be reverted.\n";
        return false;
    }
}