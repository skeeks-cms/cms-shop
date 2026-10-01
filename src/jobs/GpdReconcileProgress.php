<?php
namespace skeeks\cms\shop\jobs;

use skeeks\cms\job\models\CmsJobRun;

/** Read-only projection of persisted stages; also supports pre-stage job history. */
final class GpdReconcileProgress
{
    public const LABELS=['products'=>'Товары','collections'=>'Коллекции','brands'=>'Бренды'];

    public static function message(string $kind,array $counts,?int $total=null): string
    {
        return (self::LABELS[$kind]??$kind).': проверено '.(int)($counts['checked']??0)
            .($total===null?'':' из '.$total).'; удалено '.(int)($counts['deleted']??0)
            .'; деактивировано '.(int)($counts['deactivated']??0)
            .'; требуют уточнения '.(int)($counts['pending']??0).'.';
    }

    public static function collect(CmsJobRun $run): array
    {
        $result=json_decode($run->result_json??'{}',true)?:[];
        $cursor=json_decode($run->cursor_json??'{}',true)?:[];
        $complete=in_array($run->status,[CmsJobRun::STATUS_SUCCEEDED,CmsJobRun::STATUS_SUCCEEDED_WITH_WARNINGS],true);
        $rows=[];
        foreach(self::LABELS as $kind=>$label) {
            $counts=$result['counts'][$kind]??[];$stage=$result['stages'][$kind]??[];
            $status=$stage['status']??($complete&&empty($result['disabled'])?'complete':(isset($result['counts'][$kind])?'running':'waiting'));
            if(!empty($result['disabled']))$status='skipped';
            if($status==='running'&&$run->isFinished)$status='stopped';
            $seconds=isset($stage['started_at'])?max(0,(int)($stage['finished_at']??$run->finished_at??time())-(int)$stage['started_at']):null;
            $pending=(int)($counts['pending']??0);
            $reasons=[];$classified=0;
            foreach(['not_available'=>'Нет записи в журнале API v2','publication_pending'=>'Ожидается публикация','access_change_pending'=>'Ожидается изменение доступа','dependencies'=>'Ожидаются связанные справочники','unknown'=>'Причина не уточнена'] as $key=>$text) {
                $n=(int)($counts['pending_'.$key]??0);if($n){$reasons[]=$text.': '.$n;$classified+=$n;}
            }
            if($pending>$classified)$reasons[]='Причина не сохранена в этом запуске: '.($pending-$classified);
            $rows[]=['kind'=>$kind,'label'=>$label,'status'=>$status,'seconds'=>$seconds,
                'checked'=>(int)($counts['checked']??0),'total'=>$stage['total']??null,
                'deleted'=>(int)($counts['deleted']??0),'deactivated'=>(int)($counts['deactivated']??0),
                'protected'=>(int)($counts['protected']??0)+(int)($counts['other_source']??0),
                'pending'=>$pending,'reasons'=>implode('; ',$reasons)];
        }
        return ['rows'=>$rows,'active'=>!$run->isFinished,'message'=>$result['message']??$run->progress_message];
    }
}
