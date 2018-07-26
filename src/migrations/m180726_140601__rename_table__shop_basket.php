<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

use yii\db\Migration;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class m180726_140601__rename_table__shop_basket extends Migration
{

    public function safeUp()
    {
        $this->renameTable("{{%shop_basket}}", "{{%shop_order_item}}");
    }

    public function safeDown()
    {
        echo "m180726_140601__rename_table__shop_basket cannot be reverted.\n";
        return false;
    }
}