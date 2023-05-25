<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\backend\controllers\BackendModelController;
use skeeks\cms\backend\ViewBackendAction;
use skeeks\cms\components\Cms;
use skeeks\cms\shop\models\ShopBuyer;
use skeeks\cms\shop\models\ShopOrder;
use yii\base\Event;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\UnsetArrayValue;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class UpaFavoriteController extends BackendModelController
{
    public function init()
    {
        $this->name = "Избранное";
        $this->modelShowAttribute = "asText";
        $this->modelClassName = ShopOrder::class;
        $this->generateAccessActions = false;
        $this->permissionNames = [
            Cms::UPA_PERMISSION => 'Доступ к персональной части',
        ];
        parent::init();
    }


    public function actions()
    {
        return ArrayHelper::merge(parent::actions(), [
            "index" => [
                'class' => ViewBackendAction::class
            ],
        ]);
    }
}