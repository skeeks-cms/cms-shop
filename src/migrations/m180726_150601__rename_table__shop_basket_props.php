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
class m180726_150601__rename_table__shop_basket_props extends Migration
{

    public function safeUp()
    {
        $this->renameTable("{{%shop_basket_props}}", "{{%shop_order_item_props}}");
    }

    public function safeDown()
    {
        echo "m180726_150601__rename_table__shop_basket_props cannot be reverted.\n";
        return false;
    }
}