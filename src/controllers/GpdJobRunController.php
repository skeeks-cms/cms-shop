<?php
namespace skeeks\cms\shop\controllers;

use skeeks\cms\backend\actions\BackendModelAction;
use skeeks\cms\job\models\CmsJobRun;
use skeeks\cms\shop\jobs\GpdReconcileProgress;
use yii\helpers\Url;

/** Domain report, with standard job actions and authorization preserved. */
class GpdJobRunController extends \skeeks\cms\job\controllers\AdminCmsJobRunController
{
    public function viewRun(BackendModelAction $action)
    {
        $model=$action->model;
        if($model->job_type!=='shop.gpd.catalog.reconcile')return parent::viewRun($action);
        return $this->render('@skeeks/cms/shop/views/job/gpd-reconcile',[
            'model'=>$model,'summary'=>GpdReconcileProgress::collect($model),
            'canCancel'=>$this->canCancel($model),'canRetry'=>$this->canRetry($model),
            'progressUrl'=>Url::to(['progress','id'=>$model->id]),
        ]);
    }
    public function actionProgress($id=null)
    {
        $response=parent::actionProgress($id);
        if(\Yii::$app->request->get('gpd_details')!=='1'||empty($response['data']))return $response;
        // Details stay scoped to the current site even on multi-site installations.
        $models=CmsJobRun::find()->where(['id'=>array_keys($response['data']),
            'cms_site_id'=>\Yii::$app->skeeks->site->id,'job_type'=>'shop.gpd.catalog.reconcile'])->all();
        foreach($models as $model)$response['data'][$model->id]['gpdReconcile']=GpdReconcileProgress::collect($model);
        \Yii::$app->response->headers->set('Cache-Control','private, no-store');
        return $response;
    }
}
