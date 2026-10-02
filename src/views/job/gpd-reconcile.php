<?php
/**
 * GPD reconcile report; cms-job renders it above the standard operation card.
 *
 * @var \yii\web\View $this
 * @var \skeeks\cms\job\models\CmsJobRun $model
 * @var array $details
 * @var string $detailsUrl
 */
use yii\helpers\Html;
use yii\helpers\Json;
use skeeks\cms\backend\widgets\BackendSurfaceWidget;
$id='gpd-reconcile-summary-'.(int)$model->id;
?>
<section id="<?= Html::encode($id) ?>">
<?php BackendSurfaceWidget::begin(['title'=>'Результаты сверки GPD','headerBordered'=>true]); ?>
<p data-sx-gpd-summary role="status"></p>
<p class="sx-collection-cell__secondary">Этапы выполняются по порядку: товары, коллекции, бренды. Объекты удаляются только при подтверждённом исключении из API, с учётом настроек синхронизации и защищающих связей.</p>
<div class="sx-data-table-wrapper"><table class="sx-data-table">
<thead><tr><th>Этап</th><th>Состояние</th><th>Проверено</th><th>Удалено</th><th>Деактивировано</th><th>Сохранено по связям</th><th>Требуют уточнения</th><th>Время</th></tr></thead>
<tbody data-sx-gpd-rows></tbody>
</table></div>
<p class="sx-collection-cell__secondary">«Нет записи в журнале API v2» — API ответило, но состояние старой связи не известно. Это не подтверждение удаления. «Ожидается публикация / изменение доступа» — API ещё готовит согласованное состояние. Такие объекты сохраняются.</p>
<p data-sx-gpd-refresh class="sx-collection-cell__secondary"></p>
<?php BackendSurfaceWidget::end(); ?>
</section>
<?php
$options=Json::htmlEncode(['selector'=>'#'.$id,'initial'=>$details,'url'=>$detailsUrl,'id'=>(int)$model->id]);
$this->registerJs(<<<JS
(function(options){
 const root=document.querySelector(options.selector);if(!root)return;
 const labels={waiting:'Ожидает',running:'Выполняется',complete:'Завершён',skipped:'Пропущен',stopped:'Остановлен'};
 let timer,stopped=false;
 function cell(tr,text){const td=document.createElement('td');td.textContent=text??'—';tr.appendChild(td);return td;}
 function paint(data){
  root.querySelector('[data-sx-gpd-summary]').textContent=data.message||'';
  const body=root.querySelector('[data-sx-gpd-rows]');body.textContent='';
  data.rows.forEach(row=>{const tr=document.createElement('tr');
   cell(tr,row.label);cell(tr,labels[row.status]||row.status);
   cell(tr,row.checked+(row.total===null?'':' / '+row.total));cell(tr,row.deleted);cell(tr,row.deactivated);cell(tr,row.protected);
   const pending=cell(tr,row.pending);if(row.reasons){pending.appendChild(document.createElement('br'));const detail=document.createElement('span');detail.className='sx-collection-cell__secondary';detail.textContent=row.reasons;pending.appendChild(detail);}
   cell(tr,row.seconds===null?'—':Math.floor(row.seconds/60)+' мин '+row.seconds%60+' сек');body.appendChild(tr);
  });return data.active;
 }
 function schedule(){timer=setTimeout(poll,5000);}
 async function poll(){
  if(stopped||!root.isConnected)return;if(document.hidden){schedule();return;}
  const abort=new AbortController(),timeout=setTimeout(()=>abort.abort(),15000);
  try{const r=await fetch(options.url,{signal:abort.signal,credentials:'same-origin',headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}});
   if(!r.ok)throw Error();const data=await r.json(),report=data.data?.[options.id]?.report;if(!report)throw Error();
   root.querySelector('[data-sx-gpd-refresh]').textContent='';if(paint(report))schedule();
  }catch(e){root.querySelector('[data-sx-gpd-refresh]').textContent='Не удалось обновить сведения. Повторяем запрос…';schedule();}
  finally{clearTimeout(timeout);}
 }
 if(paint(options.initial))schedule();
 window.addEventListener('pagehide',()=>{stopped=true;clearTimeout(timer);},{once:true});
})($options);
JS);
