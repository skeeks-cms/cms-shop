<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 12.10.2015
 */
namespace skeeks\cms\shop\components;
use skeeks\cms\base\Component;
use skeeks\cms\base\ConfigFormInterface;
use skeeks\cms\traits\HasComponentDescriptorTrait;
use yii\base\Model;
use yii\widgets\ActiveForm;

/**
 * Class PaySystemHandlerComponent
 * @package skeeks\cms\shop\components
 */
class PaySystemHandlerComponent extends Model implements ConfigFormInterface
{
    use HasComponentDescriptorTrait;

    public function renderConfigForm(ActiveForm $activeForm)
    {}

    static public function logError($message, $group = "")
    {
        \Yii::error($message, static::className() . "::" . $group);
    }

    static public function logInfo($message, $group = "")
    {
        \Yii::info($message, static::className() . "::" . $group);
    }
}