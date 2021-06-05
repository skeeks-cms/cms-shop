<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\helpers;

use skeeks\cms\base\DynamicModel;
use skeeks\cms\models\CmsContentProperty;
use skeeks\cms\relatedProperties\models\RelatedPropertyModel;
use skeeks\cms\relatedProperties\PropertyType;
use skeeks\cms\shop\models\ShopCmsContentElement;
use skeeks\cms\shop\models\ShopProduct;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 * @property DynamicModel            $chooseModel
 * @property ShopCmsContentElement   $offerCmsContentElement
 * @property array                   $chooseFields
 * @property ShopCmsContentElement[] $availableOffers
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class ShopOfferChooseHelper extends Component
{
    /**
     * @var null|ShopProduct
     */
    public $shopProduct = null;

    /**
     * @var DynamicModel
     */
    protected $_chooseModel = null;
    /**
     * @var ShopCmsContentElement
     */
    protected $_offerCmsContentElement = null;

    /**
     * @var array
     */
    protected $_chooseFields = [];

    /**
     * @var ShopCmsContentElement[]
     */
    protected $_availableOffers = [];

    /**
     * @return ShopCmsContentElement[]
     */
    public function getAvailableOffers()
    {
        return $this->_availableOffers;
    }

    /**
     * @return DynamicModel
     */
    public function getChooseModel()
    {
        return $this->_chooseModel;
    }

    /**
     * @return array
     */
    public function getChooseFields()
    {
        return $this->_chooseFields;
    }

    /**
     * @return ShopCmsContentElement
     */
    public function getOfferCmsContentElement()
    {
        return $this->_offerCmsContentElement;
    }
    /**
     * @return ShopCmsContentElement
     */
    public function setOfferCmsContentElement(ShopCmsContentElement $shopCmsContentElement)
    {
        $this->_offerCmsContentElement = $shopCmsContentElement;
        return $this;
    }

    public $viewFile = '@skeeks/cms/shop/views/helpers/shop-offer-choose';

    /**
     * @var bool Есть ли выбираемые свойства?
     */
    public $is_offers_properties = false;

    /**
     * @var bool 
     */
    public $is_filter_by_quantity = true;

    public function init()
    {
        parent::init();

        if (!$this->shopProduct) {
            throw new InvalidConfigException("Не указан объект товара!");
        }

        if (!$this->shopProduct->isOffersProduct) {
            throw new InvalidConfigException("Товар должен быть с предложениями!");
        }

        /**
         * @var ShopCmsContentElement[] $offersCsmContentElement
         */
        $offersCsmContentElementQuery = $this->shopProduct
            ->getTradeOffers()
            ->with("shopProduct");

        if ($this->is_filter_by_quantity) {
               \Yii::$app->shop->filterByQuantityQuery($offersCsmContentElementQuery);
        }

        $offersCsmContentElement = $offersCsmContentElementQuery->all();

        if (!$offersCsmContentElement) {
            return false;
        }

        /**
         * Если есть главный товар
         */
        $hasMainProduct = false;
        if ($this->shopProduct->cmsContentElement->main_cce_id) {
            $hasMainProduct = true;
        }

        $this->_availableOffers = $offersCsmContentElement;

        $this->_chooseModel = new \skeeks\cms\base\DynamicModel([
            'offer_id',
        ], ['formName' => 'offers']);

        $this->_chooseModel->addRule('offer_id', 'safe');

        //Если указаны свойства
        if (\Yii::$app->shop->offerCmsContentProperties) {

            $counter = 0;
            foreach (\Yii::$app->shop->offerCmsContentProperties as $cmsContentProperty) {
                $counter++;

                $code = $cmsContentProperty->code;

                foreach ($offersCsmContentElement as $tradeOfferElement) {

                    if ($hasMainProduct) {
                        $mainCCE = $tradeOfferElement->mainCmsContentElement;
                        if ($mainCCE) {
                            $tradeOfferElement = $mainCCE;
                        }
                    }

                    if ($value = $tradeOfferElement->relatedPropertiesModel->getAttribute($code)) {

                        $this->is_offers_properties = true;

                        if (!isset($this->_chooseFields[$code])) {
                            $this->_chooseModel->defineAttribute($code);
                            $this->_chooseModel->addRule($code, 'safe');
                            $this->_chooseModel->addRule($code, 'required');
                            /**
                             * @var $property CmsContentProperty
                             */
                            if ($property = CmsContentProperty::find()->where(['code' => $code])->one()) {
                                $name = $property->name;
                                if ($property->cms_measure_code) {
                                    $name = $name.", ".$property->cmsMeasure->symbol;
                                }
                                $this->_chooseFields[$code]['property'] = $property;
                                $this->_chooseFields[$code]['label'] = $name;
                                $this->_chooseFields[$code]['disabledOptions'] = [];
                            }
                        }

                        if (is_array($value)) {
                            foreach ($value as $v) {
                                //$this->_chooseFields[$code]['options'][$v] = $v;
                            }
                        } else {

                            $this->_chooseFields[$code]['options'][$value] = $tradeOfferElement->relatedPropertiesModel->getAttributeAsText($code);
                        }

                    }

                }

            }
        }

        if (\Yii::$app->request->post()) {
            $this->_chooseModel->load(\Yii::$app->request->post());
            $this->_chooseModel->validate();

            //$this->_chooseModel->offer_id = $this->shopProductOffer->id;

            //Если мы выбрали конкретный оффер, то нужно просто его показать и загрузить его данные в опции
            if ($this->_chooseModel->offer_id) {
                $this->_offerCmsContentElement = ShopCmsContentElement::findOne($this->_chooseModel->offer_id);
            } else {
                //Если конкретный офер не указан, нужно его вычислить загрузив опции
                if ($this->_chooseFields) {
                    $counter = 0;
                    $availableOffers = $this->_availableOffers;

                    foreach ($this->_chooseFields as $code => $dataField) {
                        $tmpAvailableOffers = $availableOffers;
                        $counter++;

                        //Берем все опции
                        if ($counter == 1) {
                            $selectedValue = $this->chooseModel->{$code};
                            foreach ($tmpAvailableOffers as $key => $availableOffer) {
                                if ($hasMainProduct) {
                                    $mainCCE = $availableOffer->mainCmsContentElement;
                                    if ($mainCCE) {
                                        $availableOffer = $mainCCE;
                                    }
                                }
                                if ($availableOffer->relatedPropertiesModel->getAttribute($code) != $selectedValue) {
                                    unset($tmpAvailableOffers[$key]);
                                }
                            }
                            //Если доступных опций нет, то нужно не учитывать этот выбор
                            if (!$tmpAvailableOffers) {
                                //continue;
                                $tmpAvailableOffers = $availableOffers;
                            }
                        } else {
                            //Нужно исключить опции которые недоступны
                            $options = ArrayHelper::getValue($dataField, 'options', []);
                            $disabledOptions = [];
                            foreach ($options as $optionKey => $optionValue) {
                                $availableOptions = [];
                                foreach ($tmpAvailableOffers as $key => $availableOffer) {

                                    if ($hasMainProduct) {
                                        $mainCCE = $availableOffer->mainCmsContentElement;
                                        if ($mainCCE) {
                                            $availableOffer = $mainCCE;
                                        }
                                    }

                                    $availableOptions[$availableOffer->relatedPropertiesModel->getAttribute($code)] = $availableOffer->relatedPropertiesModel->getAttribute($code);

                                    /*if ($availableOffer->relatedPropertiesModel->getAttribute($code) != $optionKey) {
                                        $disabledOptions[$optionKey] = $optionKey;
                                    }*/
                                }

                                if (!in_array($optionKey, $availableOptions)) {
                                    $disabledOptions[$optionKey] = $optionKey;
                                    if ($this->_chooseModel->{$code} == $optionKey) {
                                        $this->_chooseModel->{$code} = '';
                                    }
                                }
                                //print_r($availableOptions);
                            }
                            //print_r($options);
                            //print_r($disabledOptions);die;

                            $this->_chooseFields[$code]['disabledOptions'] = $disabledOptions;

                            $selectedValue = $this->chooseModel->{$code};
                            foreach ($tmpAvailableOffers as $key => $availableOffer) {
                                if ($hasMainProduct) {
                                    $mainCCE = $availableOffer->mainCmsContentElement;
                                    if ($mainCCE) {
                                        $availableOffer = $mainCCE;
                                    }
                                }
                                if ($availableOffer->relatedPropertiesModel->getAttribute($code) != $selectedValue) {
                                    unset($tmpAvailableOffers[$key]);
                                }
                            }
                            //Если доступных опций нет, то нужно не учитывать этот выбор
                            if (!$tmpAvailableOffers) {
                                //continue;
                                $tmpAvailableOffers = $availableOffers;
                            }
                        }

                        $availableOffers = $tmpAvailableOffers;

                    }

                    $this->_availableOffers = $availableOffers;
                }


                $this->_chooseModel->validate();
                //if (!$this->_offerCmsContentElement && !$this->_chooseModel->errors) {
                if (!$this->_chooseModel->errors) {

                    $this->_offerCmsContentElement = array_values($this->_availableOffers)[0];
                }
            }

            if ($this->_offerCmsContentElement) {
                //Нужно для смены url в браузере
                \Yii::$app->response->redirect($this->_offerCmsContentElement->url, 200);
            }

        } elseif ($this->offerCmsContentElement) {
            $this->_chooseModel->offer_id = $this->offerCmsContentElement->id;
        }

        //Сортировка значений
        if ($this->_chooseFields) {
            foreach ($this->_chooseFields as $code => $data) {
                /**
                 * @var $property RelatedPropertyModel
                 */
                $property = ArrayHelper::getValue($data, "property");
                $options = (array) ArrayHelper::getValue($data, "options");

                if ($property && $options && $property->property_type == PropertyType::CODE_LIST) {
                    $options = $property->getEnums()->andWhere(['id' => array_keys($options)])->orderBy(['priority' => SORT_ASC])->all();
                    $this->_chooseFields[$code]['options'] = ArrayHelper::map($options, 'id', 'value');
                } elseif ($property && $property->property_type == PropertyType::CODE_NUMBER) {
                    $options = $this->_chooseFields[$code]['options'];
                    ksort($options);
                    $this->_chooseFields[$code]['options'] = $options;
                }
            }
        }

    }

    public function render()
    {
        return \Yii::$app->view->render($this->viewFile, [
            'helper' => $this,
        ]);
    }
}