<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 15.05.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\modules\admin\actions\modelEditor\AdminModelEditorAction;
use skeeks\cms\shop\models\ShopProductPrice;
use yii\base\Event;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class AdminCmsContentElementSubController extends AdminCmsContentElementController
{

    public $editForm = '@skeeks/cms/shop/views/admin-cms-content-element/_form';

    public function initGridData($action, $content)
    {
        parent::initGridData($action, $content);

        $action->filters['visibleFilters'] = ArrayHelper::merge((array)ArrayHelper::getValue($action->filters, ['visibleFilters']), [
            'shop_supplier_id'
        ]);

        //Приджоивание магазинных данных
        $action->grid['on init'] = function (Event $event) {
            /**
             * @var $query ActiveQuery
             */
            $query = $event->sender->dataProvider->query;
            if ($this->content) {
                $query->andWhere(['content_id' => $this->content->id]);
            }

            $query->joinWith('shopProduct as sp');
            $query->joinWith('shopProduct.shopSupplier as shopSupplier');

            $query->andWhere([
                //'or',
                //['is not', 'shopSupplier.is_main', null],
                'shopSupplier.is_main' => 0
            ]);

            if (\Yii::$app->shop->shopTypePrices) {
                foreach (\Yii::$app->shop->shopTypePrices as $shopTypePrice) {
                    $query->leftJoin(["p{$shopTypePrice->id}" => ShopProductPrice::tableName()], [
                        "p{$shopTypePrice->id}.product_id"    => new Expression("sp.id"),
                        "p{$shopTypePrice->id}.type_price_id" => $shopTypePrice->id,
                    ]);
                }
            }
        };

    }

}
