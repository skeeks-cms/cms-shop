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
class ShopCasheboxShiftQuery extends CmsActiveQuery
{
    /**
     * Смены определенной кассы
     *
     * @param int|array $id
     * @return ShopCasheboxShiftQuery
     */
    public function cachebox($id)
    {
        return $this->andWhere(['shop_cashebox_id' => (int)$id]);
    }

    /**
     * Закрытые смены
     *
     * @param $value
     * @return ShopCasheboxShiftQuery
     */
    public function isClosed()
    {
        return $this->andWhere(['is not', 'closed_at', null]);
    }

    /**
     * Не закрытые смены
     * @param $value
     * @return ShopCasheboxShiftQuery
     */
    public function notClosed()
    {
        return $this->andWhere(['closed_at' => null]);
    }
}