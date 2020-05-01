<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\assets\admin;

use common\themes\lex\assets\LexThemeFontsAsset;
use skeeks\cms\base\AssetBundle;
use skeeks\cms\themes\unify\admin\assets\UnifyAdminAsset;
use skeeks\sx\assets\Custom;

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
        Custom::class,
        UnifyAdminAsset::class,
    ];
}