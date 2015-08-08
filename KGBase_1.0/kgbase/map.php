<?php
include('geospace/gsp.php');
$xmldir = 'kgbase/base/';
$xml_ind = simplexml_load_file('kgbase/base/bases/'.$base.'/index.xml');
$tree_title = $xml_ind->title;
$tree_description = $xml_ind->description;
print('<b><i>'.$txn.'</i></b><br><br>');
$gsp_src = 'kgbase/base/bases/'.$base.'/'.$map.'.jpg';
$gsp_map = 'kgbase/base/bases/'.$base.'/'.$map.'.kml';
$map = new GeoSpace;
//array of specimens & function for print
$index=array();
foreach($xml_ind->children() as $name=>$ch){
 $index[$name]=trim($ch);
}
//make arrays of specimens, locations and series
$sp_geo = simplexml_load_file('kgbase/base/bases/'.$base.'/'.$index['geodata']);
$sp_ind = simplexml_load_file('kgbase/base/bases/'.$base.'/'.$index['collection']);
$geodata=array();//array location-properties
$spec_xarr=$sp_ind->xpath("//specimen[taxon='$txn']");
foreach($spec_xarr as $xnum=>$specimen){
 $series_xarr = $spec_xarr[$xnum]->xpath("parent::*");
 $series=$series_xarr[0];
 $no_loc = $series->location; trim($no_loc);
 foreach($sp_geo->location as $eloc){
  $shloc = $eloc->location;trim($shloc);
  if("$no_loc"=="$shloc"){
   $geo_series=$eloc;
  }else{}
 }
 //
 foreach($geo_series->placemark as $placemark){
  $no_placemark = $placemark->placemark;
  if(!isset($geodata["$no_placemark"])){$geodata["$no_placemark"]=array();}else{}
  $type = $placemark->type;
  $coordinates = $placemark->coordinates;
  $geodata["$no_placemark"]['type']=trim($type);
  $geodata["$no_placemark"]['coordinates']=trim($coordinates);
 }
 //
}
$dest = 'geospace/maps/';
$map_name=$txn.'.jpg';
$date=date("F j, Y, g:i a");
$name = 'KGBase: '.$txn.': map created in:'.$date;
$font = 'geospace/fonts/ARIAL.TTF';
$fsize=12;
$map->gsp_src = $gsp_src;
$map->gsp_map = $gsp_map;
$map->geodata = $geodata;
$map->gsp_getsize($gsp_src,$gsp_map);
$map->gsp_drowmap($name,$font,$fsize,$gsp_src,$geodata,$dest,$map_name,20,3);
print('<img src="'.$dest.$map_name.'">');

?>