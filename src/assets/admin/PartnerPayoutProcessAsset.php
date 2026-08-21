<?php

namespace skeeks\cms\shop\assets\admin;

use skeeks\cms\backend\assets\BackendUiAsset;
use skeeks\cms\base\AssetBundle;

class PartnerPayoutProcessAsset extends AssetBundle
{
    public $sourcePath = '@skeeks/cms/shop/assets/admin/src';
    public $js = ['partner-payout-process.js'];
    public $depends = [BackendUiAsset::class];
}
