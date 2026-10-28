<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\models\queries;

use skeeks\cms\models\CmsCompany;
use skeeks\cms\models\CmsUser;
use skeeks\cms\models\User;
use skeeks\cms\query\CmsActiveQuery;
use skeeks\cms\rbac\CmsManager;
use skeeks\cms\shop\models\ShopBill;
use skeeks\cms\shop\models\ShopBill2payment;
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class ShopPaymentQuery extends CmsActiveQuery
{
    /**
     * Поиск компаний доступных пользователю
     *
     * @param User|null $user
     * @return $this
     */
    public function forManager(User $user = null)
    {
        if ($user === null) {
            $user = \Yii::$app->user->identity;
            $isCanAdmin = \Yii::$app->user->can(CmsManager::PERMISSION_ROLE_ADMIN_ACCESS);
        } else {
            $isCanAdmin = \Yii::$app->authManager->checkAccess($user->id, CmsManager::PERMISSION_ROLE_ADMIN_ACCESS);
        }

        if (!$user) {
            return $this;
        }


        //Если нет прав админа, нужно показать только доступные сделки
        if (!$isCanAdmin) {

            $table = $this->getPrimaryTableName();
            $this->andWhere(ShopBillQuery::clientCondition($user, $table, static function (array $ownerIds) use ($table) {
                // "Своё": a payment he or his subordinates registered, or a payment of his own bill.
                return [
                    [$table.'.created_by' => $ownerIds],
                    [$table.'.id' => ShopBill2payment::find()->alias('ownBill2payment')->select('ownBill2payment.shop_payment_id')
                        ->innerJoin(['ownBill' => ShopBill::tableName()], 'ownBill.id = ownBill2payment.shop_bill_id')
                        ->andWhere(['ownBill.created_by' => $ownerIds])],
                ];
            }));
        }

        return $this;
    }
}
