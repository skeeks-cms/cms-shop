<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 21.09.2015
 */

namespace skeeks\cms\shop\controllers;

use Imagine\Image\Box;
use Imagine\Image\Palette\RGB;
use Imagine\Image\Point;
use Imagine\Imagick\Font;
use skeeks\cms\base\Controller;
use skeeks\cms\helpers\FileHelper;
use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\shop\assets\RuntimeAsset;
use skeeks\cms\shop\models\ShopBasket;
use skeeks\cms\shop\models\ShopDiscountCoupon;
use skeeks\cms\shop\models\ShopOrder2discountCoupon;
use skeeks\cms\shop\models\ShopOrderItem;
use skeeks\cms\shop\models\ShopProduct;
use skeeks\imagine\Image;
use yii\base\Exception;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;

/**
 * Class CartController
 * @package skeeks\cms\shop\controllers
 */
class CouponController extends Controller
{
    public $defaultAction = 'view';

    public function actionView()
    {
        $shopDiscountCoupon = null;
        if ($couponCode = \Yii::$app->request->get("c")) {
            $shopDiscountCoupon = ShopDiscountCoupon::find()->where(['coupon' => $couponCode])->one();
        }

        if (!$shopDiscountCoupon) {
            throw new NotFoundHttpException("Купон не существует");
        }

        \Yii::$app->view->title = $shopDiscountCoupon->coupon . ' - купон на скидку';

        \Yii::$app->view->registerMetaTag([
            'property' => 'og:title',
            'content'  => $shopDiscountCoupon->coupon . ' - купон на скидку',
        ], 'og:title');

        \Yii::$app->view->registerMetaTag([
            'property' => 'description',
            'content'  => "Дополнительная скидка в магазине: " . \Yii::$app->name,
        ], 'description');

        \Yii::$app->view->registerMetaTag([
            'property' => 'og:description',
            'content'  => "Дополнительная скидка в магазине: " . \Yii::$app->name,
        ], 'og:description');



        $qrCodeBase64 = (new \chillerlan\QRCode\QRCode())->render($shopDiscountCoupon->coupon);




        $dir = \Yii::getAlias("@runtime/shop-coupon");
        $couponUrl = \Yii::getAlias("@runtime/shop-coupon/{$shopDiscountCoupon}.png");
        if (!file_exists($couponUrl)) {
            FileHelper::createDirectory($dir);

            $palette = new RGB();
            $color = $palette->color("#FFF");
            $fileSrc = \Yii::getAlias("@skeeks/cms/shop/assets/fonts/Open_Sans/OpenSans-Bold.ttf");
            //var_dump(file_exists($fileSrc));die;
            $font = Image::getImagine()->font($fileSrc, 50, $color);

            $palette = new RGB();
            $position = new Point(180, 125);

            $image = Image::getImagine()->create(new Box(600, 300), $palette->color('#000'));
            $image->draw()
                ->text($shopDiscountCoupon->coupon, $font, $position);

            $image->save($couponUrl);
        }

        $couponPublicImageUrl = RuntimeAsset::getAssetUrl("shop-coupon/{$shopDiscountCoupon}.png");
        if (Url::isRelative($couponPublicImageUrl)){
            $couponPublicImageUrl = \Yii::$app->urlManager->hostInfo . $couponPublicImageUrl;
        }

        \Yii::$app->view->registerMetaTag([
            'property' => 'og:image',
            'content'  => $couponPublicImageUrl,
        ], 'og:image');

        return $this->render($this->action->id, [
            'shopDiscountCoupon' => $shopDiscountCoupon,
            'qrCodeBase64' => $qrCodeBase64,
            'couponPublicImageUrl' => $couponPublicImageUrl
        ]);
    }

}