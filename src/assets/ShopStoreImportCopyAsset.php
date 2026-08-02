<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\assets;

use skeeks\cms\assets\JsTaskManagerAsset;
use skeeks\cms\base\AssetBundle;
use skeeks\cms\backend\assets\BackendUiAsset;

class ShopStoreImportCopyAsset extends AssetBundle
{
    public $sourcePath = "@skeeks/cms/shop/assets/src/import";

    public $css = [
        'import.css',
    ];

    public $js = [
        //'base.js',
        'import.js',
    ];

    public $depends = [
        BackendUiAsset::class,
        JsTaskManagerAsset::class,
    ];
}
