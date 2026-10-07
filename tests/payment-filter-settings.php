<?php
/** Read-only regression: php payment-filter-settings.php /path/to/web-bootstrap.php [showing_id] */
require $argv[1];

use skeeks\cms\backend\models\BackendShowing;
use skeeks\cms\backend\widgets\FiltersWidget;
use skeeks\cms\models\CmsUser;
use skeeks\cms\queryfilters\QueryFiltersEvent;
use skeeks\cms\shop\controllers\AdminPaymentController;
use skeeks\cms\shop\models\ShopPayment;
use skeeks\cms\shop\widgets\PaymentFiltersWidget;
use skeeks\yii2\config\storages\ConfigDbModelStorage;
use yii\data\ActiveDataProvider;

class PaymentSettingsFixtureStorage extends ConfigDbModelStorage
{
    public $fixture;
    public function getModel() { return $this->fixture; }
}

$checks = 0;
function check($ok, $message) {
    if (!$ok) { throw new RuntimeException($message); }
    ++$GLOBALS['checks'];
}
function widget($records, $explicitClass = null) {
    $controller = new AdminPaymentController('admin-payment', Yii::$app->getModule('shop'));
    $config = $controller->actions()['index']['filters'];
    $config['dataProvider'] = new ActiveDataProvider(['query' => ShopPayment::find()->forManager()]);
    $config['configBehaviorData'] = [
        'configKey' => 'shop/admin-payment/index',
        'configStorage' => [
            'class' => PaymentSettingsFixtureStorage::class,
            'fixture' => new BackendShowing(['config_jsoned' => $records]),
            'attribute' => 'config_jsoned',
        ],
    ];
    if ($explicitClass !== null) { $config['configBehaviorData']['configClassName'] = $explicitClass; }
    return Yii::createObject($config);
}

Yii::$app->user->setIdentity(CmsUser::findOne(1));
$key = 'shop/admin-payment/index';
$legacy = [$key => ['visibleFilters' => ['q', 'additional'], 'filterValues' => ['additional' => 'need_to_bill']]];
$newer = [$key => ['visibleFilters' => ['q', 'additional', 'date'], 'filterValues' => ['additional' => 'need_to_bill', 'date' => 'test']]];
foreach ([
    [[], FiltersWidget::class, null],
    [[FiltersWidget::class => $legacy], FiltersWidget::class, 'need_to_bill'],
    [[PaymentFiltersWidget::class => $newer], PaymentFiltersWidget::class, 'need_to_bill'],
    [[FiltersWidget::class => $legacy, PaymentFiltersWidget::class => $newer], FiltersWidget::class, 'need_to_bill'],
] as [$records, $expectedClass, $value]) {
    $filters = widget($records);
    check($filters->configClassName === $expectedClass, 'Compatible storage namespace');
    check($filters->filtersModel->additional === $value, 'Saved payment condition restored');
    check(in_array('available_for_user_id', $filters->visibleFilters, true), 'Administrator retains employee filter');
    check($filters->callAttributes['configBehaviorData']['configClassName'] === $expectedClass, 'Editor retains chosen storage namespace');
}
$filters = widget([PaymentFiltersWidget::class => $newer]);
check($filters->filtersModel->date === 'test', 'Subclass-only settings remain intact');
$filters = widget(['custom' => $legacy], 'custom');
check($filters->configClassName === 'custom' && $filters->filtersModel->additional === 'need_to_bill', 'Explicit namespace respected');

if (!empty($argv[2])) {
    $record = BackendShowing::findOne((int)$argv[2]);
    check($record !== null, 'Requested local representation exists');
    $filters = widget($record->config_jsoned);
    check($filters->filtersModel->additional === 'need_to_bill', 'Real Разобрать representation restores condition');
    $field = $filters->filtersModel->builderFields()['additional'];
    $field['on apply'](new QueryFiltersEvent(['field' => (object)['value' => $filters->filtersModel->additional], 'dataProvider' => $filters->dataProvider]));
    $query = $filters->dataProvider->query;
    check(!$query->andWhere(['or', ['is not', 'bills.id', null], ['is not', 'deals.id', null]])->exists(), 'Restored filter excludes allocated payments');
}

Yii::$app->user->setIdentity(CmsUser::findOne(110));
$filters = widget([FiltersWidget::class => $legacy]);
check($filters->filtersModel->additional === 'need_to_bill', 'Worker retains saved condition');
check(!in_array('available_for_user_id', $filters->visibleFilters, true), 'Worker has no privileged employee selector');
echo 'PASS '.$checks." payment filter settings checks\n";
