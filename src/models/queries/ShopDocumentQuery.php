<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 */

namespace skeeks\cms\shop\models\queries;

use skeeks\cms\models\CmsCompany;
use skeeks\cms\models\CmsUser;
use skeeks\cms\models\User;
use skeeks\cms\query\CmsActiveQuery;
use skeeks\cms\rbac\CmsManager;

class ShopDocumentQuery extends CmsActiveQuery
{
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

        if (!$isCanAdmin) {
            $cmsCompanyQuery = CmsCompany::find()->forManager()->select(CmsCompany::tableName().'.id');
            $cmsUserQuery = CmsUser::find()->forManager()->select(CmsUser::tableName().'.id');

            $this->andWhere([
                'or',
                [$this->getPrimaryTableName().'.cms_company_id' => $cmsCompanyQuery],
                [$this->getPrimaryTableName().'.cms_user_id' => $cmsUserQuery],
            ]);
        }

        return $this;
    }

    public function search($word = '')
    {
        $word = trim((string)$word);
        if ($word === '') {
            return $this;
        }

        $table = $this->getPrimaryTableName();
        $this->andWhere([
            'or',
            ['like', $table.'.id', $word],
            ['like', $table.'.number', $word],
            ['like', $table.'.description', $word],
            ['like', $table.'.seller_contractor_name', $word],
            ['like', $table.'.buyer_contractor_name', $word],
            ['like', $table.'.seller_contractor_inn', $word],
            ['like', $table.'.buyer_contractor_inn', $word],
        ]);

        return $this;
    }
}
