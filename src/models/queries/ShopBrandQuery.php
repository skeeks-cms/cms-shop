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
class ShopBrandQuery extends CmsActiveQuery
{
    /**
     * @param string|int|array $external_id
     * @return ShopBrandQuery
     */
    public function externalId(mixed $external_id)
    {
        return $this->andWhere(['external_id' => $external_id]);
    }
    /**
     * @param string|int|array $id
     * @return ShopBrandQuery
     */
    public function sxId(mixed $id)
    {
        return $this->andWhere(['sx_id' => $id]);
    }
}