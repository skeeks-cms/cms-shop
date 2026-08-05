<?php

$autoloadCandidates = [
    '/app/vendor/autoload.php',
    dirname(__DIR__).'/vendor/autoload.php',
    dirname(__DIR__, 3).'/autoload.php',
];
foreach ($autoloadCandidates as $autoload) {
    if (is_file($autoload)) {
        require $autoload;
        break;
    }
}

$yiiCandidates = [
    '/app/vendor/yiisoft/yii2/Yii.php',
    dirname(__DIR__).'/vendor/yiisoft/yii2/Yii.php',
    dirname(__DIR__, 3).'/yiisoft/yii2/Yii.php',
];
foreach ($yiiCandidates as $yiiBootstrap) {
    if (is_file($yiiBootstrap)) {
        require_once $yiiBootstrap;
        break;
    }
}

use skeeks\cms\backend\assets\BackendUiAsset;
use skeeks\cms\shop\assets\admin\AdminShopGalleryAsset;

function galleryExpect($condition, $message)
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$asset = (new ReflectionClass(AdminShopGalleryAsset::class))->newInstanceWithoutConstructor();
galleryExpect($asset->depends === [BackendUiAsset::class], 'Gallery must depend only on BackendUiAsset.');

$views = [
    dirname(__DIR__).'/src/views/admin-cms-content-element/view.php',
    dirname(__DIR__).'/src/views/admin-shop-collection/view.php',
];
foreach ($views as $view) {
    $source = file_get_contents($view);
    galleryExpect(strpos($source, 'ShopAdminGallery::widget') !== false, basename($view).' does not use ShopAdminGallery.');
    galleryExpect(strpos($source, 'UnifyThemeStickAsset') === false, basename($view).' still loads frontend Unify.');
    galleryExpect(strpos($source, 'js-carousel') === false, basename($view).' still emits the legacy carousel contract.');
}

echo "Admin shop gallery asset contract: OK\n";
