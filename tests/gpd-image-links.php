<?php
// Isolated writer regression: storage doubles, no network or site database.
namespace skeeks\cms\shop\components { class GpdComponent { public $imageMode='download'; } }
namespace skeeks\cms\models {
    #[\AllowDynamicProperties]
    class CmsStorageFile {
        public static $rows=[];public $id;public $sx_id;public $external_id;public $cluster_id;public $cluster_file;
        public static function find(){return new ImageQuery();}
        public function save(){if(!$this->id)$this->id=count(self::$rows)+1;self::$rows[$this->id]=$this;return true;}
    }
    class ImageQuery {
        private $conditions=[];
        public function where($v){$this->conditions=$v;return $this;}
        public function andWhere($v){$this->conditions=array_merge($this->conditions,$v);return $this;}
        public function one(){foreach(CmsStorageFile::$rows as $row){foreach($this->conditions as $k=>$v)if($row->$k!=$v)continue 2;return $row;}return null;}
        public function exists(){return $this->one()!==null;}
    }
}
namespace {
class Yii {public static $app;}
Yii::$app=(object)['storage'=>new class { public $uploads=0; public function upload($url){++$this->uploads;$f=new \skeeks\cms\models\CmsStorageFile();$f->cluster_id='local';$f->cluster_file=$url;return $f;} }];
require __DIR__.'/../src/gpd/CatalogWriterInterface.php';
require __DIR__.'/../src/gpd/ShopCatalogWriter.php';
use skeeks\cms\models\CmsStorageFile;
use skeeks\cms\shop\gpd\ShopCatalogWriter;
use skeeks\cms\shop\components\GpdComponent;
$settings=new GpdComponent();
$api=new class {public $is_download_images=false; public function getImageUrl($src){return 'https://fixture.invalid'.$src;}};
$writer=new ShopCatalogWriter(1,$settings,$api,static function(){});
$check=static function($ok,$message){if(!$ok)throw new RuntimeException($message);};
$old=new CmsStorageFile();$old->sx_id=17;$old->cluster_id='local';$old->cluster_file='existing.webp';$old->save();
$id=$writer->image(['id'=>17,'src'=>'/image.webp']);$file=CmsStorageFile::$rows[$id];
$check($id!==$old->id&&$file->cluster_id==='sx'&&$file->cluster_file==='/image.webp','Legacy download setting must produce a link');
$check($old->cluster_id==='local'&&$old->sx_id===17&&$file->sx_id===null,'Existing shared file and unique source identity preserved');
$check($writer->image(['id'=>17,'src'=>'/image.webp'])===$id,'Repeated image is reused');
$next=new ShopCatalogWriter(1,$settings,$api,static function(){});
$check($next->image(['id'=>17,'src'=>'/image.webp'])===$id,'Persisted link is reused by next writer');
$newId=$next->image(['id'=>17,'src'=>'/changed.webp']);
$check($newId!==$id&&$file->cluster_file==='/image.webp','Changed URL does not overwrite shared record');
$check($next->image(null)===null,'Removed image clears relation');
$check(Yii::$app->storage->uploads===0,'Default images do not download');
$forced=$writer->image(['id'=>18,'src'=>'/brand.webp'],true);
$check(CmsStorageFile::$rows[$forced]->cluster_id==='local'&&Yii::$app->storage->uploads===1,'Dictionary forces download');
$check($writer->image(['id'=>18,'src'=>'/brand.webp'],true)===$forced&&Yii::$app->storage->uploads===1,'Downloaded image reused');
$link=$writer->image(['id'=>18,'src'=>'/brand.webp']);
$check($link!==$forced&&CmsStorageFile::$rows[$link]->cluster_id==='sx','Forced download does not contaminate product link');
$api->is_download_images=true;
$download=$writer->image(['id'=>19,'src'=>'/product.webp']);
$check(CmsStorageFile::$rows[$download]->cluster_id==='local'&&Yii::$app->storage->uploads===2,'Existing v1 API setting respected');
echo "OK: 11 GPD v1-compatible image checks\n";
}