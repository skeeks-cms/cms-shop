<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m221111_152301__alter_table__shop_payment extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_payment";

        $this->addColumn($tableName, "cms_site_id", $this->integer());

        $this->db->createCommand(
        "UPDATE 
            `shop_payment` as spayment 
            LEFT JOIN shop_order as so ON so.id = spayment.shop_order_id 
        SET 
            spayment.`cms_site_id` = so.cms_site_id"
        )->execute();

        $this->alterColumn($tableName, "cms_site_id", $this->integer()->notNull());
        $this->createIndex("cms_site_id", $tableName, "cms_site_id");

        $this->addForeignKey(
            "{$tableName}__cms_site_id", $tableName,
            'cms_site_id', '{{%cms_site}}', 'id', 'CASCADE', 'CASCADE'
        );

    }

    public function safeDown()
    {
        echo "m200717_132301__alter_table__shop_site cannot be reverted.\n";
        return false;
    }
}