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
class SmartWeightShortInputWidget extends InputWidget
{
    /**
     * @var array
     */
    public $defaultOptions = [
        'type'  => 'text',
        'class' => 'form-control',
    ];

    static public $autoIdPrefix = "SmartWeightShortInputWidget";

    public $viewFile = 'smart-weight-short';
}