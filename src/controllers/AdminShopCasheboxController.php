<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\backend\actions\BackendGridModelRelatedAction;
use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\backend\grid\DefaultActionColumn;
use skeeks\cms\grid\BooleanColumn;
use skeeks\cms\helpers\Image;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\rbac\CmsManager;
use skeeks\cms\shop\models\ShopCachebox;
use skeeks\cms\shop\models\ShopCashebox;
use skeeks\cms\shop\models\ShopStore;
use skeeks\cms\shop\models\ShopStoreProduct;
use skeeks\cms\shop\store\StoreUrlRule;
use skeeks\cms\widgets\AjaxFileUploadWidget;
use skeeks\cms\widgets\AjaxSelectModel;
use skeeks\cms\widgets\GridView;
use skeeks\cms\ya\map\widgets\YaMapDecodeInput;
use skeeks\cms\ya\map\widgets\YaMapInput;
use skeeks\yii2\ckeditor\CKEditorWidget;
use skeeks\yii2\form\fields\BoolField;
use skeeks\yii2\form\fields\FieldSet;
use skeeks\yii2\form\fields\HtmlBlock;
use skeeks\yii2\form\fields\NumberField;
use skeeks\yii2\form\fields\SelectField;
use skeeks\yii2\form\fields\WidgetField;
use yii\base\Event;
use yii\bootstrap\Alert;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class AdminShopCasheboxController extends BackendModelStandartController
{
    public function init()
    {
        $this->name = "Кассы";
        $this->modelShowAttribute = "asText";
        $this->modelClassName = ShopCashebox::class;

        $this->generateAccessActions = false;
        $this->permissionName = CmsManager::PERMISSION_ROLE_ADMIN_ACCESS;

        parent::init();
    }


    /**
     * @inheritdoc
     */
    public function actions()
    {
        return ArrayHelper::merge(parent::actions(), [
            'index' => [
                'on beforeRender' => function (Event $e) {
                    $e->content = Alert::widget([
                        'closeButton' => false,
                        'options'     => [
                            'class' => 'alert-default',
                        ],

                        'body' => <<<HTML
Для работы магазина можно добавить кассу
HTML
                        ,
                    ]);
                },
                "filters"         => false,
                "backendShowings" => false,
                'grid'            => [
                    'on init'        => function (Event $e) {
                        /**
                         * @var $dataProvider ActiveDataProvider
                         * @var $query ActiveQuery
                         */
                        $query = $e->sender->dataProvider->query;

                        $query->cmsSite();
                        //$query->andWhere(['is_supplier' => 0]);
                    },
                    'defaultOrder'   => [
                        'priority' => SORT_ASC,
                    ],
                    'visibleColumns' => [

                        'checkbox',
                        'actions',

                        //'id',
                        'name',
                        'shop_store_id',
                        'is_active',
                    ],
                    'columns'        => [

                        'is_active' => [
                            'class'      => BooleanColumn::class,
                            'trueValue'  => 1,
                            'falseValue' => 1,
                        ],

                        'name' => [
                            'class'         => DefaultActionColumn::class,
                            'viewAttribute' => 'asText',
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



    public function updateFields($action)
    {
        $action->model->load(\Yii::$app->request->get());

        return [
            'main'           => [
                'class'  => FieldSet::class,
                'name'   => \Yii::t('skeeks/shop/app', 'Main'),
                'fields' => [
                    'name',
                    'shop_store_id' => [
                        'class'        => WidgetField::class,
                        'widgetClass'  => AjaxSelectModel::class,
                        'widgetConfig' => [
                            'modelClass' => ShopStore::class,
                            'searchQuery' => function($word = '') {
                                $query = ShopStore::find()->isSupplier(false)->cmsSite();
                                if ($word) {
                                    $query->search($word);
                                }
                                return $query;
                            },
                        ],

                    ],

                    'is_active'   => [
                        'class'     => BoolField::class,
                        'allowNull' => false,
                    ],
                    'priority'   => [
                        'class'     => NumberField::class,
                    ],


                ],
            ],
        ];
    }


}
