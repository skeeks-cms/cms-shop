<?php
namespace skeeks\cms\shop\helpers;

use skeeks\cms\shop\models\ShopStore;
use skeeks\cms\agent\models\CmsAgentModel;
use skeeks\cms\job\models\CmsJobRun;
use yii\db\Query;

/** Explicit site-scoped association; latest completed run, not last dispatch. */
class SupplierImportStatus
{
    private $siteId;
    private $states;
    public function __construct(int $siteId) { $this->siteId = $siteId; }
    public static function labels(): array
    {
        return ['success'=>'Обновляется успешно', 'none'=>'Не обновляется автоматически',
            'error'=>'Ошибка обновления', 'unknown'=>'Нет результата обновления'];
    }
    public static function available(): bool
    {
        return class_exists(CmsAgentModel::class)
            && isset(\Yii::$app->db->schema->getTableSchema(ShopStore::tableName())->columns['import_agent_id'])
            && \Yii::$app->db->schema->getTableSchema(CmsAgentModel::tableName()) !== null;
    }
    public function options(): array
    {
        if (!self::available()) { return []; }
        $items = [];
        foreach (CmsAgentModel::find()->where(['cms_site_id'=>$this->siteId])->all() as $agent) {
            $items[$agent->id] = '#'.$agent->id.' '.$agent->displayName.($agent->is_active ? '' : ' (отключено)');
        }
        return $items;
    }
    public function get(int $storeId): array
    {
        $this->load();
        return $this->states[$storeId] ?? ['state'=>'none','agent_id'=>null,'name'=>''];
    }
    public function apply($query, $value): void
    {
        if ($value === null || $value === '' || $value === []) { return; }
        $this->load(); $ids = [];
        foreach ($this->states as $id=>$state) {
            if (in_array($state['state'], (array)$value, true)) { $ids[]=$id; }
        }
        $query->andWhere([ShopStore::tableName().'.id'=>$ids]);
    }
    private function load(): void
    {
        if ($this->states !== null) { return; }
        $this->states = [];
        if (!self::available()) { return; }
        $stores = (new Query())->select(['id','import_agent_id'])->from(ShopStore::tableName())
            ->where(['cms_site_id'=>$this->siteId,'is_supplier'=>1])->all();
        $agents = CmsAgentModel::find()->where(['cms_site_id'=>$this->siteId,
            'id'=>array_column($stores,'import_agent_id')])->indexBy('id')->all();
        $keys = [];
        foreach ($agents as $agent) {
            if (!$agent->is_active || !$agent->isJobBased) { continue; }
            if (!\Yii::$app->has('jobs')) { continue; }
            try {
                $registry = \Yii::$app->jobs->getRegistry();
                if (!$registry->has($agent->effectiveJobType)) { continue; }
                $permission = $registry->get($agent->effectiveJobType)->permission;
                if ($permission && \Yii::$app->has('user') && !\Yii::$app->user->can($permission)) { continue; }
                $keys[$agent->id] = [$agent->jobDedupKey, $agent->effectiveJobType];
            }
            catch (\Throwable $e) { /* Invalid registry: keep unknown, never success. */ }
        }
        $history = [];
        if ($keys && class_exists(CmsJobRun::class) && \Yii::$app->db->schema->getTableSchema(CmsJobRun::tableName())) {
            $latest = (new Query())->select('MAX(id)')->from(CmsJobRun::tableName())
                ->where(['cms_site_id'=>$this->siteId,'dedup_key'=>array_column($keys,0),
                    'status'=>['succeeded','succeeded_with_warnings','failed','timed_out','cancelled']])
                ->groupBy(['dedup_key','job_type']);
            foreach ((new Query())->select(['id','dedup_key','job_type','status'])->from(CmsJobRun::tableName())->where(['id'=>$latest])->all() as $row) {
                $history[$row['dedup_key'].'|'.$row['job_type']] = $row;
            }
        }
        foreach ($stores as $store) {
            $agent = $agents[$store['import_agent_id']] ?? null;
            $state = ['state'=>'none','agent_id'=>$agent ? (int)$agent->id : null,'name'=>$agent ? $agent->displayName : ''];
            if ($agent && $agent->is_active) {
                $key = $keys[$agent->id] ?? null;
                $run = $key ? ($history[$key[0].'|'.$key[1]] ?? null) : null;
                $state['state'] = self::resultState($run['status'] ?? null);
            }
            $this->states[$store['id']] = $state;
        }
    }
    public static function resultState(?string $status): string
    {
        return ['succeeded'=>'success','failed'=>'error','timed_out'=>'error',
            'succeeded_with_warnings'=>'error'][$status] ?? 'unknown';
    }
}
