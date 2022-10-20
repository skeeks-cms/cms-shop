<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\models\queries;

use skeeks\cms\query\CmsActiveQuery;
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class ShopTypePriceQuery extends CmsActiveQuery
{
    /**
     * @return ShopTypePriceQuery
     */
    public function isPurchase()
    {
        return $this->andWhere(['is_purchase' => 1]);
    }

    /**
     * @return ShopTypePriceQuery
     */
    public function isRetail()
    {
        return $this->andWhere(['is_default' => 1]);
    }
}