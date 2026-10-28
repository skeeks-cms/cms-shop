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
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class ShopBillQuery extends CmsActiveQuery
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
            $this->andWhere(static::clientCondition($user, $table, static function (array $ownerIds) use ($table) {
                // "Своё": a bill he or his subordinates issued (created_by).
                return [[$table.'.created_by' => $ownerIds]];
            }));
        }

        return $this;
    }

    /**
     * The visibility of a client finance record (bill, payment, document) for
     * an employee: the client seen in full, or "своё" for the level "Свои
     * сделки" (skeeks/cms CrmAccess::clientRecordCondition()). $own gets the
     * ids of the employee and his subordinates and returns OR conditions. With
     * an older skeeks/cms — the former rule: every visible client.
     *
     * @param callable $own function (int[] $ownerIds): array
     */
    public static function clientCondition(User $user, string $table, callable $own): array
    {
        $accessClass = \skeeks\cms\crm\CrmAccess::class;
        if (class_exists($accessClass) && method_exists($accessClass, 'clientRecordCondition')) {
            $access = $accessClass::instance();

            return $access->clientRecordCondition($user, $table, $own($access->ownerIds($user)));
        }

        return [
            'or',
            [$table.'.cms_company_id' => CmsCompany::find()->forManager($user)->select(CmsCompany::tableName().'.id')],
            [$table.'.cms_user_id' => CmsUser::find()->forManager($user)->select(CmsUser::tableName().'.id')],
        ];
    }
}
