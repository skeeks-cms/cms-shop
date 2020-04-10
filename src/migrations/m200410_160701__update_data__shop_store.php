<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200410_160701__update_data__shop_store extends Migration
{
    public function safeUp()
    {
        $subQuery = $this->db->createCommand("
            UPDATE 
                `shop_store` as c
            SET 
                c.cms_site_id = (select cms_site.id from cms_site where cms_site.is_default = 1)
        ")->execute();
    }

    public function safeDown()
    {
        echo "m200406_080601__alter_table__shop_order cannot be reverted.\n";
        return false;
    }
}