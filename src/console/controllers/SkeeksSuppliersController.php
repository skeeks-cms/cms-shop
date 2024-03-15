<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 22.03.2016
 */

namespace skeeks\cms\shop\console\controllers;

use skeeks\cms\measure\models\CmsMeasure;
use skeeks\cms\models\CmsContentProperty;
use skeeks\cms\models\CmsContentPropertyEnum;
use skeeks\cms\models\CmsCountry;
use skeeks\cms\models\CmsStorageFile;
use skeeks\cms\models\CmsTree;
use skeeks\cms\relatedProperties\PropertyType;
use skeeks\cms\relatedProperties\propertyTypes\PropertyTypeList;
use skeeks\cms\relatedProperties\propertyTypes\PropertyTypeNumber;
use skeeks\cms\relatedProperties\propertyTypes\PropertyTypeText;
use skeeks\cms\shop\models\BrandCmsContentElement;
use skeeks\cms\shop\models\ShopBrand;
use skeeks\cms\shop\models\ShopCmsContentElement;
use skeeks\cms\shop\models\ShopCollection;
use skeeks\cms\shop\models\ShopProduct;
use skeeks\cms\shop\models\ShopStore;
use skeeks\cms\shop\models\ShopStoreProduct;
use yii\base\Exception;
use yii\console\Controller;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class SkeeksSuppliersController extends Controller
{
    /**
     * @var bool Сравнивать дату последнего обновления? 1 - обновлять только данные свежее; 0 - обновлять все пришедшие данные.
     */
    public $is_check_updated_at = 1;

    /**
     * @var int
     */
    public $is_stop_on_error = 1;

    /**
     * @var int
     */
    public $is_reload_images = 0;

    /**
     * @var bool
     */
    private $_is_updated_all = false;

    public function options($actionID)
    {
        // $actionId might be used in subclasses to provide options specific to action id
        return ArrayHelper::merge(parent::options($actionID), [
            'is_check_updated_at',
            'is_stop_on_error',
            'is_reload_images',
        ]);
    }

    /**
     * @return false|void
     */
    public function init()
    {
        if (!isset(\Yii::$app->skeeksSuppliersApi)) {
            throw new Exception("Компонент skeeksSuppliersApi не подключен");
        }

        if (!\Yii::$app->skeeksSuppliersApi->api_key) {
            throw new Exception("Skeeks Suppliers API не настроено, не указан api_key");
        }

        if (!\Yii::$app->shop->contentProducts) {
            throw new Exception("Магазин не настроен, не настроен товарный контент");
        }

        if (!\Yii::$app->cms->cmsSite->shopSite->catalogMainCmsTree) {
            throw new Exception("Магазин не настроен, нет корневого раздела для товаров.");
        }

        return parent::init();
    }

    /**
     * Обновление информации по всем справочным данным
     */
    public function actionUpdateAll()
    {
        if ($this->_is_updated_all) {
            return true;
        }

        $this->actionUpdateCountries();
        $this->actionUpdateMeasures();
        $this->actionUpdateCategories();
        $this->actionUpdateProperties();
        $this->actionUpdateBrands();
        $this->actionUpdateCollections();
        $this->actionUpdateStores();

        $this->_is_updated_all = true;
    }

    /**
     * @return void
     * @throws Exception
     */
    public function actionUpdateCountries()
    {
        $response = \Yii::$app->skeeksSuppliersApi->methodCountries();

        $this->stdout("Обновление стран [{$response->time} сек]", Console::BG_BLUE);
        $this->stdout("\n");

        $updated = 0;
        $created = 0;

        if ($response->isOk) {

            $counter = 0;
            $total = count($response->data);
            Console::startProgress($counter, $total);

            foreach ($response->data as $apiData) {

                $alpha2 = trim((string)ArrayHelper::getValue($apiData, "alpha2"));
                /**
                 * @var $cmsCountry CmsCountry
                 */
                if ($cmsCountry = CmsCountry::find()->alpha2($alpha2)->one()) {
                    //TODO:добавить обновление
                    //$updated++;
                } else {
                    $t = \Yii::$app->db->beginTransaction();

                    try {
                        $cmsCountry = new CmsCountry();

                        $cmsCountry->alpha2 = trim((string)ArrayHelper::getValue($apiData, "alpha2"));
                        $cmsCountry->alpha3 = trim((string)ArrayHelper::getValue($apiData, "alpha3"));
                        $cmsCountry->iso = trim((string)ArrayHelper::getValue($apiData, "iso"));
                        $cmsCountry->phone_code = trim((string)ArrayHelper::getValue($apiData, "phone_code"));
                        $cmsCountry->domain = trim((string)ArrayHelper::getValue($apiData, "domain"));
                        $cmsCountry->name = trim((string)ArrayHelper::getValue($apiData, "name"));

                        $image = $this->_addImage(ArrayHelper::getValue($apiData, "image"));
                        if ($image) {
                            $cmsCountry->flag_image_id = $image->id;
                        }

                        if ($cmsCountry->save()) {
                            $created++;
                        } else {
                            throw new Exception("Страна не создана".print_r($cmsCountry->errors, true));
                        }

                        $t->commit();

                    } catch (\Exception $exception) {
                        $t->rollBack();
                        throw $exception;
                    }
                }

                $counter++;
                Console::updateProgress($counter, $total);
            }

            Console::endProgress();

            $this->stdout("Обновлено: {$updated}\n");
            $this->stdout("Добавлено: {$created}\n");

        } else {
            throw new Exception("Ошибка ответа API {$response->request_url}; code: {$response->code}; code: {$response->content}");
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    public function actionUpdateMeasures()
    {
        $response = \Yii::$app->skeeksSuppliersApi->methodMeasures();

        $this->stdout("Обновление едениц измерения [{$response->time} сек]", Console::BG_BLUE);
        $this->stdout("\n");

        $updated = 0;
        $created = 0;

        if ($response->isOk) {

            $counter = 0;
            $total = count($response->data);
            Console::startProgress($counter, $total);

            foreach ($response->data as $apiData) {

                $code = trim((string)ArrayHelper::getValue($apiData, "code"));

                if ($cmsMeasure = CmsMeasure::find()->andWhere(['code' => $code])->one()) {
                    //TODO:добавить обновление
                    //$updated++;
                } else {

                    $measure = new CmsMeasure();
                    $measure->code = $code;
                    $measure->name = trim((string)ArrayHelper::getValue($apiData, "name"));
                    $measure->symbol = trim((string)ArrayHelper::getValue($apiData, "symbol"));

                    if ($measure->save()) {
                        $created++;
                    } else {
                        throw new Exception("Единица измерений не создана".print_r($measure->errors, true));
                    }
                }

                $counter++;
                Console::updateProgress($counter, $total);
            }

            Console::endProgress();

            $this->stdout("Обновлено: {$updated}\n");
            $this->stdout("Добавлено: {$created}\n");

        } else {
            throw new Exception("Ошибка ответа API {$response->request_url}; code: {$response->code}; code: {$response->content}");
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    public function actionUpdateCategories($page = 1)
    {
        $response = \Yii::$app->skeeksSuppliersApi->methodCategories([
            'page' => $page
        ]);

        $this->stdout("Обновление категорий, страница {$page} [{$response->time} сек]", Console::BG_BLUE);
        $this->stdout("\n");

        $total = $response->headers->get("x-pagination-total-count");
        $pageCount = $response->headers->get("x-pagination-page-count");

        if ($page == 1) {
            $this->stdout("Всего категорий к обновлению: {$total}\n");
            $this->stdout("Страниц: {$pageCount}\n");
        }

        $this->stdout("Страница: {$page}\n");


        $updated = 0;
        $created = 0;
        if ($response->isOk) {

            $counter = 0;
            $total = count($response->data);
            Console::startProgress($counter, $total);

            foreach ($response->data as $apiData) {
                $id = (int)ArrayHelper::getValue($apiData, "id");
                /**
                 * @var $cmsTree CmsTree
                 */
                if ($cmsTree = CmsTree::find()->sxId($id)->one()) {
                    if ($this->_updateTree($apiData, $cmsTree)) {
                        $updated++;
                    }
                } else {
                    if ($this->_updateTree($apiData)) {
                        $created++;
                    }
                }

                $counter++;
                Console::updateProgress($counter, $total);
            }

            Console::endProgress();

            $this->stdout("Обновлено: {$updated}\n");
            $this->stdout("Добавлено: {$created}\n");

        } else {
            throw new Exception("Ошибка ответа API {$response->request_url}; code: {$response->code}; code: {$response->content}");
        }

        if ($page < $pageCount) {
            $this->actionUpdateCategories($page + 1);
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    public function actionUpdateProperties($page = 1)
    {
        $response = \Yii::$app->skeeksSuppliersApi->methodProperties([
            'page' => $page
        ]);

        $this->stdout("Обновление характеристик, страница {$page} [{$response->time} сек]", Console::BG_BLUE);
        $this->stdout("\n");

        $total = $response->headers->get("x-pagination-total-count");
        $pageCount = $response->headers->get("x-pagination-page-count");

        if ($page == 1) {
            $this->stdout("Всего характеристик к обновлению: {$total}\n");
            $this->stdout("Страниц: {$pageCount}\n");
        }

        $this->stdout("Страница: {$page}\n");
        
        $updated = 0;
        $created = 0;

        if ($response->isOk) {

            $counter = 0;
            $total = count($response->data);
            Console::startProgress($counter, $total);

            foreach ($response->data as $apiData) {

                $id = (int)ArrayHelper::getValue($apiData, "id");
                if ($model = CmsContentProperty::find()->sxId($id)->one()) {
                    if ($this->_updateProperty($apiData, $model)) {
                        $updated++;
                    }
                } else {
                    if ($this->_updateProperty($apiData)) {
                        $created++;
                    }
                }

                $counter++;
                Console::updateProgress($counter, $total);
            }

            Console::endProgress();

            $this->stdout("Обновлено: {$updated}\n");
            $this->stdout("Добавлено: {$created}\n");

        } else {
            throw new Exception("Ошибка ответа API {$response->request_url}; code: {$response->code}; code: {$response->content}");
        }
        
        if ($page < $pageCount) {
            $this->actionUpdateProperties($page + 1);
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    public function actionUpdateBrands($page = 1)
    {
        $response = \Yii::$app->skeeksSuppliersApi->methodBrands([
            'page' => $page
        ]);

        $this->stdout("Обновление брендов, страница {$page} [{$response->time} сек]", Console::BG_BLUE);
        $this->stdout("\n");

        $total = $response->headers->get("x-pagination-total-count");
        $pageCount = $response->headers->get("x-pagination-page-count");

        if ($page == 1) {
            $this->stdout("Всего брендов к обновлению: {$total}\n");
            $this->stdout("Страниц: {$pageCount}\n");
        }

        $this->stdout("Страница: {$page}\n");

        $updated = 0;
        $created = 0;

        if ($response->isOk) {

            $counter = 0;
            $total = count($response->data);
            Console::startProgress($counter, $total);

            foreach ($response->data as $apiData) {

                $id = (int)ArrayHelper::getValue($apiData, "id");
                if ($model = ShopBrand::find()->sxId($id)->one()) {
                    if ($this->_updateBrand($apiData, $model)) {
                        $updated++;
                    }
                } else {
                    if ($this->_updateBrand($apiData)) {
                        $created++;
                    }
                }

                $counter++;
                Console::updateProgress($counter, $total);
            }

            Console::endProgress();

            $this->stdout("Обновлено: {$updated}\n");
            $this->stdout("Добавлено: {$created}\n");

        } else {
            throw new Exception("Ошибка ответа API {$response->request_url}; code: {$response->code}; code: {$response->content}");
        }
        
        if ($page < $pageCount) {
            $this->actionUpdateBrands($page + 1);
        }
    }


    /**
     * @return void
     * @throws Exception
     */
    public function actionUpdateCollections($page = 1)
    {
        $response = \Yii::$app->skeeksSuppliersApi->methodCollections([
            'page' => $page
        ]);

        $this->stdout("Обновление коллекций, страница {$page} [{$response->time} сек]", Console::BG_BLUE);
        $this->stdout("\n");

        $total = $response->headers->get("x-pagination-total-count");
        $pageCount = $response->headers->get("x-pagination-page-count");

        if ($page == 1) {
            $this->stdout("Всего коллекций к обновлению: {$total}\n");
            $this->stdout("Страниц: {$pageCount}\n");
        }

        $this->stdout("Страница: {$page}\n");
        
        $updated = 0;
        $created = 0;

        if ($response->isOk) {

            $counter = 0;
            $total = count($response->data);
            Console::startProgress($counter, $total);

            foreach ($response->data as $apiData) {

                $id = (int)ArrayHelper::getValue($apiData, "id");
                if ($model = ShopCollection::find()->sxId($id)->one()) {
                    if ($this->_updateCollection($apiData, $model)) {
                        $updated++;
                    }
                } else {
                    if ($this->_updateCollection($apiData)) {
                        $created++;
                    }
                }

                $counter++;
                Console::updateProgress($counter, $total);
            }

            Console::endProgress();

            $this->stdout("Обновлено: {$updated}\n");
            $this->stdout("Добавлено: {$created}\n");

        } else {
            throw new Exception("Ошибка ответа API {$response->request_url}; code: {$response->code}; code: {$response->content}");
        }
        
        if ($page < $pageCount) {
            $this->actionUpdateCollections($page + 1);
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    public function actionUpdateStores($page = 1)
    {
        $response = \Yii::$app->skeeksSuppliersApi->methodStores([
            'page' => $page
        ]);

        $this->stdout("Обновление складов, страница {$page} [{$response->time} сек]", Console::BG_BLUE);
        $this->stdout("\n");

        $total = $response->headers->get("x-pagination-total-count");
        $pageCount = $response->headers->get("x-pagination-page-count");

        if ($page == 1) {
            $this->stdout("Всего складов к обновлению: {$total}\n");
            $this->stdout("Страниц: {$pageCount}\n");
        }

        $this->stdout("Страница: {$page}\n");
        
        
        $updated = 0;
        $created = 0;

        if ($response->isOk) {

            $counter = 0;
            $total = count($response->data);
            Console::startProgress($counter, $total);

            foreach ($response->data as $apiData) {

                $id = (int)ArrayHelper::getValue($apiData, "id");
                if ($model = ShopStore::find()->sxId($id)->one()) {
                    if ($this->_updateStore($apiData, $model)) {
                        $updated++;
                    }
                } else {
                    if ($this->_updateStore($apiData)) {
                        $created++;
                    }
                }

                $counter++;
                Console::updateProgress($counter, $total);
            }

            Console::endProgress();

            $this->stdout("Обновлено: {$updated}\n");
            $this->stdout("Добавлено: {$created}\n");

        } else {
            throw new Exception("Ошибка ответа API {$response->request_url}; code: {$response->code}; code: {$response->content}");
        }
        
        if ($page < $pageCount) {
            $this->actionUpdateStores($page + 1);
        }
    }


    /**
     * @param $page
     * @return void
     * @throws Exception
     * @throws \Throwable
     */
    public function actionUpdateProducts($is_new = 1, $page = 1)
    {
        $apiQuery = [
            'page' => $page,
        ];
        
        if ($is_new) {
            /**
             * @var ShopCmsContentElement $lastProduct
             */
            $lastProduct = ShopCmsContentElement::find()
                ->innerJoinWith("shopProduct as shopProduct")
                ->andWhere(['is not', 'sx_id', null])
                ->orderBy(['updated_at' => SORT_DESC])
                ->one()
            ;
            if ($lastProduct) {
                $apiQuery['updated_at'] = $lastProduct->updated_at;
            }
        }
        
        $response = \Yii::$app->skeeksSuppliersApi->methodProducts($apiQuery);

        $this->stdout("Обновление товаров, страница {$page} [{$response->time} сек]", Console::BG_BLUE);
        $this->stdout("\n");

        $total = $response->headers->get("x-pagination-total-count");
        $pageCount = $response->headers->get("x-pagination-page-count");

        if ($page == 1) {
            $this->stdout("Всего товаров к обновлению: {$total}\n");
            $this->stdout("Страниц: {$pageCount}\n");
        }

        if (!$total) {
            return false;
        }

        $this->stdout("Страница: {$page}\n");

        $updated = 0;
        $created = 0;

        if ($response->isOk) {

            $counter = 0;
            $total = count($response->data);
            Console::startProgress($counter, $total);

            foreach ($response->data as $apiData) {

                $id = (int)ArrayHelper::getValue($apiData, "id");
                if ($model = ShopCmsContentElement::find()->sxId($id)->one()) {
                    if ($this->_updateProduct($apiData, $model)) {
                        $updated++;
                    }
                } else {
                    if ($this->_updateProduct($apiData)) {
                        $created++;
                    }
                }

                $counter++;
                Console::updateProgress($counter, $total);
            }

            Console::endProgress();

            $this->stdout("Обновлено: {$updated}\n");
            $this->stdout("Добавлено: {$created}\n");

        } else {
            throw new Exception("Ошибка ответа API {$response->request_url}; code: {$response->code}; code: {$response->content}");
        }

        if ($page < $pageCount) {
            $this->actionUpdateProducts($is_new, $page + 1);
        }
    }

    /**
     * @param              $apiData
     * @param CmsTree|null $cmsTree
     * @return bool
     * @throws \Throwable
     */
    private function _updateTree($apiData = [], CmsTree $cmsTree = null)
    {
        $id = (int)ArrayHelper::getValue($apiData, "id");
        $updated_at = (int)ArrayHelper::getValue($apiData, "updated_at.timestamp");
        $result = false;

        $t = \Yii::$app->db->beginTransaction();
        try {
            if ($cmsTree) {
                //Обновить
                $isNeedUpdate = false;
                if ($this->is_check_updated_at) {
                    if ($cmsTree->updated_at < $updated_at) {
                        $isNeedUpdate = true;
                    }
                } else {
                    $isNeedUpdate = true;
                }

                if ($isNeedUpdate) {
                    //TODO:добавить обновление
                    $cmsTree->name = trim((string)ArrayHelper::getValue($apiData, "name"));
                    $cmsTree->description_short = trim((string)ArrayHelper::getValue($apiData, "description_short"));
                    $cmsTree->description_full = trim((string)ArrayHelper::getValue($apiData, "description_full"));
                    $cmsTree->is_adult = (int)ArrayHelper::getValue($apiData, "is_adult");
                    $cmsTree->shop_has_collections = (int)ArrayHelper::getValue($apiData, "has_collections");

                    if ($image = $this->_addImage(ArrayHelper::getValue($apiData, "image"))) {
                        $cmsTree->image_id = $image->id;
                    }

                    if ($cmsTree->save()) {
                        $result = true;
                    } else {
                        throw new Exception("Ошибка обновления раздела {$cmsTree->id}: ".print_r($cmsTree->errors, true));
                    }

                }
            } else {
                //Создать

                $parentTree = \Yii::$app->cms->cmsSite->shopSite->catalogMainCmsTree;

                $cmsTree = new CmsTree();

                $cmsTree->sx_id = (int)ArrayHelper::getValue($apiData, "id");
                
                $cmsTree->name = trim((string)ArrayHelper::getValue($apiData, "name"));
                $cmsTree->description_short = trim((string)ArrayHelper::getValue($apiData, "description_short"));
                $cmsTree->description_full = trim((string)ArrayHelper::getValue($apiData, "description_full"));
                $cmsTree->is_adult = (int)ArrayHelper::getValue($apiData, "is_adult");
                $cmsTree->shop_has_collections = (int)ArrayHelper::getValue($apiData, "has_collections");

                if ($image = $this->_addImage(ArrayHelper::getValue($apiData, "image"))) {
                    $cmsTree->image_id = $image->id;
                }

                $cmsTree->appendTo($parentTree);

                if ($cmsTree->save()) {
                    $result = true;
                } else {
                    throw new Exception("Ошибка создания раздела: ".print_r($cmsTree->errors, true));
                }
            }

            $t->commit();
        } catch (\Exception $exception) {

            $t->rollBack();

            if ($this->is_stop_on_error) {
                throw $exception;
            }

            $this->stdout($exception->getMessage(), Console::FG_RED);
        }

        return $result;
    }

    /**
     * @param                         $apiData
     * @param CmsContentProperty|null $model
     * @return bool
     * @throws \Throwable
     */
    private function _updateProperty($apiData = [], CmsContentProperty $model = null)
    {
        $id = (int)ArrayHelper::getValue($apiData, "id");
        $updated_at = (int)ArrayHelper::getValue($apiData, "updated_at.timestamp");
        $result = false;

        $content_id = \Yii::$app->shop->contentProducts->id;

        $t = \Yii::$app->db->beginTransaction();
        try {
            if ($model) {
                //Обновить
                $isNeedUpdate = false;
                if ($this->is_check_updated_at) {
                    if ($model->updated_at < $updated_at) {
                        $isNeedUpdate = true;
                    }
                } else {
                    $isNeedUpdate = true;
                }

                if ($isNeedUpdate) {
                    //TODO:добавить обновление
                    $model->cmsContentProperty2contents = [$content_id];
                    $model->name = trim((string)ArrayHelper::getValue($apiData, "name"));
                    $model->cms_measure_code = trim((string)ArrayHelper::getValue($apiData, "measure_code"));
                    $model->is_multiple = (int)ArrayHelper::getValue($apiData, "is_multiple");

                    $category_ids = (array)ArrayHelper::getValue($apiData, "category_ids");
                    if ($category_ids) {
                        $cmsTrees = CmsTree::find()->sxId($category_ids)->all();
                        if ($cmsTrees) {
                            $model->cmsTrees = array_keys(ArrayHelper::map($cmsTrees, "id", "id"));
                        }
                    }

                    $type = (string)ArrayHelper::getValue($apiData, "type");
                    if ($type == 'list') {
                        $model->component = PropertyTypeList::class;

                        if ($model->is_multiple) {
                            $model->component_settings = [
                                'fieldElement' => PropertyTypeList::FIELD_ELEMENT_SELECT_MULTI,
                            ];
                        } else {
                            $model->component_settings = [
                                'fieldElement' => PropertyTypeList::FIELD_ELEMENT_SELECT,
                            ];
                        }


                    } elseif ($type == 'number') {
                        $model->component = PropertyTypeNumber::class;
                    } else {
                        $model->component = PropertyTypeText::class;
                    }
                    
                    $model->is_multiple = (int) $model->handler->isMultiple;
                    
                    
                    if ($model->save()) {
                        $result = true;
                    } else {
                        throw new Exception("Ошибка обновления характеристики {$model->id}: ".print_r($model->errors, true));
                    }

                }
            } else {
                //Создать
                $model = new CmsContentProperty();
                $model->cmsContents = [$content_id];
                $model->name = trim((string)ArrayHelper::getValue($apiData, "name"));
                $model->cms_measure_code = trim((string)ArrayHelper::getValue($apiData, "measure_code"));
                $model->is_multiple = (int)ArrayHelper::getValue($apiData, "is_multiple");
                $model->sx_id = (int)ArrayHelper::getValue($apiData, "id");

                $category_ids = (array)ArrayHelper::getValue($apiData, "category_ids");
                if ($category_ids) {
                    $cmsTrees = CmsTree::find()->sxId($category_ids)->all();
                    if ($cmsTrees) {
                        $model->cmsTrees = array_keys(ArrayHelper::map($cmsTrees, "id", "id"));
                    }
                }

                $type = (string)ArrayHelper::getValue($apiData, "type");
                if ($type == 'list') {
                    $model->component = PropertyTypeList::class;

                    if ($model->is_multiple) {
                        $model->component_settings = [
                            'fieldElement' => PropertyTypeList::FIELD_ELEMENT_SELECT_MULTI,
                        ];
                    } else {
                        $model->component_settings = [
                            'fieldElement' => PropertyTypeList::FIELD_ELEMENT_SELECT,
                        ];
                    }


                } elseif ($type == 'number') {
                    $model->component = PropertyTypeNumber::class;
                } else {
                    $model->component = PropertyTypeText::class;
                }

                if ($model->save()) {
                    $result = true;
                } else {
                    throw new Exception("Ошибка создания характеристики: ".print_r($model->errors, true));
                }

            }

            $t->commit();
        } catch (\Exception $exception) {
            $t->rollBack();

            if ($this->is_stop_on_error) {
                throw $exception;
            }

            $this->stdout($exception->getMessage(), Console::FG_RED);
        }

        return $result;
    }

    /**
     * @param                $apiData
     * @param ShopBrand|null $model
     * @return bool
     * @throws \Throwable
     */
    private function _updateBrand($apiData = [], ShopBrand $model = null)
    {
        $id = (int)ArrayHelper::getValue($apiData, "id");
        $updated_at = (int)ArrayHelper::getValue($apiData, "updated_at.timestamp");
        $result = false;

        $t = \Yii::$app->db->beginTransaction();
        try {
            if ($model) {
                //Обновить
                $isNeedUpdate = false;
                if ($this->is_check_updated_at) {
                    if ($model->updated_at < $updated_at) {
                        $isNeedUpdate = true;
                    }
                } else {
                    $isNeedUpdate = true;
                }

                if ($isNeedUpdate) {
                    //TODO:добавить обновление
                    $model->name = trim((string)ArrayHelper::getValue($apiData, "name"));
                    $model->website_url = trim((string)ArrayHelper::getValue($apiData, "website_url"));
                    $model->country_alpha2 = trim((string)ArrayHelper::getValue($apiData, "country_alpha2"));
                    $model->description_short = trim((string)ArrayHelper::getValue($apiData, "description_short"));
                    $model->description_full = trim((string)ArrayHelper::getValue($apiData, "description_full"));

                    if ($image = $this->_addImage(ArrayHelper::getValue($apiData, "image"))) {
                        $model->logo_image_id = $image->id;
                    }

                    if ($model->save()) {
                        $result = true;
                    } else {
                        throw new Exception("Ошибка обновления бренда {$model->id}: ".print_r($model->errors, true));
                    }

                }
            } else {
                //Создать
                $model = new ShopBrand();

                $model->sx_id = (int)ArrayHelper::getValue($apiData, "id");
                $model->name = trim((string)ArrayHelper::getValue($apiData, "name"));
                $model->website_url = trim((string)ArrayHelper::getValue($apiData, "website_url"));
                $model->country_alpha2 = trim((string)ArrayHelper::getValue($apiData, "country_alpha2"));
                $model->description_short = trim((string)ArrayHelper::getValue($apiData, "description_short"));
                $model->description_full = trim((string)ArrayHelper::getValue($apiData, "description_full"));

                if ($image = $this->_addImage(ArrayHelper::getValue($apiData, "image"))) {
                    $model->logo_image_id = $image->id;
                }

                if ($model->save()) {
                    $result = true;
                } else {
                    throw new Exception("Ошибка создания бренда: ".print_r($model->errors, true));
                }

            }

            $t->commit();
        } catch (\Exception $exception) {
            $t->rollBack();

            if ($this->is_stop_on_error) {
                throw $exception;
            }

            $this->stdout($exception->getMessage(), Console::FG_RED);
        }

        return $result;
    }

    /**
     * @param                     $apiData
     * @param ShopCollection|null $model
     * @return bool
     * @throws \Throwable
     */
    private function _updateCollection($apiData = [], ShopCollection $model = null)
    {
        $id = (int)ArrayHelper::getValue($apiData, "id");
        $updated_at = (int)ArrayHelper::getValue($apiData, "updated_at.timestamp");
        $result = false;

        $t = \Yii::$app->db->beginTransaction();
        try {
            if ($model) {
                //Обновить
                $isNeedUpdate = false;
                if ($this->is_check_updated_at) {
                    if ($model->updated_at < $updated_at) {
                        $isNeedUpdate = true;
                    }
                } else {
                    $isNeedUpdate = true;
                }

                if ($isNeedUpdate) {
                    //TODO:добавить обновление
                    $model->name = trim((string)ArrayHelper::getValue($apiData, "name"));
                    $model->description_short = trim((string)ArrayHelper::getValue($apiData, "description_short"));
                    $model->description_full = trim((string)ArrayHelper::getValue($apiData, "description_full"));

                    $brand_id = (int)ArrayHelper::getValue($apiData, "brand_id");
                    if ($brand_id) {
                        if ($shopBrand = ShopBrand::find()->sxId($brand_id)->one()) {
                            $model->shop_brand_id = $shopBrand->id;
                        }
                    }

                    if ($image = $this->_addImage(ArrayHelper::getValue($apiData, "image"))) {
                        $model->cms_image_id = $image->id;
                    }

                    if ($images = ArrayHelper::getValue($apiData, "images")) {

                        /*foreach ($images as $imgApiData) {
                            $img = $this->_addImage($imgApiData);
                            $model->link("images", $img);
                        }*/

                        $imgIds = [];

                        foreach ($images as $imgApiData) {
                            $img = $this->_addImage($imgApiData);
                            $imgIds[] = $img->id;
                        }

                        $model->setImageIds($imgIds);
                    }

                    if ($model->save()) {
                        $result = true;
                    } else {
                        throw new Exception("Ошибка обновления бренда {$model->id}: ".print_r($model->errors, true));
                    }

                }
            } else {
                //Создать
                $model = new ShopCollection();

                $model->sx_id = (int)ArrayHelper::getValue($apiData, "id");
                $model->name = trim((string)ArrayHelper::getValue($apiData, "name"));
                $model->description_short = trim((string)ArrayHelper::getValue($apiData, "description_short"));
                $model->description_full = trim((string)ArrayHelper::getValue($apiData, "description_full"));

                $brand_id = (int)ArrayHelper::getValue($apiData, "brand_id");
                if ($brand_id) {
                    if ($shopBrand = ShopBrand::find()->sxId($brand_id)->one()) {
                        $model->shop_brand_id = $shopBrand->id;
                    }
                }

                if ($image = $this->_addImage(ArrayHelper::getValue($apiData, "image"))) {
                    $model->cms_image_id = $image->id;
                }

                if ($images = ArrayHelper::getValue($apiData, "images")) {

                    /*foreach ($images as $imgApiData) {
                        $img = $this->_addImage($imgApiData);
                        $model->link("images", $img);
                    }*/

                    $imgIds = [];

                    foreach ($images as $imgApiData) {
                        $img = $this->_addImage($imgApiData);
                        $imgIds[] = $img->id;
                    }

                    $model->setImageIds($imgIds);
                }

                if ($model->save()) {

                } else {
                    throw new Exception("Ошибка создания коллекции: ".print_r($model->errors, true));
                }

                $result = true;

            }

            $t->commit();
        } catch (\Exception $exception) {
            $t->rollBack();

            if ($this->is_stop_on_error) {
                throw $exception;
            }

            $this->stdout($exception->getMessage(), Console::FG_RED);
        }

        return $result;
    }


    /**
     * @param                            $apiData
     * @param ShopCmsContentElement|null $model
     * @return bool
     * @throws \Throwable
     */
    private function _updateProduct($apiData = [], ShopCmsContentElement $model = null)
    {
        $id = (int)ArrayHelper::getValue($apiData, "id");
        $updated_at = (int)ArrayHelper::getValue($apiData, "updated_at.timestamp");
        $result = false;

        $content_id = \Yii::$app->shop->contentProducts->id;

        $t = \Yii::$app->db->beginTransaction();
        
        try {
            if ($model) {
                //Обновить
                $isNeedUpdate = false;
                if ($this->is_check_updated_at) {
                    /*var_dump($model->updated_at);
                    var_dump($updated_at);*/
                    if ($model->updated_at < $updated_at) {
                        $isNeedUpdate = true;
                    }
                } else {
                    $isNeedUpdate = true;
                }

                if ($isNeedUpdate) {
                    //TODO:добавить обновление
                    $model->name = trim((string)ArrayHelper::getValue($apiData, "name"));
                    $model->description_short = trim((string)ArrayHelper::getValue($apiData, "description_short"));
                    $model->description_full = trim((string)ArrayHelper::getValue($apiData, "description_full"));
                    $model->is_adult = (int)ArrayHelper::getValue($apiData, "is_adult");

                    $shopProduct = $model->shopProduct;

                    $category_id = (int)ArrayHelper::getValue($apiData, "category_id");
                    if ($category_id) {
                        $cmsTree = CmsTree::find()->sxId($category_id)->one();
                        if (!$cmsTree) {
                            $this->actionUpdateAll();
                            $cmsTree = CmsTree::find()->sxId($category_id)->one();
                        }
                        if ($cmsTree) {
                            $model->tree_id = $cmsTree->id;
                        }
                    }

                    $brand_id = (int)ArrayHelper::getValue($apiData, "brand_id");
                    if ($brand_id) {
                        $shopBrand = ShopBrand::find()->sxId($brand_id)->one();
                        if (!$shopBrand) {
                            $this->actionUpdateAll();
                            $shopBrand = ShopBrand::find()->sxId($brand_id)->one();
                        }

                        if ($shopBrand) {
                            $shopProduct->brand_id = $shopBrand->id;
                        }
                    }

                    $collection_ids = (array)ArrayHelper::getValue($apiData, "collection_ids");
                    if ($collection_ids) {
                        $tmpCollectionIds = [];
                        foreach ($collection_ids as $sx_collection_id) {
                            $shopCollection = ShopCollection::find()->sxId($sx_collection_id)->one();
                            if (!$shopCollection) {
                                $this->actionUpdateAll();
                                $shopCollection = ShopCollection::find()->sxId($sx_collection_id)->one();
                            }

                            if ($shopCollection) {
                                $tmpCollectionIds[] = $shopCollection->id;
                                //$shopProduct->link("collections", $shopCollection);
                            }
                        }

                        $shopProduct->collections = $tmpCollectionIds;
                    }


                    $shopProduct->brand_sku = trim((string)ArrayHelper::getValue($apiData, "brand_sku"));
                    $shopProduct->country_alpha2 = trim((string)ArrayHelper::getValue($apiData, "country_alpha2"));
                    $shopProduct->measure_code = trim((string)ArrayHelper::getValue($apiData, "measure_code"));
                    $shopProduct->weight = (float)ArrayHelper::getValue($apiData, "weight");
                    $shopProduct->width = (float)ArrayHelper::getValue($apiData, "width");
                    $shopProduct->length = (float)ArrayHelper::getValue($apiData, "length");
                    $shopProduct->height = (float)ArrayHelper::getValue($apiData, "height");
                    $shopProduct->measure_ratio = (float)ArrayHelper::getValue($apiData, "measure_ratio");
                    $shopProduct->measure_ratio_min = (float)ArrayHelper::getValue($apiData, "measure_ratio_min");
                    $shopProduct->expiration_time = (int)ArrayHelper::getValue($apiData, "expiration_time");
                    $shopProduct->service_life_time = (int)ArrayHelper::getValue($apiData, "service_life_time");
                    $shopProduct->warranty_time = (int)ArrayHelper::getValue($apiData, "warranty_time");
                    $shopProduct->expiration_time_comment = trim((string)ArrayHelper::getValue($apiData, "expiration_time_comment"));
                    $shopProduct->service_life_time_comment = trim((string)ArrayHelper::getValue($apiData, "service_life_time_comment"));
                    $shopProduct->warranty_time_comment = trim((string)ArrayHelper::getValue($apiData, "warranty_time_comment"));


                    if ($image = $this->_addImage(ArrayHelper::getValue($apiData, "image"))) {
                        $model->image_id = $image->id;
                    }

                    if ($images = ArrayHelper::getValue($apiData, "images")) {

                        $imgIds = [];

                        foreach ($images as $imgApiData) {
                            $img = $this->_addImage($imgApiData);
                            $imgIds[] = $img->id;
                        }

                        $model->setImageIds($imgIds);
                    }

                    if (!$model->save()) {
                        throw new Exception("Ошибка обновления товара {$model->id}: ".print_r($model->errors, true));
                    }

                    if (!$shopProduct->save()) {
                        throw new Exception("Ошибка обновления товара {$model->id}: ".print_r($shopProduct->errors, true));
                    }

                    $store_items = (array)ArrayHelper::getValue($apiData, "store_items");
                    $this->_updateStoreItemsForProduct($shopProduct, $store_items);

                    $properties = (array)ArrayHelper::getValue($apiData, "properties");
                    $this->_updatePropertiesForProduct($model, $properties);

                    $result = true;
                }
            } else {
                //Создать
                $model = new ShopCmsContentElement();

                $model->sx_id = (int)ArrayHelper::getValue($apiData, "id");
                $model->content_id = $content_id;


                //TODO:добавить обновление
                $model->name = trim((string)ArrayHelper::getValue($apiData, "name"));
                $model->description_short = trim((string)ArrayHelper::getValue($apiData, "description_short"));
                $model->description_full = trim((string)ArrayHelper::getValue($apiData, "description_full"));
                $model->is_adult = (int)ArrayHelper::getValue($apiData, "is_adult");

                $shopProduct = new ShopProduct();

                $category_id = (int)ArrayHelper::getValue($apiData, "category_id");
                if ($category_id) {
                    $cmsTree = CmsTree::find()->sxId($category_id)->one();
                    if (!$cmsTree) {
                        $this->actionUpdateAll();
                        $cmsTree = CmsTree::find()->sxId($category_id)->one();
                    }
                    if ($cmsTree) {
                        $model->tree_id = $cmsTree->id;
                    }
                }

                $brand_id = (int)ArrayHelper::getValue($apiData, "brand_id");
                if ($brand_id) {
                    $shopBrand = ShopBrand::find()->sxId($brand_id)->one();
                    if (!$shopBrand) {
                        $this->actionUpdateAll();
                        $shopBrand = ShopBrand::find()->sxId($brand_id)->one();
                    }

                    if ($shopBrand) {
                        $shopProduct->brand_id = $shopBrand->id;
                    }
                }

                $collection_ids = (array)ArrayHelper::getValue($apiData, "collection_ids");
                if ($collection_ids) {
                    $tmpCollectionIds = [];
                    foreach ($collection_ids as $sx_collection_id) {
                        $shopCollection = ShopCollection::find()->sxId($sx_collection_id)->one();
                        if (!$shopCollection) {
                            $this->actionUpdateAll();
                            $shopCollection = ShopCollection::find()->sxId($sx_collection_id)->one();
                        }

                        if ($shopCollection) {
                            $tmpCollectionIds[] = $shopCollection->id;
                            //$shopProduct->link("collections", $shopCollection);
                        }
                    }

                    $shopProduct->collections = $tmpCollectionIds;
                }


                $shopProduct->brand_sku = trim((string)ArrayHelper::getValue($apiData, "brand_sku"));
                $shopProduct->country_alpha2 = trim((string)ArrayHelper::getValue($apiData, "country_alpha2"));
                $shopProduct->measure_code = trim((string)ArrayHelper::getValue($apiData, "measure_code"));
                $shopProduct->weight = (float)ArrayHelper::getValue($apiData, "weight");
                $shopProduct->width = (float)ArrayHelper::getValue($apiData, "width");
                $shopProduct->length = (float)ArrayHelper::getValue($apiData, "length");
                $shopProduct->height = (float)ArrayHelper::getValue($apiData, "height");
                $shopProduct->measure_ratio = (float)ArrayHelper::getValue($apiData, "measure_ratio");
                $shopProduct->measure_ratio_min = (float)ArrayHelper::getValue($apiData, "measure_ratio_min");
                $shopProduct->expiration_time = (int)ArrayHelper::getValue($apiData, "expiration_time");
                $shopProduct->service_life_time = (int)ArrayHelper::getValue($apiData, "service_life_time");
                $shopProduct->warranty_time = (int)ArrayHelper::getValue($apiData, "warranty_time");
                $shopProduct->expiration_time_comment = trim((string)ArrayHelper::getValue($apiData, "expiration_time_comment"));
                $shopProduct->service_life_time_comment = trim((string)ArrayHelper::getValue($apiData, "service_life_time_comment"));
                $shopProduct->warranty_time_comment = trim((string)ArrayHelper::getValue($apiData, "warranty_time_comment"));


                if ($image = $this->_addImage(ArrayHelper::getValue($apiData, "image"))) {
                    $model->image_id = $image->id;
                }

                if ($images = ArrayHelper::getValue($apiData, "images")) {

                    $imgIds = [];

                    foreach ($images as $imgApiData) {
                        $img = $this->_addImage($imgApiData);
                        $imgIds[] = $img->id;
                    }

                    $model->setImageIds($imgIds);
                }

                if (!$model->save()) {
                    throw new Exception("Ошибка обновления товара {$model->id}: ".print_r($model->errors, true));
                }


                $shopProduct->id = $model->id;

                if (!$shopProduct->save()) {
                    throw new Exception("Ошибка обновления товара {$model->id}: ".print_r($shopProduct->errors, true));
                }

                
                $store_items = (array)ArrayHelper::getValue($apiData, "store_items");
                $this->_updateStoreItemsForProduct($shopProduct, $store_items);

                $properties = (array)ArrayHelper::getValue($apiData, "properties");
                $this->_updatePropertiesForProduct($model, $properties);

                $result = true;

            }

            $t->commit();


        } catch (\Exception $exception) {
            $t->rollBack();

            if ($this->is_stop_on_error) {
                throw $exception;
            }

            $this->stdout($exception->getMessage(), Console::FG_RED);
        }

        return $result;
    }

    private $_stores = [];

    /**
     * @param int $sx_store_id
     * @return ShopStore|null
     */
    private function _getStore(int $sx_store_id)
    {

        $shopStore = ArrayHelper::getValue($this->_stores, $sx_store_id, null);

        if (!$shopStore) {
            $shopStore = ShopStore::find()->sxId($sx_store_id)->one();
            if ($shopStore) {
                $this->_stores[$sx_store_id] = $shopStore;
            }
        }

        return $shopStore;
    }

    private function _updateStoreItemsForProduct(ShopProduct $shopProduct, array $apiData = [])
    {
        if ($apiData) {
            foreach ($apiData as $store_item_data) {
                $sx_store_id = (int)ArrayHelper::getValue($store_item_data, "store_id");
                $supplier_code = trim((string)ArrayHelper::getValue($store_item_data, "supplier_code"));
                $shopStore = $this->_getStore($sx_store_id);

                if ($shopStore) {
                    /**
                     * @var $shopStoreItem ShopStoreProduct
                     */
                    $shopStoreItem = $shopStore->getShopStoreProducts()->andWhere(['external_id' => $supplier_code])->one();

                    $api_supplier_name = trim((string)ArrayHelper::getValue($store_item_data, "supplier_name"));
                    $api_quantity = (float)ArrayHelper::getValue($store_item_data, "quantity");
                    $api_purchase_price = (float)ArrayHelper::getValue($store_item_data, "purchase_price");
                    $api_selling_price = (float)ArrayHelper::getValue($store_item_data, "selling_price");

                    if (!$shopStoreItem) {
                        $shopStoreItem = new ShopStoreProduct();
                        $shopStoreItem->shop_store_id = $shopStore->id;
                        $shopStoreItem->shop_product_id = $shopProduct->id;
                        $shopStoreItem->external_id = $supplier_code;
                        $shopStoreItem->name = $api_supplier_name;
                        $shopStoreItem->quantity = $api_quantity;
                        $shopStoreItem->purchase_price = $api_purchase_price;
                        $shopStoreItem->selling_price = $api_selling_price;
                        if (!$shopStoreItem->save()) {
                            throw new Exception(print_r($shopStoreItem->errors, true));
                        }
                    } else {
                        $changedAttrs = [];

                        if ($shopStoreItem->shop_product_id != $shopProduct->id) {
                            $shopStoreItem->shop_product_id = $shopProduct->id;
                            $changedAttrs[] = "shop_product_id";
                        }

                        if ($shopStoreItem->name != $api_supplier_name) {
                            $shopStoreItem->name = $api_supplier_name;
                            $changedAttrs[] = "name";
                        }

                        if ($shopStoreItem->quantity != $api_quantity) {
                            $shopStoreItem->quantity = $api_quantity;
                            $changedAttrs[] = "quantity";
                        }

                        if ($shopStoreItem->selling_price != $api_selling_price) {
                            $shopStoreItem->selling_price = $api_selling_price;
                            $changedAttrs[] = "selling_price";
                        }

                        if ($shopStoreItem->purchase_price != $api_purchase_price) {
                            $shopStoreItem->purchase_price = $api_purchase_price;
                            $changedAttrs[] = "purchase_price";
                        }
                        
                        if ($changedAttrs) {
                            if (!$shopStoreItem->update(true, $changedAttrs)) {
                                throw new Exception(print_r($shopStoreItem->errors, true));;
                            }
                        }
                    }

                }

            }
        }
    }

    private function _updatePropertiesForProduct(ShopCmsContentElement $model, array $apiData = []) {
        if ($apiData) {
            
            $rpmModel = $model->relatedPropertiesModel;
            
            $apiData = ArrayHelper::map($apiData, "property_id", "value");
            $properties = CmsContentProperty::find()->sxId(array_keys($apiData))->all();
            if (count($apiData) != count($properties)) {
                $this->actionUpdateAll();
                $properties = CmsContentProperty::find()->sxId(array_keys($apiData))->all();
            }
            
            $properties = ArrayHelper::map($properties, "sx_id", function ($model){
                return $model;
            });

            foreach ($apiData as $sx_id => $value) {
                /**
                 * @var CmsContentProperty $property
                 */
                $property = ArrayHelper::getValue($properties, $sx_id);
                
                if ($property->property_type == PropertyType::CODE_LIST) {
                    
                    if ($property->is_multiple) {
                        $enumIds = [];
                        
                        foreach ($value as $valueObject)
                        {
                            $enumSxId = (int)ArrayHelper::getValue($valueObject, "id");
                            $enumSxValue = (string)ArrayHelper::getValue($valueObject, "value");
                            
                            $enum = $property->getEnums()->andWhere(['sx_id' => $enumSxId])->one();
                            if (!$enum) {
                                $enum = new CmsContentPropertyEnum();
                                $enum->property_id =  $property->id;
                                $enum->value = $enumSxValue;
                                $enum->sx_id = $enumSxId;
                                if (!$enum->save()) {
                                    throw new Exception(print_r($enum->errors, true));
                                }
                            }
                            
                            $enumIds[] = $enum->id;
                        }
                        
                        $rpmModel->{$property->code} = $enumIds;
                    } else {

                        if ($value) {
                            
                        }
                        $enumSxId = (int)ArrayHelper::getValue($value, "id");
                        $enumSxValue = (string)ArrayHelper::getValue($value, "value");
                        
                        $enum = $property->getEnums()->andWhere(['sx_id' => $enumSxId])->one();
                        if (!$enum) {
                            $enum = new CmsContentPropertyEnum();
                            $enum->property_id =  $property->id;
                            $enum->value = $enumSxValue;
                            $enum->sx_id = $enumSxId;
                            if (!$enum->save()) {
                                throw new Exception(print_r($enum->errors, true) . print_r($enum->toArray(), true));
                            }
                        }
                        
                        $rpmModel->{$property->code} = $enum->id;
                        
                    }
                    
                } else {
                    $rpmModel->{$property->code} = $value;
                }
            }
            
            $rpmModel->save();
        }
    }

    /**
     * @param                $apiData
     * @param ShopStore|null $model
     * @return bool
     * @throws \Throwable
     */
    private function _updateStore($apiData = [], ShopStore $model = null)
    {
        $id = (int)ArrayHelper::getValue($apiData, "id");
        $updated_at = (int)ArrayHelper::getValue($apiData, "updated_at.timestamp");

        $result = false;

        $t = \Yii::$app->db->beginTransaction();
        try {
            if ($model) {
                //Обновить
                $isNeedUpdate = false;
                if ($this->is_check_updated_at) {
                    if ($model->updated_at < $updated_at) {
                        $isNeedUpdate = true;
                    }
                } else {
                    $isNeedUpdate = true;
                }

                if ($isNeedUpdate) {
                    //TODO:добавить обновление
                    $model->name = trim((string)ArrayHelper::getValue($apiData, "name"));
                    $model->address = trim((string)ArrayHelper::getValue($apiData, "address"));
                    $model->latitude = (float)ArrayHelper::getValue($apiData, "latitude");
                    $model->longitude = (float)ArrayHelper::getValue($apiData, "longitude");

                    if ($image = $this->_addImage(ArrayHelper::getValue($apiData, "image"))) {
                        $model->cms_image_id = $image->id;
                    }

                    if ($model->save()) {
                        $result = true;
                    } else {
                        throw new Exception("Ошибка обновления бренда {$model->id}: ".print_r($model->errors, true));
                    }

                }
            } else {
                //Создать
                $model = new ShopStore();

                $model->sx_id = (int)ArrayHelper::getValue($apiData, "id");

                $model->name = trim((string)ArrayHelper::getValue($apiData, "name"));
                $model->address = trim((string)ArrayHelper::getValue($apiData, "address"));
                $model->latitude = (float)ArrayHelper::getValue($apiData, "latitude");
                $model->longitude = (float)ArrayHelper::getValue($apiData, "longitude");

                if ($image = $this->_addImage(ArrayHelper::getValue($apiData, "image"))) {
                    $model->cms_image_id = $image->id;
                }

                if ($model->save()) {

                } else {
                    throw new Exception("Ошибка создания бренда: ".print_r($model->errors, true));
                }

                $result = true;
            }

            $t->commit();
        } catch (\Exception $exception) {
            $t->rollBack();

            if ($this->is_stop_on_error) {
                throw $exception;
            }

            $this->stdout($exception->getMessage(), Console::FG_RED);
        }

        return $result;
    }

    /**
     * @param $imageData
     * @return \skeeks\cms\models\StorageFile|null
     * @throws Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    private function _addImage($imageData = [])
    {
        $image_src = (string)ArrayHelper::getValue($imageData, "src");
        $image_id = (int)ArrayHelper::getValue($imageData, "id");

        if (!$image_src) {
            return null;
        }


        if ($file = CmsStorageFile::find()->sxId($image_id)->one()) {
            if ($this->is_reload_images) {
                $file->delete();
            } else {
                return $file;
            }
        }

        $file = \Yii::$app->storage->upload($image_src);
        $file->sx_id = $image_id;
        $file->update(false, ['sx_id']);

        return $file;
    }
}
