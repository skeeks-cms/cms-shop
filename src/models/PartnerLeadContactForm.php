<?php

namespace skeeks\cms\shop\models;

use skeeks\cms\validators\PhoneValidator;
use yii\base\Model;

/**
 * Контактные данные потенциального клиента из кабинета партнёра.
 */
class PartnerLeadContactForm extends Model
{
    public $phone = '';
    public $email = '';

    public function rules()
    {
        return [
            [['phone', 'email'], 'trim'],
            [['phone', 'email'], 'string', 'max' => 255],
            [['phone'], PhoneValidator::class, 'skipOnEmpty' => true],
            [['email'], 'email', 'enableIDN' => true, 'skipOnEmpty' => true],
            [['phone'], 'validateContactRequired'],
        ];
    }

    public function validateContactRequired(string $attribute): void
    {
        if (trim((string)$this->phone) === '' && trim((string)$this->email) === '') {
            $this->addError($attribute, 'Укажите телефон или email клиента.');
        }
    }

    public function attributeLabels()
    {
        return [
            'phone' => 'Телефон',
            'email' => 'Email',
        ];
    }
}
