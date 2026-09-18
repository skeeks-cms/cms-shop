<?php
namespace skeeks\cms\shop\jobs;

use skeeks\cms\job\handlers\ChunkedJobHandler;
use skeeks\cms\job\runtime\JobContext;
use skeeks\cms\job\contracts\JobReporterInterface;
use skeeks\cms\job\exceptions\JobCancelledException;
use skeeks\cms\shop\gpd\StableReferenceSync;
use skeeks\cms\shop\gpd\ShopCatalogWriter;

class GpdStableReferencesJobHandler extends ChunkedJobHandler
{
    public $maxRunSeconds=15;
    private $service;private $next;
    protected function loadChunk(JobContext $context,array $cursor)
    {
        if($context->getPayload())throw new \InvalidArgumentException('Expected empty payload.');
        return [$cursor];
    }
    protected function processChunk(array $items,JobContext $context,JobReporterInterface $reporter)
    {
        $site=(int)$context->getRun()->cms_site_id;$settings=\Yii::$app->gpd->forSite($site);
        if(!$settings->enabled){$this->next=null;$reporter->setStage('disabled','Синхронизация GPD отключена.');return;}
        $checkpoint=static function()use($reporter){$reporter->heartbeat();if($reporter->isCancelled())throw new JobCancelledException('Синхронизация справочников отменена.');};
        if(!$this->service){$api=\Yii::$app->get(\Yii::$app->gpdReceiver->apiComponent);$media=new ShopCatalogWriter($site,$settings,$api,$checkpoint);$this->service=StableReferenceSync::forSite($site,$media,$checkpoint);}
        $cursor=$items[0];$kind=$cursor['kind']??'countries';$after=(string)($cursor['after']??'');
        $saved=json_decode($context->getRun()->result_json??'{}',true)?:[];
        $counts=$saved['counts']??['countries_created'=>0,'measures_created'=>0,'existing'=>0,'errors'=>0];
        $key=$kind==='countries'?'alpha2':'code';$rows=$this->service->rows($kind);
        $pending=array_values(array_filter($rows,static fn($row)=>strcmp(trim((string)$row[$key]),$after)>0));
        $reporter->setStage($kind,$kind==='countries'?'Проверяем страны':'Проверяем единицы измерения');
        foreach(array_slice($pending,0,20) as $row) {
            $checkpoint();$after=trim((string)$row[$key]);
            try{$result=$this->service->apply($kind,$row);++$counts[$result==='created'?$kind.'_created':'existing'];if($result==='created')$reporter->countSuccess();else $reporter->countSkipped();}
            catch(JobCancelledException $e){throw $e;}
            catch(\Throwable $e){++$counts['errors'];$reporter->itemError('gpd_'.$kind,$after,$e instanceof \yii\db\Exception?'Ошибка записи справочника в БД.':$e->getMessage());}
            $reporter->advance();
        }
        $this->next=count($pending)>20?['kind'=>$kind,'after'=>$after]:($kind==='countries'?['kind'=>'measures','after'=>'']:null);
        $message='Добавлено стран: '.$counts['countries_created'].'; единиц измерения: '.$counts['measures_created'].'; уже существовало: '.$counts['existing'].'; ошибок: '.$counts['errors'].'.';
        $reporter->setResult(['counts'=>$counts,'message'=>$message]);$reporter->setStage($this->next?$kind:'complete',$message);
    }
    protected function nextCursor(array $cursor,array $items,JobContext $context){return $this->next;}
}
