<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\cashier\assets;

use skeeks\assets\unify\base\UnifyAsset;
use skeeks\assets\unify\base\UnifyHsScrollbarAsset;
use skeeks\assets\unify\base\UnifyPopperAsset;
use skeeks\cms\assets\FancyboxAssets;
use skeeks\cms\themes\unify\assets\FontAwesomeAsset;
use skeeks\sx\assets\Custom;
use yii\bootstrap\BootstrapPluginAsset;
use yii\web\YiiAsset;
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class CashierAsset extends UnifyAsset
{
    public $sourcePath = '@skeeks/cms/shop/cashier/assets/src/';

    public $css = [
        //'https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,600;1,300;1,400;1,600&display=swap',
        //'https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap',
        'https://fonts.googleapis.com/css2?family=Fira+Sans+Extra+Condensed:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap',
        'css/cashier.css'
    ];
    public $js = [
        'js/cashier.js'
    ];
    public $depends = [
        YiiAsset::class,
        Custom::class,
        BootstrapPluginAsset::class,
        UnifyHsScrollbarAsset::class,
        FancyboxAssets::class,
        FontAwesomeAsset::class
    ];
}