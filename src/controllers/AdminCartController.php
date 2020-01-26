<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\models\CmsAgent;
use skeeks\cms\shop\components\CartComponent;
use yii\base\Event;
use yii\helpers\ArrayHelper;
use yii\helpers\UnsetArrayValue;

/**
 * Class AdminFuserController
 * @package skeeks\cms\shop\controllers
 */
class AdminCartController extends AdminOrderController
{

    public function init()
    {
        $this->name = \Yii::t('skeeks/shop/app', 'Baskets');
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        $actions = parent::actions();

        $actions = ArrayHelper::merge($actions, [
            'index' => [
                'grid' => [
                    'on init' => function (Event $e) {
                        /**
                         * @var $dataProvider ActiveDataProvider
                         * @var $query ActiveQuery
                         */
                        $query = $e->sender->dataProvider->query;

                        $query->andWhere(['is_created' => 0]);
                    },
                ],
            ],

            'create' => new UnsetArrayValue(),
            'create-order' => new UnsetArrayValue(),
        ]);

        $actions['index']['grid']['visibleColumns'] = [
            'checkbox',
            'actions',
            'id',

            'updated_at',

            'buyer',
            'shop_pay_system_id',
            'shop_delivery_id',

            'items',

            'amount',
            //'is_created',
            //'go',
        ];

        return $actions;
    }

}
