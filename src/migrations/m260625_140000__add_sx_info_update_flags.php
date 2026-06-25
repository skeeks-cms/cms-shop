<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS
 * @date 25.06.2026
 */

use yii\db\Migration;

class m260625_140000__add_sx_info_update_flags extends Migration
{
    public function safeUp()
    {
        $this->addColumnIfNotExists("cms_content_element", "is_sx_info_update", $this->integer(1)->notNull()->defaultValue(1)->comment("Update info from SkeekS Products service"));
        $this->addColumnIfNotExists("shop_brand", "is_sx_info_update", $this->integer(1)->notNull()->defaultValue(1)->comment("Update info from SkeekS Products service"));
        $this->addColumnIfNotExists("shop_collection", "is_sx_info_update", $this->integer(1)->notNull()->defaultValue(1)->comment("Update info from SkeekS Products service"));
    }

    public function safeDown()
    {
        echo "m260625_140000__add_sx_info_update_flags cannot be reverted.\n";
        return false;
    }

    protected function addColumnIfNotExists($tableName, $columnName, $type)
    {
        $tableSchema = $this->db->getTableSchema($tableName, true);
        if ($tableSchema && !isset($tableSchema->columns[$columnName])) {
            $this->addColumn($tableName, $columnName, $type);
        }
    }
}
