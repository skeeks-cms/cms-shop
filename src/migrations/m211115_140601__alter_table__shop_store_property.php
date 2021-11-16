<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m211115_140601__alter_table__shop_store_property extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_store_property";

        $this->addColumn($tableName, "property_nature", $this->string(255));
        $this->createIndex($tableName.'__property_nature', $tableName, ['property_nature']);

        $this->addColumn($tableName, "import_multiply", $this->float());
    }

    public function safeDown()
    {
        echo "m210123_130601__alter_table__shop_product_price cannot be reverted.\n";
        return false;
    }
}