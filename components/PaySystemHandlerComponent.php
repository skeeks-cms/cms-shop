<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 12.10.2015
 */
namespace skeeks\cms\shop\components;
use yii\base\Component;

/**
 * Class PaySystemHandlerComponent
 * @package skeeks\cms\shop\components
 */
class PaySystemHandlerComponent extends Component
{
    /**
     * @var bool Если платежная система онлайн, то пользователь может платить сам
     */
    public $isOnline = false;
}