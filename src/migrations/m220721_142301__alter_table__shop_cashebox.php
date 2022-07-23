<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m220721_142301__alter_table__shop_cashebox extends Migration
{
    public function safeUp()
    {
        $tableName = "shop_cashebox";

        $this->addColumn($tableName, "shop_cloudkassa_id", $this->integer()->comment("Облачная касса"));

        $this->createIndex($tableName.'__shop_cloudkassa_id', $tableName, ['shop_cloudkassa_id']);

        $this->addForeignKey(
            "{$tableName}__shop_cloudkassa_id", $tableName,
            'shop_cloudkassa_id', '{{%shop_cloudkassa}}', 'id', 'SET NULL', 'SET NULL'
        );
    }

    public function safeDown()
    {
        echo "m200717_132301__alter_table__shop_site cannot be reverted.\n";
        return false;
    }
}