<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200504_111201__alter_table__shop_delivery extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_delivery";

        $this->dropColumn($tableName, "period_from");
        $this->dropColumn($tableName, "period_to");
        $this->dropColumn($tableName, "period_type");

        $this->dropColumn($tableName, "store");

        $this->addColumn($tableName, "is_active", $this->integer(1)->notNull()->defaultValue(1));
        $this->update($tableName, ['is_active' => 0], ['active' => 'N']);

        $this->dropForeignKey("shop_delivery__site_id", $tableName);
        $this->renameColumn($tableName, "site_id", "cms_site_id");

        $this->addForeignKey(
            "{$tableName}__cms_site_id", $tableName,
            'cms_site_id', '{{%cms_site}}', 'id', 'CASCADE', 'CASCADE'
        );

    }

    public function safeDown()
    {
        echo "m200504_111201__alter_table__shop_delivery cannot be reverted.\n";
        return false;
    }
}