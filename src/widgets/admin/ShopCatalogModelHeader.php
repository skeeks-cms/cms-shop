<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 */

namespace skeeks\cms\shop\widgets\admin;

use skeeks\cms\backend\helpers\BackendIcon;
use skeeks\cms\backend\widgets\BackendModelHeader;
use yii\helpers\Html;

/**
 * Shared model header policy for supplier-connected shop dictionaries.
 */
class ShopCatalogModelHeader extends BackendModelHeader
{
    /** @var string|null External supplier entity URL. */
    public $supplierUrl;

    /** @var string|null Public storefront URL. */
    public $publicUrl;

    public function init()
    {
        parent::init();

        if ($this->imageSrc === null) {
            $image = isset($this->model->logo) && $this->model->logo
                ? $this->model->logo
                : (isset($this->model->image) ? $this->model->image : null);
            $this->imageSrc = $image && $image->src ? $image->src : null;
        }

        $this->titleSuffix .= $this->renderSupplierReference();
        if ($this->publicUrl) {
            $label = \Yii::t('skeeks/cms', 'Watch to site (opens new window)');
            $this->toolbar = Html::a(
                BackendIcon::render('external-link', ['size' => 16]),
                $this->publicUrl,
                [
                    'class'       => 'btn btn-default',
                    'target'      => '_blank',
                    'rel'         => 'noopener noreferrer',
                    'data-pjax'   => '0',
                    'data-toggle' => 'tooltip',
                    'title'       => $label,
                    'aria-label'  => $label,
                ]
            ).$this->toolbar;
        }
    }

    protected function renderSupplierReference()
    {
        if (!isset($this->model->sx_id) || !$this->model->sx_id) {
            return '';
        }

        $isUpdated = isset($this->model->is_sx_info_update) && $this->model->is_sx_info_update;
        $stateClass = $isUpdated ? 'sx-text--success' : 'sx-text--danger';
        $title = $isUpdated
            ? "SkeekS ID: {$this->model->sx_id}. Информация обновляется из сервиса SkeekS Товары"
            : "SkeekS ID: {$this->model->sx_id}. Обновление информации из сервиса SkeekS Товары запрещено";
        $icon = BackendIcon::render('external-link', ['size' => 15]);
        $options = [
            'class'       => $stateClass,
            'data-toggle' => 'tooltip',
            'title'       => $title,
            'aria-label'  => $title,
        ];

        $reference = $this->supplierUrl
            ? Html::a($icon, $this->supplierUrl, array_merge($options, [
                'target'    => '_blank',
                'rel'       => 'noopener noreferrer',
                'data-pjax' => '0',
            ]))
            : Html::tag('span', $icon, $options);

        return Html::tag('span', $reference, ['class' => 'sx-model-header__external-id']);
    }
}
