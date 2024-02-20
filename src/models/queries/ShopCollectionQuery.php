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
class ShopCollectionQuery extends CmsActiveQuery
{
    /**
     * @param string|int|array $external_id
     * @return ShopBrandQuery
     */
    public function externalId(mixed $external_id)
    {
        return $this->andWhere(['external_id' => $external_id]);
    }
}