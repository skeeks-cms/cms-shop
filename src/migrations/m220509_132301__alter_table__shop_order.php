<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m220509_132301__alter_table__shop_order extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_order";

        $this->addColumn($tableName, "contact_first_name", $this->string(255)->comment('Имя покупателя'));
        $this->addColumn($tableName, "contact_last_name", $this->string(255)->comment('Фамилия Покупателя'));

        $this->addColumn($tableName, "shop_store_id", $this->integer()->comment('Магазин'));

        $this->createIndex($tableName.'__shop_store_id', $tableName, 'shop_store_id');

        $this->addColumn($tableName, "comment", $this->text()->comment('Комментарий к заказу'));

        $this->addForeignKey(
            "{$tableName}__shop_store_id", $tableName,
            'shop_store_id', '{{%shop_store}}', 'id', 'SET NULL', 'SET NULL'
        );
    }

    public function safeDown()
    {
        echo "m200717_132301__alter_table__shop_site cannot be reverted.\n";
        return false;
    }
}