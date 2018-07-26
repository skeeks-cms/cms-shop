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
class m180726_130601__rename_table__shop_fuser extends Migration
{

    public function safeUp()
    {
        $this->renameTable("{{%shop_fuser}}", "{{%shop_cart}}");
    }

    public function safeDown()
    {
        echo "m180726_130601__rename_table__shop_fuser cannot be reverted.\n";
        return false;
    }
}