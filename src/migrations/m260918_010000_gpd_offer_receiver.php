<?php
use yii\db\Migration;
class m260918_010000_gpd_offer_receiver extends Migration {
 public function up(){
  $this->execute('CREATE TABLE {{%shop_gpd_offer_connection}} LIKE {{%shop_gpd_connection}}');
  $this->execute('CREATE TABLE {{%shop_gpd_offer_state}} LIKE {{%shop_gpd_catalog_state}}');
 }
 public function down(){return false;}
}
