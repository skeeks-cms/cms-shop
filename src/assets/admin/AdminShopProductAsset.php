<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\assets\admin;

use skeeks\cms\base\AssetBundle;
use skeeks\cms\backend\assets\BackendLegacyIconAsset;
use skeeks\cms\backend\assets\BackendUiAsset;

class AdminShopProductAsset extends AssetBundle
{
    public $sourcePath = "@skeeks/cms/shop/assets/admin/src";

    public $css = [
        'product-list.css',
    ];

    public $js = [
        //'base.js',
        'product-list.js',
    ];

    public $depends = [
        BackendUiAsset::class,
        BackendLegacyIconAsset::class,
    ];
}
