<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 08.10.2015
 */

namespace skeeks\cms\shop\widgets\admin;

use yii\base\InvalidConfigException;
use yii\helpers\Html;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class SmartWeightInputWidget extends \yii\widgets\InputWidget
{
    static public $autoIdPrefix = "SmartWeightInputWidget";
    /**
     * @var array опции контейнера
     */
    public $options = [];

    /**
     * @var array
     */
    public $clientOptions = [];

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();

        $this->options['id'] = $this->id."-widget";
        $this->options['class'] = "sx-smart-weight-wrapper";
        $this->clientOptions['id'] = $this->id."-widget";
    }


    /**
     * @inheritdoc
     */
    public function run()
    {
        $element = '';

        if ($this->hasModel()) {
            $element = Html::activeTextInput($this->model, $this->attribute, [
                'type'  => 'number',
                'class' => 'form-control',
                //'value' => $this->model->{$this->attribute},
            ]);
            $this->clientOptions['inputId'] = Html::getInputId($this->model, $this->attribute);

        } else {
            throw new InvalidConfigException;
        }


        return $this->render('smart-weight', [
            'element' => $element,
        ]);
    }
}