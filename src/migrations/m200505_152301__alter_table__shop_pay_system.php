<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200505_152301__alter_table__shop_pay_system extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_pay_system";

        $subQuery = $this->db->createCommand("
            UPDATE 
                `shop_pay_system` as c
            SET 
                c.cms_site_id = (select cms_site.id from cms_site where cms_site.is_default = 1)
        ")->execute();

        $this->addColumn($tableName, "is_active", $this->integer(1)->notNull()->defaultValue(1));
        $this->update($tableName, ['is_active' => 0], ['active' => 'N']);
        $this->dropColumn($tableName, "active");

        $this->dropForeignKey(
            "{$tableName}__cms_site_id", $tableName
        );
        
        $this->alterColumn($tableName, "cms_site_id", $this->integer()->notNull());
        
        $this->addForeignKey(
            "{$tableName}__cms_site_id", $tableName,
            'cms_site_id', '{{%cms_site}}', 'id', 'CASCADE', 'CASCADE'
        );
    }

    public function safeDown()
    {
        echo "m200505_101201__rename_table__shop_cart cannot be reverted.\n";
        return false;
    }
}