<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\models\CmsAgent;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\cms\modules\admin\traits\AdminModelEditorStandartControllerTrait;
use skeeks\cms\shop\models\ShopQuantityNoticeEmail;
use yii\helpers\ArrayHelper;

/**
 * Class AdminQuantityNoticeEmailController
 *
 * @package skeeks\cms\shop\controllers
 */
class AdminQuantityNoticeEmailController extends AdminModelEditorController
{
    use AdminModelEditorStandartControllerTrait;

    public function init()
    {
        $this->name = \Yii::t('skeeks/shop/app', 'Notification of receipt products by email');
        $this->modelShowAttribute = "name";
        $this->modelClassName = ShopQuantityNoticeEmail::className();

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return ArrayHelper::merge(parent::actions(),
            [
                'create' =>
                    [
                        'isVisible' => false,
                    ],

                'update' =>
                    [
                        'isVisible' => false,
                    ],

            ]
        );
    }

}
