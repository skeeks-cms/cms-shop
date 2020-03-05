<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 08.10.2015
 */

namespace skeeks\cms\shop\widgets\admin;

use skeeks\cms\shop\models\ShopProduct;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\helpers\Html;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class SubProductExternalDataWidget extends Widget
{
    /**
     * @var null|ShopProduct
     */
    public $shopProduct = null;
    public $options = [];

    public function init()
    {
        if (!$this->shopProduct) {
            throw new InvalidConfigException("Не передан продукт!");
        }

        parent::init();
    }

    public function run()
    {
        $this->options['id'] = $this->id;

        Html::addCssClass($this->options, 'sx-supproduct-external-widget');

        return $this->render("supproduct-external-data", [
            'widget' => $this
        ]);
    }
}