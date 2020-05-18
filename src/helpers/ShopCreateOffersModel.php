<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

/* @var $this yii\web\View */

namespace skeeks\cms\shop\helpers;

use skeeks\cms\base\DynamicModel;
use skeeks\cms\models\CmsContentElement;
use skeeks\cms\models\CmsContentProperty;
use skeeks\cms\shop\models\ShopCmsContentElement;
use skeeks\cms\shop\models\ShopCmsContentProperty;
use skeeks\cms\shop\models\ShopProduct;
use yii\base\Exception;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class ShopCreateOffersModel extends DynamicModel
{
    public $formName = 'createOffers';
    public $formKey = 'sx-createOffers';

    /**
     * @var null|ShopProduct
     */
    public $shopProduct = null;

    /**
     * @var CmsContentProperty[]
     */
    public $availableProperties = [];

    public function init()
    {
        if (!$this->shopProduct) {
            throw new InvalidConfigException("Не указан объект товара!");
        }

        if (!$this->shopProduct->isOffersProduct) {
            throw new InvalidConfigException("Товар должен быть с предложениями!");
        }

        $rpm = $this->shopProduct->cmsContentElement->relatedPropertiesModel;
        $rpm->initAllProperties();

        foreach ($rpm->toArray() as $code => $value) {
            if ($property = ShopCmsContentProperty::findCmsContentProperties()->andWhere(['code' => $code])->one()) {
                $this->defineAttribute($code);
                $this->addRule($code, 'safe');
                $this->availableProperties[$code] = $property;
            }
        };
    }

    public function createOffers()
    {
        $offersForCreate = [];

        if (!$this->toArray()) {
            return true;
        }

        $values = $this->toArray();
        foreach ($values as $code => $value)
        {
            if (!$value) {
                unset($values[$code]);
            }
        }
        if (!$values) {
            return true;
        }

        $valueKeys = array_keys($values);
        $offers = $this->combinations($values);
        $result = [];
        foreach ($offers as $offer)
        {
            $newOfferData = [];
            foreach ($offer as $k => $v)
            {
                $newOfferData[$valueKeys[$k]] = $v;
            }
            $result[] = $newOfferData;
        }

        //$result - набор предложений к созданию

        $spParent = $this->shopProduct;
        $cceParent = $spParent->cmsContentElement;
        foreach ($result as $rpData)
        {
            try {
                $t = \Yii::$app->db->beginTransaction();

                $cce = new ShopCmsContentElement();
                $cce->name = $cceParent->name;
                $cce->tree_id = $cceParent->tree_id;
                $cce->content_id = $cceParent->content_id;
                if (!$cce->save()) {
                    throw new Exception(print_r($cce->errors, true));
                }

                $sp = new ShopProduct();
                $sp->id = $cce->id;
                $sp->offers_pid = $spParent->id;
                $sp->product_type = ShopProduct::TYPE_OFFER;

                if (!$sp->save()) {
                    throw new Exception(print_r($sp->errors, true));
                }

                $rpm = $cce->relatedPropertiesModel;


                foreach ($rpData as $keyRRP => $valueRRP)
                {
                    $rpm->setAttribute($keyRRP, $valueRRP);
                }

                if (!$rpm->save()) {
                    throw new Exception(print_r($rpm->errors, true));
                }

                $t->commit();
            } catch (\Exception $e) {
                $t->rollBack();
                throw $e;
            }


        }
    }


    /**
     * @see https://maaaks1.livejournal.com/186539.html
     *
     * @param      $arrays
     * @param int  $N
     * @param bool $count
     * @param bool $weight
     * @return array
     */
    public function combinations($arrays, $N = -1, $count = FALSE, $weight = FALSE)
    {
        /*
            Делает примерно то, о чём написано, например, здесь:
            http://www.sql.ru/Forum/actualthread.aspx?tid=725312
            Только мне было лень вникать в чужой код, и я написал свой :)
        */

        if ($N == -1) {
            // Функция запущена в первый раз и запущена "снаружи", а не из самой себя.

            $arrays = array_values($arrays);
            $count = count($arrays);
            $weight = array_fill(-1, $count + 1, 1);
            $Q = 1;

            // Подсчитываем:
            // $Q - количество возможных комбинаций,
            // $weight - массив "весов" разрядов.
            foreach ($arrays as $i => $array) {
                $size = count($array);
                $Q = $Q * $size;
                $weight[$i] = $weight[$i - 1] * $size;
            }

            $result = [];
            for ($n = 0; $n < $Q; $n++)
                $result[] = $this->combinations($arrays, $n, $count, $weight);

            return $result;
        } else {
            // Дано конкретное число, надо его "преобразовать" в комбинацию.
            // Чтобы не переспрашивать функцию count() обо всём каждый раз, нам уже даны:
            // $count - общее количество массивов, т.е. count($arrays),
            // $weight - "вес" одной единицы "разряда", с учётом веса предыдущих разрядов.

            // Заготавливаем нулевой массив состояний
            $SostArr = array_fill(0, $count, 0);

            $oldN = $N;

            // Идём по радрядам начиная с наибольшего
            for ($i = $count - 1; $i >= 0; $i--) {
                // Поступаем как с числами в позиционных системах счисления,
                // то есть максимально заполняем наибольшие значения
                // и по остаточному принципу - наименьшие.
                // Число в i-ом разряде выражается как количество весов (i-1)0ых разрядов...
                // Да-да, я очень криво объясняю, просто поверьте на слово.
                // Вообще, эти две строки можно проверить и самостоятельно... =)
                $SostArr[$i] = floor($N / $weight[$i - 1]);
                $N = $N - $SostArr[$i] * $weight[$i - 1];
            }

            // Наконец, переводим "состояния" в реальные значения
            $result = [];
            for ($i = 0; $i < $count; $i++)
                $result[$i] = $arrays[$i][$SostArr[$i]];

            #echo "<br>$oldN: {" . implode(',',$result) . '}';
            return $result;
        }

    }
}