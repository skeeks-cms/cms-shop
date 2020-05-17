<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\shop\models\ShopOrderStatus;
use yii\base\Event;
use yii\bootstrap\Alert;
use yii\helpers\ArrayHelper;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class AdminOrderStatusController extends BackendModelStandartController
{
    public function init()
    {
        $this->name = \Yii::t('skeeks/shop/app', 'Order statuses');
        $this->modelShowAttribute = "name";
        $this->modelClassName = ShopOrderStatus::class;

        $this->generateAccessActions = false;

        $this->accessCallback = function () {
            if (!\Yii::$app->skeeks->site->is_default) {
                return false;
            }
            return \Yii::$app->user->can($this->uniqueId);
        };

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return ArrayHelper::merge(parent::actions(), [

            "index" => [
                'on beforeRender' => function (Event $e) {
                    $e->content = Alert::widget([
                        'closeButton' => false,
                        'options'     => [
                            'class' => 'alert-default',
                        ],

                        'body' => <<<HTML
<p>Настройте статусы заказов вашего магазина.</p>
HTML
                        ,
                    ]);
                },

                "filters"         => false,
                "backendShowings" => false,
                'grid'            => [


                    'defaultOrder' => [
                        'priority' => SORT_ASC,
                    ],

                    'visibleColumns' => [
                        'checkbox',
                        'actions',
                        /*'id',*/

                        'name',

                        'description',

                        'priority',

                    ],
                    'columns'        => [
                        'name' => [
                            'value' => function (ShopOrderStatus $shopOrderStatus) {
                                return \yii\helpers\Html::label($shopOrderStatus->name, null, [
                                    'style' => "background: {$shopOrderStatus->color}; color: white; border-radius: 3px;",
                                    'class' => "label g-pl-5 g-pr-5",
                                ]);
                            },
                        ],
                    ],

                ],
            ],

        ]);
    }
}
