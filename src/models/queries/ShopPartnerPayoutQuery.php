<?php

namespace skeeks\cms\shop\models\queries;

use skeeks\cms\models\CmsUser;
use skeeks\cms\models\User;
use skeeks\cms\query\CmsActiveQuery;
use skeeks\cms\rbac\CmsManager;
use skeeks\cms\shop\models\ShopPartnerPayout;

class ShopPartnerPayoutQuery extends CmsActiveQuery
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

    public function forClient(User $user = null, $siteId = null)
    {
        $user = $user ?: \Yii::$app->user->identity;
        if (!$user) {
            return $this->andWhere('0=1');
        }

        return $this->cmsSite($siteId)->andWhere([
            ShopPartnerPayout::tableName().'.cms_user_id' => $user->id,
        ]);
    }
}
