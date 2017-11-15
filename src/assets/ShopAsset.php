<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 21.09.2015
 */

namespace skeeks\cms\shop\assets;

/**
 * Class ShopAsset
 * @package skeeks\cms\shop\assets
 */
class ShopAsset extends Asset
{
    public $css = [];
    public $js = [
        'classes/Shop.js',
        'classes/Cart.js'
    ];
    public $depends = [
        '\skeeks\sx\assets\Core',
    ];
}
