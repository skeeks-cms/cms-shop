<?php
namespace skeeks\cms\shop\gpd;

use skeeks\cms\models\CmsCountry;
use skeeks\cms\measure\models\CmsMeasure;

/** Stable dictionaries: insert missing codes only, never overwrite local records. */
final class StableReferenceSync
{
    private $transport;private $image;private $checkpoint;private $lists=[];
    public function __construct(CatalogTransportInterface $transport,callable $image,callable $checkpoint)
    {$this->transport=$transport;$this->image=$image;$this->checkpoint=$checkpoint;}
    public static function forSite(int $site,ShopCatalogWriter $media,callable $checkpoint): self
    {
        $state=\Yii::$app->gpdReceiver->receiverForSite($site)->state();
        $api=\Yii::$app->get(\Yii::$app->gpdReceiver->apiComponent);
        return new self(new CatalogTransport($state['source_url'],(string)$api->api_key,null,'dictionaries'),static fn($data)=>$media->image($data,true),$checkpoint);
    }
    private function definition(string $kind): array
    {
        if($kind==='countries')return [CmsCountry::class,'alpha2'];
        if($kind==='measures')return [CmsMeasure::class,'code'];
        throw new ProtocolException('invalid_dictionary');
    }
    public function rows(string $kind): array
    {
        [, $key]=$this->definition($kind);
        if(!isset($this->lists[$kind])) {
            ($this->checkpoint)();$rows=$this->transport->request('GET',$kind);
            if(array_values($rows)!==$rows||count($rows)>10000)throw new ProtocolException('invalid_dictionary');
            $seen=[];
            foreach($rows as $row) {
                if(!is_array($row)||!is_scalar($row[$key]??null)||trim((string)$row[$key])==='')throw new ProtocolException('invalid_dictionary_code');
                $code=trim((string)$row[$key]);
                if(isset($seen[$code]))throw new ProtocolException('duplicate_dictionary_code');
                $seen[$code]=true;
            }
            usort($rows,static fn($a,$b)=>strcmp(trim((string)$a[$key]),trim((string)$b[$key])));
            $this->lists[$kind]=$rows;
        }
        return $this->lists[$kind];
    }
    public function apply(string $kind,array $row): string
    {
        [$class,$key]=$this->definition($kind);$code=trim((string)($row[$key]??''));
        if($code==='')throw new ProtocolException('invalid_dictionary_code');
        ($this->checkpoint)();
        if($class::find()->where([$key=>$code])->exists())return 'existing';
        // Media must be prepared before starting the destination transaction.
        $image=$kind==='countries'?($this->image)($row['image']??null):null;
        return \Yii::$app->db->transaction(function()use($class,$key,$code,$row,$kind,$image){
            if($class::find()->where([$key=>$code])->exists())return 'existing';
            $model=new $class();
            $fields=$kind==='countries'?['alpha2','alpha3','iso','phone_code','domain','name']:['code','name','symbol'];
            foreach($fields as $field)$model->$field=trim((string)($row[$field]??''));
            if($kind==='countries')$model->flag_image_id=$image;
            if(!$model->save())throw new \RuntimeException('Не удалось добавить '.$kind.' #'.$code.': '.json_encode($model->errors,JSON_UNESCAPED_UNICODE));
            return 'created';
        });
    }
    public function ensure(string $kind,string $code): void
    {
        $code=trim($code);if($code==='')return;
        [$class,$key]=$this->definition($kind);
        if($class::find()->where([$key=>$code])->exists())return;
        foreach($this->rows($kind) as $row)if(trim((string)$row[$key])===$code){$this->apply($kind,$row);return;}
        throw new ReferencePendingException('Ожидается код '.$code.' в справочнике '.$kind.' GPD v2.');
    }
}
