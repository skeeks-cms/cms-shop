<?php
namespace skeeks\cms\shop\gpd;

use yii\db\Connection;
use yii\db\Query;

/** Must run in the same transaction as removal of the catalog model. */
final class CatalogRemoval
{
    private $db;
    public function __construct(Connection $db) { $this->db=$db; }

    public function positions(int $productId, int $site): ?array
    {
        if (!$this->db->getTransaction()) throw new \LogicException('Removal requires a transaction.');
        if (!$this->db->createCommand('SELECT id FROM {{%cms_content_element}} WHERE id=:id AND cms_site_id=:site FOR UPDATE', [':id'=>$productId, ':site'=>$site])->queryScalar()) return null;
        $this->db->createCommand('SELECT id FROM {{%shop_product}} WHERE id=:id FOR UPDATE', [':id'=>$productId])->queryScalar();
        foreach (['shop_order_item','shop_bill_item','shop_document_item','shop_product_quantity_change','shop_feedback'] as $table) {
            if ($this->linked($table, ['shop_product_id'=>$productId])) return null;
        }
        if ($this->linked('cms_content_element', ['parent_content_element_id'=>$productId])) return null;
        $rows=$this->db->createCommand('SELECT p.id, s.sx_id, s.cms_site_id FROM {{%shop_store_product}} p LEFT JOIN {{%shop_store}} s ON s.id=p.shop_store_id WHERE p.shop_product_id=:id FOR UPDATE', [':id'=>$productId])->queryAll();
        foreach ($rows as $row) {
            // An inactive local position still belongs to the site, not to GPD.
            if ((int)$row['sx_id']<=0 || (int)$row['cms_site_id']!==$site) return null;
            if ($this->linked('shop_store_product_move', ['shop_store_product_id'=>(int)$row['id']])) return null;
        }
        return array_map('intval', array_column($rows, 'id'));
    }

    private function linked(string $table, array $where): bool
    {
        $name='{{%'.$table.'}}';
        // Some installations do not have the newer accounting modules yet.
        if (!$this->db->schema->getTableSchema($name)) return false;
        $command=(new Query())->select(new \yii\db\Expression('1'))->from($name)->where($where)->limit(1)->createCommand($this->db);
        return $this->db->createCommand($command->sql.' FOR UPDATE', $command->params)->queryScalar()!==false;
    }
}
