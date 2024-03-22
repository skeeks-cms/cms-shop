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
use skeeks\cms\relatedProperties\propertyTypes\PropertyTypeBool;
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
use yii\helpers\Json;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class SkeeksSuppliersController extends Controller
{
    /**
     * @var bool Сравнивать дату последнего обновления?
     *      0 - обновлять все пришедшие данные
     *      1 - обновлять только данные свежее
     */
    public $is_check_updated_at = 1;

    /**
     * @var bool Если будет ошибка останавливат скриипт?
     *      0 - продолжать обновление игнорируя ошибки
     *      1 - остановить скрипт
     */
    public $is_stop_on_error = 0;

    /**
     * @var bool Перезагружать картинки?
     *      0 - картинки будут пропускаться
     *      1 - заново скачивать и обновлять картинки
     */
    public $is_reload_images = 0;


    /**
     * @var bool
     */
    private $_is_updated_all = false;

    /**
     * @var bool 1 раз за сценарий делаются проверки настроин ли сайт (в дальнейшем можно вынести куда то в другое место). Эти же проверки понадобятся и в web интерфейсе.
     */
    private $_isChecked = false;

    public function options($actionID)
    {
        // $actionId might be used in subclasses to provide options specific to action id
        return ArrayHelper::merge(parent::options($actionID), [
            'is_check_updated_at',
            'is_stop_on_error',
            'is_reload_images',
        ]);
    }

    private $_base_memory_usage = 0;

    /**
     * @return false|void
     */
    public function init()
    {
        $this->_base_memory_usage = memory_get_usage();
        return parent::init();
    }

    /**
     * @param $action
     * @return bool
     */
    public function beforeAction($action)
    {
        $this->_checkBeforeStart();
        return parent::beforeAction($action);
    }

    /**
     * @return string
     */
    private function _memoryUsage()
    {
        return \Yii::$app->formatter->asShortSize(memory_get_usage() - $this->_base_memory_usage);
    }


    private function _checkBeforeStart()
    {

        if ($this->_isChecked) {
            return true;
        }

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

        /*\Yii::$app->skeeks->site->shopSite->required_collection_fields = [];
        \Yii::$app->skeeks->site->shopSite->required_brand_fields = [];
        \Yii::$app->skeeks->site->shopSite->required_product_fields = [];*/
    }


    /**
     * Обновление информации по всем справочным данным
     */
    public function _updateAllData()
    {
        if ($this->_is_updated_all) {
            return true;
        }
        
        $this->stdout("Обновление недостающих данных [{$this->_memoryUsage()}]", Console::BG_BLUE);
        $this->stdout("\n");
        
        exec("php yii shop/skeeks-suppliers/update-all", $output);
        print_r($output);
        
        $this->stdout("Обновление недостающих данных завершено [{$this->_memoryUsage()}]", Console::BG_BLUE);
        $this->stdout("\n");
        
        $this->_is_updated_all = true;
    }

    /**
     * Обновление информации по всем справочным данным
     * @return bool|void
     * @throws Exception
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
     * Обновление информации по странам
     * @return void
     * @throws Exception
     */
    public function actionUpdateCountries()
    {
        $response = \Yii::$app->skeeksSuppliersApi->methodCountries();

        $this->stdout("Обновление стран [{$response->time} сек] [{$this->_memoryUsage()}]", Console::BG_BLUE);
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
                    /*$image = $this->_addImage(ArrayHelper::getValue($apiData, "image"));
                    if ($image) {
                        $cmsCountry->flag_image_id = $image->id;
                    }
                    
                    if ($cmsCountry->save()) {
                        $updated++;
                    } else {
                        throw new Exception("Страна не создана".print_r($cmsCountry->errors, true));
                    }*/

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
     * Обновление информации по еденицам измерения
     * @return void
     * @throws Exception
     */
    public function actionUpdateMeasures()
    {
        $response = \Yii::$app->skeeksSuppliersApi->methodMeasures();

        $this->stdout("Обновление едениц измерения [{$response->time} сек] [{$this->_memoryUsage()}]", Console::BG_BLUE);
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
     * Обновление категорий
     * @param $page
     * @return void
     * @throws Exception
     * @throws \Throwable
     */
    public function actionUpdateCategories($page = 1)
    {
        $response = \Yii::$app->skeeksSuppliersApi->methodCategories([
            'page' => $page,
        ]);

        $this->stdout("Обновление категорий, страница {$page} [{$response->time} сек]  [{$this->_memoryUsage()}]", Console::BG_BLUE);
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
            unset($response);
            $this->actionUpdateCategories($page + 1);
        }
    }

    /**
     * Обновление характеристик
     * @return void
     * @throws Exception
     */
    public function actionUpdateProperties($page = 1)
    {
        $response = \Yii::$app->skeeksSuppliersApi->methodProperties([
            'page' => $page,
        ]);

        $this->stdout("Обновление характеристик, страница {$page} [{$response->time} сек] [{$this->_memoryUsage()}]", Console::BG_BLUE);
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
            unset($response);
            $this->actionUpdateProperties($page + 1);
        }
    }

    /**
     * Обновление брендов
     * @return void
     * @throws Exception
     */
    public function actionUpdateBrands($page = 1)
    {
        $response = \Yii::$app->skeeksSuppliersApi->methodBrands([
            'page' => $page,
        ]);

        $this->stdout("Обновление брендов, страница {$page} [{$response->time} сек] [{$this->_memoryUsage()}]", Console::BG_BLUE);
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
            unset($response);
            $this->actionUpdateBrands($page + 1);
        }
    }


    /**
     * Обновление коллекций
     * @return void
     * @throws Exception
     */
    public function actionUpdateCollections($page = 1)
    {
        $response = \Yii::$app->skeeksSuppliersApi->methodCollections([
            'page' => $page,
        ]);

        $this->stdout("Обновление коллекций, страница {$page} [{$response->time} сек] [{$this->_memoryUsage()}]", Console::BG_BLUE);
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
            unset($response);
            $this->actionUpdateCollections($page + 1);
        }
    }

    /**
     * Обновление складов
     * @return void
     * @throws Exception
     */
    public function actionUpdateStores($page = 1)
    {
        $response = \Yii::$app->skeeksSuppliersApi->methodStores([
            'page' => $page,
        ]);

        $this->stdout("Обновление складов, страница {$page} [{$response->time} сек] [{$this->_memoryUsage()}]", Console::BG_BLUE);
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
            unset($response);
            $this->actionUpdateStores($page + 1);
        }
    }

    /**
     * Полное обновление, всех товаров
     * @return void
     * @throws Exception
     * @throws \Throwable
     */
    public function actionUpdateProductsAll()
    {
        $this->actionUpdateProducts(0);
    }

    /**
     * @var null Вспомогательная переменная
     */
    private $_last_product_updated = null;

    /**
     * Получает информацию по новым товарам, и недавно измененным
     * @param $is_new
     * @param $page
     * @return false|void
     * @throws Exception
     * @throws \Throwable
     */
    public function actionUpdateProducts($is_new = 1, $page = 1)
    {
        $apiQuery = [
            'page' => $page,
        ];

        if ($is_new) {
            
            //Если это уже не первая страница
            if ($this->_last_product_updated) {
                $apiQuery['updated_at'] = $this->_last_product_updated;
            } else {
                /**
                 * @var ShopCmsContentElement $lastProduct
                 */
                $lastProduct = ShopCmsContentElement::find()
                    ->innerJoinWith("shopProduct as shopProduct")
                    ->andWhere(['is not', 'sx_id', null])
                    ->orderBy(['updated_at' => SORT_DESC])
                    ->one();
                
                if ($lastProduct) {
                    //Если ранее уже получали SX товары
                    $this->_last_product_updated = $lastProduct->updated_at;
                    $apiQuery['updated_at'] = $lastProduct->updated_at;
                } else {
                    //Если нет еще товаров то по сути получаем все
                    $is_new = 0;
                }
            }
            
        }

        //$apiQuery['f_id'] = 6830902;

        $response = \Yii::$app->skeeksSuppliersApi->methodProducts($apiQuery);

        $this->stdout("Обновление товаров, страница {$page} [{$response->time} сек] [{$this->_memoryUsage()}]", Console::BG_BLUE);
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
            unset($response);
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
                    } elseif ($type == 'bool') {
                        $model->component = PropertyTypeBool::class;
                        $model->component_settings = [
                            'fieldElement' => "checkbox",
                        ];
                    } else {
                        $model->component = PropertyTypeText::class;
                    }

                    $model->is_multiple = (int)$model->handler->isMultiple;


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
                } elseif ($type == 'bool') {
                    $model->component = PropertyTypeBool::class;
                    $model->component_settings = [
                        'fieldElement' => "checkbox",
                    ];
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

                    if ($image = $this->_addImage(ArrayHelper::getValue($apiData, "image"), false)) {
                        $model->cms_image_id = $image->id;
                    }

                    if ($images = ArrayHelper::getValue($apiData, "images")) {

                        /*foreach ($images as $imgApiData) {
                            $img = $this->_addImage($imgApiData);
                            $model->link("images", $img);
                        }*/

                        $imgIds = [];

                        foreach ($images as $imgApiData) {
                            $img = $this->_addImage($imgApiData, false);
                            $imgIds[] = $img->id;
                        }

                        $model->setImageIds($imgIds);
                    }

                    if ($model->save()) {
                        $result = true;
                    } else {
                        throw new Exception("Ошибка создания коллекции {$model->id}: ".print_r($model->errors, true).print_r($apiData, true));
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

                if ($image = $this->_addImage(ArrayHelper::getValue($apiData, "image"), false)) {
                    $model->cms_image_id = $image->id;
                }

                if ($images = ArrayHelper::getValue($apiData, "images")) {

                    /*foreach ($images as $imgApiData) {
                        $img = $this->_addImage($imgApiData);
                        $model->link("images", $img);
                    }*/

                    $imgIds = [];

                    foreach ($images as $imgApiData) {
                        $img = $this->_addImage($imgApiData, false);
                        $imgIds[] = $img->id;
                    }

                    $model->setImageIds($imgIds);
                }

                if ($model->save(false)) {

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
                            $this->_updateAllData();
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
                            $this->_updateAllData();
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
                                $this->_updateAllData();
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
                    $shopProduct->measure_matches_jsondata = Json::encode((array)ArrayHelper::getValue($apiData, "measure_matches"));
                    $shopProduct->expiration_time = (int)ArrayHelper::getValue($apiData, "expiration_time");
                    $shopProduct->service_life_time = (int)ArrayHelper::getValue($apiData, "service_life_time");
                    $shopProduct->warranty_time = (int)ArrayHelper::getValue($apiData, "warranty_time");
                    $shopProduct->expiration_time_comment = trim((string)ArrayHelper::getValue($apiData, "expiration_time_comment"));
                    $shopProduct->service_life_time_comment = trim((string)ArrayHelper::getValue($apiData, "service_life_time_comment"));
                    $shopProduct->warranty_time_comment = trim((string)ArrayHelper::getValue($apiData, "warranty_time_comment"));


                    if ($image = $this->_addImage(ArrayHelper::getValue($apiData, "image"), false)) {
                        $model->image_id = $image->id;
                    }

                    if ($images = ArrayHelper::getValue($apiData, "images")) {

                        $imgIds = [];

                        foreach ($images as $imgApiData) {
                            $img = $this->_addImage($imgApiData, false);
                            $imgIds[] = $img->id;
                        }

                        $model->setImageIds($imgIds);
                    }

                    if (!$model->save()) {
                        throw new Exception("Ошибка обновления товара {$model->id}: ".print_r($model->errors, true).print_r($apiData, true));
                    }

                    if (!$shopProduct->save(false)) {
                        throw new Exception("Ошибка обновления товара {$model->id}: ".print_r($shopProduct->errors, true).print_r($apiData, true));
                    }

                    $store_items = (array)ArrayHelper::getValue($apiData, "store_items");
                    $this->_updateStoreItemsForProduct($shopProduct, $store_items);

                    $properties = (array)ArrayHelper::getValue($apiData, "properties");
                    $this->_updatePropertiesForProduct($model, $properties);

                    $model->updated_at = $updated_at;
                    $model->update(false, ['updated_at']);

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
                        $this->_updateAllData();
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
                        $this->_updateAllData();
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
                            $this->_updateAllData();
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
                $shopProduct->measure_matches_jsondata = Json::encode((array)ArrayHelper::getValue($apiData, "measure_matches"));
                $shopProduct->measure_ratio = (float)ArrayHelper::getValue($apiData, "measure_ratio");
                $shopProduct->measure_ratio_min = (float)ArrayHelper::getValue($apiData, "measure_ratio_min");
                $shopProduct->expiration_time = (int)ArrayHelper::getValue($apiData, "expiration_time");
                $shopProduct->service_life_time = (int)ArrayHelper::getValue($apiData, "service_life_time");
                $shopProduct->warranty_time = (int)ArrayHelper::getValue($apiData, "warranty_time");
                $shopProduct->expiration_time_comment = trim((string)ArrayHelper::getValue($apiData, "expiration_time_comment"));
                $shopProduct->service_life_time_comment = trim((string)ArrayHelper::getValue($apiData, "service_life_time_comment"));
                $shopProduct->warranty_time_comment = trim((string)ArrayHelper::getValue($apiData, "warranty_time_comment"));


                if ($image = $this->_addImage(ArrayHelper::getValue($apiData, "image"), false)) {
                    $model->image_id = $image->id;
                }

                if ($images = ArrayHelper::getValue($apiData, "images")) {

                    $imgIds = [];

                    foreach ($images as $imgApiData) {
                        $img = $this->_addImage($imgApiData, false);
                        $imgIds[] = $img->id;
                    }

                    $model->setImageIds($imgIds);
                }

                if (!$model->save()) {
                    throw new Exception("Ошибка создания товара: ".print_r($model->errors, true).print_r($model->toArray(), true));
                }


                $shopProduct->id = $model->id;

                if (!$shopProduct->save(false)) {
                    throw new Exception("Ошибка создания товара: ".print_r($shopProduct->errors, true).print_r($shopProduct->toArray(), true));
                }


                $store_items = (array)ArrayHelper::getValue($apiData, "store_items");
                $this->_updateStoreItemsForProduct($shopProduct, $store_items);

                $properties = (array)ArrayHelper::getValue($apiData, "properties");
                $this->_updatePropertiesForProduct($model, $properties);

                $model->updated_at = $updated_at;
                $model->update(false, ['updated_at']);

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

    private function _updatePropertiesForProduct(ShopCmsContentElement $model, array $apiData = [])
    {
        if ($apiData) {

            $rpmModel = $model->relatedPropertiesModel;

            $apiData = ArrayHelper::map($apiData, "property_id", "value");
            $properties = CmsContentProperty::find()->sxId(array_keys($apiData))->all();
            if (count($apiData) != count($properties)) {
                $this->_updateAllData();
                $properties = CmsContentProperty::find()->sxId(array_keys($apiData))->all();
            }

            $properties = ArrayHelper::map($properties, "sx_id", function ($model) {
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

                        foreach ($value as $valueObject) {
                            $enumSxId = (int)ArrayHelper::getValue($valueObject, "id");
                            $enumSxValue = (string)ArrayHelper::getValue($valueObject, "value");
                            //if ($enumSxValue) {
                            /**
                             * @var $enum CmsContentPropertyEnum
                             */
                            $enum = CmsContentPropertyEnum::find()->andWhere(['sx_id' => $enumSxId])->one();
                            //$enum = $property->getEnums()->andWhere(['sx_id' => $enumSxId])->one();
                            if (!$enum) {
                                $enum = new CmsContentPropertyEnum();
                                $enum->property_id = $property->id;
                                $enum->value = $enumSxValue;
                                $enum->sx_id = $enumSxId;
                                if (!$enum->save()) {
                                    throw new Exception(print_r($enum->errors, true));
                                }
                            } else {
                                if ($enum->property_id != $property->id) {
                                    $enum->property_id = $property->id;
                                    $enum->update(false, ['property_id']);
                                }
                            }

                            $enumIds[] = $enum->id;
                            //} else {
                            //    echo '1111';
                            //    var_dump($valueObject);die;
                            //}


                        }

                        $rpmModel->{$property->code} = $enumIds;
                    } else {

                        if ($value) {
                            $enumSxId = (int)ArrayHelper::getValue($value, "id");
                            $enumSxValue = (string)ArrayHelper::getValue($value, "value");

                            if ($enumSxValue) {
                                //$enum = $property->getEnums()->andWhere(['sx_id' => $enumSxId])->one();
                                $enum = CmsContentPropertyEnum::find()->andWhere(['sx_id' => $enumSxId])->one();
                                if (!$enum) {
                                    $enum = new CmsContentPropertyEnum();
                                    $enum->property_id = $property->id;
                                    $enum->value = $enumSxValue;
                                    $enum->sx_id = $enumSxId;
                                    if (!$enum->save()) {
                                        throw new Exception(print_r($enum->errors, true).print_r($enum->toArray(), true));
                                    }
                                } else {
                                    if ($enum->property_id != $property->id) {
                                        $enum->property_id = $property->id;
                                        $enum->update(false, ['property_id']);
                                    }
                                }

                                $rpmModel->{$property->code} = $enum->id;
                            } else {
                                /*print_r($property->toArray());die;
                                echo '2222';
                                print_r($value);die;*/
                            }


                        } else {
                            $rpmModel->{$property->code} = "";
                        }


                    }

                } else {
                    $rpmModel->{$property->code} = $value;
                }
            }

            if (!$rpmModel->save()) {
                throw new Exception("Ошибка сохранения характеристик: ".print_r($rpmModel->errors, true));
            }
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
                    $model->is_supplier = 1;

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

                $model->is_supplier = 1;
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
     * @param $isForceDownload всегда скачивать изображение
     * @return array|\skeeks\cms\models\StorageFile|void|\yii\db\ActiveRecord|null
     * @throws Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    private function _addImage($imageData = [], $isForceDownload = true)
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


        if ($isForceDownload) {
            $file = \Yii::$app->storage->upload($image_src);
        } else {
            if (\Yii::$app->skeeksSuppliersApi->is_download_images) {
                $file = \Yii::$app->storage->upload($image_src);
            } else {
                $imgUrlData = explode("/", $image_src);
                $urlName = ArrayHelper::getValue($imgUrlData, count($imgUrlData) - 1);
                if (strpos($urlName, ".") !== false) {
                    $urlName = substr($urlName, 0, strpos($urlName, "."));
                }

                $file = new CmsStorageFile();

                $file->cluster_id = "sx";
                $file->cluster_file = (string)ArrayHelper::getValue($imageData, "src");

                $file->original_name = $urlName;
                $file->sx_data = $imageData;

                $file->extension = "webp";
                $file->mime_type = "image/webp";

                if (ArrayHelper::getValue($imageData, "width")) {
                    $file->image_width = (int)ArrayHelper::getValue($imageData, "width");
                }

                if (ArrayHelper::getValue($imageData, "height")) {
                    $file->image_height = (int)ArrayHelper::getValue($imageData, "height");
                }
            }
        }

        $file->sx_id = $image_id;
        $file->save();

        if ($file->extension != "webp") {
            $this->stdout("Uploading ...\n");
            /*print_r($imageData);die;*/
            $this->stdout($image_src."\n");

            $this->stdout("{$file->src}\n");
            $this->stdout("---------------\n");
            die;
        }


        return $file;
    }

    /*public function actionTest()
    {
        $image_src = "https://skeeks-market.ru/uploads/all/1a/d0/e3/1ad0e385ef9ef31a4a05886b2728c94f/sx-filter__skeeks-cms-components-imaging-filters-Thumbnail/150956fd0dcf249e18b4b4a51758abc9/galaxy-330707.webp?w=0&h=1200&q=90&m=2&ext=jpg";
        $file = \Yii::$app->storage->upload($image_src);
        print_r($file->toArray());die;
    }*/
}
