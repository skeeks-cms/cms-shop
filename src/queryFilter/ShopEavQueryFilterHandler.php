<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\queryFilter;

use skeeks\cms\eavqueryfilter\CmsEavQueryFilterHandler;
use skeeks\cms\models\CmsContentElement;
use skeeks\cms\shop\models\ShopCmsContentElement;
use skeeks\cms\shop\models\ShopProduct;
use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class ShopEavQueryFilterHandler extends CmsEavQueryFilterHandler
{
    /**
     * @param $query
     * @return $this
     */
    public function initRPByQuery($query)
    {
        if ($rps = $query->all()) {
            foreach ($rps as $rp) {
                $this->_rpInit($rp);
            }
        }

        /**
         * @var $query \yii\db\ActiveQuery
         */
        $query = clone $this->baseQuery;
        $query->with = [];
        $query->select(['cms_content_element.id as id']);

        $this->elementIds = [];
        if ($ids = $query->column()) {

            if ($ids) {
                $string_ids = implode(",", $ids);

                //Добавить к этому предожения по товарам
                $child_ids = CmsContentElement::find()->select(['id'])->where(new Expression("parent_content_element_id in ({$string_ids})"))->column();
                if ($child_ids) {
                    $ids = array_merge($ids, $child_ids);
                }

                //Если показывается сайт который собирает товары с других сайтов
                $tmpIds = implode(",", $ids);
                if (\Yii::$app->skeeks->site->shopSite->is_receiver) {
                    //$mainIds = ShopProduct::find()->select(['main_pid'])->where(new Expression("id in ({$tmpIds})"))->andWhere(['is not', 'main_pid', null])->column();
                    $mainIds = ShopCmsContentElement::find()
                                ->joinWith("shopProduct as sp", true, "INNER JOIN")
                                ->select(['main_cce_id'])->where(new Expression(ShopCmsContentElement::tableName() . ".id in ({$tmpIds})"))
                                ->andWhere(['is not', 'main_cce_id', null])->column();

                    if ($mainIds) {
                        $ids = array_merge($ids, $mainIds);
                    }
                }

                $ids = implode(",", $ids);
                $this->elementIds = CmsContentElement::find()
                    ->andWhere(new Expression("id in ({$ids})"))
                    ->select(['id'])
                    
                    ->column()
                ;
                

            } else {
                $this->elementIds = [];
            }


            //$this->elementIds = $ids;
        }


        return $this;
    }


    protected function _applyToQuery(ActiveQuery $activeQuery, $unionQuery) {
        $activeQuery->joinWith("childrenContentElements as childrenContentElements");
        $activeQuery->andWhere([
            'or',
            ['in', CmsContentElement::tableName() . '.main_cce_id', $unionQuery],
            ['in', CmsContentElement::tableName().'.id', $unionQuery],
            ['in', 'childrenContentElements.id', $unionQuery],
        ]);
    }
}