<?php
namespace skeeks\cms\shop\jobs;

use skeeks\cms\job\handlers\ChunkedJobHandler;
use skeeks\cms\job\runtime\JobContext;
use skeeks\cms\job\contracts\JobReporterInterface;
use skeeks\cms\job\exceptions\JobCancelledException;
use skeeks\cms\shop\gpd\CatalogReconciler;
use skeeks\cms\shop\gpd\CatalogTransport;
use skeeks\cms\shop\gpd\ShopCatalogWriter;
use skeeks\cms\shop\gpd\ShopReferenceWriter;

final class GpdCatalogReconcileJobHandler extends ChunkedJobHandler
{
    public $maxRunSeconds=15;
    private $next;private $service;
    protected function loadChunk(JobContext $context,array $cursor){if($context->getPayload())throw new \InvalidArgumentException('Expected empty payload.');return [$cursor];}
    protected function processChunk(array $items,JobContext $context,JobReporterInterface $reporter)
    {
        $site=(int)$context->getRun()->cms_site_id;$settings=\Yii::$app->gpd->forSite($site);
        if(!$settings->enabled){$this->next=null;$reporter->setResult(['disabled'=>true]);$reporter->setStage('disabled','Синхронизация GPD отключена.');return;}
        $checkpoint=static function()use($reporter){$reporter->heartbeat();if($reporter->isCancelled())throw new JobCancelledException('Сверка GPD отменена.');};
        if(!$this->service) {
            $base=\Yii::$app->gpdReceiver->receiverForSite($site)->state();$api=\Yii::$app->get(\Yii::$app->gpdReceiver->apiComponent);
            $writer=new ShopCatalogWriter($site,$settings,$api,$checkpoint);
            $this->service=new CatalogReconciler($site,$base['id'],new CatalogTransport($base['source_url'],(string)$api->api_key),new CatalogTransport($base['source_url'],(string)$api->api_key,null,'references'),$writer,new ShopReferenceWriter($site,$base['id'],$writer,$settings));
        }
        $cursor=$items[0];$kind=$cursor['kind']??'products';$upper=$cursor['upper']??$this->service->upper($kind);
        $result=json_decode($context->getRun()->result_json??'{}',true)?:[];
        $skipped=$kind!=='products'&&!\Yii::$app->gpdReceiver->referencesEnabled;
        if(!isset($result['stages'][$kind])) {
            $result['stages'][$kind]=[
            'started_at'=>time(),'status'=>'running',
            'total'=>(int)($result['counts'][$kind]['checked']??0)+($skipped?0:$this->service->remaining($kind,(int)($cursor['after']??0),(int)$upper)),
            ];
            $reporter->setResult($result);
            $reporter->setStage($kind,GpdReconcileProgress::message($kind,$result['counts'][$kind]??[],$result['stages'][$kind]['total']));
        }
        if($skipped)$page=['done'=>true,'counts'=>[],'after'=>0];
        else {$checkpoint();$page=$this->service->page($kind,(int)($cursor['after']??0),(int)$upper,$checkpoint);}
        foreach($page['counts'] as $key=>$value)$result['counts'][$kind][$key]=($result['counts'][$kind][$key]??0)+$value;
        $reporter->advance((int)($page['counts']['checked']??0));
        if($page['done']) {
            $result['stages'][$kind]['status']=$skipped?'skipped':'complete';
            $result['stages'][$kind]['finished_at']=time();
            $next=['products'=>'collections','collections'=>'brands','brands'=>null][$kind];
            $this->next=$next?['kind'=>$next,'after'=>0]:null;
        } else $this->next=['kind'=>$kind,'after'=>$page['after'],'upper'=>$upper];
        $reporter->setResult($result);
        $reporter->setStage($kind,GpdReconcileProgress::message($kind,$result['counts'][$kind]??[],$result['stages'][$kind]['total']));
    }
    protected function nextCursor(array $cursor,array $items,JobContext $context){return $this->next;}
    protected function finish(JobContext $context,JobReporterInterface $reporter)
    {
        $result=json_decode($context->getRun()->result_json??'{}',true)?:[];$pending=0;$checked=0;$deleted=0;$deactivated=0;$protected=0;
        if(!empty($result['disabled']))return;
        foreach($result['counts']??[] as $counts) {
            $pending+=(int)($counts['pending']??0);$checked+=(int)($counts['checked']??0);
            $deleted+=(int)($counts['deleted']??0);$deactivated+=(int)($counts['deactivated']??0);
            $protected+=(int)($counts['protected']??0)+(int)($counts['other_source']??0);
        }
        if($pending)$reporter->countWarning($pending);
        if($checked>$pending)$reporter->countSuccess($checked-$pending);
        $reporter->setTotal((int)$context->getRun()->progress_current);
        $result['message']='Проверено: '.$checked.'; удалено: '.$deleted.'; деактивировано: '.$deactivated.'; сохранено по связям: '.$protected.'; требуют уточнения: '.$pending.'.';
        $reporter->setResult($result);$reporter->setStage('complete',$result['message']);
    }
}
