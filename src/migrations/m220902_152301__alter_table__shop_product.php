<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m220902_152301__alter_table__shop_product extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_product";

        $this->addColumn($tableName, "expiration_time", $this->integer()->defaultValue(0)->comment("Срок годности"));
        $this->addColumn($tableName, "expiration_time_comment", $this->text()->comment("Комментарий к сроку годности"));


        $this->addColumn($tableName, "service_life_time", $this->integer()->defaultValue(0)->comment("Срок службы"));
        $this->addColumn($tableName, "service_life_time_comment", $this->text()->comment("Комментарий к сроку службы"));

        $this->addColumn($tableName, "warranty_time", $this->integer()->defaultValue(0)->comment("Срок гарантии"));
        $this->addColumn($tableName, "warranty_time_comment", $this->text()->comment("Комментарий к сроку гарантии"));

        $this->createIndex($tableName.'__expiration_time', $tableName, ['expiration_time']);
        $this->createIndex($tableName.'__service_life_time', $tableName, ['service_life_time']);
        $this->createIndex($tableName.'__warranty_time', $tableName, ['warranty_time']);
    }

    public function safeDown()
    {
        echo "m200717_132301__alter_table__shop_site cannot be reverted.\n";
        return false;
    }
}