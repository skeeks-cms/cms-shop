<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

namespace skeeks\cms\shop\controllers;

use chillerlan\QRCode\Data\Number;
use skeeks\cms\backend\actions\BackendModelUpdateAction;
use skeeks\cms\backend\controllers\BackendModelController;
use skeeks\cms\backend\widgets\SelectModelDialogTreeWidget;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\models\CmsContentProperty;
use skeeks\cms\shop\models\CmsSite;
use skeeks\cms\shop\models\ShopSite;
use skeeks\yii2\form\fields\BoolField;
use skeeks\yii2\form\fields\FieldSet;
use skeeks\yii2\form\fields\NumberField;
use skeeks\yii2\form\fields\SelectField;
use skeeks\yii2\form\fields\TextareaField;
use skeeks\yii2\form\fields\WidgetField;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\web\Application;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class AdminShopSiteController extends BackendModelController
{
    public function init()
    {
        $this->name = "Настройки магазина";
        $this->modelShowAttribute = false;
        $this->modelClassName = ShopSite::class;

        $this->defaultAction = "update";
        $this->generateAccessActions = false;

        /*$this->accessCallback = function () {
            if (!\Yii::$app->skeeks->site->is_default) {
                return false;
            }
            return \Yii::$app->user->can($this->uniqueId);
        };*/

        parent::init();
    }

    /**
     * @return Model|ActiveRecord
     */
    public function getModel()
    {
        if ($this->_model === null && \Yii::$app instanceof Application) {
            $shopSite = ShopSite::find()->where(['id' => \Yii::$app->skeeks->site->id])->one();
            if (!$shopSite) {
                $shopSite = new ShopSite();
                if (!$shopSite->save(false)) {
                    throw new Exception("!!!");
                }
            }
            $this->_model = $shopSite;
        }

        return $this->_model;
    }

    public function actions()
    {
        return [
            'update' => [
                'class'  => BackendModelUpdateAction::class,
                'fields' => [$this, 'updateFields'],
            ],
        ];
    }

    /**
     * @return array
     */
    public function updateFields()
    {

        $propertyQuery = CmsContentProperty::find()
                            //->cmsSite()

                            ->orderBy(['priority' => SORT_ASC]);

        $propertyQuery->andWhere([
            'or',
            [CmsContentProperty::tableName().'.cms_site_id' => \Yii::$app->skeeks->site->id],
            [CmsContentProperty::tableName().'.cms_site_id' => null],
        ]);


        return [

            'main' => [
                'class'  => FieldSet::class,
                'name'   => \Yii::t('skeeks/shop/app', 'Основное'),
                'fields' => [
                    'catalog_cms_tree_id' => [
                        'class'       => WidgetField::class,
                        'widgetClass' => SelectModelDialogTreeWidget::class,
                    ],

                    'notify_emails'         => [
                        'class' => TextareaField::class,
                    ],
                    'is_show_cart'      => [
                        'class'       => BoolField::class,
                        'allowNull'   => false,
                        'formElement' => BoolField::ELEMENT_RADIO_LIST,
                    ],
                    'is_show_prices'      => [
                        'class'       => BoolField::class,
                        'allowNull'   => false,
                        'formElement' => BoolField::ELEMENT_RADIO_LIST,
                    ],
                ],
            ],

            'catalog' => [
                'class' => FieldSet::class,
                'name'  => \Yii::t('skeeks/shop/app', 'Каталог'),

                'fields' => [

                    'is_show_product_no_price'      => [
                        'class'       => BoolField::class,
                        'allowNull'   => false,
                        'formElement' => BoolField::ELEMENT_RADIO_LIST,
                    ],
                    'is_show_button_no_price'       => [
                        'class'       => BoolField::class,
                        'allowNull'   => false,
                        'formElement' => BoolField::ELEMENT_RADIO_LIST,
                    ],

                    'is_show_product_only_quantity' => [
                        /*'class'       => BoolField::class,
                        'allowNull'   => false,
                        'formElement' => BoolField::ELEMENT_RADIO_LIST,*/
                        'class'       => SelectField::class,
                        'items'   => [
                            0 => 'Показывать все товары',
                            1 => 'Показывать только в наличии',
                            2 => 'Показывать В наличии и под заказ',
                        ],
                    ],

                    'is_show_quantity_product'      => [
                        'class'       => BoolField::class,
                        'allowNull'   => false,
                        'formElement' => BoolField::ELEMENT_RADIO_LIST,
                    ],
                ],
            ],

            'filters' => [
                'class' => FieldSet::class,
                'name'  => \Yii::t('skeeks/shop/app', 'Фильтры'),

                'fields' => [

                    'show_filter_property_ids' => [
                        'class'    => SelectField::class,
                        'multiple' => true,
                        'items'    => ArrayHelper::map($propertyQuery->all(), 'id', 'asText'),
                    ],

                    'open_filter_property_ids' => [
                        'class'    => SelectField::class,
                        'multiple' => true,
                        'items'    => ArrayHelper::map(CmsContentProperty::find()
                            //->cmsSite()
                            ->andWhere([
                                'or',
                                [CmsContentProperty::tableName().'.cms_site_id' => \Yii::$app->skeeks->site->id],
                                [CmsContentProperty::tableName().'.cms_site_id' => null],
                            ])
                            ->orderBy(['priority' => SORT_ASC])->all(), 'id', 'asText'),
                    ],
                ],

            ],

            'cart' => [
                'class' => FieldSet::class,
                'name'  => \Yii::t('skeeks/shop/app', 'Оформление заказа'),

                'fields' => [

                    'order_required_fields' => [
                        'class' => SelectField::class,
                        'multiple' => true,
                        'items' => [
                            'phone' => 'Телефон',
                            'email' => 'Email',
                            'first_name' => 'Имя',
                            'last_name' => 'Фамилия',
                        ]
                    ]
                    /*'order_free_shipping_from_price' => [
                        'class'    => NumberField::class,
                    ],*/
                ],

            ],

            'rating' => [
                'class' => FieldSet::class,
                'name'  => \Yii::t('skeeks/shop/app', 'Рейтинг товаров'),

                'fields' => [

                    'max_product_rating_value' => [
                        'class' => NumberField::class,
                    ],
                    'is_generate_product_rating' => [
                        'class' => BoolField::class,
                        'allowNull' => false,
                    ],
                    
                    'generate_min_product_rating_value' => [
                        'class' => NumberField::class,
                        'step' => 0.0001,
                    ],
                    'generate_max_product_rating_value' => [
                        'class' => NumberField::class,
                        'step' => 0.0001,
                    ],
                    'generate_min_product_rating_count' => [
                        'class' => NumberField::class,
                    ],
                    'generate_max_product_rating_count' => [
                        'class' => NumberField::class,
                    ],
                    
                    /*'order_free_shipping_from_price' => [
                        'class'    => NumberField::class,
                    ],*/
                ],

            ],

        ];
    }

}
