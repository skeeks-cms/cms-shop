<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\shop\helpers\PartnerProgramCabinetHelper as PartnerProgramHelper;
use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\backend\helpers\BackendUrlHelper;
use skeeks\cms\components\Cms;
use skeeks\cms\queryfilters\QueryFiltersEvent;
use skeeks\cms\shop\models\ShopBonusTransaction;
use skeeks\yii2\form\fields\SelectField;
use yii\base\Event;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\UnsetArrayValue;

/**
 * Движение бонусов партнёра. Только просмотр: начисления и списания
 * создаются обработкой заявок партнёрской программы.
 *
 * @property ShopBonusTransaction $model
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class UpaPartnerBonusController extends BackendModelStandartController
{
    public function init()
    {
        $this->name = "Бонусы";
        $this->modelShowAttribute = 'asText';
        $this->modelClassName = ShopBonusTransaction::class;
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
                'name' => 'Бонусы',
                'configKey' => 'upa-client-v2/partner-bonus',
                'presentationMode' => 'page',
                'navigationActionIds' => false,
                'pageHeader' => [
                    'title' => 'Мои бонусы',
                    'description' => $this->balanceDescription(),
                    'actions' => [
                        [
                            'label' => 'Запросить вывод',
                            'url' => (string)BackendUrlHelper::createByParams(['/shop/upa-partner-payout/create'])
                                ->enableEmptyLayout()
                                ->enableNoActions()
                                ->url,
                            'icon' => 'fas fa-hand-holding-usd',
                            'options' => [
                                'data-sx-client-action' => 'drawer',
                                'data-pjax' => '0',
                            ],
                        ],
                        [
                            'label' => 'Добавить клиента',
                            'url' => (string)BackendUrlHelper::createByParams(['/shop/upa-partner-lead/create'])
                                ->enableEmptyLayout()
                                ->enableNoActions()
                                ->url,
                            'variant' => 'secondary',
                            'icon' => 'fas fa-user-plus',
                            'options' => [
                                'data-sx-client-action' => 'drawer',
                                'data-pjax' => '0',
                            ],
                        ],
                    ],
                ],
                'emptyState' => [
                    'title' => 'Бонусов пока нет',
                    'description' => 'Приведите нам клиента: после продажи услуги начислим '
                        .PartnerProgramHelper::rewardRangeText().' — вознаграждение появится здесь. '
                        .'1 бонус = 1 рубль — бонусы можно потратить на наши услуги или вывести деньгами.',
                    'icon' => 'fas fa-coins',
                    'action' => [
                        'label' => 'Добавить клиента',
                        'url' => (string)BackendUrlHelper::createByParams(['/shop/upa-partner-lead/create'])
                            ->enableEmptyLayout()
                            ->enableNoActions()
                            ->url,
                        'icon' => 'fas fa-user-plus',
                        'options' => [
                            'data-sx-client-action' => 'drawer',
                            'data-pjax' => '0',
                        ],
                    ],
                ],
                'noResultsState' => [
                    'title' => 'Операции не найдены',
                    'description' => 'Измените поисковый запрос или выбранные фильтры.',
                    'icon' => 'fas fa-search',
                ],
                'filters' => [
                    'visibleFilters' => [
                        'q',
                        'operation',
                    ],
                    'filtersModel' => [
                        'rules' => [
                            [['q', 'operation'], 'safe'],
                        ],
                        'attributeDefines' => [
                            'q',
                            'operation',
                        ],
                        'fields' => [
                            'q' => [
                                'label' => 'Поиск',
                                'elementOptions' => [
                                    'placeholder' => 'Комментарий к операции',
                                ],
                                'on apply' => static function (QueryFiltersEvent $event) {
                                    $value = trim((string)$event->field->value);
                                    if ($value === '') {
                                        return;
                                    }

                                    $event->dataProvider->query->andWhere([
                                        'like',
                                        ShopBonusTransaction::tableName().'.comment',
                                        $value,
                                    ]);
                                },
                            ],
                            'operation' => [
                                'class' => SelectField::class,
                                'label' => 'Тип операции',
                                'items' => [
                                    'credit' => 'Начисления',
                                    'debit' => 'Списания',
                                ],
                                'on apply' => static function (QueryFiltersEvent $event) {
                                    $value = (string)$event->field->value;
                                    if ($value === 'credit') {
                                        $event->dataProvider->query->andWhere([
                                            ShopBonusTransaction::tableName().'.is_debit' => 0,
                                        ]);
                                    } elseif ($value === 'debit') {
                                        $event->dataProvider->query->andWhere([
                                            ShopBonusTransaction::tableName().'.is_debit' => 1,
                                        ]);
                                    }
                                },
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
                        $query->cmsSite();
                        $query->andWhere([
                            ShopBonusTransaction::tableName().'.cms_user_id' => \Yii::$app->user->id,
                        ]);
                    },
                    'defaultPageSize' => 50,
                    'defaultOrder' => [
                        'created_at' => SORT_DESC,
                    ],
                    'visibleColumns' => [
                        'operation',
                        'value',
                        'created_at',
                    ],
                    'columns' => [
                        'operation' => [
                            'label' => 'Операция',
                            'format' => 'raw',
                            'headerOptions' => [
                                'style' => 'min-width: 320px;',
                            ],
                            'value' => static function (ShopBonusTransaction $model) {
                                $isDebit = (bool)$model->is_debit;
                                $icon = $isDebit ? 'fas fa-arrow-up' : 'fas fa-arrow-down';
                                $title = $isDebit ? 'Списание бонусов' : 'Начисление бонусов';
                                $comment = trim((string)$model->comment) ?: 'Без комментария';

                                return Html::tag(
                                    'span',
                                    Html::tag(
                                        'span',
                                        '<i class="'.$icon.'" aria-hidden="true"></i>',
                                        ['class' => 'sx-collection-cell__media']
                                    ).Html::tag(
                                        'span',
                                        Html::tag('strong', Html::encode($title), [
                                            'class' => 'sx-collection-cell__primary',
                                        ]).Html::tag('small', Html::encode($comment), [
                                            'class' => 'sx-collection-cell__secondary',
                                        ]),
                                        ['class' => 'sx-collection-cell sx-collection-cell--stack']
                                    ),
                                    ['class' => 'sx-collection-cell sx-collection-cell--entity']
                                );
                            },
                        ],
                        'value' => [
                            'label' => 'Бонусы',
                            'format' => 'raw',
                            'value' => static function (ShopBonusTransaction $model) {
                                $isDebit = (bool)$model->is_debit;
                                $sign = $isDebit ? '−' : '+';
                                $tone = $isDebit ? ' sx-text--danger' : ' sx-text--success';

                                return Html::tag(
                                    'span',
                                    Html::tag(
                                        'strong',
                                        $sign.\Yii::$app->formatter->asDecimal((float)$model->value, 2)
                                    ).Html::tag('small', 'бонусов'),
                                    ['class' => 'sx-collection-cell--metric'.$tone]
                                );
                            },
                        ],
                        'created_at' => [
                            'label' => 'Дата',
                            'attribute' => 'created_at',
                            'format' => 'raw',
                            'value' => static function (ShopBonusTransaction $model) {
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
            'create' => new UnsetArrayValue(),
            'update' => new UnsetArrayValue(),
            'delete' => new UnsetArrayValue(),
            'delete-multi' => new UnsetArrayValue(),
        ]);
    }

    /**
     * @return string
     */
    protected function balanceDescription(): string
    {
        return 'История начислений и списаний. Каждая операция подписана: видно, '
            .'по какой заявке начислено и за что списано.';
    }
}
