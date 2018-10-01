<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\models\CmsAgent;
use skeeks\cms\modules\admin\controllers\AdminController;
use skeeks\cms\shop\models\search\AdminReportProductSearch;

/**
 * Class AdminExtraController
 * @package skeeks\cms\shop\controllers
 */
class AdminReportProductController extends AdminController
{
    public function init()
    {
        $this->name = \Yii::t('skeeks/shop/app', 'Reports on orders');
        parent::init();
    }

    public function actionIndex()
    {
        $search = new AdminReportProductSearch();
        $dataProvider = $search->search(\Yii::$app->request->get());

        return $this->render($this->action->id, [
            'search'       => $search,
            'dataProvider' => $dataProvider,
        ]);
    }
}
