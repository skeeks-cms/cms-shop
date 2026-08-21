<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 */

namespace skeeks\cms\shop\helpers;

use skeeks\cms\shop\models\ShopBonusTransaction;
use skeeks\cms\shop\models\ShopPartnerPayout;
use yii\base\InvalidConfigException;
use yii\db\Connection;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * Site-scoped balance and reservation calculations for the partner program.
 */
class PartnerProgramHelper
{
    public static function balance($userId, $siteId = null): float
    {
        $userId = (int)$userId;
        $siteId = static::resolveSiteId($siteId);
        if (!$userId || !$siteId) {
            return 0.0;
        }

        $row = ShopBonusTransaction::find()
            ->andWhere([
                'cms_user_id' => $userId,
                'cms_site_id' => $siteId,
            ])
            ->select([
                'result' => new Expression('SUM(CASE WHEN is_debit = 1 THEN value * -1 ELSE value END)'),
            ])
            ->asArray()
            ->one();

        return round((float)ArrayHelper::getValue($row, 'result'), 2);
    }

    public static function reserved($userId, $siteId = null, $excludePayoutId = null): float
    {
        $userId = (int)$userId;
        $siteId = static::resolveSiteId($siteId);
        if (!$userId || !$siteId) {
            return 0.0;
        }

        $query = ShopPartnerPayout::find()
            ->andWhere([
                ShopPartnerPayout::tableName().'.cms_user_id' => $userId,
                ShopPartnerPayout::tableName().'.cms_site_id' => $siteId,
                ShopPartnerPayout::tableName().'.status' => ShopPartnerPayout::pendingStatuses(),
            ]);

        if ($excludePayoutId) {
            $query->andWhere(['<>', ShopPartnerPayout::tableName().'.id', (int)$excludePayoutId]);
        }

        $row = $query
            ->select(['result' => new Expression('SUM(value)')])
            ->asArray()
            ->one();

        return round((float)ArrayHelper::getValue($row, 'result'), 2);
    }

    public static function available($userId, $siteId = null, $excludePayoutId = null): float
    {
        return round(
            static::balance($userId, $siteId)
            - static::reserved($userId, $siteId, $excludePayoutId),
            2
        );
    }

    /**
     * Serialize balance-changing and reservation-changing operations per user.
     */
    public static function lockUser(Connection $db, $userId): void
    {
        $transaction = $db->getTransaction();
        if (!$transaction || !$transaction->isActive) {
            throw new InvalidConfigException('Partner balance lock requires an active database transaction.');
        }

        $id = $db->createCommand(
            'SELECT [[id]] FROM {{%cms_user}} WHERE [[id]] = :id FOR UPDATE',
            [':id' => (int)$userId]
        )->queryScalar();

        if (!$id) {
            throw new InvalidConfigException('Partner user was not found.');
        }
    }

    public static function resolveSiteId($siteId = null): ?int
    {
        if ($siteId !== null) {
            return (int)$siteId ?: null;
        }

        $skeeks = \Yii::$app->get('skeeks', false);
        return $skeeks && $skeeks->site ? (int)$skeeks->site->id : null;
    }
}
