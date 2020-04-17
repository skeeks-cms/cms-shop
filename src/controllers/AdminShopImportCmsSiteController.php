<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\backend\events\ViewRenderEvent;
use skeeks\cms\backend\grid\DefaultActionColumn;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\shop\models\ShopImportCmsSite;
use skeeks\cms\shop\models\ShopSite;
use skeeks\cms\shop\models\ShopTypePrice;
use skeeks\yii2\form\fields\SelectField;
use yii\base\Event;
use yii\bootstrap\Alert;
use yii\helpers\ArrayHelper;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class AdminShopImportCmsSiteController extends BackendModelStandartController
{

    public function init()
    {
        $this->name = \Yii::t('skeeks/shop/app', 'Поставщики');
        $this->modelShowAttribute = "asText";
        $this->modelClassName = ShopImportCmsSite::class;

        $this->generateAccessActions = false;

        $this->accessCallback = function () {
            //Если это сайт по умолчанию, этот раздел не показываем
            if (\Yii::$app->skeeks->site->is_default) {
                return false;
            }

            /**
             * @var ShopSite $shopSite
             */
            $shopSite = \Yii::$app->skeeks->site->shopSite;
            if (!$shopSite) {
                return false;
            }

            /**
             *
             */
            if ($shopSite->is_supplier) {
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
                'on beforeRender' => function (ViewRenderEvent $e) {

                    if (!\Yii::$app->skeeks->site->receiverShopImportCmsSites) {
                        $e->isRenderContent = false;

                        \Yii::$app->view->registerJs(<<<JS
$(".sx-content-actions [data-id=create]").popover({
'content': 'Добавьте первого поставщика!', 
'trigger': 'focus', 
'container': false
});
_.delay(function() {
    $(".sx-content-actions [data-id=create]").popover("show");    
}, 1000);
JS

                        );
                    }


                    //$e->isRenderContent = false;
                    $e->content = Alert::widget([
                        'closeButton' => false,
                        'options'     => [
                            'class' => 'alert-default',
                        ],

                        'body' => <<<HTML
В этом разделе вы можете настроить автоматический сбор товаров на сайт от поставщиков.
HTML
                        ,
                    ]);
                },
                "filters"         => false,
                'grid'            => [
                    'on init' => function (Event $e) {
                        /**
                         * @var $dataProvider ActiveDataProvider
                         * @var $query ActiveQuery
                         */
                        $query = $e->sender->dataProvider->query;

                        $query->andWhere(['receiver_cms_site_id' => \Yii::$app->skeeks->site->id]);
                    },

                    'defaultOrder' => [
                        'priority' => SORT_ASC,
                    ],

                    'visibleColumns' => [
                        'checkbox',
                        'actions',

                        ///'id',

                        'sender_cms_site_id',
                    ],
                    'columns'        => [
                        'sender_cms_site_id' => [
                            'class' => DefaultActionColumn::class,
                            //'viewAttribute' => 'asText',
                        ],
                    ],

                ],
            ],

            "create" => [
                'fields' => [$this, 'updateFields'],
            ],

            "update" => [
                'fields' => [$this, 'updateFields'],
            ],

        ]);
    }

    /**
     * @param $action
     * @return array
     */
    public function updateFields($action)
    {
        /**
         * @var $model ShopImportCmsSite
         */
        $model = $action->model;

        $model->load(\Yii::$app->request->get());

        $result = [
            'sender_cms_site_id'        => [
                'class' => SelectField::class,
                'items' => ArrayHelper::map(
                    ShopSite::find()->where(['is_supplier' => 1])->all(),
                    'id',
                    'asText'
                ),
            ],
            'sender_shop_type_price_id' => [
                'class' => SelectField::class,
                'items' => ArrayHelper::map(
                    ShopTypePrice::find()->where(['cms_site_id' => 1])->all(),
                    'id',
                    'asText'
                ),
            ],
        ];

        return $result;
    }

}
