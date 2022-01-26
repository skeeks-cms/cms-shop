<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link https://skeeks.com/
 * @copyright (c) 2010 SkeekS
 * @date 11.03.2017
 */

namespace skeeks\cms\shop\store;

use skeeks\assets\unify\base\UnifyIconSimpleLineAsset;
use skeeks\cms\admin\AdminComponent;
use skeeks\cms\backend\BackendComponent;
use skeeks\cms\rbac\CmsManager;
use yii\web\ForbiddenHttpException;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class StoreBackendComponent extends BackendComponent
//class StoreBackendComponent extends AdminComponent
{
    /**
     * @var string
     */
    public $controllerPrefix = "store";

    /**
     * @var array
     */
    public $urlRule = [
        'urlPrefix' => '~store',
        'class' => StoreUrlRule::class,
    ];

    public $defaultRoute = '/shop/store-product';


    protected function _run()
    {
        $theme = new \skeeks\cms\themes\unify\admin\UnifyThemeAdmin();
        $theme->pathMap = [
            '@app/views' => [
                '@skeeks/cms/shop/store/views',
                '@skeeks/cms/admin/views',
                '@skeeks/cms/themes/unify/admin/views',
            ],
        ];

        $theme->logoHref = \yii\helpers\Url::to([$this->defaultRoute]);
        if (\Yii::$app->shop->backendShopStore) {
            $theme->logoTitle = "";
            if (\Yii::$app->shop->backendShopStore->cmsImage) {
                $theme->logoSrc = \Yii::$app->shop->backendShopStore->cmsImage->src;
            }
        }

        UnifyIconSimpleLineAsset::register(\Yii::$app->view);
        \skeeks\cms\themes\unify\admin\UnifyThemeAdmin::initBeforeRender();
        \Yii::$app->view->theme = $theme;

        $cmsManager = \Yii::$app->authManager;
        //$cmsManager = new CmsManager();
        //$cmsManager->cmsSite = \Yii::$app->shop->backendShopStore->cmsSite;
        if (!$cmsManager->checkAccess(\Yii::$app->user->id, "shop/admin-shop-store-supplier")) {
            throw new ForbiddenHttpException("Нет доступа");
        }
    }
}