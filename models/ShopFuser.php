<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */
namespace skeeks\cms\shop\models;
use skeeks\cms\components\Cms;
use skeeks\cms\models\CmsSite;
use skeeks\cms\models\Core;
use skeeks\cms\models\User;
use skeeks\modules\cms\money\Money;
use \Yii;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "shop_fuser".
 *
 *
 * @property User           $user
 * @property ShopBasket[]   $shopBaskets
 * @property ShopBuyer      $buyer
 * @property ShopPaySystem $paySystem
 *
 * @property ShopPersonType $personType
 * @property CmsSite $site
 *
 * @property int $countShopBaskets
 * @property ShopBuyer[] $shopBuyers
 * @property ShopPaySystem[] $paySystems
 *
 *
 * @property integer $id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $user_id
 * @property string $additional
 * @property integer $person_type_id
 * @property integer $site_id
 * @property string $delivery_code
 * @property integer $buyer_id
 * @property integer $pay_system_id
 *
 * @property Money $money
 * @property Money $moneyOriginal
 * @property Money $moneyVat
 * @property Money $moneyDiscount
 * @property Money $moneyDelivery
 * @property int $weight
 *
 * @property ShopTypePrice $buyTypePrices
 * @property ShopTypePrice $viewTypePrices
 *
 */
class ShopFuser extends Core
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_fuser}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), []);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'user_id'           => \skeeks\cms\shop\Module::t('app', 'User'),
            'additional'        => \skeeks\cms\shop\Module::t('app', 'Additional'),
            'person_type_id'    => \skeeks\cms\shop\Module::t('app', 'Person Type ID'),
            'site_id'           => \skeeks\cms\shop\Module::t('app', 'Site ID'),
            'delivery_code'     => \skeeks\cms\shop\Module::t('app', 'Delivery Code'),
            'buyer_id'          => \skeeks\cms\shop\Module::t('app', 'Buyer ID'),
            'pay_system_id'     => \skeeks\cms\shop\Module::t('app', 'Payment system'),
        ]);
    }

    const SCENARIO_CREATE_ORDER = 'scentarioCreateOrder';

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_CREATE_ORDER] = $scenarios[self::SCENARIO_DEFAULT];
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'user_id', 'person_type_id', 'site_id'], 'integer'],
            [['additional'], 'string'],
            [['delivery_code'], 'string', 'max' => 50],
            [['user_id'], 'unique'],
            [['buyer_id'], 'integer'],
            [['pay_system_id'], 'integer'],
            [['pay_system_id', 'buyer_id', 'site_id', 'person_type_id', 'user_id'], 'required', 'on' => self::SCENARIO_CREATE_ORDER],

        ]);
    }

    public function extraFields()
    {
        return [
            'countShopBaskets',
            'shopBaskets',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPersonType()
    {
        return $this->hasOne(ShopPersonType::className(), ['id' => 'person_type_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSite()
    {
        return $this->hasOne(CmsSite::className(), ['id' => 'site_id']);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBuyer()
    {
        return $this->hasOne(ShopBuyer::className(), ['id' => 'buyer_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopBaskets()
    {
        return $this->hasMany(ShopBasket::className(), ['fuser_id' => 'id']);
    }


    /**
     *
     * @return ActiveQuery
     */
    public function getShopBuyers()
    {
        return $this->hasMany(ShopBuyer::className(), ['cms_user_id' => 'id'])->via('user');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPaySystem()
    {
        return $this->hasOne(ShopPaySystem::className(), ['id' => 'pay_system_id']);
    }



    /**
     * Добавить корзины этому пользователю
     *
     * @param ShopBasket[] $baskets
     * @return $this
     */
    public function addBaskets($baskets = [])
    {
        /**
         * @var $currentBasket ShopBasket
         */
        foreach ($baskets as $basket)
        {
            //Если в корзине которую необходимо добавить продукт такой же который уже есть у текущего пользователя, то нужно обновить количество.
            if ($currentBasket = $this->getShopBaskets()->andWhere(['product_id' => $basket->product_id])->one())
            {
                $currentBasket->quantity = $currentBasket->quantity + $basket->quantity;
                $currentBasket->save();

                $basket->delete();
            } else
            {
                $basket->fuser_id = $this->id;
                $basket->save();
            }
        }

        return $this;
    }






     /**
     * Количество позиций в корзине
     *
     * @return int
     */
    public function getCountShopBaskets()
    {
        return count($this->shopBaskets);
    }

    /**
     *
     * Итоговая стоимость корзины с учетом скидок, то что будет платить человек
     *
     * @return Money
     */
    public function getMoney()
    {
        $money = \Yii::$app->money->newMoney();

        foreach ($this->shopBaskets as $shopBasket)
        {
            $money = $money->add($shopBasket->money->multiply($shopBasket->quantity));
        }

        return $money;
    }

    /**
     *
     * Итоговая стоимость корзины, без учета скидок
     *
     * @return Money
     */
    public function getMoneyOriginal()
    {
        $money = \Yii::$app->money->newMoney();

        foreach ($this->shopBaskets as $shopBasket)
        {
            $money = $money->add($shopBasket->moneyOriginal->multiply($shopBasket->quantity));
        }

        return $money;
    }


    /**
     * @return int
     */
    public function getWeight()
    {
        $result = 0;

        foreach ($this->shopBaskets as $shopBasket)
        {
            $result = $result + ($shopBasket->weight * $shopBasket->quantity);
        }

        return $result;
    }

    /**
     *
     * Итоговая стоимость налога
     *
     * @return Money
     */
    public function getMoneyVat()
    {
        $money = \Yii::$app->money->newMoney();

        foreach ($this->shopBaskets as $shopBasket)
        {
            $money = $money->add($shopBasket->moneyVat->multiply($shopBasket->quantity));
        }

        return $money;
    }

    /**
     *
     * Итоговая скидка по всей корзине
     *
     * @return Money
     */
    public function getMoneyDiscount()
    {
        $money = \Yii::$app->money->newMoney();
        foreach ($this->shopBaskets as $shopBasket)
        {
            $money = $money->add($shopBasket->moneyDiscount->multiply($shopBasket->quantity));
        }
        return $money;
    }

    /**
     *
     * Итоговая скидка по всей корзине
     *
     * @return Money
     */
    public function getMoneyDelivery()
    {
        $money = \Yii::$app->money->newMoney();
        return $money;
    }





    /**
     * @return bool
     */
    public function isEmpty()
    {
        return (bool) $this->countShopBaskets == 0;
    }



    /**
     * Возможные опции для выбора покупателя
     * @return array
     */
    public function getBuyersList()
    {
        $result = [];

        if (\Yii::$app->shop->shopPersonTypes)
        {
            foreach (\Yii::$app->shop->shopPersonTypes as $shopPersonType)
            {
                $result[$shopPersonType->name] = [
                    'shopPersonType-' . $shopPersonType->id => " + Новый профиль ({$shopPersonType->name})"
                ];

                if ($existsBuyers = $this->getShopBuyers()->andWhere(['shop_person_type_id' => $shopPersonType->id])->all())
                {
                    $result[$shopPersonType->name] = ArrayHelper::merge($result[$shopPersonType->name], ArrayHelper::map($existsBuyers, 'id', 'name'));
                }
            }
        }

        return $result;
    }


    /**
     * Доступные платежные системы
     *
     * @return ShopPaySystem[]
     */
    public function getPaySystems()
    {
        return $this->personType->getPaySystems()->andWhere([ShopPaySystem::tableName() . ".active" => Cms::BOOL_Y]);
    }



    /**
     *
     * Доступные типы цен для просмотра
     *
     * @return ShopTypePrice[]
     */
    public function getViewTypePrices()
    {
        $result = [];

        foreach (\Yii::$app->shop->shopTypePrices as $typePrice)
        {
            if (\Yii::$app->authManager->checkAccess($this->user->id, $typePrice->viewPermissionName))
            {
                $result[$typePrice->id] = $typePrice;
            }
        }

        return $result;
    }

    /**
     *
     * Доступные цены для покупки на сайте
     *
     * @return ShopTypePrice[]
     */
    public function getBuyTypePrices()
    {
        $result = [];

        foreach (\Yii::$app->shop->shopTypePrices as $typePrice)
        {
            if (\Yii::$app->authManager->checkAccess($this->user->id, $typePrice->buyPermissionName))
            {
                $result[$typePrice->id] = $typePrice;
            }
        }

        return $result;
    }

}