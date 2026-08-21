<?php

namespace skeeks\cms\shop\models\queries;

use skeeks\cms\models\CmsUser;
use skeeks\cms\models\User;
use skeeks\cms\query\CmsActiveQuery;
use skeeks\cms\rbac\CmsManager;

class ShopBonusTransactionQuery extends CmsActiveQuery
{
    public function forManager(User $user = null)
    {
        if ($user === null) {
            $user = \Yii::$app->user->identity;
            $isCanAdmin = \Yii::$app->user->can(CmsManager::PERMISSION_ROLE_ADMIN_ACCESS);
        } else {
            $isCanAdmin = \Yii::$app->authManager->checkAccess(
                $user->id,
                CmsManager::PERMISSION_ROLE_ADMIN_ACCESS
            );
        }

        if (!$user) {
            return $this->andWhere('0=1');
        }
        if ($isCanAdmin) {
            return $this;
        }

        $availableClientIds = CmsUser::find()
            ->forManager($user)
            ->select(CmsUser::tableName().'.id');

        return $this->andWhere([
            $this->getPrimaryTableName().'.cms_user_id' => $availableClientIds,
        ]);
    }
}
