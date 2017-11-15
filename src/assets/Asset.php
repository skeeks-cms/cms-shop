<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 01.04.2015
 */

namespace skeeks\cms\shop\assets;

use skeeks\cms\base\AssetBundle;

/**
 * Class Asset
 * @package skeeks\modules\cms\shop\assets
 */
class Asset extends AssetBundle
{
    public $sourcePath = '@skeeks/cms/shop/assets';

    public $css = [];
    public $js = [];
    public $depends = [];
}
