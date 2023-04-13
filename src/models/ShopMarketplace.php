<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\models;

use skeeks\cms\models\CmsSite;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "shop_cashebox".
 *
 * @property int                                     $id
 * @property int                                     $cms_site_id
 * @property string                                  $name
 * @property int                                     $is_active
 * @property string                                  $marketplace Маркетплейс (oz, wb, ym)
 * @property int                                     $priority
 *
 * @property string                                  $wb_key_stat Ключ «Статистика»
 * @property string                                  $wb_key_standart Ключ «Стандартный»
 *
 * @property int                                     $oz_client_id Client ID
 * @property string                                  $oz_api_key API Key
 *
 * @property int                                     $ym_company_id Кампания №
 *
 * @property CmsSite|\skeeks\cms\shop\models\CmsSite $cmsSite
 */
class ShopMarketplace extends \skeeks\cms\base\ActiveRecord
{
    const MARKETPLACE_OZON = "oz";
    const MARKETPLACE_YANDEX_MARKET = "ym";
    const MARKETPLACE_WILDBERRIES = "wb";

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%shop_marketplace}}';
    }


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [

            [
                [
                    'is_active',
                    'oz_client_id',
                    'ym_company_id',
                ],
                'integer',
            ],
            [
                [
                    'wb_key_stat',
                    'wb_key_standart',
                    'oz_api_key',
                    'marketplace',
                ],
                'string',
            ],
            [['priority'], 'integer'],
            [['name'], 'required'],
            [['is_active'], 'default', 'value' => 1],
            [['priority'], 'default', 'value' => 100],
            [['name'], 'string', 'max' => 255],
            [
                'cms_site_id',
                'default',
                'value' => function () {
                    if (\Yii::$app->skeeks->site) {
                        return \Yii::$app->skeeks->site->id;
                    }
                },
            ],
            [
                ['wb_key_stat', 'wb_key_standart'],
                'required',
                'when' => function () {
                    return $this->marketplace == self::MARKETPLACE_WILDBERRIES;
                },
            ],
            [
                ['oz_client_id', 'oz_api_key'],
                'required',
                'when' => function () {
                    return $this->marketplace == self::MARKETPLACE_OZON;
                },
            ],
            [
                ['ym_company_id'],
                'required',
                'when' => function () {
                    return $this->marketplace == self::MARKETPLACE_YANDEX_MARKET;
                },
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'id'              => 'ID',
            'cms_site_id'     => 'Сайт',
            'name'            => 'Название',
            'is_active'       => 'Активность',
            'priority'        => 'Сортировка',
            'marketplace'     => 'Маркетплейс',
            'wb_key_stat'     => 'Ключ «Статистика»',
            'wb_key_standart' => 'Ключ «Стандартный»',
            'oz_client_id'    => 'Client ID',
            'oz_api_key'      => 'API Key',
            'ym_company_id'   => 'Кампания №',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeHints()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
        ]);
    }


    /**
     * Gets query for [[CmsSite]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCmsSite()
    {
        $siteClass = \Yii::$app->skeeks->siteClass;
        return $this->hasOne($siteClass, ['id' => 'cms_site_id']);
    }
}