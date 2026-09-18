<?php
namespace skeeks\cms\shop\components;

use skeeks\cms\base\Component;
use skeeks\cms\models\CmsSite;
use skeeks\yii2\form\fields\BoolField;
use skeeks\yii2\form\fields\SelectField;
use skeeks\yii2\form\fields\FieldSet;

/** Site policy only. Protocol cursors and credentials are not editable settings. */
class GpdComponent extends Component
{
    public $enabled = 0;
    public $createProducts = 1;
    public $updateProducts = 1;
    /** @deprecated Compatibility with saved settings; media policy belongs to the writer and existing API component. */
    public $imageMode = 'link';
    public $excludedAction = 'deactivate';
    public $reactivateProducts = 1;
    public $newProductsActive = 1;

    public static function descriptorConfig()
    {
        return array_merge(parent::descriptorConfig(), ['name'=>'GPD — синхронизация каталога','image'=>[\skeeks\cms\assets\CmsAsset::class,'images/icons/admin-menu/export-import.svg'],'description'=>'Обновление товаров, изображения и обработка исключений из API']);
    }
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['enabled','createProducts','updateProducts','reactivateProducts','newProductsActive'],'boolean'],
            ['imageMode','in','range'=>['link','download']],
            ['excludedAction','in','range'=>['keep','deactivate','delete']],
        ]);
    }
    public function attributeLabels()
    {
        return ['enabled'=>'Синхронизация включена','createProducts'=>'Создавать новые товары','updateProducts'=>'Обновлять существующие товары',
            'imageMode'=>'Изображения','excludedAction'=>'Товар исключён из API','reactivateProducts'=>'Восстанавливать активность при возвращении в API','newProductsActive'=>'Создавать новые товары активными'];
    }
    public function attributeHints()
    {
        return ['enabled'=>'Управляет новыми заданиями GPD. Курсор сохраняется при отключении. Старые команды имеют отдельное расписание.',
            'updateProducts'=>'Запрет синхронизации в самом товаре сохраняет его название, описание, характеристики и изображения.',
            'excludedAction'=>'Только подтверждённое исключение из API. Товары с другими источниками сохраняются. При невозможности удаления товар деактивируется.',
            'reactivateProducts'=>'Только если товар ранее деактивировала GPD. Вручную отключённые товары не включаются.'];
    }
    public function getConfigFormFields()
    {
        $bool=static fn()=>['class'=>BoolField::class,'allowNull'=>false];
        return [
            'catalog'=>['class'=>FieldSet::class,'name'=>'Синхронизация','fields'=>['enabled'=>$bool(),'createProducts'=>$bool(),'updateProducts'=>$bool(),'newProductsActive'=>$bool()]],
            'exclusions'=>['class'=>FieldSet::class,'name'=>'Исключённые товары','fields'=>['excludedAction'=>['class'=>SelectField::class,'items'=>['keep'=>'Оставлять без изменений','deactivate'=>'Деактивировать','delete'=>'Удалять, если нет связанных данных']],'reactivateProducts'=>$bool()]],
        ];
    }
    public function forSite(int $id): self
    {
        $site=CmsSite::findOne($id);
        if (!$site) throw new \RuntimeException('Сайт задания GPD не найден.');
        $copy=clone $this;
        $copy->setAttributes($this->callAttributes,false);
        $copy->cmsSite=$site; $copy->cmsUser=null;
        $copy->overridePath=[self::OVERRIDE_DEFAULT,self::OVERRIDE_SITE];
        $copy->setAttributes($copy->getSettings(false));
        if (!$copy->validate()) throw new \RuntimeException('Некорректные настройки GPD.');
        return $copy;
    }
}
