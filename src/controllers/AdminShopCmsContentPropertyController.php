<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\backend\grid\DefaultActionColumn;
use skeeks\cms\grid\BooleanColumn;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\models\CmsContentProperty;
use skeeks\cms\shop\models\ShopContent;
use skeeks\cms\shop\models\ShopCmsContentProperty;
use skeeks\yii2\form\fields\BoolField;
use skeeks\yii2\form\fields\SelectField;
use yii\base\Event;
use yii\bootstrap\Alert;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class AdminShopCmsContentPropertyController extends BackendModelStandartController
{
    public function init()
    {
        $this->name = \Yii::t('skeeks/shop/app', 'Свойства предложений');
        $this->modelShowAttribute = "asText";
        $this->modelClassName = ShopCmsContentProperty::class;

        $this->generateAccessActions = false;

        $this->accessCallback = function () {
            /*if (!\Yii::$app->skeeks->site->is_default) {
                return false;
            }*/
            return \Yii::$app->user->can($this->uniqueId);
        };

        parent::init();
    }


    public function actions()
    {
        $result = ArrayHelper::merge(parent::actions(), [
            "index" => [
                'on beforeRender' => function (Event $e) {
                    $e->content = Alert::widget([
                        'closeButton' => false,
                        'options'     => [
                            'class' => 'alert-default',
                        ],

                        'body' => <<<HTML
<p>Дополнительные настройки контента, которые участвуют в работе магазина.</p>
<p>Например, в этом разделе можно выделить свойства, которые являются свойствами предложений. И будут влиять на формирование сложной карточки товара.</p>
HTML
                        ,
                    ]);
                },

                "backendShowings" => false,
                "filters"         => false,
                "grid"            => [

                    'on init' => function (Event $e) {

                        $query = $e->sender->dataProvider->query;

                        /**
                         * @var $dataProvider ActiveDataProvider
                         * @var $query ActiveQuery
                         */
                        $query->joinWith('cmsContentProperty as cmsContentProperty', true, "INNER JOIN");
                        $query->andWhere(['cmsContentProperty.cms_site_id' => \Yii::$app->skeeks->site->id]);
                    },


                    'visibleColumns' => [
                        'checkbox',
                        'actions',

                        'id',
                        'is_offer_property',
                        
                        'is_vendor',
                        'is_vendor_code',
                    ],

                    'columns' => [
                        'id' => [
                            'label'         => 'Свойство',
                            'viewAttribute' => 'asText',
                            'class'         => DefaultActionColumn::class,
                        ],
                        'is_offer_property' => [
                            'class'         => BooleanColumn::class,
                        ],
                        'is_vendor' => [
                            'class'         => BooleanColumn::class,
                        ],
                        'is_vendor_code' => [
                            'class'         => BooleanColumn::class,
                        ],

                    ],
                ],
            ],

            "create" => [
                'fields' => [$this, 'updateFields'],
            ],
            "update" => [
                'fields' => [$this, 'updateFields'],
            ],

        ]);

        return $result;
    }

    public function updateFields()
    {
        return [
            'cms_content_property_id'          => [
                'class' => SelectField::class,
                'items' => ArrayHelper::map(CmsContentProperty::find()->cmsSite()->all(), 'id', 'asText'),
            ],
            'is_offer_property'          => [
                'class' => BoolField::class,
                'allowNull' => false,
            ],
            'is_vendor'          => [
                'class' => BoolField::class,
                'allowNull' => false,
            ],
            'is_vendor_code'          => [
                'class' => BoolField::class,
                'allowNull' => false,
            ],
        ];
    }

}
