<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\shop\helpers\PartnerProgramCabinetHelper as PartnerProgramHelper;
use skeeks\cms\shop\models\PartnerLeadContactForm;
use skeeks\cms\backend\BackendAction;
use skeeks\cms\backend\actions\BackendModelCreateAction;
use skeeks\cms\models\CmsLead;
use skeeks\cms\shop\models\ShopPartnerLead;
use skeeks\cms\widgets\assets\CmsProfilePhoneAsset;
use skeeks\cms\widgets\admin\CmsLeadStatusWidget;
use skeeks\cms\backend\actions\BackendModelAction;
use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\backend\helpers\BackendUrlHelper;
use skeeks\cms\components\Cms;
use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\models\CmsLog;
use skeeks\cms\queryfilters\filters\modes\FilterModeEq;
use skeeks\cms\queryfilters\QueryFiltersEvent;
use skeeks\cms\services\CmsLeadService;
use skeeks\yii2\form\fields\SelectField;
use skeeks\yii2\form\fields\TextareaField;
use yii\base\Event;
use yii\base\Application;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\db\Transaction;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
use yii\helpers\Json;
use yii\helpers\UnsetArrayValue;
use yii\web\NotFoundHttpException;

/**
 * Партнёрская программа клиента: заявки на потенциальных клиентов.
 *
 * @property CmsLead $model
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class UpaPartnerLeadController extends BackendModelStandartController
{
    private ?Transaction $_creationTransaction = null;

    public function init()
    {
        $this->name = "Партнёрская программа";
        $this->modelShowAttribute = 'name';
        $this->modelClassName = CmsLead::class;
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
                'name' => 'Потенциальные клиенты',
                'configKey' => 'upa-client-v2/partner-lead',
                'presentationMode' => 'page',
                'navigationActionIds' => false,
                'pageHeader' => [
                    'title' => 'Партнёрская программа',
                    'description' => $this->programDescription(),
                    'actions' => $this->pageHeaderActions(),
                ],
                'emptyState' => [
                    'title' => 'Заявок пока нет',
                    'description' => 'Расскажите нам о потенциальном клиенте: мы свяжемся с ним, '
                        .'а после продажи услуги начислим вам '.PartnerProgramHelper::rewardRangeText()
                        .' бонусами. 1 бонус = 1 рубль.',
                    'icon' => 'fas fa-user-plus',
                    'action' => [
                        'backendAction' => 'create',
                        'label' => 'Добавить клиента',
                    ],
                ],
                'noResultsState' => [
                    'title' => 'Заявки не найдены',
                    'description' => 'Измените поисковый запрос или выбранные фильтры.',
                    'icon' => 'fas fa-search',
                ],
                'filters' => [
                    'visibleFilters' => [
                        'q',
                        'status',
                    ],
                    'filtersModel' => [
                        'rules' => [
                            [['q'], 'safe'],
                        ],
                        'attributeDefines' => [
                            'q',
                        ],
                        'fields' => [
                            'q' => [
                                'label' => 'Поиск',
                                'elementOptions' => [
                                    'placeholder' => 'Имя, телефон или email клиента',
                                ],
                                'on apply' => static function (QueryFiltersEvent $event) {
                                    $value = trim((string)$event->field->value);
                                    if ($value === '') {
                                        return;
                                    }

                                    $event->dataProvider->query->search($value);
                                },
                            ],
                            'status' => [
                                'defaultMode' => FilterModeEq::ID,
                                'isAllowChangeMode' => false,
                                'field' => [
                                    'class' => SelectField::class,
                                    'items' => CmsLead::statuses(),
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
                    'on init' => function (Event $event) {
                        /** @var ActiveQuery $query */
                        $query = $event->sender->dataProvider->query;
                        $query
                            ->forPartner((int)\Yii::$app->user->id)
                            ->cmsSite()
                            ->with(['phones', 'emails']);

                        $table = CmsLead::tableName();
                        $new = CmsLead::STATUS_NEW;
                        $inWork = CmsLead::STATUS_IN_WORK;
                        $success = CmsLead::STATUS_SUCCESS;

                        $query->addSelect([
                            $table.'.*',
                            'client_status_sort' => new Expression("
                                CASE
                                    WHEN {$table}.status = '{$new}' THEN 1
                                    WHEN {$table}.status = '{$inWork}' THEN 2
                                    WHEN {$table}.status = '{$success}' THEN 3
                                    ELSE 4
                                END
                            "),
                        ]);
                    },
                    'defaultPageSize' => 50,
                    'defaultOrder' => [
                        'client_status_sort' => SORT_ASC,
                        'created_at' => SORT_DESC,
                    ],
                    'sortAttributes' => [
                        'client_status_sort' => [
                            'asc' => ['client_status_sort' => SORT_ASC],
                            'desc' => ['client_status_sort' => SORT_DESC],
                            'name' => 'Приоритет',
                        ],
                    ],
                    'visibleColumns' => [
                        'lead',
                        'status',
                        'reward',
                        'created_at',
                    ],
                    'columns' => [
                        'lead' => [
                            'label' => 'Потенциальный клиент',
                            'format' => 'raw',
                            'headerOptions' => [
                                'style' => 'min-width: 320px;',
                            ],
                            'value' => static function (CmsLead $model) {
                                $url = (string)BackendUrlHelper::createByParams([
                                    '/shop/upa-partner-lead/view',
                                    'pk' => $model->id,
                                ])
                                    ->enableEmptyLayout()
                                    ->enableNoActions()
                                    ->url;
                                $actionData = Json::encode([
                                    'isOpenNewWindow' => true,
                                    'url' => $url,
                                ]);

                                $contacts = array_filter([
                                    $model->mainPhone ? $model->mainPhone->value : null,
                                    $model->mainEmail ? $model->mainEmail->value : null,
                                ]);
                                $secondary = $contacts ? implode(' · ', $contacts) : 'Открыть заявку';

                                $content = Html::tag(
                                    'span',
                                    '<i class="fas fa-user-plus" aria-hidden="true"></i>',
                                    ['class' => 'sx-collection-cell__media']
                                ).Html::tag(
                                    'span',
                                    Html::tag('strong', Html::encode($model->name), [
                                        'class' => 'sx-collection-cell__primary',
                                    ]).Html::tag('small', Html::encode($secondary), [
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
                            'value' => static function (CmsLead $model) {
                                return CmsLeadStatusWidget::widget(['lead' => $model]);
                            },
                        ],
                        'reward' => [
                            'label' => 'Вознаграждение',
                            'format' => 'raw',
                            'value' => static function (CmsLead $model) {
                                $reward = ShopPartnerLead::find()->andWhere(['cms_lead_id' => $model->id])->one();
                                if (!$reward) {
                                    return Html::tag(
                                        'span',
                                        Html::tag('strong', '—').Html::tag('small', 'бонусов'),
                                        ['class' => 'sx-collection-cell--metric']
                                    );
                                }

                                return Html::tag(
                                    'span',
                                    Html::tag(
                                        'strong',
                                        '+'.\Yii::$app->formatter->asDecimal((float)$reward->reward_value, 2)
                                    ).Html::tag('small', 'бонусов'),
                                    ['class' => 'sx-collection-cell--metric sx-text--success']
                                );
                            },
                        ],
                        'created_at' => [
                            'label' => 'Добавлена',
                            'attribute' => 'created_at',
                            'format' => 'raw',
                            'value' => static function (CmsLead $model) {
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
                'name' => 'Добавить клиента',
                'icon' => 'fas fa-user-plus',
                'fields' => [$this, 'createFields'],
                'successMessage' => '✓ Заявка принята, мы свяжемся с клиентом',
                'on '.BackendModelCreateAction::EVENT_INIT_FORM_MODELS => static function (Event $event) {
                    $event->sender->formModels['contacts'] = new PartnerLeadContactForm();
                },
                'on beforeValidate' => function (Event $event) {
                    $this->prepareClientLead($event->sender->model);
                },
                'on '.BackendModelCreateAction::EVENT_BEFORE_SAVE => function (): void {
                    $this->beginCreation();
                },
                'on '.BackendModelCreateAction::EVENT_AFTER_SAVE => function (Event $event) {
                    /** @var PartnerLeadContactForm $contacts */
                    $contacts = $event->sender->formModels['contacts'];
                    (new CmsLeadService())->syncContacts(
                        $event->sender->model,
                        [$contacts->phone],
                        [$contacts->email]
                    );
                    $this->_creationTransaction->commit();
                },
            ],
            'view' => [
                'class' => BackendModelAction::class,
                'name' => 'Заявка',
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

    private function beginCreation(): void
    {
        $this->_creationTransaction = CmsLead::getDb()->beginTransaction();
        \Yii::$app->on(Application::EVENT_AFTER_REQUEST, function (): void {
            if ($this->_creationTransaction && $this->_creationTransaction->isActive) {
                $this->_creationTransaction->rollBack();
            }
        });
    }

    /**
     * Форма клиента намеренно короткая: только то, что клиент понимает и может заполнить.
     *
     * @param $action
     * @return array
     */
    public function createFields($action): array
    {
        CmsProfilePhoneAsset::register(\Yii::$app->view);

        return [
            'name' => [
                'elementOptions' => [
                    'placeholder' => 'Например, Иван Петров или ООО «Ромашка»',
                ],
            ],
            'contacts.phone' => [
                'elementOptions' => [
                    'placeholder' => '+7 903 722-28-73',
                    'data-sx-phone-mask' => '+7 999 999-99-99',
                ],
            ],
            'contacts.email' => [
                'elementOptions' => [
                    'placeholder' => 'client@example.com',
                ],
            ],
            'description' => [
                'class' => TextareaField::class,
                'elementOptions' => [
                    'rows' => 6,
                    'placeholder' => 'Что нужно клиенту: сайт, хостинг, товарная база, доработки, продвижение…',
                ],
            ],
        ];
    }

    /**
     * Владелец и статус заявки задаются только на сервере.
     *
     * @param CmsLead $model
     */
    public function prepareClientLead(CmsLead $model): void
    {
        $model->cms_site_id = (int)\Yii::$app->skeeks->site->id;
        $model->submitted_by_id = (int)\Yii::$app->user->id;
        $model->partner_id = (int)\Yii::$app->user->id;
        $model->source_type = CmsLead::SOURCE_PARTNER;
        $model->source_name = 'Партнёрская программа';
        $model->status = CmsLead::STATUS_NEW;
        $model->executor_id = null;
        $model->cms_company_id = null;
        $model->cms_user_id = null;
        $model->reject_reason = null;
        $model->result_comment = null;
        $model->processed_at = null;
    }

    /**
     * @inheritdoc
     */
    public function getModel()
    {
        if ($this->_model === null && $pk = \Yii::$app->request->get($this->requestPkParamName)) {
            $this->_model = CmsLead::find()
                ->with(['phones', 'emails'])
                ->forPartner((int)\Yii::$app->user->id)
                ->cmsSite()
                ->andWhere([CmsLead::tableName().'.id' => (int)$pk])
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
     * Добавляет комментарий только к заявке текущего партнёра.
     * Идентификаторы модели и автора из POST намеренно не используются.
     *
     * @return RequestResponse
     */
    public function addComment()
    {
        $rr = new RequestResponse();

        try {
            if (!$rr->isRequestAjaxPost()) {
                return $rr;
            }

            /** @var CmsLead $lead */
            $lead = $this->model;
            $log = new CmsLog();

            if (!$log->load(\Yii::$app->request->post())) {
                throw new \RuntimeException('Не удалось прочитать комментарий. Обновите страницу и попробуйте ещё раз.');
            }

            $log->log_type = CmsLog::LOG_TYPE_COMMENT;
            $log->model_code = $lead->skeeksModelCode;
            $log->model_id = $lead->id;
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

            $lead->notifyManagersAboutPartnerComment();

            $rr->success = true;
            $rr->message = 'Комментарий отправлен';
        } catch (\Throwable $e) {
            $rr->success = false;
            $rr->message = $e->getMessage();
        }

        return $rr;
    }

    /**
     * @return string
     */
    protected function programDescription(): string
    {
        return 'Приводите нам клиентов на любую услугу: мы свяжемся, обсудим задачу, и после продажи '
            .'начислим вам '.PartnerProgramHelper::rewardRangeText().' бонусами.';
    }

    /**
     * Действия в заголовке раздела. Ссылка на страницу партнёрской программы
     * появляется только если страница заведена в дереве CMS.
     *
     * @return array
     */
    protected function pageHeaderActions(): array
    {
        $actions = [
            [
                'backendAction' => 'create',
                'label' => 'Добавить клиента',
                'icon' => 'fas fa-user-plus',
            ],
            [
                'label' => 'Мои бонусы',
                'url' => ['/shop/upa-partner-bonus'],
                'variant' => 'secondary',
                'icon' => 'fas fa-coins',
                //переход в другой раздел: pjax подменяет только контент,
                //и активный пункт меню остаётся на прежнем разделе
                'options' => ['data-pjax' => '0'],
            ],
        ];

        return $actions;
    }
}
