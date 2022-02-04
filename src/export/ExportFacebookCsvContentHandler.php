<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 29.08.2016
 */

namespace skeeks\cms\shop\export;

use skeeks\cms\export\ExportHandler;
use skeeks\cms\export\ExportHandlerFilePath;
use skeeks\cms\helpers\StringHelper;
use skeeks\cms\importCsv\handlers\CsvHandler;
use skeeks\cms\importCsvContent\widgets\MatchingInput;
use skeeks\cms\models\CmsContent;
use skeeks\cms\models\CmsContentElement;
use skeeks\cms\shop\models\ShopCmsContentElement;
use skeeks\cms\shop\models\ShopProduct;
use yii\base\Exception;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/**
 * @property CmsContent $cmsContent
 *
 * Class CsvContentHandler
 *
 * @package skeeks\cms\importCsvContent
 */
class ExportFacebookCsvContentHandler extends ExportHandler
{
    public $file_path = '';

    /**
     * @var string
     */
    public $brand_default_name = 'NoName';


    const CSV_CHARSET_UTF8 = 'UTF-8';             //другой
    const CSV_CHARSET_WINDOWS1251 = 'windows-1251';             //другой

    /**
     * @var string
     */
    public $charset = self::CSV_CHARSET_UTF8;


    /**
     * Доступные кодировки
     * @return array
     */
    static public function getCsvCharsets()
    {
        return [
            self::CSV_CHARSET_UTF8        => self::CSV_CHARSET_UTF8,
            self::CSV_CHARSET_WINDOWS1251 => self::CSV_CHARSET_WINDOWS1251,
        ];
    }


    public function init()
    {
        $this->name = \Yii::t('skeeks/exportCsvContent', '[CSV] Экспорт товаров для facebook');

        if (!$this->file_path) {
            $rand = \Yii::$app->formatter->asDate(time(), "Y-M-d")."-".\Yii::$app->security->generateRandomString(5);
            $this->file_path = "/export/fb/fb-{$rand}.csv";
        }

        parent::init();
    }

    public function getAvailableFields()
    {
        $fields = [];

        foreach ($element->attributeLabels() as $key => $name) {
            $fields['element.'.$key] = $name;
        }

        foreach ($element->relatedPropertiesModel->attributeLabels() as $key => $name) {
            $fields['property.'.$key] = $name." [свойство]";
        }

        return array_merge(['' => ' - '], $fields);
    }




    /**
     * Соответствие полей
     * @var array
     */
    public $matching = [];

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [

            ['charset', 'string'],
            ['brand_default_name', 'string'],
            ['brand_default_name', 'required'],
        ]);
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'charset'    => \Yii::t('skeeks/importCsvContent', 'Кодировка'),
            'brand_default_name'    => \Yii::t('skeeks/importCsvContent', 'Название бренда по умолчанию'),
        ]);
    }


    public function attributeHints()
    {
        return ArrayHelper::merge(parent::attributeHints(), [
            'brand_default_name'    => \Yii::t('skeeks/importCsvContent', 'Если у товара не задано название бренда, то будет использоваться это'),
        ]);
    }

    /**
     * @param ActiveForm $form
     */
    public function renderConfigForm(ActiveForm $form)
    {
        parent::renderConfigForm($form);

        /*echo $form->field($this, 'charset')->listBox(
            $this->getCsvCharsets(), [
            'size'             => 1,
            'data-form-reload' => 'true',
        ]);*/

        echo $form->field($this, 'brand_default_name');

    }


    public function export()
    {
        ini_set("memory_limit", "8192M");
        set_time_limit(0);

        //Создание дирректории
        if ($dirName = dirname($this->rootFilePath)) {
            //$this->result->stdout("Создание дирректории\n");

            if (!is_dir($dirName) && !FileHelper::createDirectory($dirName)) {
                throw new Exception("Не удалось создать директорию для файла");
            }
        }

        //$this->charset = $this->charset."//IGNORE";
        $elements = ShopCmsContentElement::find()
            ->active()
            //->hasImage()
            ->joinWith("shopProduct as sp", true, "INNER JOIN")
            ->andWhere(['in', 'sp.product_type', [
                ShopProduct::TYPE_SIMPLE,
                ShopProduct::TYPE_OFFER
            ]])

            ->groupBy(ShopCmsContentElement::tableName() . ".id")
        ;

        $countTotal = $elements->count();
        $this->result->stdout("Товаров найдено: {$countTotal}\n");

        $fp = fopen($this->rootFilePath, 'w');

        $head = [];

        $head[] = "id";
        $head[] = "title";
        $head[] = "description";
        $head[] = "availability";
        $head[] = "condition";
        $head[] = "price";
        $head[] = "link";
        $head[] = "image_link";
        $head[] = "brand";

        /*foreach ($element->toArray() as $code => $value) {
            $head[] = "element.".$code;
        }

        $head[] = "element.mainImageSrc";*/

        /**
         * @var $element CmsContentElement
         */
        /*foreach ($element->relatedPropertiesModel->toArray() as $code => $value) {
            $head[] = 'property.'.$code;
        }*/

        fputcsv($fp, $head, ",");


        $counter = 0;
        /**
         * @var $element ShopCmsContentElement
         */
        foreach ($elements->each(10) as $element) {

            $row = [];

            $row['id'] = $element->id;
            $shopProduct = $element->shopProduct;


            $title = $element->productName;
            if (StringHelper::strlen($title) > 150) {
                $title = StringHelper::substr(0, 150, $title);
            }
            $row['title'] = $title;

            /**
             * @link https://www.facebook.com/business/help/120325381656392?id=725943027795860
             */
            $description = '';
            if (trim($element->productDescriptionShort)) {
                $description = strip_tags(trim($element->productDescriptionShort));
            }
            if (!$description) {
                if (trim($element->productDescriptionFull)) {
                    $description = strip_tags(trim($element->productDescriptionFull));
                }
            }
            if (!$description) {
                $description = $element->productName;
            }
            if (StringHelper::strlen($description) > 5000) {
                $description = StringHelper::substr(0, 5000, $description);
            }
            $row['description'] = $description;



            $quantity = $element->shopProduct->getShopStoreProducts()->select(['sum' => new Expression("sum(quantity)")])
                ->asArray()
                ->one()
            ;
            $row['quantity'] = $quantity['sum'] ? "in stock" : "out of stock";

            $row['condition'] = "new";

            if (!$element->shopProduct->minProductPrice) {
                $this->result->stdout("\tУ товара {$shopProduct->id} не задана цена\n");
                continue;
            }

            $money = $element->shopProduct->minProductPrice->money;

            $row['price'] = ((float) $money->amount) . " " . $money->currency->code;
            $row['link'] = $element->getUrl(true);

            if (!$element->mainProductImage) {
                $this->result->stdout("\tУ товара {$shopProduct->id} не задано фото\n");
                continue;
            }

            $row['image_link'] = $element->mainProductImage->absoluteSrc;
            $row['brand'] = $this->brand_default_name;

            fputcsv($fp, $row, ",");

            $counter ++;
        }

        $filtPath = str_replace(\Yii::getAlias("@webroot"), "", $this->rootFilePath);
        $filtPath = Url::home(true) . $filtPath;
        $filtPath = FileHelper::normalizePath($filtPath);
        
        $this->result->stdout("-----------------------------\n");
        $this->result->stdout("Добавлено товаров: {$counter}\n");
        $this->result->stdout("Файл: {$filtPath}\n");
        $this->result->stdout("-----------------------------\n");

        fclose($fp);

        return $this->result;
    }
}