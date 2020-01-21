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
use skeeks\cms\shop\models\ShopTypePrice;
use yii\helpers\ArrayHelper;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class AdminTypePriceController extends BackendModelStandartController
{

    public function init()
    {
        $this->name = \Yii::t('skeeks/shop/app', 'Types of prices');
        $this->modelShowAttribute = "name";
        $this->modelClassName = ShopTypePrice::class;

        $this->generateAccessActions = false;

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return ArrayHelper::merge(parent::actions(), [

            "index" => [
                "filters" => [
                    "visibleFilters" => [
                        'id',
                        'name',
                    ],
                ],
                'grid'    => [
                    'defaultOrder' => [
                        'priority' => SORT_ASC,
                    ],

                    'visibleColumns' => [
                        'checkbox',
                        'actions',

                        'id',

                        'name',

                        'priority',

                    ],
                    'columns'        => [
                        'name' => [
                            'class' => DefaultActionColumn::class,
                        ],
                    ],

                ],
            ],

        ]);
    }

}
