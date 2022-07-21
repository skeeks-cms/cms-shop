<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */
namespace skeeks\cms\shop\cloudkassa;

use skeeks\cms\IHasConfigForm;
use skeeks\cms\models\CmsSmsMessage;
use skeeks\cms\models\CmsSmsProvider;
use skeeks\cms\shop\models\ShopOrder;
use skeeks\cms\traits\HasComponentDescriptorTrait;
use skeeks\cms\traits\TConfigForm;
use yii\base\Exception;
use yii\base\Model;
use yii\widgets\ActiveForm;

/**
 * @property Model $checkoutModel
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 */
abstract class CloudkassaHandler extends Model implements IHasConfigForm
{
    use HasComponentDescriptorTrait;
    use TConfigForm;
}