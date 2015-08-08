<?php
@$chset=$_GET['chset'];
@$nset=$_POST['nset'];
@$control=$_POST['control'];
$basetitle='KGBase DataBuilder';
if(isset($chset)){
 $title=$basetitle.': '.$chset;
}else{$title=$basetitle;}
print('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" 
  "http://www.w3.org/TR/html4/strict.dtd">
<html>
 <head>
  <title>'.$title.'</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
 </head>
  <body>');
include('functions.php');
include('settings.php');
//----------------------------------------------------------------
if(!isset($nset)){
 print('<p>Генератор баз данных KGBase. Настройки баз в файле settings.php. Выберите базу для генерации:</p>');
 foreach($set as $ns=>$sitem){
  print('<p><b>'.$sitem['name'].': </b>'.$sitem['description'].'<br>');
  print('<form action="index.php?chset='.$sitem['name'].'" method="post" target="_blank">');
  print('<input type="submit" value="Generate"/>');
  print('<input type="checkbox" name="control" value="1" checked/>Показать не включенные в итоговую базу из исходных данных образцы');
  print('<input type="hidden" name="nset" value="'.$ns.'"/></form>');
  print('-----------------------------------------------------------</p>');
 }
}else{
 //----------------------------------------------------------------
 //build the base
 //parse taxonomy
 if($set[$nset]['src']['tax']['type']=='ODS'){
  $tax=GetODSContent($src_path['tax'],$set[$nset]['src']['tax']['file']);
  $tax_xml=taxODS2xml($tax);
 }else{}
 $tax_names=GetXMLNames($tax_xml);
 //parse collection data & geodata
 $result=array();
 $result['geo']=new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><geodata></geodata>');
 $result['coll']=new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><collection></collection>');
 foreach($set[$nset]['src']['data'] as $src_data){
  //geodata
  if($src_data['geo']['type']=='ODS'){
   $geo=GetODSContent($src_path['geo'],$src_data['geo']['file']);
   $src_geo=geoODS2xml($geo);
  }else{}
  //collection data
  if($src_data['coll']['type']=='ODBC'){
   $coll=GetColl_ODBC_Content($src_data['coll']['src'],$src_data['coll']['user'],$src_data['coll']['pass']);
   $src_coll=collODS2xml($coll);
  }else{}
  if($src_data['coll']['type']=='ODS'){
   $coll=GetODSContent($src_path['coll'],$src_data['coll']['file']);
   $src_coll=geoODS2xml($coll);
  }else{}
  $result=ExtCollGeoByTax($result,$tax_names,$src_coll,$src_geo,$control);
 }
 $res_collections=MakeCollIndex($src_path['collections'],$result['coll']);
 //load the base
 $basedir=$upl_path.'/'.$set[$nset]['name'];
 if(is_dir($basedir)){
  rm_rf($basedir);
 }else{}
 mkdir($basedir);
 $base_index=new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><base></base>');
 $base_index->addChild('title',$set[$nset]['title']);
 $base_index->addChild('description',$set[$nset]['description']);
 $base_index->addChild('collection',$set[$nset]['collection']);
 $base_index->addChild('geodata',$set[$nset]['geodata']);
 $base_index->addChild('taxonomy',$set[$nset]['taxonomy']);
 $base_index->asXML($basedir.'/index.xml');
 $tax_xml->asXML($basedir.'/'.$set[$nset]['taxonomy']);
 $result['geo']->asXML($basedir.'/'.$set[$nset]['geodata']);
 $result['coll']->asXML($basedir.'/'.$set[$nset]['collection']);
 $res_collections->asXML($basedir.'/collections.xml');
 copy($src_path['map'].'/'.$set[$nset]['map'].'/blank.jpg',$basedir.'/blank.jpg');
 copy($src_path['map'].'/'.$set[$nset]['map'].'/blank.kml',$basedir.'/blank.kml');
 ZipDir($upl_path,$set[$nset]['name']);
 print('<p>Генерация завершена. Архив с базой данных: <a href="'.$basedir.'/'.$set[$nset]['name'].'.zip">'.$set[$nset]['name'].'</a></p>');
}
//===============================================================================
print('</body></html>');
?>