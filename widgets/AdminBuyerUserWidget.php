<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 08.10.2015
 */
namespace skeeks\cms\shop\widgets;
use skeeks\cms\mail\helpers\Html;
use skeeks\cms\models\CmsUser;
use skeeks\cms\modules\admin\widgets\AdminImagePreviewWidget;
use yii\base\Widget;

/**
 * Class AdminBuyerUserWidget
 * @package skeeks\cms\shop\widgets
 */
class AdminBuyerUserWidget extends Widget
{
    /**
     * @var CmsUser
     */
    public $user = null;

    /**
     * Подготовка данных для шаблона
     * @return $this
     */
    public function run()
    {
        return (new AdminImagePreviewWidget(['image' => $this->user->image]))->run() . " " . Html::a($this->user->displayName, \skeeks\cms\helpers\UrlHelper::construct(['/shop/admin-buyer-user/update', 'pk' => $this->user->id ])->enableAdmin(), [
            'data-pjax' => 0
        ] );
    }


}
