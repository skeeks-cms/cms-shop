<?php
namespace skeeks\cms\shop\services;

use yii\base\BaseObject;
use yii\db\Query;

/** Installation-wide shop maintenance shared by native jobs and legacy CLI. */
class ScheduledMaintenance extends BaseObject
{
    public $batchSize = 1000;

    protected function checkpoint(?callable $checkpoint, string $stage, int $processed): void
    {
        if ($checkpoint) { $checkpoint(['stage' => $stage, 'processed' => $processed]); }
    }

    protected function deleteBatches(string $table, array $condition, ?callable $checkpoint, string $stage, int &$processed, ?int $limit = null): int
    {
        if ($this->batchSize < 1) { throw new \InvalidArgumentException('batchSize must be positive.'); }
        $query = (new Query())->from($table)->where($condition);
        $upper = (int)(clone $query)->max('id');
        $last = 0;
        $selected = 0;
        $deleted = 0;
        while ($last < $upper && ($limit === null || $selected < $limit)) {
            $this->checkpoint($checkpoint, $stage, $processed);
            $size = $limit === null ? $this->batchSize : min($this->batchSize, $limit - $selected);
            $ids = (clone $query)->select('id')->andWhere(['>', 'id', $last])
                ->andWhere(['<=', 'id', $upper])->orderBy(['id' => SORT_ASC])->limit($size)->column();
            if (!$ids) { break; }
            // Recheck eligibility at DELETE time: an order may have been submitted since SELECT.
            $count = \Yii::$app->db->createCommand()->delete($table, ['and', $condition, ['id' => $ids]])->execute();
            $last = (int)end($ids);
            $selected += count($ids);
            $deleted += $count;
            $processed += $count;
            $this->checkpoint($checkpoint, $stage, $processed);
        }
        return $deleted;
    }

    public function deleteEmptyCarts(int $days = 3, ?callable $checkpoint = null): array
    {
        if ($days < 0) { throw new \InvalidArgumentException('days must not be negative.'); }
        $processed = 0;
        $this->checkpoint($checkpoint, 'orders', $processed);
        // Preserve legacy scope: old unsubmitted orders, including non-empty ones; max 5000 per run.
        $orders = $this->deleteBatches('{{%shop_order}}', ['and', ['is_created' => 0],
            ['<=', 'created_at', time() - 86400 * $days]], $checkpoint, 'orders', $processed, 5000);
        $carts = $this->deleteBatches('{{%shop_user}}', ['shop_order_id' => null], $checkpoint, 'carts', $processed);
        return ['orders_deleted' => $orders, 'carts_deleted' => $carts, 'processed' => $processed];
    }

    public function deletePriceChanges(int $days = 30, ?callable $checkpoint = null): array
    {
        if ($days < 0) { throw new \InvalidArgumentException('days must not be negative.'); }
        $processed = 0;
        $this->checkpoint($checkpoint, 'price_changes', $processed);
        $deleted = $this->deleteBatches('{{%shop_product_price_change}}',
            ['<=', 'created_at', time() - 86400 * $days], $checkpoint, 'price_changes', $processed);
        return ['deleted' => $deleted, 'processed' => $processed];
    }

    protected function execute(string $file, array $params, ?callable $checkpoint, int &$processed): void
    {
        $this->checkpoint($checkpoint, $file, $processed);
        $sql = file_get_contents(dirname(__DIR__).'/sql/maintenance/'.$file.'.sql');
        if ($sql === false) { throw new \RuntimeException('Missing maintenance SQL: '.$file); }
        if ($this->batchSize < 1) { throw new \InvalidArgumentException('batchSize must be positive.'); }
        $db = \Yii::$app->db;
        $table = $file === 'auto-prices-update' ? '{{%shop_product_price}}' : '{{%shop_product}}';
        $query = (new Query())->select('id')->from($table);
        $upper = (int)(clone $query)->max('id');
        $last = 0;
        // MariaDB supports time limits for writes; keep connection session settings unchanged.
        if ($checkpoint && stripos($db->getServerVersion(), 'MariaDB') !== false) {
            $sql = 'SET STATEMENT max_statement_time=60 FOR '.$sql;
        }
        while ($last < $upper) {
            $this->checkpoint($checkpoint, $file, $processed);
            $ids = (clone $query)->where(['>', 'id', $last])->andWhere(['<=', 'id', $upper])
                ->orderBy(['id' => SORT_ASC])->limit($this->batchSize)->column();
            if (!$ids) { break; }
            $end = (int)end($ids);
            $processed += $db->createCommand($sql, $params + [':from_id' => $last + 1, ':to_id' => $end])->execute();
            $last = $end;
            $this->checkpoint($checkpoint, $file, $processed);
        }
    }

    public function updateProductType(?callable $checkpoint = null): array
    {
        $processed = 0;
        $this->checkpoint($checkpoint, 'product_types', $processed);
        if (!\Yii::$app->shop->contentProducts) { return ['processed' => 0, 'enabled' => false]; }
        for ($i = 1; $i <= 4; ++$i) { $this->execute('product-type-'.$i, [], $checkpoint, $processed); }
        return ['processed' => $processed, 'enabled' => true];
    }

    public function updateProductRating(?callable $checkpoint = null): array
    {
        $processed = 0;
        $this->execute('product-rating', [], $checkpoint, $processed);
        return ['processed' => $processed];
    }

    public function updateAutoPrices(?callable $checkpoint = null): array
    {
        $processed = 0;
        $this->checkpoint($checkpoint, 'auto_prices', $processed);
        $query = (new Query())->from('{{%shop_type_price}}')->where(['is_auto' => 1])->orderBy(['id' => SORT_ASC]);
        foreach ($query->each(10) as $price) {
            if (!$price['base_auto_shop_type_price_id'] || !is_numeric($price['auto_extra_charge'])) {
                throw new \RuntimeException('Invalid automatic price settings: '.$price['id']);
            }
            $this->execute('auto-prices-insert', [':type_id' => $price['id'],
                ':base_id' => $price['base_auto_shop_type_price_id'], ':extra' => $price['auto_extra_charge']], $checkpoint, $processed);
            // Preserve the existing global recalculation pass after every automatic price type.
            $this->execute('auto-prices-update', [], $checkpoint, $processed);
        }
        return ['processed' => $processed];
    }

    public function updateStorePrices(?int $siteId = null, ?callable $checkpoint = null): array
    {
        $processed = 0;
        $sites = 0;
        $this->checkpoint($checkpoint, 'store_prices', $processed);
        $query = (new Query())->select('id')->from('{{%shop_site}}')->orderBy(['id' => SORT_ASC]);
        if ($siteId !== null) { $query->andWhere(['id' => $siteId]); }
        foreach ($query->each(10) as $site) {
            $this->checkpoint($checkpoint, 'store_prices', $processed);
            \Yii::$app->db->transaction(function () use ($site, $checkpoint, &$processed) {
                for ($i = 1; $i <= 4; ++$i) {
                    $this->execute('store-prices-'.$i, [':site_id' => (int)$site['id']], $checkpoint, $processed);
                }
            });
            ++$sites;
        }
        return ['processed' => $processed, 'sites' => $sites];
    }
}
