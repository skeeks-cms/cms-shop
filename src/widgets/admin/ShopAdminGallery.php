<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 */

namespace skeeks\cms\shop\widgets\admin;

use skeeks\cms\backend\helpers\BackendIcon;
use skeeks\cms\shop\assets\admin\AdminShopGalleryAsset;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * Lightweight image gallery for product and collection backend cards.
 *
 * Every item accepts full, preview, thumbnail and alt. Values are URLs/plain
 * text; the widget owns markup, controls and accessibility state.
 */
class ShopAdminGallery extends Widget
{
    /** @var array[] */
    public $items = [];

    /** @var string Fancybox group shared by the rendered full-image links. */
    public $fancyboxGroup = 'shop-admin-gallery';

    /** @var array Root options. */
    public $options = [];

    public function init()
    {
        parent::init();

        Html::addCssClass($this->options, 'sx-shop-gallery');
        $this->options['data-sx-shop-gallery'] = '1';
        $this->options['tabindex'] = '0';
        $this->options['role'] = 'region';
        $this->options['aria-roledescription'] = \Yii::t('skeeks/shop/app', 'Галерея изображений');
        AdminShopGalleryAsset::register($this->getView());
    }

    public function run()
    {
        if (!$this->items) {
            return '';
        }

        $slides = '';
        $thumbs = '';
        foreach (array_values($this->items) as $index => $item) {
            $item = (array)$item;
            $preview = (string)ArrayHelper::getValue($item, 'preview', '');
            $full = (string)ArrayHelper::getValue($item, 'full', $preview);
            $thumbnail = (string)ArrayHelper::getValue($item, 'thumbnail', $preview);
            $alt = (string)ArrayHelper::getValue($item, 'alt', '');

            $image = Html::img($preview, [
                'class' => 'sx-shop-gallery__image',
                'alt' => $alt,
                'loading' => $index === 0 ? 'eager' : 'lazy',
            ]);
            if ($full !== '') {
                $image = Html::a($image, $full, [
                    'class' => 'sx-shop-gallery__link',
                    'data-fancybox' => $this->fancyboxGroup,
                    'data-pjax' => '0',
                ]);
            }

            $slides .= Html::tag('div', $image, [
                'class' => 'sx-shop-gallery__slide'.($index === 0 ? ' is-active' : ''),
                'data-sx-gallery-slide' => (string)$index,
                'aria-hidden' => $index === 0 ? 'false' : 'true',
            ]);

            if (count($this->items) > 1) {
                $thumbs .= Html::button(Html::img($thumbnail, ['alt' => '']), [
                    'type' => 'button',
                    'class' => 'sx-shop-gallery__thumb'.($index === 0 ? ' is-active' : ''),
                    'data-sx-gallery-thumb' => (string)$index,
                    'aria-label' => \Yii::t('skeeks/shop/app', 'Показать изображение {number}', [
                        'number' => $index + 1,
                    ]),
                    'aria-current' => $index === 0 ? 'true' : 'false',
                ]);
            }
        }

        $stage = $slides;
        if (count($this->items) > 1) {
            $stage .= Html::button(BackendIcon::render('chevron-left', ['size' => 20]), [
                'type' => 'button',
                'class' => 'sx-shop-gallery__control sx-shop-gallery__control--previous',
                'data-sx-gallery-previous' => '1',
                'aria-label' => \Yii::t('skeeks/shop/app', 'Предыдущее изображение'),
            ]);
            $stage .= Html::button(BackendIcon::render('chevron-right', ['size' => 20]), [
                'type' => 'button',
                'class' => 'sx-shop-gallery__control sx-shop-gallery__control--next',
                'data-sx-gallery-next' => '1',
                'aria-label' => \Yii::t('skeeks/shop/app', 'Следующее изображение'),
            ]);
        }

        $content = Html::tag('div', $stage, ['class' => 'sx-shop-gallery__stage']);
        if ($thumbs !== '') {
            $content .= Html::tag('div', $thumbs, ['class' => 'sx-shop-gallery__thumbs']);
        }

        return Html::tag('div', $content, $this->options);
    }
}
