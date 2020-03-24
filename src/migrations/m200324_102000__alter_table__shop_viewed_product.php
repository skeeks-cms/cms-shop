<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200324_102000__alter_table__shop_viewed_product extends Migration
{
    public function safeUp()
    {

        $tableName = "shop_viewed_product";

        $this->dropColumn($tableName, "name");
        $this->dropColumn($tableName, "url");
    }

    public function safeDown()
    {
        echo "m200324_102000__alter_table__shop_viewed_product cannot be reverted.\n";
        return false;
    }
}