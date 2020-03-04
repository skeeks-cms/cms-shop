<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\widgets\admin;

use yii\base\InvalidConfigException;
use yii\helpers\Html;
use yii\widgets\InputWidget;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class ProductMeasureMatchesInputWidget extends InputWidget
{
    public static $autoIdPrefix = 'ProductMeasureMatchesInputWidget';

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
        $this->options['class'] = "sx-product-measure-matches-wrapper";
        $this->clientOptions['id'] = $this->id."-widget";
    }


    /**
     * @inheritdoc
     */
    public function run()
    {
        $element = '';

        if ($this->hasModel()) {
            $element = Html::activeTextarea($this->model, $this->attribute);
            $this->clientOptions['inputId'] = Html::getInputId($this->model, $this->attribute);

        } else {
            throw new InvalidConfigException;
        }


        return $this->render('product-measure-matches', [
            'element' => $element,
        ]);
    }

}