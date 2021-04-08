<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 08.10.2015
 */

namespace skeeks\cms\shop\widgets\admin;

use skeeks\cms\shop\models\ShopStoreProduct;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\helpers\Html;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class StoreProductExternalDataWidget extends Widget
{
    /**
     * @var null|ShopStoreProduct
     */
    public $storeProduct = null;
    public $options = [];

    public function init()
    {
        if (!$this->storeProduct) {
            throw new InvalidConfigException("Не передан продукт!");
        }

        parent::init();
    }

    public function run()
    {
        $this->options['id'] = $this->id;

        Html::addCssClass($this->options, 'sx-supproduct-external-widget');

        return $this->render("store-product-external-data", [
            'widget' => $this
        ]);
    }
}