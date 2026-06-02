<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m260602_120601__alter_table__shop_discount_coupon extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_discount_coupon";

        $this->addColumn($tableName, "max_use_per_user", $this->integer()->notNull()->defaultValue(0)->comment('Максимальное число использования для пользователя'));

        $this->createIndex($tableName . '__max_use_per_user', $tableName, ['max_use_per_user']);
    }


    public function safeDown()
    {
        echo "m250926_132301__alter_table__shop_collection cannot be reverted.\n";
        return false;
    }
}