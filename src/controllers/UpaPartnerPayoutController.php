<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\shop\helpers\PartnerProgramCabinetHelper as PartnerProgramHelper;
use skeeks\cms\shop\models\ShopPartnerPayout;
use skeeks\cms\backend\BackendAction;
use skeeks\cms\backend\actions\BackendModelAction;
use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\backend\helpers\BackendUrlHelper;
use skeeks\cms\components\Cms;
use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\models\CmsLog;
use skeeks\cms\queryfilters\filters\modes\FilterModeEq;
use skeeks\yii2\form\fields\NumberField;
use skeeks\yii2\form\fields\SelectField;
use skeeks\yii2\form\fields\TextareaField;
use yii\base\Event;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
use yii\helpers\Json;
use yii\helpers\UnsetArrayValue;
use yii\web\NotFoundHttpException;

/**
 * Заявки партнёра на вывод бонусов деньгами.
 *
 * @property ShopPartnerPayout $model
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class UpaPartnerPayoutController extends BackendModelStandartController
{
    public function init()
    {
        $this->name = "Вывод бонусов";
        $this->modelShowAttribute = 'asText';
        $this->modelClassName = ShopPartnerPayout::class;
        $this->modelDefaultAction = 'view';
        $this->modelHeader = '';

        $this->permissionName = Cms::UPA_PERMISSION;
        $this->permissionNames = [
            Cms::UPA_PERMISSION => 'Доступ к персональной части',
        ];
        $this->generateAccessActions = false;

        parent::init();
    }

    public function actions()
    {
        return ArrayHelper::merge(parent::actions(), [
            'index' => [
                'name' => 'Вывод бонусов',
                'configKey' => 'upa-client-v2/partner-payout',
                'presentationMode' => 'page',
                'navigationActionIds' => false,
                'pageHeader' => [
                    'title' => 'Вывод бонусов',
                    'description' => $this->availableDescription(),
                    'actions' => [
                        [
                            'backendAction' => 'create',
                            'label' => 'Запросить вывод',
                            'icon' => 'fas fa-hand-holding-usd',
                        ],
                        [
                            'label' => 'Мои бонусы',
                            'url' => ['/shop/upa-partner-bonus'],
                            'variant' => 'secondary',
                            'icon' => 'fas fa-coins',
                            //переход в другой раздел: pjax не обновляет активный пункт меню
                            'options' => ['data-pjax' => '0'],
                        ],
                    ],
                ],
                'emptyState' => [
                    'title' => 'Заявок на вывод пока нет',
                    'description' => 'Бонусы копятся с приведённых клиентов — '
                        .PartnerProgramHelper::rewardRangeText().'. Накопленное можно вывести деньгами '
                        .'по курсу 1 бонус = 1 рубль или направить на оплату наших услуг.',
                    'icon' => 'fas fa-hand-holding-usd',
                    'action' => [
                        'backendAction' => 'create',
                        'label' => 'Запросить вывод',
                    ],
                ],
                'noResultsState' => [
                    'title' => 'Заявки не найдены',
                    'description' => 'Измените выбранные фильтры.',
                    'icon' => 'fas fa-search',
                ],
                'filters' => [
                    'visibleFilters' => [
                        'status',
                    ],
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
                        ],
                    ],
                ],
                'on beforeRender' => PartnerProgramHelper::renderBanner(),
                'on afterRender' => PartnerProgramHelper::renderDetails(),
                'grid' => [
                    'presentation' => 'client',
                    'on init' => static function (Event $event) {
                        /** @var ActiveQuery $query */
                        $query = $event->sender->dataProvider->query;
                        $query->forClient();
                    },
                    'defaultPageSize' => 50,
                    'defaultOrder' => [
                        'created_at' => SORT_DESC,
                    ],
                    'visibleColumns' => [
                        'payout',
                        'status',
                        'value',
                        'created_at',
                    ],
                    'columns' => [
                        'payout' => [
                            'label' => 'Заявка',
                            'format' => 'raw',
                            'headerOptions' => [
                                'style' => 'min-width: 280px;',
                            ],
                            'value' => static function (ShopPartnerPayout $model) {
                                $url = (string)BackendUrlHelper::createByParams([
                                    '/shop/upa-partner-payout/view',
                                    'pk' => $model->id,
                                ])
                                    ->enableEmptyLayout()
                                    ->enableNoActions()
                                    ->url;
                                $actionData = Json::encode([
                                    'isOpenNewWindow' => true,
                                    'url' => $url,
                                ]);

                                $content = Html::tag(
                                    'span',
                                    '<i class="fas fa-hand-holding-usd" aria-hidden="true"></i>',
                                    ['class' => 'sx-collection-cell__media']
                                ).Html::tag(
                                    'span',
                                    Html::tag('strong', 'Заявка №'.(int)$model->id, [
                                        'class' => 'sx-collection-cell__primary',
                                    ]).Html::tag('small', 'Открыть заявку', [
                                        'class' => 'sx-collection-cell__secondary',
                                    ]),
                                    ['class' => 'sx-collection-cell sx-collection-cell--stack']
                                );

                                return Html::a($content, $url, [
                                    'class' => 'sx-collection-cell sx-collection-cell--entity',
                                    'data-pjax' => '0',
                                    'onclick' => "new sx.classes.backend.widgets.Action({$actionData}).go(); return false;",
                                ]);
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
                        'value' => [
                            'label' => 'Сумма',
                            'format' => 'raw',
                            'value' => static function (ShopPartnerPayout $model) {
                                return Html::tag(
                                    'span',
                                    Html::tag('strong', \Yii::$app->formatter->asDecimal((float)$model->value, 2))
                                    .Html::tag('small', 'бонусов = руб.'),
                                    ['class' => 'sx-collection-cell--metric']
                                );
                            },
                        ],
                        'created_at' => [
                            'label' => 'Создана',
                            'attribute' => 'created_at',
                            'format' => 'raw',
                            'value' => static function (ShopPartnerPayout $model) {
                                return Html::tag(
                                    'time',
                                    Html::tag('strong', \Yii::$app->formatter->asDate($model->created_at)).
                                    Html::tag('small', \Yii::$app->formatter->asTime($model->created_at, 'short')),
                                    ['class' => 'sx-collection-cell__date']
                                );
                            },
                        ],
                    ],
                ],
            ],
            'create' => [
                'name' => 'Запросить вывод',
                'icon' => 'fas fa-hand-holding-usd',
                'fields' => [$this, 'createFields'],
                'successMessage' => '✓ Заявка на вывод принята',
                'on beforeValidate' => function (Event $event) {
                    $this->prepareClientPayout($event->sender->model);
                },
            ],
            'view' => [
                'class' => BackendModelAction::class,
                'name' => 'Заявка на вывод',
                'icon' => 'fa fa-eye',
                'callback' => [$this, 'view'],
                'priority' => 10,
            ],
            'add-comment' => [
                'class' => BackendAction::class,
                'isVisible' => false,
                'callback' => [$this, 'addComment'],
                'accessCallback' => static function () {
                    return !\Yii::$app->user->isGuest;
                },
            ],
            'update' => new UnsetArrayValue(),
            'delete' => new UnsetArrayValue(),
            'delete-multi' => new UnsetArrayValue(),
        ]);
    }

    /**
     * @param $action
     * @return array
     */
    public function createFields($action): array
    {
        /** @var ShopPartnerPayout $model */
        $model = $action->model;
        $available = PartnerProgramHelper::available();

        if (!$model->value && $available > 0) {
            $model->value = $available;
        }

        return [
            'value' => [
                'class' => NumberField::class,
                'label' => 'Сумма к выводу, бонусов',
                'hint' => 'Доступно к выводу: '.\Yii::$app->formatter->asDecimal($available, 2)
                    .' бонусов. 1 бонус = 1 рубль.',
                'elementOptions' => [
                    'min' => 1,
                    'step' => 1,
                    'max' => $available > 0 ? $available : 1,
                ],
            ],
            'requisites' => [
                'class' => TextareaField::class,
                'label' => 'Реквизиты для выплаты',
                'hint' => 'Укажите, куда перевести деньги: карта, счёт, ИП/ООО с реквизитами или другой способ.',
                'elementOptions' => [
                    'rows' => 5,
                    'placeholder' => 'Например: карта 0000 0000 0000 0000, получатель Иван Петров',
                ],
            ],
        ];
    }

    /**
     * Владелец и статус заявки задаются только на сервере.
     *
     * @param ShopPartnerPayout $model
     */
    public function prepareClientPayout(ShopPartnerPayout $model): void
    {
        $site = \Yii::$app->skeeks->site;
        $model->cms_site_id = $site ? (int)$site->id : null;
        $model->cms_user_id = (int)\Yii::$app->user->id;
        $model->status = ShopPartnerPayout::STATUS_NEW;
        $model->reject_reason = null;
        $model->manager_comment = null;
        $model->shop_bonus_transaction_id = null;
        $model->paid_at = null;
    }

    /**
     * @inheritdoc
     */
    public function getModel()
    {
        if ($this->_model === null && $pk = \Yii::$app->request->get($this->requestPkParamName)) {
            $this->_model = ShopPartnerPayout::find()
                ->forClient()
                ->andWhere([ShopPartnerPayout::tableName().'.id' => (int)$pk])
                ->one();

            if (!$this->_model) {
                throw new NotFoundHttpException('Заявка не найдена.');
            }
        }

        return $this->_model;
    }

    public function view()
    {
        return $this->render('view', [
            'model' => $this->model,
        ]);
    }

    /**
     * Adds a comment only to a payout belonging to the signed-in partner.
     * Model and author identifiers from POST are intentionally ignored.
     */
    public function addComment()
    {
        $response = new RequestResponse();
        try {
            if (!$response->isRequestAjaxPost()) {
                return $response;
            }

            $payout = $this->model;
            $log = new CmsLog();
            if (!$log->load(\Yii::$app->request->post())) {
                throw new \RuntimeException('Не удалось прочитать комментарий. Обновите страницу и попробуйте ещё раз.');
            }
            $log->log_type = CmsLog::LOG_TYPE_COMMENT;
            $log->model_code = $payout->skeeksModelCode;
            $log->model_id = $payout->id;
            $log->model_as_text = $payout->asText;
            $log->comment = HtmlPurifier::process((string)$log->comment);
            if (trim(html_entity_decode(strip_tags($log->comment), ENT_QUOTES | ENT_HTML5, 'UTF-8')) === '') {
                throw new \RuntimeException('Напишите текст комментария.');
            }
            $log->fileIds = [];
            $log->is_pinned = 0;
            $log->created_by = (int)\Yii::$app->user->id;
            $log->updated_by = (int)\Yii::$app->user->id;
            $log->cms_user_id = (int)\Yii::$app->user->id;
            $log->cms_company_id = null;
            if (!$log->save()) {
                $errors = $log->getFirstErrors();
                throw new \RuntimeException($errors ? (string)reset($errors) : 'Не удалось сохранить комментарий.');
            }

            $payout->notifyManagersAboutPartnerComment((int)$log->id);
            $response->success = true;
            $response->message = 'Комментарий отправлен';
        } catch (\Throwable $e) {
            $response->success = false;
            $response->message = $e->getMessage();
        }

        return $response;
    }

    /**
     * @return string
     */
    protected function availableDescription(): string
    {
        return 'Выводите накопленные бонусы деньгами на карту или счёт. '
            .'Сумма в необработанной заявке резервируется и возвращается, если заявку отклонить.';
    }
}
