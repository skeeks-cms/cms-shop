<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 25.05.2015
 */
namespace skeeks\cms\shop\cmsWidgets\filters\models;
use skeeks\cms\models\CmsContent;
use skeeks\cms\models\CmsContentElement;
use skeeks\cms\models\CmsContentProperty;
use skeeks\cms\relatedProperties\models\RelatedPropertyModel;
use yii\base\DynamicModel;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

/**
 * Class SearchRelatedPropertiesModel
 * @package skeeks\cms\shop\cmsWidgets\filters\models
 */
class SearchRelatedPropertiesModel extends DynamicModel
{
    /**
     * @var CmsContent
     */
    public $cmsContent = null;
    /**
     * @var CmsContentProperty[]
     */
    public $properties = [];


    public function initCmsContent(CmsContent $cmsContent)
    {
        $this->cmsContent = $cmsContent;

        /**
         * @var $prop CmsContentProperty
         */
        if ($props = $this->cmsContent->getCmsContentProperties()->all())
        {
            foreach ($props as $prop)
            {
                $this->defineAttribute($prop->code, "");
                $this->addRule([$prop->code], "safe");

                $this->properties[$prop->code] = $prop;
            }
        }
    }

    /**
     * @param $code
     * @return CmsContentProperty
     */
    public function getProperty($code)
    {
        return ArrayHelper::getValue($this->properties, $code);
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        $result = [];

        foreach ($this->attributes() as $code)
        {
            $result[$code] = $this->getProperty($code)->name;
        }

        return $result;
    }


    /**
     * @param ActiveDataProvider $activeDataProvider
     */
    public function search(ActiveDataProvider $activeDataProvider)
    {

    }
}