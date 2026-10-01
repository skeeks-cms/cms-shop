<?php
/**
 * Read-only integration audit using the real controller callbacks and site data.
 * php tests/admin-product-pagination.php /path/to/web-bootstrap.php [content_id]
 * The bootstrap creates a web Application without running it or logging in a user.
 */
require $argv[1];

use skeeks\cms\models\CmsContent;
use skeeks\cms\models\CmsContentElement;
use skeeks\cms\queryfilters\QueryFiltersEvent;
use skeeks\cms\shop\controllers\AdminCmsContentElementController;
use skeeks\cms\shop\models\ShopCmsContentElement;
use skeeks\cms\shop\models\ShopProduct;
use yii\base\Event;
use yii\data\ActiveDataProvider;
use yii\db\Query;

class PaginationAuditController extends AdminCmsContentElementController
{
    public $auditGrouped = true;
    public function isProductGroup() { return $this->auditGrouped; }
}

$app = Yii::$app;
$db = $app->db;
$db->createCommand('SET TRANSACTION READ ONLY')->execute();
$transaction = $db->beginTransaction(yii\db\Transaction::REPEATABLE_READ);
$checks = 0;
function check($ok, $message) {
    global $checks;
    if (!$ok) throw new RuntimeException($message);
    ++$checks;
}
function applyField($action, $provider, $name, $value) {
    $callback = $action->filters['filtersModel']['fields'][$name]['on apply'];
    $callback(new QueryFiltersEvent(['field'=>(object)['value'=>$value], 'dataProvider'=>$provider]));
}
function auditPages($provider, $label) {
    $query = clone $provider->query;
    // Inspect SQL rows, not AR's post-LIMIT deduplication or eager-loaded graphs.
    $select = (array)$query->select;
    foreach ($select as $key=>$column) {
        if ($column === CmsContentElement::tableName().'.*') unset($select[$key]);
    }
    $query->select($select)->addSelect(CmsContentElement::tableName().'.id');
    $all = $query->createCommand()->queryAll();
    $ids = array_map('intval', array_column($all, 'id'));
    check(count($ids) === count(array_unique($ids)), $label.': duplicate SQL rows');
    $provider->refresh();
    check($provider->getTotalCount() === count($ids), $label.': count differs from unique rows');
    $paged = [];
    for ($offset=0; $offset<count($ids); $offset+=20) {
        $page = (clone $query)->limit(20)->offset($offset)->createCommand()->queryAll();
        $pageIds = array_map('intval', array_column($page, 'id'));
        check(count($pageIds) === min(20,count($ids)-$offset), $label.': incomplete page');
        array_push($paged, ...$pageIds);
    }
    check($paged === $ids, $label.': repeated or skipped products between pages');
    fwrite(STDERR, $label.': '.count($ids)." unique products, all pages checked\n");
    return ['count'=>count($ids),'pages'=>(int)ceil(count($ids)/20),'first_page'=>array_slice($ids,0,20)];
}

try {
    $controller = new PaginationAuditController('admin-cms-content-element', $app->getModule('shop'));
    $controller->setContent(CmsContent::findOne((int)($argv[2] ?? 2)));
    check((bool)$controller->content, 'content exists');
    $site = (int)$app->skeeks->site->id;
    $content = (int)$controller->content->id;
    $action = (object)['filters'=>[], 'grid'=>[]];
    $controller->initGridData($action, $controller->content);
    $base = function($grouped=true) use ($controller,$action) {
        $controller->auditGrouped = $grouped;
        $provider = new ActiveDataProvider(['query'=>ShopCmsContentElement::find(),'sort'=>false]);
        $action->grid['on init'](new Event(['sender'=>(object)['dataProvider'=>$provider]]));
        $provider->query->orderBy([
            CmsContentElement::tableName().'.active'=>SORT_DESC,
            CmsContentElement::tableName().'.priority'=>SORT_ASC,
            CmsContentElement::tableName().'.id'=>SORT_DESC,
        ]);
        return $provider;
    };
    $oracle = (new Query())->select('ce.id')->from(['ce'=>'{{%cms_content_element}}'])
        ->innerJoin(['sp'=>'{{%shop_product}}'],'sp.id=ce.id')
        ->where(['ce.cms_site_id'=>$site,'ce.content_id'=>$content])
        ->andWhere(['sp.product_type'=>[ShopProduct::TYPE_SIMPLE,ShopProduct::TYPE_OFFERS]])
        ->orderBy(['ce.active'=>SORT_DESC,'ce.priority'=>SORT_ASC,'ce.id'=>SORT_DESC]);
    $report = ['site_id'=>$site,'content_id'=>$content,
        'database_count'=>(int)(clone $oracle)->count('*',$db),
        'activity_counts'=>(clone $oracle)->select(['ce.active','count'=>new yii\db\Expression('COUNT(*)')])->groupBy('ce.active')->orderBy([])->all($db)];

    foreach ([null,'',[]] as $empty) {
        $provider = $base(); $before = $provider->query->createCommand()->rawSql;
        applyField($action,$provider,'stores',$empty);
        check($provider->query->createCommand()->rawSql === $before, 'empty store filter must not change SQL');
    }
    $provider = $base(); applyField($action,$provider,'stores',[]);
    $report['empty'] = auditPages($provider,'empty');
    check($report['empty']['count'] === $report['database_count'], 'unfiltered count matches independent database query');

    $pair = $db->createCommand('SELECT a.shop_store_id AS a,b.shop_store_id AS b,COUNT(DISTINCT a.shop_product_id) AS overlap
        FROM {{%shop_store_product}} a INNER JOIN {{%shop_store_product}} b
        ON a.shop_product_id=b.shop_product_id AND a.shop_store_id<b.shop_store_id
        INNER JOIN {{%cms_content_element}} ce ON ce.id=a.shop_product_id
        WHERE ce.cms_site_id=:site AND ce.content_id=:content
        GROUP BY a.shop_store_id,b.shop_store_id ORDER BY overlap DESC LIMIT 1', [':site'=>$site,':content'=>$content])->queryOne();
    check((bool)$pair, 'site has two overlapping stores for regression test');
    $report['store_pair'] = $pair;
    foreach ([[(int)$pair['a']],[(int)$pair['a'],(int)$pair['b']]] as $stores) {
        $provider = $base(); applyField($action,$provider,'stores',$stores);
        $expected = (clone $oracle)->innerJoin(['ssp'=>'{{%shop_store_product}}'],'ssp.shop_product_id=sp.id')
            ->andWhere(['ssp.shop_store_id'=>$stores])->distinct()->column($db);
        $ids = (clone $provider->query)->select(CmsContentElement::tableName().'.id')->column();
        check(array_map('intval',$ids) === array_map('intval',$expected), 'stores match independent DISTINCT oracle');
        $report['stores_'.implode('_',$stores)] = auditPages($provider,'stores '.implode(',',$stores));
    }

    $provider=$base(false); applyField($action,$provider,'stores',[]);
    $report['ungrouped']=auditPages($provider,'ungrouped modifications');
    $expected=(clone $oracle)->where(['ce.cms_site_id'=>$site,'ce.content_id'=>$content])
        ->andWhere(['sp.product_type'=>[ShopProduct::TYPE_SIMPLE,ShopProduct::TYPE_OFFER]])->count('*',$db);
    check($report['ungrouped']['count']==(int)$expected, 'modification grouping preserves product types');

    foreach (['а','Kerama'] as $word) {
        $provider=$base(); applyField($action,$provider,'q',$word);
        $report['search_'.$word]=auditPages($provider,'search '.$word);
        check(stripos($provider->query->createCommand()->rawSql,'SELECT DISTINCT')!==false,'parent search is unique before joining');
    }
    $collections=(new Query())->select('pc.shop_collection_id')->distinct()->from(['pc'=>'{{%shop_product2collection}}'])
        ->innerJoin(['ce'=>'{{%cms_content_element}}'],'ce.id=pc.shop_product_id')
        ->innerJoin(['ssp'=>'{{%shop_store_product}}'],'ssp.shop_product_id=ce.id')
        ->where(['ce.cms_site_id'=>$site,'ce.content_id'=>$content])
        ->andWhere(['ssp.shop_store_id'=>[(int)$pair['a'],(int)$pair['b']]])->limit(2)->column($db);
    if ($collections) {
        $provider=$base(); applyField($action,$provider,'collections',$collections);
        applyField($action,$provider,'stores',[(int)$pair['a'],(int)$pair['b']]);
        $report['collections_and_stores']=auditPages($provider,'collections and stores');
        check($report['collections_and_stores']['count']>0, 'combined collection/store fixture is populated');
    }
    $barcode=(new Query())->select('b.value')->from(['b'=>'{{%shop_product_barcode}}'])
        ->innerJoin(['ce'=>'{{%cms_content_element}}'],'ce.id=b.shop_product_id')
        ->where(['ce.cms_site_id'=>$site,'ce.content_id'=>$content])->limit(1)->scalar($db);
    $report['barcode_fixture_found']=(bool)$barcode;
    $barcode=$barcode ?: '__pagination_audit_absent_barcode__';
    $provider=$base(); applyField($action,$provider,'barcodes',['value'=>[$barcode],'mode'=>'eq']);
    $provider->query->andWhere(['barcodes.value'=>$barcode]);
    applyField($action,$provider,'stores',[(int)$pair['a'],(int)$pair['b']]);
    $report['barcode_and_stores']=auditPages($provider,'barcode and stores');
    foreach ($app->skeeks->site->shopTypePrices as $price) {
        $provider=$base(); applyField($action,$provider,'stores',[(int)$pair['a'],(int)$pair['b']]);
        $provider->query->addSelect(['audit_price'=>"p{$price->id}.price"])
            ->orderBy(["p{$price->id}.price"=>SORT_ASC,CmsContentElement::tableName().'.id'=>SORT_ASC]);
        $report['price_'.$price->id]=auditPages($provider,'price '.$price->id);
        $provider->query->andHaving(['>','audit_price',0]);
        $report['price_positive_'.$price->id]=auditPages($provider,'positive price '.$price->id);
    }
    $report['checks']=$checks;
    echo json_encode($report,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT)."\nPASS: read-only pagination audit\n";
} finally {
    $transaction->rollBack();
}
