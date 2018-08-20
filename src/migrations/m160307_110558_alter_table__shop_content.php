<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m160307_110558_alter_table__shop_content extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%shop_content}}', 'children_content_id', $this->integer());
        $this->createIndex('children_content_id', '{{%shop_content}}', 'children_content_id');

        $this->addForeignKey(
            'shop_content__children_content_id', "{{%shop_content}}",
            'children_content_id', '{{%cms_content}}', 'id', 'CASCADE', 'CASCADE'
        );

        $this->addColumn('{{%shop_product}}', 'product_type', $this->string(10)->notNull()->defaultValue('simple'));
        $this->createIndex('product_type', '{{%shop_product}}', 'product_type');
    }

    public function safeDown()
    {
        $this->dropForeignKey("shop_content__children_content_id", "{{%shop_content}}");

        $this->dropIndex('children_content_id', '{{%shop_content}}');
        $this->dropColumn('{{%shop_content}}', 'children_content_id');

        $this->dropIndex('product_type', '{{%shop_product}}');
        $this->dropColumn('{{%shop_product}}', 'product_type');
    }
}