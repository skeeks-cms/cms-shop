<?php
// Usage: php tests/gpd-apply-backlog.php /path/to/vendor/autoload.php
$autoload = $argv[1] ?? dirname(__DIR__, 3).'/autoload.php';
$loader = require $autoload;
$loader->addPsr4('skeeks\\cms\\shop\\', dirname(__DIR__).'/src', true);
require dirname($autoload).'/yiisoft/yii2/Yii.php';

use skeeks\cms\job\contracts\JobReporterInterface;
use skeeks\cms\job\exceptions\JobCancelledException;
use skeeks\cms\job\runtime\JobContext;
use skeeks\cms\shop\jobs\GpdCatalogApplyJobHandler;

function check($ok, $message) {
    if (!$ok) throw new RuntimeException($message);
}

// A deterministic service double models pending rows, failures and incoming
// revisions. The real handler is loaded unchanged through Composer.
class BacklogService {
    public $rows = [], $calls = [], $visited = [], $onApply;
    public function __construct(array $outcomes) {
        foreach ($outcomes as $id => $outcome) $this->rows[$id] = $outcome;
    }
    public function remaining(int $after = 0): int {
        $this->calls[] = $after;
        return count(array_filter(array_keys($this->rows), fn($id) => $id > $after));
    }
    public function page(int $after = 0): array {
        $ids = array_filter(array_keys($this->rows), fn($id) => $id > $after);
        sort($ids);
        return array_map(fn($id) => ['state' => ['product_id' => $id], 'item' => ['id' => $id]], array_slice($ids, 0, 10));
    }
    public function apply(array $entry): array {
        $id = $entry['item']['id'];
        $this->visited[] = $id;
        if ($this->onApply) ($this->onApply)($this, $id);
        $outcome = $this->rows[$id];
        if ($outcome === 'error') throw new RuntimeException('fixture failure');
        if ($outcome === 'reference') throw new \skeeks\cms\shop\gpd\ReferencePendingException('fixture reference');
        if ($outcome !== 'pending') unset($this->rows[$id]);
        return ['outcome' => $outcome];
    }
}

class BacklogReporter implements JobReporterInterface {
    public $run, $results = [], $total, $warnings = 0, $success = 0, $skipped = 0, $errors = 0, $cancelAt;
    public function __construct($run) {$this->run = $run;}
    public function setStage(string $s, ?string $m = null): void {}
    public function setTotal(?int $n): void {$this->total = $n;}
    public function advance(int $n = 1): void {$this->run->progress_current += $n;}
    public function countSuccess(int $n = 1): void {$this->success += $n;}
    public function countWarning(int $n = 1): void {$this->warnings += $n;}
    public function countError(int $n = 1): void {$this->errors += $n;}
    public function countSkipped(int $n = 1): void {$this->skipped += $n;}
    public function info(string $m, array $c = []): void {}
    public function warning(string $m, array $c = []): void {}
    public function error(string $m, array $c = []): void {}
    public function itemError(string $t, $id, string $m, array $r = []): void {$this->errors++;}
    public function heartbeat(): void {}
    public function isCancelled(): bool {return $this->cancelAt !== null && $this->run->progress_current >= $this->cancelAt;}
    public function addArtifact(string $t, string $p, array $o = []): \skeeks\cms\job\models\CmsJobRunArtifact {throw new LogicException();}
    public function setResult(array $r): void {$this->results[] = $r; $this->run->result_json = json_encode($r);}
}

function handler($service, $enabled = true) {
    $handler = new GpdCatalogApplyJobHandler();
    foreach (['service' => $service, 'settings' => (object)['enabled' => $enabled]] as $name => $value) {
        $property = new ReflectionProperty(GpdCatalogApplyJobHandler::class, $name);
        $property->setAccessible(true);
        $property->setValue($handler, $value);
    }
    return $handler;
}
function invoke($handler, $method, ...$args) {
    $reflection = new ReflectionMethod($handler, $method);
    $reflection->setAccessible(true);
    return $reflection->invokeArgs($handler, $args);
}
function context() {
    return new JobContext(['run' => (object)['cms_site_id' => 1, 'progress_current' => 0, 'result_json' => '{}']]);
}

foreach ([1, 10] as $size) {
    $service = new BacklogService(array_fill(1, $size, 'updated'));
    $ctx = context(); $reporter = new BacklogReporter($ctx->run); $h = handler($service);
    invoke($h, 'processChunk', [['after' => 0]], $ctx, $reporter);
    check($service->calls === [0, 0], 'COUNT budget must be two, independent of products in first chunk');
    check($reporter->total === $size && $ctx->run->progress_current === $size, 'Per-product progress preserved');
    check(end($reporter->results)['remaining'] === 0, 'Exact chunk-end backlog');
    check($reporter->results[0]['remaining'] === $size, 'Per-item result uses the start snapshot');
}

$service = new BacklogService([1 => 'updated', 2 => 'pending', 3 => 'error', 4 => 'reference', 5 => 'protected', 6 => 'kept', 7 => 'other_source', 8 => 'absent', 9 => 'unchanged', 10 => 'created', 11 => 'updated']);
$service->onApply = function ($s, $id) {if ($id === 10) $s->rows[12] = 'updated';};
$ctx = context(); $reporter = new BacklogReporter($ctx->run); $h = handler($service);
invoke($h, 'processChunk', [['after' => 0]], $ctx, $reporter);
$result = end($reporter->results);
check($result['remaining'] === 5 && $result['counts']['pending'] === 2 && $result['counts']['errors'] === 1, 'Pending, errors and newly received work remain');
check(count($result['pending_details']) === 1 && $reporter->success === 2 && $reporter->skipped === 7 && $reporter->errors === 1, 'Detailed outcomes preserved');
$cursor = invoke($h, 'nextCursor', [], [], $ctx);
check($cursor === ['after' => 10], 'Cursor follows visited product, not backlog');

// Fresh handler represents another delivery of the same run.
$h = handler($service); $service->calls = [];
invoke($h, 'processChunk', [$cursor], $ctx, $reporter);
check($service->calls === [0, 10, 0], 'Resumed chunk uses bounded global and cursor counts');
check($reporter->total === 12 && $ctx->run->progress_current === 12, 'Pending before cursor must not inflate total');
check(end($reporter->results)['remaining'] === 3, 'Fresh backlog includes earlier pending/error rows');
$cursor = invoke($h, 'nextCursor', [], [], $ctx);
$service->calls = [];
invoke($h, 'processChunk', [$cursor], $ctx, $reporter);
check($service->calls === [], 'Empty terminal page must not count backlog');
check(invoke($h, 'nextCursor', [], [], $ctx) === null, 'Empty page ends pass despite pending rows');
// Receipt may change the backlog even between the empty page and finish.
$service->rows[1] = 'updated';
invoke($h, 'finish', $ctx, $reporter);
check($reporter->total === 12 && $reporter->warnings === 2 && end($reporter->results)['remaining'] === 4, 'Final exact backlog, full pass progress and warnings');
check(count($service->visited) === 12, 'No pending item retried in same pass');
check($service->calls === [0], 'Exactly one final COUNT after empty terminal page');

$service = new BacklogService(array_fill(1, 10, 'updated'));
$ctx = context(); $reporter = new BacklogReporter($ctx->run); $reporter->cancelAt = 2;
try {
    invoke(handler($service), 'processChunk', [['after' => 0]], $ctx, $reporter);
    throw new RuntimeException('Cancellation ignored');
} catch (JobCancelledException $e) {}
check(count($service->visited) === 2 && $service->calls === [0], 'Cancellation does not trigger per-item COUNT');
check(end($reporter->results)['counts']['updated'] === 2, 'Last per-item counts survive cancellation');

$service = new BacklogService([]); $ctx = context(); $reporter = new BacklogReporter($ctx->run);
$h = handler($service, false);
invoke($h, 'processChunk', [[]], $ctx, $reporter); invoke($h, 'finish', $ctx, $reporter);
check($service->calls === [] && $reporter->skipped === 1, 'Disabled sync does not query backlog');

// An empty initial page still initializes result counters for finish().
$service = new BacklogService([]); $ctx = context(); $reporter = new BacklogReporter($ctx->run);
$h = handler($service);
invoke($h, 'processChunk', [['after' => 0]], $ctx, $reporter);
check($service->calls === [] && invoke($h, 'nextCursor', [], [], $ctx) === null, 'Empty start does not count and ends pass');
invoke($h, 'finish', $ctx, $reporter);
$result = end($reporter->results);
check($service->calls === [0], 'Empty run performs exactly one COUNT in finish');
check($reporter->total === 0 && $result['remaining'] === 0 && array_sum($result['counts']) === 0 && $result['pending_details'] === [], 'Empty run has complete zero result');

// Full multi-page pass: 2 + 3 + 3 + 0 chunk counts, then 1 final count.
$service = new BacklogService(array_fill(1, 25, 'updated'));
$ctx = context(); $reporter = new BacklogReporter($ctx->run); $h = handler($service);
$cursor = ['after' => 0];
for ($pages = 0; $cursor !== null && $pages < 5; $pages++) {
    invoke($h, 'processChunk', [$cursor], $ctx, $reporter);
    $cursor = invoke($h, 'nextCursor', [], [], $ctx);
}
check($cursor === null && $pages === 4, '25 products finish after three populated pages and one empty page');
invoke($h, 'finish', $ctx, $reporter);
check($service->calls === [0, 0, 0, 10, 0, 0, 20, 0, 0], 'Full pass COUNT budget and cursor scope');
check($reporter->total === 25 && $ctx->run->progress_current === 25 && end($reporter->results)['counts']['updated'] === 25 && end($reporter->results)['remaining'] === 0, 'Full pass preserves progress and outcomes');

// Verify real SQL filtering independently on isolated SQLite (no site config).
new yii\console\Application(['id' => 'backlog-test', 'basePath' => __DIR__, 'vendorPath' => dirname($autoload), 'extensions' => [], 'components' => ['db' => ['class' => yii\db\Connection::class, 'dsn' => 'sqlite::memory:']]]);
$db = Yii::$app->db;
$db->createCommand('CREATE TABLE shop_gpd_catalog_state(connection_id TEXT,product_id INT,revision INT,applied_revision INT,needs_resolution INT,operation TEXT)')->execute();
$db->createCommand()->batchInsert('shop_gpd_catalog_state', ['connection_id','product_id','revision','applied_revision','needs_resolution','operation'], [['a',1,2,1,0,'upsert'],['a',2,2,1,0,'revoke'],['a',3,2,2,0,'upsert'],['a',4,2,1,1,'upsert'],['b',5,2,1,0,'upsert']])->execute();
$rc = new ReflectionClass(\skeeks\cms\shop\gpd\CatalogApplier::class); $applier = $rc->newInstanceWithoutConstructor();
foreach (['db' => $db, 'id' => 'a', 'stateTable' => 'shop_gpd_catalog_state'] as $name => $value) {
    $property = $rc->getProperty($name); $property->setAccessible(true); $property->setValue($applier, $value);
}
check($applier->remaining() === 2 && $applier->remaining(1) === 1 && $applier->remaining(2) === 0, 'Actual SQL filters scope, applied revisions and unresolved rows');
echo "PASS: bounded counts, outcomes, concurrent receipt, resume, finish, cancellation, disabled sync and SQL scope\n";
