<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 31.05.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\grid\DateTimeColumnData;
use skeeks\cms\grid\UpdatedByColumn;
use skeeks\cms\models\CmsUser;
use skeeks\cms\queryfilters\QueryFiltersEvent;
use skeeks\cms\rbac\CmsManager;
use skeeks\cms\shop\grid\ShopProductColumn;
use skeeks\cms\shop\models\ShopCmsContentElement;
use skeeks\cms\shop\models\ShopCollection;
use skeeks\cms\shop\models\ShopFeedback;
use skeeks\cms\shop\widgets\stars\StarsInputWidget;
use skeeks\cms\widgets\AjaxFileUploadWidget;
use skeeks\cms\widgets\AjaxSelectModel;
use skeeks\yii2\form\fields\FieldSet;
use skeeks\yii2\form\fields\HtmlBlock;
use skeeks\yii2\form\fields\SelectField;
use skeeks\yii2\form\fields\TextareaField;
use skeeks\yii2\form\fields\WidgetField;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\UnsetArrayValue;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class AdminShopFeedbackController extends BackendModelStandartController
{
    public function init()
    {
        $this->name = \Yii::t('skeeks/cms', "Отзывы к товарам");
        $this->modelShowAttribute = "name";
        $this->modelClassName = ShopFeedback::class;

        $this->generateAccessActions = false;
        $this->permissionName = CmsManager::PERMISSION_ROLE_ADMIN_ACCESS;

        $this->accessCallback = function () {
            if (!\Yii::$app->skeeks->site->is_default) {
                return false;
            }
            return \Yii::$app->user->can(CmsManager::PERMISSION_ROLE_ADMIN_ACCESS);
        };


        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return ArrayHelper::merge(parent::actions(), [
            'index'  => [
                "filters" => [
                    'visibleFilters' => [
                        'q',
                        'brand',
                    ],
                    "filtersModel"   => [
                        'rules'            => [
                            ['q', 'safe'],
                        ],
                        'attributeDefines' => [
                            'q',
                        ],

                        'fields' => [
                            'q' => [
                                'label'          => 'Поиск',
                                'elementOptions' => [
                                    'placeholder' => 'Поиск',
                                ],
                                'on apply'       => function (QueryFiltersEvent $e) {
                                    /**
                                     * @var $query ActiveQuery
                                     */
                                    $query = $e->dataProvider->query;

                                    if ($e->field->value) {
                                        $query->andWhere([
                                            'or',
                                            ['like', ShopCollection::tableName().'.message', $e->field->value],
                                        ]);
                                    }
                                },
                            ],


                        ],
                    ],
                ],
                'grid'    => [
                    'defaultOrder' => [
                        'id' => SORT_DESC,
                    ],
                    /*'sortAttributes' => [
                        'countProducts'   => [
                            'asc'     => ['countProducts' => SORT_ASC],
                            'desc'    => ['countProducts' => SORT_DESC],
                            'label'   => 'Количество товаров',
                            'default' => SORT_ASC,
                        ],
                    ],*/

                    'visibleColumns' => [
                        'checkbox',
                        'actions',

                        'created_at',
                        'custom',
                        'created_by',
                        'shop_product_id',
                        'status',
                    ],

                    'columns' => [


                        'created_by' => [
                            'class' => UpdatedByColumn::class
                        ],
                        'created_at' => [
                            'class' => DateTimeColumnData::class
                        ],
                        'custom' => [
                            'attribute' => 'id',
                            'format'    => 'raw',
                            'value'     => function (ShopFeedback $model) {

                                $data = [];
                                $data[] = StarsInputWidget::widget([
                                    'value' => $model->rate,
                                    'name' => $model->id,
                                    'options' => [
                                        'disabled' => 'disabled'
                                    ]
                                ]);

                                if ($model->message) {
                                    $data[] = "<div>" . $model->message . "</div>";
                                }
                                $info = implode("", $data);

                                return $info;
                            },
                        ],


                        'view' => [
                            'value'         => function (ShopFeedback $model) {
                                return \yii\helpers\Html::a('<i class="fas fa-external-link-alt"></i>', $model->absoluteUrl,
                                    [
                                        'target'    => '_blank',
                                        'title'     => \Yii::t('skeeks/cms', 'Watch to site (opens new window)'),
                                        'data-pjax' => '0',
                                        'class'     => 'btn btn-sm',
                                    ]);
                            },
                            'format'        => 'raw',
                            /*'label'  => "Смотреть",*/
                            'headerOptions' => [
                                'style' => 'max-width: 40px; width: 40px;',
                            ],
                        ],
                        'status' => [
                            'value'         => function (ShopFeedback $model) {
                                return $model->statusAsText;
                            },
                        ],
                        /*'shop_product_id' => [
                            'class' => ShopProductColumn::class
                        ],*/
                    ],
                ],
            ],
            "create" => new UnsetArrayValue(),
            "update" => [
                'fields' => [$this, 'updateFields'],
            ],

        ]);
    }

    public function updateFields($action)
    {
        /**
         * @var ShopFeedback $model
         */
        $model = $this->model;
        $fields = [];

        if ($model->createdBy) {
            $createdBy = \skeeks\cms\widgets\admin\CmsUserViewWidget::widget(['cmsUser' => $model->createdBy]);
            $fields['user'] = [
                'class'        => HtmlBlock::class,
                'content'  => <<<HTML
<div class="form-group">
    <label class="control-label">Покупатель</label>
    {$createdBy}
</div>
HTML
            ];
        }
        $fields = ArrayHelper::merge($fields, [

            'shop_product_id' => [
                'class'        => WidgetField::class,
                'widgetClass'  => AjaxSelectModel::class,
                'widgetConfig' => [
                    'modelClass' => ShopCmsContentElement::class,
                    'searchQuery' => function($word = '') {
                        $query = ShopCmsContentElement::find()->cmsSite();
                        if ($word) {
                            $query->search($word);
                        }
                        return $query;
                    },
                    'options' => [
                        'disabled' => 'disabled',
                    ],
                ],
            ],
            'rate' => [
                'class'        => WidgetField::class,
                'widgetClass'  => StarsInputWidget::class,
                'widgetConfig' => [
                    'options' => [
                        'disabled' => 'disabled',
                    ],
                ],
            ],

            'message' => [
                'class' => TextareaField::class,
                'elementOptions' => [
                    'disabled' => 'disabled',
                ],
            ],

            'imageIds' => [
                'class'        => WidgetField::class,
                'widgetClass'  => AjaxFileUploadWidget::class,
                'widgetConfig' => [
                    'options' => [
                        'disabled' => 'disabled',
                    ],
                    'accept'   => 'image/*',
                    'multiple' => true,
                ],
            ],
        ]);
        return [

            'main' => [
                'class'  => FieldSet::class,
                'name'   => \Yii::t('skeeks/cms', 'Main'),
                'fields' => $fields
            ],

            'response' => [
                'class'  => FieldSet::class,
                'name'   => \Yii::t('skeeks/cms', 'Main'),
                'fields' => [
                    'status' => [
                        'class' => SelectField::class,
                        'items' => ShopFeedback::getStatuses(),
                    ],

                    'seller_message' => [
                        'class' => TextareaField::class,
                    ],
                ],
            ],


        ];
    }
}
