<?php

use yii\db\Migration;

/**
 * A bill and a document are tied to a deal (CmsLead, skeeks/cms) — «Полноценная
 * работа в рамках своих сделок» (owner's decision 29.10.2026): the levels
 * «Свои сделки» and "edit" see the bills, payments (through their bill) and
 * documents of their deals, the deal card shows them. A repeated run changes
 * nothing.
 */
class m261029_190000__alter_tables__shop_bill_document_cms_lead extends Migration
{
    private const TABLES = ['shop_bill', 'shop_document'];

    public function safeUp()
    {
        foreach (self::TABLES as $table) {
            $schema = $this->db->getTableSchema('{{%'.$table.'}}', true);
            if (!$schema) {
                continue;
            }
            if ($schema->getColumn('cms_lead_id') === null) {
                $this->addColumn('{{%'.$table.'}}', 'cms_lead_id', $this->integer()->null()->comment('Сделка'));
            }
            if (!$this->hasIndex($table, $table.'__cms_lead_id')) {
                $this->createIndex($table.'__cms_lead_id', '{{%'.$table.'}}', 'cms_lead_id');
            }
            if (!$this->hasForeignKey($table, $table.'__cms_lead_id')) {
                $this->addForeignKey($table.'__cms_lead_id', '{{%'.$table.'}}', 'cms_lead_id', '{{%cms_lead}}', 'id', 'SET NULL', 'SET NULL');
            }
        }
    }

    public function safeDown()
    {
        // MySQL does not roll DDL back: refuse before the change rather than lose the links.
        foreach (self::TABLES as $table) {
            $schema = $this->db->getTableSchema('{{%'.$table.'}}', true);
            if ($schema && $schema->getColumn('cms_lead_id') !== null) {
                $used = (int)(new \yii\db\Query())->from('{{%'.$table.'}}')->andWhere(['not', ['cms_lead_id' => null]])->count('*', $this->db);
                if ($used) {
                    throw new \yii\base\Exception('Откат невозможен: в '.$table.' '.$used.' записей привязаны к сделкам, привязка пропала бы. Схема не изменена.');
                }
            }
        }
        foreach (self::TABLES as $table) {
            $schema = $this->db->getTableSchema('{{%'.$table.'}}', true);
            if (!$schema || $schema->getColumn('cms_lead_id') === null) {
                continue;
            }
            if ($this->hasForeignKey($table, $table.'__cms_lead_id')) {
                $this->dropForeignKey($table.'__cms_lead_id', '{{%'.$table.'}}');
            }
            if ($this->hasIndex($table, $table.'__cms_lead_id')) {
                $this->dropIndex($table.'__cms_lead_id', '{{%'.$table.'}}');
            }
            $this->dropColumn('{{%'.$table.'}}', 'cms_lead_id');
        }
    }

    private function hasIndex(string $table, string $name): bool
    {
        return (bool)$this->db->createCommand('SHOW INDEX FROM {{%'.$table.'}} WHERE Key_name = :name', [':name' => $name])->queryOne();
    }

    private function hasForeignKey(string $table, string $name): bool
    {
        return isset($this->db->getTableSchema('{{%'.$table.'}}', true)->foreignKeys[$name]);
    }
}
