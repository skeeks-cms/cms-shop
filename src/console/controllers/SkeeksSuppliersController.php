<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 22.03.2016
 */

namespace skeeks\cms\shop\console\controllers;

use skeeks\cms\measure\models\CmsMeasure;
use skeeks\cms\models\CmsCountry;
use skeeks\cms\models\CmsTree;
use skeeks\cms\shop\models\BrandCmsContentElement;
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
     * @return false|void
     */
    public function init()
    {
        if (!isset(\Yii::$app->skeeksSuppliersApi)) {
            $this->stdout("Компонент skeeksSuppliersApi не подключен");
            return false;
        }

        if (!\Yii::$app->skeeksSuppliersApi->api_key) {
            $this->stdout("Skeeks Suppliers API не настроено, не указан api_key");
            return false;
        }

        return parent::init();
    }

    /**
     * @return void
     * @throws Exception
     */
    public function actionUpdateCountries()
    {
        $response = \Yii::$app->skeeksSuppliersApi->methodCountries();

        $this->stdout("Обновление стран", Console::BG_BLUE);
        $this->stdout("\n");

        $updated = 0;
        $created = 0;

        if ($response->isOk) {

            $counter = 0;
            $total = count($response->data);
            Console::startProgress($counter, $total);

            foreach ($response->data as $row) {

                $alpha2 = trim((string)ArrayHelper::getValue($row, "alpha2"));
                /**
                 * @var $cmsCountry CmsCountry
                 */
                if ($cmsCountry = CmsCountry::find()->alpha2($alpha2)->one()) {
                    //TODO:добависть обновление
                    $updated++;
                } else {
                    $t = \Yii::$app->db->beginTransaction();
                    
                    try {
                        $cmsCountry = new CmsCountry();
                        $cmsCountry->alpha2 = trim((string)ArrayHelper::getValue($row, "alpha2"));
                        $cmsCountry->alpha3 = trim((string)ArrayHelper::getValue($row, "alpha3"));
                        $cmsCountry->iso = trim((string)ArrayHelper::getValue($row, "iso"));
                        $cmsCountry->phone_code = trim((string)ArrayHelper::getValue($row, "phone_code"));
                        $cmsCountry->domain = trim((string)ArrayHelper::getValue($row, "domain"));
                        $cmsCountry->name = trim((string)ArrayHelper::getValue($row, "name"));
                        
                        $image = $this->_addImage(ArrayHelper::getValue($row, "image"));
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
     * @param $imageData
     * @return \skeeks\cms\models\StorageFile|null
     * @throws Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    private function _addImage($imageData = []) {
        
        $image_src = (string)ArrayHelper::getValue($imageData, "src");
        $image_id = (int)ArrayHelper::getValue($imageData, "id");
        
        if (!$image_src) {
            return null;
        }
        
        $file = \Yii::$app->storage->upload($image_src);
        $file->sx_id = $image_id;
        $file->update(false, ['sx_id']);
        
        return $file;
    }

    /**
     * @return void
     * @throws Exception
     */
    public function actionUpdateMeasures()
    {
        $response = \Yii::$app->skeeksSuppliersApi->methodMeasures();

        $this->stdout("Обновление едениц измерения", Console::BG_BLUE);
        $this->stdout("\n");

        $updated = 0;
        $created = 0;

        if ($response->isOk) {

            $counter = 0;
            $total = count($response->data);
            Console::startProgress($counter, $total);

            foreach ($response->data as $row) {

                $code = trim((string)ArrayHelper::getValue($row, "code"));

                if ($cmsMeasure = CmsMeasure::find()->andWhere(['code' => $code])->one()) {
                    $updated++;
                } else {

                    $measure = new CmsMeasure();
                    $measure->code = $code;
                    $measure->name = trim((string)ArrayHelper::getValue($row, "name"));
                    $measure->symbol = trim((string)ArrayHelper::getValue($row, "symbol"));

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
    public function actionUpdateCategories()
    {
        $response = \Yii::$app->skeeksSuppliersApi->methodCategories();

        $this->stdout("Обновление категорий", Console::BG_BLUE);
        $this->stdout("\n");

        $updated = 0;
        $created = 0;
        if ($response->isOk) {

            $counter = 0;
            $total = count($response->data);
            Console::startProgress($counter, $total);

            foreach ($response->data as $row) {
                $id = (int)ArrayHelper::getValue($row, "id");
                $updated_at = (int)ArrayHelper::getValue($row, "updated_at.timestamp");
                /**
                 * @var $cmsTree CmsTree
                 */
                if ($cmsTree = CmsTree::find()->sxId($id)->one()) {
                    //Нужно обновлять только если в api обновилась категория
                    if ($cmsTree->updated_at < $updated_at) {
                        //TODO:добависть обновление
                        $updated++;
                    }
                } else {
                    $created++;
                    throw new Exception("Создать категорию!");
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
}
