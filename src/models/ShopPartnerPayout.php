<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 */

namespace skeeks\cms\shop\models;

use skeeks\cms\base\ActiveRecord;
use skeeks\cms\behaviors\CmsLogBehavior;
use skeeks\cms\models\CmsSite;
use skeeks\cms\models\CmsUser;
use skeeks\cms\models\CmsWebNotify;
use skeeks\cms\models\behaviors\traits\HasLogTrait;
use skeeks\cms\shop\helpers\PartnerProgramHelper;
use skeeks\cms\shop\models\queries\ShopPartnerPayoutQuery;
use yii\base\Exception;
use yii\helpers\ArrayHelper;

/**
 * A partner request to exchange site bonuses for a monetary payout.
 *
 * @property int $id
 * @property int $cms_site_id
 * @property int $cms_user_id Partner
 * @property float $value
 * @property string $requisites
 * @property string $status
 * @property string|null $reject_reason
 * @property string|null $manager_comment
 * @property int|null $shop_bonus_transaction_id
 * @property int|null $paid_at
 * @property int $lock_version
 *
 * @property ShopBonusTransaction|null $bonusTransaction
 * @property string $statusName
 * @property bool $isWrittenOff
 */
class ShopPartnerPayout extends ActiveRecord
{
    use HasLogTrait;

    const STATUS_NEW = 'new';
    const STATUS_PAID = 'paid';
    const STATUS_REJECTED = 'rejected';

    public static function tableName()
    {
        return '{{%shop_partner_payout}}';
    }

    public static function find()
    {
        return new ShopPartnerPayoutQuery(get_called_class());
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_NEW => 'Новая',
            self::STATUS_PAID => 'Успешная',
            self::STATUS_REJECTED => 'Отменена',
        ];
    }

    public static function pendingStatuses(): array
    {
        return [self::STATUS_NEW];
    }

    public static function statusCssClass($status): string
    {
        if ($status === self::STATUS_PAID) {
            return 'sx-status sx-status--success';
        }
        if ($status === self::STATUS_REJECTED) {
            return 'sx-status sx-status--danger';
        }
        return 'sx-status';
    }

    public function optimisticLock()
    {
        return 'lock_version';
    }

    public function transactions()
    {
        return [self::SCENARIO_DEFAULT => self::OP_INSERT | self::OP_UPDATE];
    }

    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            CmsLogBehavior::class => [
                'class' => CmsLogBehavior::class,
                'relation_map' => [
                    'cms_user_id' => 'cmsUser',
                    'shop_bonus_transaction_id' => 'bonusTransaction',
                ],
                'attribute_value_maps' => [
                    'status' => self::statuses(),
                ],
            ],
        ]);
    }

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['cms_site_id'], 'default', 'value' => static function () {
                return \Yii::$app->skeeks->site ? \Yii::$app->skeeks->site->id : null;
            }],
            [['cms_site_id', 'cms_user_id', 'value', 'requisites'], 'required'],
            [['cms_site_id', 'cms_user_id', 'shop_bonus_transaction_id', 'paid_at', 'lock_version'], 'integer'],
            [['value'], 'number', 'min' => 1],
            [['requisites', 'reject_reason', 'manager_comment'], 'string'],
            [['requisites'], 'filter', 'filter' => 'trim'],
            [['status'], 'in', 'range' => array_keys(self::statuses())],
            [['status'], 'default', 'value' => self::STATUS_NEW],
            [['cms_site_id'], 'exist', 'targetClass' => CmsSite::class, 'targetAttribute' => ['cms_site_id' => 'id']],
            [['cms_user_id'], 'exist', 'targetClass' => CmsUser::class, 'targetAttribute' => ['cms_user_id' => 'id']],
            [['reject_reason'], function ($attribute) {
                if ($this->status === self::STATUS_REJECTED && !trim((string)$this->reject_reason)) {
                    $this->addError($attribute, 'Для отменённой заявки нужно указать причину');
                }
            }, 'skipOnEmpty' => false],
            [['manager_comment'], function ($attribute) {
                if ($this->status === self::STATUS_PAID && !trim((string)$this->manager_comment)) {
                    $this->addError($attribute, 'Для успешной заявки нужно написать сообщение партнёру');
                }
            }, 'skipOnEmpty' => false],
            [['value'], function ($attribute) {
                if (!$this->isNewRecord && $this->isWrittenOff
                    && abs((float)$this->getOldAttribute('value') - (float)$this->value) > 0.001
                ) {
                    $this->addError($attribute, 'Списанную сумму выплаты нельзя изменить');
                }
            }, 'skipOnEmpty' => false],
            [['status'], function ($attribute) {
                $oldStatus = (string)$this->getOldAttribute('status');
                if (!$this->isNewRecord
                    && in_array($oldStatus, [self::STATUS_PAID, self::STATUS_REJECTED], true)
                    && $this->status !== $oldStatus
                ) {
                    $this->addError($attribute, 'Завершённую выплату нельзя вернуть в работу или изменить её результат');
                }
            }],
        ]);
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'cms_site_id' => 'Сайт',
            'cms_user_id' => 'Партнёр',
            'value' => 'Сумма к выводу, бонусов',
            'requisites' => 'Реквизиты для выплаты',
            'status' => 'Статус заявки',
            'reject_reason' => 'Причина отмены',
            'manager_comment' => 'Сообщение партнёру о выплате',
            'shop_bonus_transaction_id' => 'Транзакция списания',
            'paid_at' => 'Выплачена',
        ]);
    }

    public function asText()
    {
        return 'Вывод бонусов №'.$this->id;
    }

    public function getStatusName(): string
    {
        return (string)ArrayHelper::getValue(self::statuses(), $this->status, $this->status);
    }

    public function getIsWrittenOff(): bool
    {
        return (bool)$this->shop_bonus_transaction_id;
    }

    public function getCmsSite()
    {
        return $this->hasOne(CmsSite::class, ['id' => 'cms_site_id']);
    }

    public function getCmsUser()
    {
        return $this->hasOne(CmsUser::class, ['id' => 'cms_user_id']);
    }

    public function getBonusTransaction()
    {
        return $this->hasOne(ShopBonusTransaction::class, ['id' => 'shop_bonus_transaction_id']);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($insert) {
            foreach ($this->availableManagerIds() as $userId) {
                $this->sendWebNotify(
                    $userId,
                    'Новая заявка на вывод бонусов',
                    $this->payoutValueAsText,
                    $this->getManagerViewUrl()
                );
            }
            return;
        }

        if (!array_key_exists('status', $changedAttributes) || $changedAttributes['status'] === $this->status) {
            return;
        }

        if ($this->status === self::STATUS_PAID) {
            $this->sendWebNotify(
                (int)$this->cms_user_id,
                'Выплата бонусов выполнена',
                trim((string)$this->manager_comment),
                $this->getPartnerViewUrl()
            );
        } elseif ($this->status === self::STATUS_REJECTED) {
            $this->sendWebNotify(
                (int)$this->cms_user_id,
                'Заявка на вывод бонусов отменена',
                trim((string)$this->reject_reason),
                $this->getPartnerViewUrl()
            );
        }
    }

    public function notifyPartnerAboutComment(?int $logId = null): void
    {
        $this->sendWebNotify(
            (int)$this->cms_user_id,
            'Новый комментарий по заявке на вывод бонусов',
            null,
            $this->getPartnerViewUrl($logId)
        );
    }

    public function notifyManagersAboutPartnerComment(?int $logId = null): void
    {
        foreach ($this->availableManagerIds() as $userId) {
            $this->sendWebNotify(
                $userId,
                'Новый комментарий партнёра по выплате',
                $this->payoutValueAsText,
                $this->getManagerViewUrl($logId)
            );
        }
    }

    /**
     * A payout is visible only to employees who manage its beneficiary
     * directly or through one of the beneficiary's companies.
     */
    public function availableManagerIds(): array
    {
        $result = [];
        $workers = CmsUser::find()
            ->isWorker()
            ->andWhere([CmsUser::tableName().'.is_active' => 1])
            ->all();
        foreach ($workers as $worker) {
            $workerId = (int)$worker->id;
            if (!\Yii::$app->authManager->checkAccess($workerId, 'shop/admin-partner-payout')) {
                continue;
            }
            if (CmsUser::find()
                ->forManager($worker)
                ->andWhere([CmsUser::tableName().'.id' => (int)$this->cms_user_id])
                ->exists()
            ) {
                $result[] = $workerId;
            }
        }

        return array_values(array_unique($result));
    }

    public function getPartnerViewUrl(?int $logId = null): string
    {
        $urlPrefix = '~upa';
        if (\Yii::$app->has('upa')) {
            $urlPrefix = (string)ArrayHelper::getValue(\Yii::$app->upa->urlRule, 'urlPrefix', $urlPrefix);
        }

        return $this->buildViewUrl($urlPrefix, '/shop/upa-partner-payout/view', $logId);
    }

    public function getManagerViewUrl(?int $logId = null): string
    {
        $urlPrefix = '~sx';
        if (\Yii::$app->has('backendAdmin')) {
            $urlPrefix = (string)ArrayHelper::getValue(\Yii::$app->backendAdmin->urlRule, 'urlPrefix', $urlPrefix);
        }

        return $this->buildViewUrl($urlPrefix, '/shop/admin-partner-payout/view', $logId);
    }

    protected function buildViewUrl(string $urlPrefix, string $route, ?int $logId = null): string
    {
        $baseUrl = \Yii::$app->has('request') && \Yii::$app->request instanceof \yii\web\Request
            ? (string)\Yii::$app->request->baseUrl
            : '';
        $url = rtrim($baseUrl, '/').'/'.trim($urlPrefix, '/').$route
            .'?'.http_build_query(['pk' => (int)$this->id]);
        if ($logId) {
            $url .= '&'.http_build_query(['sx-log-id' => $logId]).'#sx-log-'.$logId;
        }

        return $url;
    }

    protected function sendWebNotify(int $userId, string $name, ?string $comment, string $url): void
    {
        if (!$userId) {
            return;
        }
        $currentUserId = \Yii::$app->has('user') && !\Yii::$app->user->isGuest
            ? (int)\Yii::$app->user->id
            : 0;
        if ($currentUserId === $userId) {
            return;
        }

        $notify = new CmsWebNotify();
        $notify->cms_user_id = $userId;
        $notify->name = $name;
        $notify->comment = $comment;
        $notify->model_code = $this->skeeksModelCode;
        $notify->model_id = (int)$this->id;
        $notify->url = $url;
        $notify->save();
    }

    public function getPayoutValueAsText(): string
    {
        return \Yii::$app->formatter->asDecimal((float)$this->value, 2).' бонусов';
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($this->status !== self::STATUS_REJECTED) {
            $this->reject_reason = null;
        }
        if ($this->status !== self::STATUS_PAID) {
            $this->manager_comment = null;
        }

        if (in_array($this->status, self::pendingStatuses(), true)) {
            PartnerProgramHelper::lockUser(static::getDb(), $this->cms_user_id);
            $available = PartnerProgramHelper::available(
                $this->cms_user_id,
                $this->cms_site_id,
                $this->isNewRecord ? null : $this->id
            );
            if ((float)$this->value > $available) {
                $this->addError(
                    'value',
                    'Доступно к выводу только '.\Yii::$app->formatter->asDecimal($available, 2).' бонусов'
                );
                return false;
            }
        }

        if ($this->status === self::STATUS_PAID && !$this->isWrittenOff) {
            PartnerProgramHelper::lockUser(static::getDb(), $this->cms_user_id);
            $balance = PartnerProgramHelper::balance($this->cms_user_id, $this->cms_site_id);
            if ((float)$this->value > $balance) {
                $this->addError(
                    'value',
                    'На балансе партнёра только '.\Yii::$app->formatter->asDecimal($balance, 2).' бонусов'
                );
                return false;
            }

            $transaction = new ShopBonusTransaction();
            $transaction->cms_site_id = $this->cms_site_id;
            $transaction->cms_user_id = $this->cms_user_id;
            $transaction->is_debit = 1;
            $transaction->value = (float)$this->value;
            $transaction->comment = 'Партнёрская программа: выплата бонусов';

            if (!$transaction->save()) {
                throw new Exception('Не удалось списать бонусы: '.print_r($transaction->errors, true));
            }

            $this->shop_bonus_transaction_id = $transaction->id;
            $this->paid_at = $this->paid_at ?: time();
        }

        return true;
    }
}
