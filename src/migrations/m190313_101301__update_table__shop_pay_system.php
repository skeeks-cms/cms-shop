<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m190313_101301__update_table__shop_pay_system extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_pay_system";
        $this->addColumn($tableName, "cms_site_id", $this->integer(11));
        $this->createIndex($tableName.'__cms_site_id', $tableName, 'cms_site_id');
        $this->addForeignKey(
            "{$tableName}__cms_site_id", $tableName,
            'cms_site_id', '{{%cms_site}}', 'id', 'CASCADE', 'CASCADE'
        );
        $this->dropIndex('name', $tableName);
    }

    public function safeDown()
    {
        echo "m190313_101301__update_table__shop_pay_system cannot be reverted.\n";
        return false;
    }
}