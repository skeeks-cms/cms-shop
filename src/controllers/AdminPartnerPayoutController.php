<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\backend\actions\BackendModelAction;
use skeeks\cms\backend\actions\BackendModelLogAction;
use skeeks\cms\backend\actions\BackendModelUpdateAction;
use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\backend\grid\BackendEntityLinkColumn;
use skeeks\cms\grid\DateTimeColumnData;
use skeeks\cms\grid\UserColumnData;
use skeeks\cms\models\CmsUser;
use skeeks\cms\models\CmsLog;
use skeeks\cms\queryfilters\filters\modes\FilterModeEq;
use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\shop\assets\admin\PartnerPayoutProcessAsset;
use skeeks\cms\shop\models\ShopPartnerPayout;
use skeeks\yii2\form\fields\FieldSet;
use skeeks\yii2\form\fields\NumberField;
use skeeks\yii2\form\fields\SelectField;
use skeeks\yii2\form\fields\TextareaField;
use yii\base\Event;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
use yii\helpers\UnsetArrayValue;
use yii\web\NotFoundHttpException;

/**
 * Administration of partner payout requests.
 *
 * @property ShopPartnerPayout $model
 */
class AdminPartnerPayoutController extends BackendModelStandartController
{
    public function init()
    {
        $this->name = 'Вывод бонусов';
        $this->modelShowAttribute = 'asText';
        $this->modelClassName = ShopPartnerPayout::class;
        $this->modelDefaultAction = 'view';
        $this->permissionName = 'shop/admin-partner-payout';
        $this->generateAccessActions = false;
        $this->modelHeader = function () {
            return $this->renderPartial('@skeeks/cms/shop/views/admin-partner-payout/_model_header', [
                'model' => $this->model,
            ]);
        };

        parent::init();
    }

    public function actions()
    {
        return ArrayHelper::merge(parent::actions(), [
            'index' => [
                'name' => 'Заявки на вывод',
                'configKey' => 'shop-partner-program-v1/payouts',
                'emptyState' => [
                    'title' => 'Заявок на вывод пока нет',
                    'description' => 'Новые заявки партнёров появятся в этом разделе.',
                    'icon' => 'fas fa-hand-holding-usd',
                ],
                'noResultsState' => [
                    'title' => 'Заявки не найдены',
                    'description' => 'Измените выбранные фильтры.',
                    'icon' => 'fas fa-search',
                ],
                'filters' => [
                    'visibleFilters' => ['status', 'cms_user_id'],
                    'filtersModel' => [
                        'fields' => [
                            'status' => [
                                'defaultMode' => FilterModeEq::ID,
                                'isAllowChangeMode' => false,
                                'field' => [
                                    'class' => SelectField::class,
                                    'items' => ShopPartnerPayout::statuses(),
                                    'multiple' => true,
                                ],
                            ],
                            'cms_user_id' => [
                                'label' => 'Партнёр',
                                'field' => [
                                    'widgetConfig' => [
                                        'searchQuery' => static function ($word = '') {
                                            $query = CmsUser::find()->forManager();
                                            if ($word) {
                                                $query->search($word);
                                            }
                                            return $query;
                                        },
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'grid' => [
                    'on init' => static function (Event $event) {
                        /** @var ActiveQuery $query */
                        $query = $event->sender->dataProvider->query;
                        $table = ShopPartnerPayout::tableName();
                        $pending = implode("','", ShopPartnerPayout::pendingStatuses());
                        $query->forManager()->cmsSite()->with(['cmsUser']);
                        $query->addSelect([
                            $table.'.*',
                            'work_sort' => new Expression("IF({$table}.status IN ('{$pending}'), 0, 1)"),
                        ]);
                    },
                    'defaultPageSize' => 50,
                    'defaultOrder' => ['work_sort' => SORT_ASC, 'created_at' => SORT_DESC],
                    'sortAttributes' => [
                        'work_sort' => [
                            'asc' => ['work_sort' => SORT_ASC],
                            'desc' => ['work_sort' => SORT_DESC],
                            'name' => 'Требуют работы',
                        ],
                    ],
                    'visibleColumns' => ['actions', 'id', 'status', 'cms_user_id', 'value', 'created_at'],
                    'columns' => [
                        'id' => [
                            'class' => BackendEntityLinkColumn::class,
                            'controllerId' => '/shop/admin-partner-payout',
                            'attribute' => 'id',
                            'label' => 'Заявка',
                            'value' => static function (ShopPartnerPayout $model) {
                                return 'Заявка №'.(int)$model->id;
                            },
                        ],
                        'status' => [
                            'label' => 'Статус',
                            'format' => 'raw',
                            'value' => static function (ShopPartnerPayout $model) {
                                return Html::tag('span', Html::encode($model->statusName), [
                                    'class' => ShopPartnerPayout::statusCssClass($model->status),
                                ]);
                            },
                        ],
                        'cms_user_id' => [
                            'class' => UserColumnData::class,
                            'label' => 'Партнёр',
                        ],
                        'value' => [
                            'label' => 'Сумма',
                            'format' => 'raw',
                            'value' => static function (ShopPartnerPayout $model) {
                                return Html::tag('span',
                                    Html::tag('strong', \Yii::$app->formatter->asDecimal((float)$model->value, 2))
                                    .Html::tag('small', $model->isWrittenOff ? 'списано' : 'бонусов'),
                                    ['class' => 'sx-collection-cell--metric'.($model->isWrittenOff ? ' sx-text--success' : '')]
                                );
                            },
                        ],
                        'created_at' => [
                            'class' => DateTimeColumnData::class,
                            'label' => 'Создана',
                        ],
                    ],
                ],
            ],
            'create' => new UnsetArrayValue(),
            'view' => [
                'class' => BackendModelAction::class,
                'name' => 'Карточка заявки',
                'icon' => 'fa fa-eye',
                'priority' => 10,
                'callback' => fn() => $this->render('view', ['model' => $this->model]),
            ],
            'process' => [
                'class' => BackendModelUpdateAction::class,
                'name' => 'Обработка заявки',
                'icon' => 'fa fa-check-circle',
                'priority' => 20,
                'fields' => [$this, 'processFields'],
                'accessCallback' => fn() => $this->canEditModel(),
            ],
            'edit' => [
                'class' => BackendModelUpdateAction::class,
                'name' => 'Редактирование',
                'icon' => 'fa fa-pencil',
                'priority' => 30,
                'fields' => [$this, 'editFields'],
                'accessCallback' => fn() => $this->canEditModel(),
            ],
            'log' => [
                'class' => BackendModelLogAction::class,
                'name' => 'Вся активность',
                'priority' => 40,
            ],
            'add-comment' => [
                'class' => BackendModelAction::class,
                'isVisible' => false,
                'callback' => [$this, 'addComment'],
            ],
            // Keep the conventional entity URL valid for BackendEntityLink and log entries,
            // but do not expose generic editing as a separate tab.
            'update' => [
                'class' => BackendModelAction::class,
                'isVisible' => false,
                'callback' => fn() => $this->render('view', ['model' => $this->model]),
            ],
            'delete' => new UnsetArrayValue(),
            'delete-multi' => new UnsetArrayValue(),
        ]);
    }

    public function editFields($action): array
    {
        return [
            'payout' => [
                'class' => FieldSet::class,
                'name' => 'Данные заявки',
                'fields' => [
                    'value' => [
                        'class' => NumberField::class,
                        'step' => 1,
                        'elementOptions' => ['min' => 1],
                    ],
                    'requisites' => [
                        'class' => TextareaField::class,
                        'elementOptions' => ['rows' => 5],
                    ],
                ],
            ],
        ];
    }

    public function processFields($action): array
    {
        /** @var ShopPartnerPayout $model */
        $model = $action->model;
        PartnerPayoutProcessAsset::register(\Yii::$app->view);

        return [
            'work' => [
                'class' => FieldSet::class,
                'name' => 'Работа по заявке',
                'fields' => [
                    'status' => [
                        'class' => SelectField::class,
                        'items' => ShopPartnerPayout::statuses(),
                        'allowNull' => false,
                        'hint' => 'Статус «Успешная» атомарно списывает бонусы. Отмена освобождает резерв.',
                        'elementOptions' => ['data-sx-payout-status' => '1'],
                    ],
                    'reject_reason' => [
                        'class' => TextareaField::class,
                        'elementOptions' => ['rows' => 4],
                        'hint' => 'Обязательно для статуса «Отменена». Причину видит партнёр.',
                        'options' => $this->statusFieldOptions([ShopPartnerPayout::STATUS_REJECTED]),
                    ],
                    'manager_comment' => [
                        'class' => TextareaField::class,
                        'elementOptions' => ['rows' => 4],
                        'hint' => 'Обязательно для статуса «Успешная». Сообщение увидит партнёр.',
                        'options' => $this->statusFieldOptions([ShopPartnerPayout::STATUS_PAID]),
                    ],
                ],
            ],
        ];
    }

    private function statusFieldOptions(array $statuses): array
    {
        return ['options' => [
            'class' => 'form-group',
            'data-sx-payout-statuses' => implode(' ', $statuses),
            'hidden' => !in_array($this->model->status, $statuses, true),
        ]];
    }

    public function addComment()
    {
        $response = new RequestResponse();
        try {
            if (!$response->isRequestAjaxPost()) {
                return $response;
            }
            $log = new CmsLog();
            if (!$log->load(\Yii::$app->request->post())) {
                throw new \RuntimeException('Не удалось прочитать комментарий.');
            }
            $log->log_type = CmsLog::LOG_TYPE_COMMENT;
            $log->model_code = $this->model->skeeksModelCode;
            $log->model_id = $this->model->id;
            $log->model_as_text = $this->model->asText;
            $log->comment = HtmlPurifier::process((string)$log->comment);
            if (trim(html_entity_decode(strip_tags($log->comment), ENT_QUOTES | ENT_HTML5, 'UTF-8')) === '') {
                throw new \RuntimeException('Напишите текст комментария.');
            }
            $log->is_pinned = 0;
            if (!$log->save()) {
                throw new \RuntimeException(implode('; ', $log->getFirstErrors()));
            }
            $this->model->notifyPartnerAboutComment((int)$log->id);
            $response->success = true;
            $response->message = 'Комментарий отправлен';
        } catch (\Throwable $e) {
            $response->success = false;
            $response->message = $e->getMessage();
        }

        return $response;
    }

    public function getModel()
    {
        if ($this->_model === null) {
            $pk = \Yii::$app->request->get($this->requestPkParamName);
            if ($pk) {
                $this->_model = ShopPartnerPayout::find()
                    ->forManager()
                    ->cmsSite()
                    ->andWhere([$this->modelPkAttribute => $pk])
                    ->limit(1)
                    ->one();
                if (!$this->_model) {
                    throw new NotFoundHttpException('Заявка на вывод не найдена.');
                }
            }
        }

        return $this->_model;
    }

    private function canEditModel(): bool
    {
        return $this->model && !in_array($this->model->status, [
            ShopPartnerPayout::STATUS_PAID,
            ShopPartnerPayout::STATUS_REJECTED,
        ], true);
    }
}
