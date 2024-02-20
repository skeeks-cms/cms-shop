<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\backend\helpers\BackendUrlHelper;
use skeeks\cms\backend\widgets\SelectModelDialogContentElementWidget;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\shop\models\ShopProductRelation;
use skeeks\yii2\form\fields\WidgetField;
use yii\helpers\ArrayHelper;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class AdminShopProductRelationController extends BackendModelStandartController
{
    public function init()
    {
        $this->name = \Yii::t('skeeks/shop/app', 'Content settings');
        $this->modelShowAttribute = "id";
        $this->modelClassName = ShopProductRelation::class;

        $this->generateAccessActions = false;

        $this->accessCallback = function () {
            if (!\Yii::$app->skeeks->site->is_default) {
                return false;
            }
            return \Yii::$app->user->can($this->uniqueId);
        };

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return ArrayHelper::merge(parent::actions(), [
            'create' => [
                'fields' => [$this, 'updateFields'],
            ],
            'update' => [
                'fields' => [$this, 'updateFields'],
            ],
        ]);
    }

    public function updateFields()
    {
        return [
            'shop_product1_id' => [
                'class'        => WidgetField::class,
                'widgetClass'  => SelectModelDialogContentElementWidget::class,
                'widgetConfig' => [
                    'content_id'  => 2,
                    'options'     => [
                        'data-form-reload' => "true",
                    ],
                    'dialogRoute' => [
                        '/shop/admin-cms-content-element',
                        BackendUrlHelper::BACKEND_PARAM_NAME => [
                            'sx-to-main' => "true",
                        ],
                        'w3-submit-key'                      => "1",
                        'findex'                             => [
                            'shop_supplier_id' => [
                                'mode' => 'empty',
                            ],
                        ],
                    ],
                ],
            ],
            'shop_product2_id' => [
                'class'        => WidgetField::class,
                'widgetClass'  => SelectModelDialogContentElementWidget::class,
                'widgetConfig' => [
                    'content_id'  => 2,
                    'options'     => [
                        'data-form-reload' => "true",
                    ],
                    'dialogRoute' => [
                        '/shop/admin-cms-content-element',
                        BackendUrlHelper::BACKEND_PARAM_NAME => [
                            'sx-to-main' => "true",
                        ],
                        'w3-submit-key'                      => "1",
                        'findex'                             => [
                            'shop_supplier_id' => [
                                'mode' => 'empty',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

}
