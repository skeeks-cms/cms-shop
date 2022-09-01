<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 08.10.2015
 */

namespace skeeks\cms\shop\widgets\admin;

use skeeks\cms\base\InputWidget;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class SmartDimensionsShortInputWidget extends InputWidget
{
    static public $autoIdPrefix = "SmartDimensionsShortInputWidget";

    public $viewFile = 'smart-dimensions-short';

    /**
     * @var array
     */
    public $defaultOptions = [
        'type'  => 'text',
        'class' => 'form-control',
    ];
}