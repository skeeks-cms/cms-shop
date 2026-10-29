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
use skeeks\cms\shop\models\ShopBill;
use skeeks\cms\shop\models\ShopDocument2bill;

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
            $table = $this->getPrimaryTableName();
            $this->andWhere(ShopBillQuery::clientCondition($user, $table, static function (array $ownerIds) use ($table, $user) {
                // "Своё": a document he or his subordinates made, a document of his deal,
                // or a document of his own bill (issued by him or a bill of his deal).
                return array_merge([
                    [$table.'.created_by' => $ownerIds],
                    [$table.'.id' => ShopDocument2bill::find()->alias('ownDocument2bill')->select('ownDocument2bill.shop_document_id')
                        ->innerJoin(['ownBill' => ShopBill::tableName()], 'ownBill.id = ownDocument2bill.shop_bill_id')
                        ->andWhere(array_merge(['or', ['ownBill.created_by' => $ownerIds]],
                            ShopBillQuery::ownLeadConditions($user, 'ownBill.cms_lead_id', 'shop_bill')))],
                ], ShopBillQuery::ownLeadConditions($user, $table.'.cms_lead_id', 'shop_document'));
            }));
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
