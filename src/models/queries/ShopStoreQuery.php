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
class ShopStoreQuery extends CmsActiveQuery
{
    /**
     * @param bool $value
     * @return ShopStoreQuery
     */
    public function isSupplier($value = true)
    {
        return $this->andWhere(['is_supplier' => (int)$value]);
    }

    /**
     * @param string|int|array $id
     * @return ShopStoreQuery
     */
    public function sxId(mixed $id)
    {
        return $this->andWhere(['sx_id' => $id]);
    }
}