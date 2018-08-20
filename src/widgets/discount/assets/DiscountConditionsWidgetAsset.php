<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\widgets\discount\assets;

use skeeks\cms\base\AssetBundle;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class DiscountConditionsWidgetAsset extends AssetBundle
{
    public $sourcePath = '@skeeks/cms/shop/widgets/discount/assets/src';

    public $css = [
        'discount-conditions.css',
    ];

    public $js = [
        'discount-conditions.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'skeeks\sx\assets\Custom',
    ];
}