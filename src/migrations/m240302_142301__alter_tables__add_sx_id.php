<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m240302_142301__alter_tables__add_sx_id extends Migration
{
    public function safeUp()
    {
        $this->addColumn("shop_brand", "sx_id", $this->integer()->null());
        $this->createIndex('shop_brand__sx_id', "shop_brand", ['sx_id'], true);

        $this->addColumn("shop_collection", "sx_id", $this->integer()->null());
        $this->createIndex('shop_collection__sx_id', "shop_collection", ['sx_id'], true);

        $this->addColumn("shop_store", "sx_id", $this->integer()->null());
        $this->createIndex('shop_store__sx_id', "shop_store", ['sx_id'], true);
    }

    public function safeDown()
    {
        echo "m200717_132301__alter_table__shop_site cannot be reverted.\n";
        return false;
    }
}