<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\models;

use skeeks\cms\models\behaviors\Implode;
use skeeks\cms\models\CmsSite;
use skeeks\cms\models\CmsTree;
use yii\helpers\ArrayHelper;

/**
 * @property int         $id
 * @property string|null $description
 * @property string|null $description_internal
 * @property int         $is_receiver Сайт получает товары от поставщиков?
 * @property int|null    $catalog_cms_tree_id Главный раздел для товаров
 * @property string|null $notify_emails Email адреса для уведомлений о заказах
 * @property int         $is_show_cart Показывать корзину?
 * @property int         $is_show_prices Показывать цены?
 * @property int         $is_show_product_no_price Показывать товары с нулевыми ценами?
 * @property int         $is_show_button_no_price Показывать кнопку «добавить в корзину» для товаров с нулевыми ценами?
 * @property int         $is_show_product_only_quantity Показывать товары только в наличии на сайте?
 * @property int         $is_show_quantity_product Показывать оставшееся количество товаров на складе?
 * @property string|null $show_filter_property_ids Какие фильтры разрешено показывать на сайте?
 * @property string|null $open_filter_property_ids Какие фильтры по умолчанию открыты на сайте?
 * @property int         $is_allow_edit_products Разрешено редактировать и добавлять товары?
 *
 * @property CmsSite     $cmsSite
 * @property CmsTree     $catalogCmsTree
 *
 * @property string[]    $notifyEmails
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class ShopSite extends \skeeks\cms\base\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%shop_site}}';
    }

    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            Implode::class => [
                'class'  => Implode::class,
                'fields' => [
                    'show_filter_property_ids',
                    'open_filter_property_ids',
                ],
            ],
        ]);
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['is_receiver'], 'integer'],

            [['catalog_cms_tree_id'], 'integer'],
            [['catalog_cms_tree_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsTree::className(), 'targetAttribute' => ['catalog_cms_tree_id' => 'id']],

            [['description'], 'string'],
            [['description_internal'], 'string'],

            [
                'id',
                'default',
                'value' => function () {
                    if (\Yii::$app->skeeks->site) {
                        return \Yii::$app->skeeks->site->id;
                    }
                },
            ],

            [
                'catalog_cms_tree_id',
                function () {
                    if ($this->catalog_cms_tree_id) {
                        if ($this->cmsSite->id != $this->catalogCmsTree->cms_site_id) {
                            $this->addError("catalog_cms_tree_id", "Раздел каталога должен лежать в этом же сайте");
                        }
                    }

                },
            ],

            [['show_filter_property_ids'], 'safe'],
            [['open_filter_property_ids'], 'safe'],

            ['notify_emails', 'string'],
            [
                [
                    'is_show_product_no_price',
                    'is_show_button_no_price',
                    'is_show_quantity_product',
                    'is_show_cart',
                    'is_show_prices',
                ],
                'boolean',
            ],
            [
                [
                    'is_show_product_only_quantity',
                ],
                'integer',
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [

            'description'          => "Описание",
            'description_internal' => "Скрытое описание",
            'is_receiver'          => "Разрешено получать товары от постащиков",
            'catalog_cms_tree_id'  => "Основной раздел для товаров",

            'notify_emails'                 => \Yii::t('skeeks/shop/app', 'Email notification address'),
            'is_show_product_no_price'      => "Показывать товары с нулевыми ценами?",
            'is_show_button_no_price'       => "Показывать кнопку «добавить в корзину» для товаров с нулевыми ценами?",
            'is_show_product_only_quantity' => "Учет наличия",
            'show_filter_property_ids'      => "Какие фильтры разрешено показывать на сайте?",
            'open_filter_property_ids'      => "Какие фильтры по умолчанию открыты на сайте?",
            'is_show_quantity_product'      => "Показывать оставшееся количество товаров на складе?",
            'is_show_cart'                  => "Показывать корзину на сайте?",
            'is_show_prices'                  => "Показывать цены на сайте?",
        ]);
    }

    /**
     * @return array
     */
    public function attributeHints()
    {
        return ArrayHelper::merge(parent::attributeHints(), [
            'is_receiver'         => "Если эта опция включена то на сайте появляется раздел «Поставщики»",
            'catalog_cms_tree_id' => "Основной раздел сайта, в который будут попадать товары по умолчанию, если раздел для них не задан.",

            'notify_emails'                 => \Yii::t('skeeks/shop/app',
                'Enter email addresses, separated by commas, they will come on new orders information'),
            'is_show_product_no_price'      => "Если выбрано «да», то товары с нулевой ценой будут показывать на сайте",
            'is_show_button_no_price'       => "Если у товара цена 0, и выбрано да, то кнопка «добавить в корзину», будет показываться рядом с товаром",
            'show_filter_property_ids'      => "Если не указано, то показываются все фильтры доступные в разделе. Если выбраны фильтры, то в разделе будут показаны только те фильтры по которым есть товары.",
            'is_show_product_only_quantity' => "Выберите как товары будут показываться на сайте по умолчанию",
            'is_show_quantity_product'      => "Если выбрано «да», то на странице товара будет отображено количество товаров, указанное в админке. Если «нет», наличие отображаться не будет.",
            'is_show_cart'      => "Если выбрано «да», то на сайте будет показана корзина, а возле товаров кнопка «в корзину»<br />
Если выбрано «нет», то фактически на сайте будет отключена корзина
",
            'is_show_prices'      => "Если выбрано «нет», то на сайте у товаров не будут отображаться цены
",

        ]);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCmsSite()
    {
        $class = \Yii::$app->skeeks->siteClass;
        return $this->hasOne($class, ['id' => 'id']);
    }


    /**
     * @return string
     */
    public function asText()
    {
        return $this->cmsSite->asText;
    }

    /**
     * Gets query for [[CatalogCmsTree]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCatalogCmsTree()
    {
        return $this->hasOne(CmsTree::className(), ['id' => 'catalog_cms_tree_id']);
    }


    /**
     * @return array
     */
    public function getNotifyEmails()
    {
        $emailsAll = [];
        if ($this->notify_emails) {
            $emails = explode(",", $this->notify_emails);

            foreach ($emails as $email) {
                $emailsAll[] = trim($email);
            }
        }

        return $emailsAll;
    }

}