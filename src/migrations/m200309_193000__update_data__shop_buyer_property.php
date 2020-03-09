<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200309_193000__update_data__shop_buyer_property extends Migration
{
    public function safeUp()
    {

        $tableName = "shop_buyer_property";
        $tablePropertyName = "shop_person_type_property";
        $tablePropertyEnumName = "shop_person_type_property_enum";

        $subQuery = $this->db->createCommand("SELECT 
                    {$tableName}.id 
                FROM 
                    `{$tableName}` 
                    LEFT JOIN {$tablePropertyName} on {$tablePropertyName}.id = {$tableName}.property_id
                    LEFT JOIN cms_content_element on cms_content_element.id = {$tableName}.value_enum 
                where 
                    {$tablePropertyName}.property_type = 'E'")->queryAll();

        $this->update("{$tableName}", [
            'value_element_id' => new \yii\db\Expression("{$tableName}.value_enum"),
        ], [
            "in",
            "id",
            $subQuery,
        ]);


    }

    public function safeDown()
    {
        echo "m200129_095515__alter_table__cms_content cannot be reverted.\n";
        return false;
    }
}