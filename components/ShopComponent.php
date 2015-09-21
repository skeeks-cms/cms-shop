<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 10.09.2015
 */
namespace skeeks\cms\shop\components;
use skeeks\cms\base\Component;
use skeeks\cms\controllers\AdminCmsContentElementController;
use skeeks\cms\kladr\models\KladrLocation;
use skeeks\cms\models\CmsContentElement;
use skeeks\cms\modules\admin\actions\modelEditor\AdminOneModelEditAction;
use skeeks\cms\modules\admin\controllers\AdminController;
use skeeks\cms\modules\admin\controllers\events\AdminInitEvent;
use skeeks\cms\reviews2\actions\AdminOneModelMessagesAction;
use skeeks\cms\shop\actions\AdminContentElementShopAction;
use skeeks\cms\shop\models\ShopContent;
use skeeks\cms\shop\models\ShopTypePrice;
use yii\helpers\ArrayHelper;

/**
 * @property CartComponent $cart
 * @property ShopTypePrice $baseTypePrice
 *
 * Class ShopComponent
 * @package skeeks\cms\shop\components
 */
class ShopComponent extends Component
{
    /**
     * @var CartComponent
     */
    private $_cart = null;

    /**
     * Можно задать название и описание компонента
     * @return array
     */
    static public function descriptorConfig()
    {
        return array_merge(parent::descriptorConfig(), [
            'name'          => 'Магазин',
        ]);
    }

    public function init()
    {
        parent::init();

        \Yii::$app->on(AdminController::EVENT_INIT, function (AdminInitEvent $e) {

            if ($e->controller instanceof AdminCmsContentElementController || $e->controller instanceof \skeeks\cms\shop\controllers\AdminCmsContentElementController)
            {
                /**
                 * @var $model CmsContentElement
                 */
                $model = $e->controller->model;

                if ( ShopContent::find()->where(['content_id' => $model->content_id])->exists() )
                {
                    $e->controller->eventActions = ArrayHelper::merge($e->controller->eventActions, [
                        'shop' =>
                            [
                                'class'         => AdminContentElementShopAction::className(),
                                'name'          => 'Для магазина',
                                'priority'      => 1000,
                            ],
                    ]);
                }
            }
        });
    }


    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
        ]);
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
        ]);
    }


    /**
     *
     * Тип цены по умолчанию
     *
     * @return ShopTypePrice
     */
    public function getBaseTypePrice()
    {
        return ShopTypePrice::find()->def()->one();
    }

    /**
     * Объект корзины
     *
     * @return CartComponent
     * @throws \yii\base\InvalidConfigException
     */
    public function getCart()
    {
        if ($this->_cart === null)
        {
            $this->_cart = \Yii::createObject(['class' => CartComponent::className()]);
        }

        return $this->_cart;
    }
}