<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\helpers;

use skeeks\cms\backend\events\ViewRenderEvent;
use skeeks\cms\backend\widgets\BackendSurfaceWidget;
use skeeks\cms\components\Cms;
use skeeks\cms\models\CmsTree;
use skeeks\cms\shop\helpers\PartnerProgramHelper as ShopPartnerProgramHelper;
use yii\helpers\Html;

/**
 * Бонусный баланс партнёрской программы.
 *
 * Движение бонусов ведётся в общей таблице shop_bonus_transaction,
 * поэтому баланс партнёра совпадает с его бонусным балансом в CMS.
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class PartnerProgramCabinetHelper
{
    /**
     * 1 бонус = 1 рубль
     */
    const BONUS_RATE = 1;

    /**
     * Границы вознаграждения партнёра в процентах от оплаты приведённого клиента.
     * Это единственное место, где заданы цифры: тексты кабинета и лендинга
     * считаются от них.
     */
    const REWARD_MIN_PERCENT = 20;
    const REWARD_MAX_PERCENT = 50;

    /**
     * Символьный код страницы партнёрской программы в дереве CMS.
     * Страница необязательна: пока узла нет, кабинет просто не показывает
     * блок «поделиться ссылкой».
     */
    const LANDING_TREE_CODE = 'partner-program';

    /**
     * @var CmsTree|false|null
     */
    static protected $_landingTree = null;

    /**
     * «20–50%» — диапазон вознаграждения
     *
     * @return string
     */
    static public function rewardRange(): string
    {
        return self::REWARD_MIN_PERCENT.'–'.self::REWARD_MAX_PERCENT.'%';
    }

    /**
     * «20–50% от оплаты клиента» — готовая формулировка для интерфейса
     *
     * @return string
     */
    static public function rewardRangeText(): string
    {
        return self::rewardRange().' от оплаты клиента';
    }

    /**
     * Страница партнёрской программы на сайте, если она заведена в CMS
     *
     * @return CmsTree|null
     */
    static public function landingTree()
    {
        if (self::$_landingTree === null) {
            self::$_landingTree = CmsTree::find()
                ->andWhere(['code' => self::LANDING_TREE_CODE])
                ->andWhere(['active' => Cms::BOOL_Y])
                ->one() ?: false;
        }

        return self::$_landingTree ?: null;
    }

    /**
     * Абсолютный адрес страницы партнёрской программы
     *
     * @return string|null
     */
    static public function landingUrl()
    {
        $tree = self::landingTree();

        return $tree ? $tree->absoluteUrl : null;
    }

    /**
     * Полный бонусный баланс пользователя
     *
     * @param int|null $userId
     * @return float
     */
    static public function balance($userId = null): float
    {
        return ShopPartnerProgramHelper::balance(self::resolveUserId($userId));
    }

    /**
     * Бонусы, зарезервированные необработанными заявками на вывод
     *
     * @param int|null $userId
     * @return float
     */
    static public function reserved($userId = null): float
    {
        return ShopPartnerProgramHelper::reserved(self::resolveUserId($userId));
    }

    /**
     * Бонусы, которые можно вывести или потратить прямо сейчас
     *
     * @param int|null $userId
     * @return float
     */
    static public function available($userId = null): float
    {
        return ShopPartnerProgramHelper::available(self::resolveUserId($userId));
    }

    /**
     * Бонусы в виде текста с рублёвым эквивалентом
     *
     * @param float $value
     * @return string
     */
    static public function asText($value): string
    {
        return \Yii::$app->formatter->asDecimal((float)$value, 2).' бонусов';
    }

    /**
     * Крупный бонусный баланс над коллекцией. Собран на общих примитивах
     * бэкенда (sx-surface, sx-metric), поэтому не требует собственного CSS
     * в теме кабинета.
     *
     * @return string
     */
    static public function balanceBannerHtml(): string
    {
        $available = self::available();
        $reserved = self::reserved();
        $formatter = \Yii::$app->formatter;

        $metric = static function ($value, $label) {
            return Html::tag(
                'div',
                Html::tag('strong', $value, ['class' => 'sx-metric__value'])
                .Html::tag('span', $label, ['class' => 'sx-metric__label']),
                ['class' => 'sx-metric']
            );
        };

        $metrics = $metric(
            $formatter->asDecimal($available, 2),
            'Доступно бонусов · '.$formatter->asDecimal($available * self::BONUS_RATE, 0).' ₽'
        );

        if ($reserved > 0) {
            $metrics .= $metric(
                $formatter->asDecimal($reserved, 2),
                'В заявках на вывод'
            );
        }

        $metrics .= $metric(self::rewardRange(), 'Вознаграждение за клиента');

        return BackendSurfaceWidget::widget([
            'content' =>
            //общий компонент знает только модификаторы --3 и --4,
            //без них сетка раскладывается на 4 колонки и ломается на мобильном
            Html::tag(
                'div',
                $metrics,
                ['class' => 'sx-metrics sx-metrics--3']
            ),
            'options' => [
                'style' => 'margin-bottom: 1rem;',
                'aria-label' => 'Бонусный баланс',
            ],
        ]);
    }

    /**
     * Подробности о программе под таблицей: курс, что можно сделать
     * с бонусами и ссылка на страницу программы.
     *
     * @return string
     */
    static public function detailsHtml(): string
    {
        $facts = [
            [
                'Как начисляем',
                'За каждого приведённого клиента — '.self::rewardRangeText()
                .'. Бонусы приходят после того, как клиент оплатил услугу.',
            ],
            [
                'Курс',
                '1 бонус = 1 рубль. Курс фиксированный, пересчётов и сгорания нет.',
            ],
            [
                'Что можно сделать',
                'Запросить вывод деньгами на карту или счёт либо оплатить бонусами наши услуги.',
            ],
        ];

        $items = '';
        foreach ($facts as [$title, $text]) {
            $items .= Html::tag(
                'div',
                Html::tag('strong', Html::encode($title), ['class' => 'sx-collection-cell__primary'])
                .Html::tag('span', Html::encode($text), ['class' => 'sx-collection-cell__secondary']),
                ['class' => 'sx-collection-cell sx-collection-cell--stack']
            );
        }

        $link = self::landingLinkHtml();

        return BackendSurfaceWidget::widget([
            'title' => 'О партнёрской программе',
            'actions' => $link,
            'content' => Html::tag(
                'div',
                $items,
                ['class' => 'sx-metrics sx-metrics--3', 'style' => 'margin-top: 0.75rem;']
            ),
            'options' => [
                'style' => 'margin-top: 1rem;',
            ],
            'responsive' => true,
        ]);
    }

    /**
     * Обработчики рендера для index-экшена раздела партнёрской программы.
     * Баланс уходит над коллекцией, подробности — под неё, поэтому таблица
     * остаётся чистой.
     *
     * Использование в actions():
     *   'on beforeRender' => PartnerProgramHelper::renderBanner(),
     *   'on afterRender'  => PartnerProgramHelper::renderDetails(),
     *
     * @return \Closure
     */
    static public function renderBanner(): \Closure
    {
        return static function (ViewRenderEvent $event) {
            $event->content = self::balanceBannerHtml();
        };
    }

    /**
     * @return \Closure
     */
    static public function renderDetails(): \Closure
    {
        return static function (ViewRenderEvent $event) {
            $event->content = self::detailsHtml();
        };
    }

    /**
     * Ссылка «подробнее» на страницу программы. Открывается в новой вкладке,
     * чтобы не уводить партнёра из кабинета. Пустая строка, если страницы нет.
     *
     * @return string
     */
    static public function landingLinkHtml(): string
    {
        $url = self::landingUrl();
        if (!$url) {
            return '';
        }

        return Html::a(
            '<i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i> Подробнее о программе',
            $url,
            [
                //btn-классы обязательны: общее правило a:not(.btn) в теме
                //проекта иначе перекрашивает текст кнопки в акцентный цвет
                'class' => 'btn btn-secondary sx-button sx-button--secondary',
                'target' => '_blank',
                'rel' => 'noopener',
                'data-pjax' => '0',
            ]
        );
    }

    /**
     * @param int|null $userId
     * @return int|null
     */
    static protected function resolveUserId($userId = null)
    {
        if ($userId === null) {
            $userId = \Yii::$app->user->id;
        }

        return $userId ? (int)$userId : null;
    }
}
