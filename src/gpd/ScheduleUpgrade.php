<?php
namespace skeeks\cms\shop\gpd;

use yii\db\Connection;
use yii\db\Query;

/** One-time schedule migration; workers are provisioned by the hosting platform. */
final class ScheduleUpgrade
{
    public const LEGACY=[
        'shop/skeeks-suppliers/update-products',
        'shop/skeeks-suppliers/update-products --product_new_info=1',
        'shop/skeeks-suppliers/update-store-items',
        'shop/skeeks-suppliers/update-store-items --store_new_prices=1',
    ];
    public const SCHEDULES=[
        'shop.gpd.catalog.receive'=>['GPD: получение изменений товаров',60,'products'],
        'shop.gpd.catalog.apply'=>['GPD: применение изменений товаров',60,'products'],
        'shop.gpd.references.sync'=>['GPD: синхронизация справочников',60,'any'],
        'shop.gpd.dictionaries.sync'=>['GPD: страны и единицы измерения',86400,'any'],
        'shop.gpd.offers.sync'=>['GPD: цены, остатки и склады',60,'offers'],
    ];
    private $db;
    private $assertStopped;
    public function __construct(Connection $db, ?callable $assertStopped = null)
    {
        $this->db=$db;
        $this->assertStopped=$assertStopped ?? static function(){ LegacyProcessGuard::assertStopped(defined("ROOT_DIR") ? ROOT_DIR : \Yii::getAlias("@root")); };
    }

    public function run(callable $configure): int
    {
        return $this->db->transaction(function()use($configure){
            $rows=(new Query())->from('{{%cms_agent}}')->where(['name'=>self::LEGACY])->orderBy('id')->all($this->db);
            $sites=[];
            foreach($rows as $row)if((int)$row['cms_site_id']>0)$sites[(int)$row['cms_site_id']][]=$row;
            foreach($sites as $site=>$agents){
                if(array_filter($agents,static fn($row)=>(bool)$row['is_running'])){ ($this->assertStopped)(); }
                $products=false;$offers=false;
                foreach($agents as $a){if(!$a['is_active'])continue;if(strpos($a['name'],'update-products')!==false)$products=true;else $offers=true;}
                $existing=(new Query())->from('{{%cms_agent}}')->where(['cms_site_id'=>$site,'job_type'=>array_keys(self::SCHEDULES)])->exists($this->db);
                foreach(self::SCHEDULES as $type=>[$title,$interval,$group]){
                    if((new Query())->from('{{%cms_agent}}')->where(['cms_site_id'=>$site,'job_type'=>$type])->exists($this->db))continue;
                    $active=$group==='products'?$products:($group==='offers'?$offers:($products||$offers));
                    $this->db->createCommand()->insert('{{%cms_agent}}',[
                        'cms_site_id'=>$site,'name'=>'job:'.$type,'description'=>$title,'job_type'=>$type,'job_payload'=>'{}',
                        'agent_interval'=>$interval,'priority'=>100,'is_period'=>0,'is_running'=>0,'is_system'=>0,
                        'is_active'=>(int)$active,'last_exec_at'=>0,'next_exec_at'=>time(),
                    ])->execute();
                }
                // Preserve already configured pilot settings and schedule activation.
                $configure($site,($products||$offers)&&!$existing);
                $this->db->createCommand()->update('{{%cms_agent}}',['is_active'=>0,'is_running'=>0],['cms_site_id'=>$site,'name'=>self::LEGACY])->execute();
            }
            return count($sites);
        });
    }
}
