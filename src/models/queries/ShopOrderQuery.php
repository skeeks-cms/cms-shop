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
class ShopOrderQuery extends CmsActiveQuery
{
    /**
     * @param bool $value
     * @return ShopOrderQuery
     */
    public function isCreated($value = true)
    {
        return $this->andWhere(['is_created' => (int)$value]);
    }
}