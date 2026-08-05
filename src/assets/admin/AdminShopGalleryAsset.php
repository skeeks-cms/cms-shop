<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 */

namespace skeeks\cms\shop\assets\admin;

use skeeks\cms\base\AssetBundle;
use skeeks\cms\backend\assets\BackendUiAsset;

/**
 * Lightweight product/collection gallery loaded only by backend model cards.
 */
class AdminShopGalleryAsset extends AssetBundle
{
    public $sourcePath = '@skeeks/cms/shop/assets/admin/src';

    public $css = [
        'shop-gallery.css',
    ];

    public $js = [
        'shop-gallery.js',
    ];

    public $depends = [
        BackendUiAsset::class,
    ];
}
