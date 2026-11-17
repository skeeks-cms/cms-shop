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
use yii\db\Query;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class ShopEavQueryFilterHandler extends CmsEavQueryFilterHandler
{

    public function getElementIds()
    {
        if ($this->_elementIds === null) {
            $this->_baseQuery->with = [];
            $this->_baseQuery->select(['cms_content_element.id as id']);

            $this->_elementIds = [];

            if ($ids = $this->_baseQuery->column()) {
                if ($ids) {
                    $string_ids = implode(",", $ids);
                    $this->elementIds = $ids;
                }
            }
        }

        return $this->_elementIds;
    }

    /**
     * @param $query
     * @return $this
     */
    /*public function initRPByQuery($query)
    {
        if ($rps = $query->all()) {
            foreach ($rps as $rp) {
                $this->_rpInit($rp);
            }
        }

        /**
         * @var $query \yii\db\ActiveQuery
        $query = clone $this->baseQuery;
        $query->with = [];
        $query->select(['cms_content_element.id as id']);

        $this->elementIds = [];
        if ($ids = $query->column()) {
            if ($ids) {
                $string_ids = implode(",", $ids);
                $this->elementIds = $ids;
            } else {
                $this->elementIds = [];
            }
        }

        return $this;
    }*/


    /**
     * Элемент подходит сам или через дочерний элемент (товарное предложение).
     *
     * Родители подходящих дочерних элементов выбираются подзапросом, без JOIN
     * childrenContentElements: JOIN размножал строки товара по числу предложений
     * и мешал MariaDB использовать индексы во внешних запросах (подсчёт товаров,
     * список коллекций сохранённого фильтра).
     */
    protected function _applyToQuery(ActiveQuery $activeQuery, $unionQuery) {
        $parentsQuery = (new Query())
            ->select(['childrenContentElements.parent_content_element_id'])
            ->from(['childrenContentElements' => CmsContentElement::tableName()])
            ->andWhere(['in', 'childrenContentElements.id', $unionQuery]);

        $activeQuery->andWhere([
            'or',
            ['in', CmsContentElement::tableName().'.id', $unionQuery],
            ['in', CmsContentElement::tableName().'.id', $parentsQuery],
        ]);
    }
}