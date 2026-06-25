<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 31.05.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\backend\actions\BackendModelAction;
use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\grid\BooleanColumn;
use skeeks\cms\grid\DateTimeColumnData;
use skeeks\cms\grid\ImageColumn2;
use skeeks\cms\grid\UserColumnData;
use skeeks\cms\helpers\Image;
use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\models\CmsCountry;
use skeeks\cms\queryfilters\QueryFiltersEvent;
use skeeks\cms\rbac\CmsManager;
use skeeks\cms\shop\models\ShopBrand;
use skeeks\cms\shop\models\ShopProduct;
use skeeks\cms\widgets\AjaxSelectModel;
use skeeks\cms\widgets\formInputs\comboText\ComboTextInputWidget;
use skeeks\cms\widgets\GridView;
use skeeks\yii2\form\fields\BoolField;
use skeeks\yii2\form\fields\FieldSet;
use skeeks\yii2\form\fields\HtmlBlock;
use skeeks\yii2\form\fields\NumberField;
use skeeks\yii2\form\fields\SelectField;
use skeeks\yii2\form\fields\TextField;
use skeeks\yii2\form\fields\WidgetField;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class AdminShopBrandController extends BackendModelStandartController
{
    public function init()
    {
        $this->name = \Yii::t('skeeks/cms', "Бренды");
        $this->modelShowAttribute = "name";
        $this->modelClassName = ShopBrand::class;
        $this->modelDefaultAction = 'view';
        $this->modelHeader = function () {
            return $this->renderPartial("@skeeks/cms/shop/views/admin-shop-brand/_model_header", [
                'model' => $this->model,
            ]);
        };

        $this->generateAccessActions = true;
        /*$this->permissionName = CmsManager::PERMISSION_ADMIN_ACCESS;*/

        /*$this->accessCallback = function () {
            if (!\Yii::$app->skeeks->site->is_default) {
                return false;
            }
            return \Yii::$app->user->can(CmsManager::PERMISSION_ROLE_ADMIN_ACCESS);
        };*/


        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return ArrayHelper::merge(parent::actions(), [
            'view' => [
                'class'    => BackendModelAction::class,
                'name'     => 'Карточка',
                'icon'     => 'fas fa-info-circle',
                'callback' => [$this, 'view'],
                'priority' => 50,
            ],
            'update-attribute' => [
                'class'     => BackendModelAction::class,
                'isVisible' => false,
                'callback'  => [$this, 'actionUpdateAttribute'],
                'accessCallback' => function (BackendModelAction $action) {
                    return (bool)$action->model && \Yii::$app->user->can($this->permissionName."/update", ['model' => $action->model]);
                },
            ],
            'index' => [
                "filters" => [
                    'visibleFilters' => [
                        'q',
                        'country',
                    ],
                    "filtersModel"   => [
                        'rules'            => [
                            ['country', 'safe'],
                            ['q', 'safe'],
                        ],
                        'attributeDefines' => [
                            'q',
                            'country',
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
                                            ['like', ShopBrand::tableName().'.description_full', $e->field->value],
                                            ['like', ShopBrand::tableName().'.description_short', $e->field->value],
                                            ['like', ShopBrand::tableName().'.name', $e->field->value],
                                        ]);
                                    }
                                },
                            ],
                            
                            'country' => [
                                'class'    => WidgetField::class,
                                'widgetClass'    => AjaxSelectModel::class,
                                'widgetConfig'    => [
                                    'modelClass'    => CmsCountry::class,
                                    'modelPkAttribute'    => "alpha2",
                                    'multiple' => true,
                                ],
                                'label'    => \Yii::t('skeeks/cms', 'Страна бренда'),
                                'on apply' => function (QueryFiltersEvent $e) {
                                    /**
                                     * @var $query ActiveQuery
                                     */
                                    $query = $e->dataProvider->query;
                                    if ($e->field->value) {
                                        $query->andFilterWhere([
                                            'country_alpha2' => $e->field->value,
                                        ]);
                                    }

                                },
                            ],
                        ],
                    ],
                ],
            'grid'  => [
                'defaultOrder'   => [
                    'created_at' => SORT_DESC,
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

                    'country_alpha2',

                    'countProducts',
                    'is_active',
                    'created_by',
                    'view',
                ],

                'columns'        => [

                    'created_at'   => [
                        'class' => DateTimeColumnData::class
                    ],
                    'updated_at'   => [
                        'class' => DateTimeColumnData::class
                    ],
                    'created_by'   => [
                        'class' => UserColumnData::class
                    ],
                    'countProducts'   => [
                        'format'    => 'raw',
                        'value'     => function (ShopBrand $shopBrand) {
                            return $shopBrand->raw_row['countProducts'];
                        },
                        'attribute' => 'countProducts',
                        'label'     => 'Количество товаров',
                        'beforeCreateCallback' => function (GridView $gridView) {
                            $query = $gridView->dataProvider->query;

                            $countProductsQuery = ShopProduct::find()
                                ->select(["total" => new \yii\db\Expression("count(id)"),])
                                ->andWhere([
                                    'brand_id' => new Expression(ShopBrand::tableName().".id"),
                                ]);

                            $query->addSelect([
                                'countProducts' => $countProductsQuery,
                            ]);

                            $gridView->sortAttributes['countProducts'] = [
                                'asc'     => ['countProducts' => SORT_ASC],
                                'desc'    => ['countProducts' => SORT_DESC],
                                'label'   => '',
                                'default' => SORT_ASC,
                            ];
                        },

                    ],

                    'country_alpha2' => [
                        'value' => function ($model) {
                            return (string)$model->country ? $model->country->name : "";
                        },
                    ],
                    'is_active'      => [
                        'class' => BooleanColumn::class,
                    ],

                    'custom' => [
                        'attribute' => 'name',
                        'format'    => 'raw',
                        'value'     => function (ShopBrand $model) {

                            $data = [];
                            $name = $model->asText;
                            if ($model->sx_id) {
                                $apiIconColor = $model->is_sx_info_update ? "green" : "red";
                                $apiIconTitle = $model->is_sx_info_update
                                    ? "SkeekS ID: {$model->sx_id}. Информация обновляется из сервиса SkeekS Товары"
                                    : "SkeekS ID: {$model->sx_id}. Обновление информации из сервиса SkeekS Товары запрещено";
                                $apiUrl = isset(\Yii::$app->skeeksSuppliersApi) ? \Yii::$app->skeeksSuppliersApi->getBrandUrl($model->sx_id) : "#";
                                $apiLink = Html::a("<small data-toggle='tooltip' title='{$apiIconTitle}'><i class='fas fa-link' style='color: {$apiIconColor};'></i></small>", $apiUrl, [
                                    'target'    => '_blank',
                                    'data-pjax' => '0',
                                    'onclick'   => 'event.stopPropagation();',
                                ]);
                                $data[] = Html::a($name, "#", ['class' => 'sx-trigger-action'])." ".$apiLink;
                            } else {
                                $data[] = Html::a($model->asText, "#", ['class' => 'sx-trigger-action']);
                            }
                            

                            $info = implode("<br />", $data);

                            return "<div class='row no-gutters'>
                                            <div class='sx-trigger-action' style='width: 50px;'>
                                                <a href='#' style='text-decoration: none; border-bottom: 0;'>
                                                    <img src='".($model->logo ? $model->logo->src : Image::getCapSrc())."' style='max-width: 50px; max-height: 50px; border-radius: 5px;' />
                                                </a>
                                            </div>
                                            <div style='margin: auto 5px;'>".$info."</div>
                                        </div>";;
                        },
                    ],

                    'flag_image_id' => [
                        'class' => ImageColumn2::class,
                    ],

                    'view' => [
                        'value'          => function (ShopBrand $model) {
                            return \yii\helpers\Html::a('<i class="fas fa-external-link-alt"></i>', $model->absoluteUrl,
                                [
                                    'target'    => '_blank',
                                    'title'     => \Yii::t('skeeks/cms', 'Watch to site (opens new window)'),
                                    'data-pjax' => '0',
                                    'class'     => 'btn btn-sm',
                                ]);
                        },
                        'format'         => 'raw',
                        /*'label'  => "Смотреть",*/
                        'headerOptions'  => [
                            'style' => 'max-width: 40px; width: 40px;',
                        ],
                    ],
                ],
            ],
        ],
            "create" => [
        'fields' => [$this, 'updateFields'],
    ],
            "update" => [
        'fields' => [$this, 'updateFields'],
        'on beforeValidate' => function ($event) {
            $this->_restoreSxInfoLockedAttributes($event, [
                'name',
                'country_alpha2',
                'website_url',
                'logo_image_id',
                'description_short',
                'description_full',
            ]);
        },
    ],

        ]);
    }

    public function updateFields($action)
    {
        $model = $action->model;
        $isSxInfoLocked = $this->_isSxInfoUpdateLocked($model);

        if ($isSxInfoLocked) {
            $this->_registerSxInfoLockedFieldsAssets($model, [
                'name',
                'country_alpha2',
                'website_url',
                'logo_image_id',
                'description_short',
                'description_full',
            ]);
        }

        return [
            'main' => [
                'class'  => FieldSet::class,
                'name'   => \Yii::t('skeeks/cms', 'Main'),
                'fields' => [
                    'sx_info_lock_notice' => [
                        'class'   => HtmlBlock::class,
                        'content' => $isSxInfoLocked ? \yii\bootstrap\Alert::widget([
                            'closeButton' => false,
                            'options'     => [
                                'class' => 'alert-warning',
                            ],
                            'body'        => 'Бренд связан с сервисом SkeekS Товары и синхронизация информации включена. Поля, которые обновляются из сервиса, закрыты от ручного редактирования, чтобы изменения не перезатирались. Чтобы изменить эти данные вручную, отключите синхронизацию в карточке бренда.',
                        ]) : '',
                    ],

                    'is_active' => [
                        'class'     => BoolField::class,
                        'allowNull' => false,
                    ],
                    'name' => [
                        'class'          => TextField::class,
                        'elementOptions' => $isSxInfoLocked ? ['disabled' => true] : [],
                    ],

                    'country_alpha2'    => [
                        'class'        => WidgetField::class,
                        'widgetClass'  => AjaxSelectModel::class,
                        'widgetConfig' => [
                            'modelClass'       => CmsCountry::class,
                            'modelPkAttribute' => "alpha2",
                            'searchQuery'      => function ($word = '') {
                                $query = \skeeks\cms\models\CmsCountry::find();
                                if ($word) {
                                    $query->search($word);
                                }
                                return $query;
                            },
                            'options'          => $isSxInfoLocked ? ['disabled' => true] : [],
                        ],

                    ],
                    'website_url' => [
                        'class'          => TextField::class,
                        'elementOptions' => $isSxInfoLocked ? ['disabled' => true] : [],
                    ],
                    'logo_image_id'     => [
                        'class'        => WidgetField::class,
                        'widgetClass'  => \skeeks\cms\widgets\AjaxFileUploadWidget::class,
                        'widgetConfig' => [
                            'accept'   => 'image/*',
                            'multiple' => false,
                            'options'  => $isSxInfoLocked ? ['disabled' => true] : [],
                        ],
                    ],
                    'description_short' => [
                        'class'       => WidgetField::class,
                        'widgetClass' => ComboTextInputWidget::class,
                        'widgetConfig' => [
                            'options' => $isSxInfoLocked ? ['disabled' => true] : [],
                        ],
                    ],
                    'description_full'  => [
                        'class'       => WidgetField::class,
                        'widgetClass' => ComboTextInputWidget::class,
                        'widgetConfig' => [
                            'options' => $isSxInfoLocked ? ['disabled' => true] : [],
                        ],
                    ],
                ],
            ],

            'seo' => [
                'class'          => FieldSet::class,
                'name'           => \Yii::t('skeeks/cms', 'SEO'),
                'elementOptions' => [
                    'isOpen' => false,
                ],
                'fields'         => [
                    'seo_h1',
                    'meta_title',
                    'meta_description',
                    'meta_keywords',
                    'code',
                ],
            ],

            'additional' => [
                'class'          => FieldSet::class,
                'elementOptions' => [
                    'isOpen' => false,
                ],
                'name'           => \Yii::t('skeeks/cms', 'Дополнительно'),
                'fields'         => [
                    'priority' => [
                        'class' => NumberField::class,
                    ],
                    'external_id',
                ],
            ],

        ];
    }

    protected function _isSxInfoUpdateLocked(ShopBrand $model = null)
    {
        return $model && !$model->isNewRecord && (bool)$model->sx_id && (bool)$model->is_sx_info_update;
    }

    protected function _restoreSxInfoLockedAttributes($event, array $attributes)
    {
        $model = $event->sender->model;
        if (!$this->_isSxInfoUpdateLocked($model)) {
            return;
        }

        foreach ($attributes as $attribute) {
            if ($model->hasAttribute($attribute)) {
                $model->{$attribute} = $model->getOldAttribute($attribute);
            }
        }
    }

    protected function _registerSxInfoLockedFieldsAssets(ShopBrand $model, array $attributes)
    {
        $inputNames = [];
        foreach ($attributes as $attribute) {
            $inputNames[] = Html::getInputName($model, $attribute);
        }

        $inputNamesJson = \yii\helpers\Json::htmlEncode($inputNames);
        \Yii::$app->view->registerCss(<<<CSS
.sx-sx-info-locked-field .form-control:disabled,
.sx-sx-info-locked-field select:disabled,
.sx-sx-info-locked-field textarea:disabled,
.sx-sx-info-locked-field input:disabled {
    background-color: #f3f5f7;
    cursor: not-allowed;
}
.sx-sx-info-locked-field .btn,
.sx-sx-info-locked-field button,
.sx-sx-info-locked-field .select2-selection,
.sx-sx-info-locked-field .file-preview,
.sx-sx-info-locked-field .fileinput-button {
    opacity: .65;
    pointer-events: none;
}
CSS
        );

        \Yii::$app->view->registerJs(<<<JS
(function() {
    var lockedInputNames = {$inputNamesJson};

    lockedInputNames.forEach(function(name) {
        var inputs = $("[name='" + name + "'], [name='" + name + "[]']");
        inputs.each(function() {
            var input = $(this);
            input.prop("disabled", true);
            if (input.is("select")) {
                input.trigger("change.select2");
            }
            input.closest(".form-group").addClass("sx-sx-info-locked-field");
        });
    });
})();
JS
        );
    }
    public function view()
    {
        return $this->render($this->action->id);
    }

    public function actionUpdateAttribute()
    {
        $rr = new RequestResponse();
        $model = $this->model;

        if ($rr->isRequestAjaxPost()) {
            try {
                $model->load(\Yii::$app->request->post());

                if (!$model->save()) {
                    throw new \yii\base\Exception("Ошибка сохранения: ".print_r($model->errors, true));
                }

                $rr->message = "Обновлено";
                $rr->success = true;
            } catch (\Exception $exception) {
                $rr->message = $exception->getMessage();
                $rr->success = false;
            }
        }

        return $rr;
    }
}
