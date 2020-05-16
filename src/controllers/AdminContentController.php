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
use skeeks\cms\models\CmsAgent;
use skeeks\cms\shop\models\ShopContent;
use skeeks\yii2\form\fields\SelectField;
use yii\base\Event;
use yii\bootstrap\Alert;
use yii\helpers\ArrayHelper;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class AdminContentController extends BackendModelStandartController
{
    public function init()
    {
        $this->name = \Yii::t('skeeks/shop/app', 'Content settings');
        $this->modelShowAttribute = "asText";
        $this->modelClassName = ShopContent::class;

        $this->generateAccessActions = false;

        $this->accessCallback = function () {
            if (!\Yii::$app->skeeks->site->is_default) {
                return false;
            }
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
Настройте контент, элементы которого будут являться товарами и будут продаваться на сайте.
HTML
                        ,
                    ]);
                },

                "backendShowings" => false,
                "filters"         => false,
                "grid"            => [

                    'visibleColumns' => [
                        'checkbox',
                        'actions',

                        'id',
                    ],

                    'columns' => [
                        'id' => [
                            'label'         => 'Контент',
                            'viewAttribute' => 'asText',
                            'class'         => DefaultActionColumn::class,
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
            'content_id'          => [
                'class' => SelectField::class,
                'items' => \skeeks\cms\models\CmsContent::getDataForSelect(),
            ],
            'children_content_id' => [
                'class' => SelectField::class,
                'items' => \skeeks\cms\models\CmsContent::getDataForSelect(),
            ],
        ];
    }

}
