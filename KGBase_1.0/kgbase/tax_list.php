<?php
include('classSimpleImage.php');
include('functions.php');
$xmldir = 'kgbase/base/';
$kmldir = 'kgbase/base/kml/';
$xml_ind = simplexml_load_file('kgbase/base/bases/'.$base.'/index.xml');
$tree_title = $xml_ind->title;
$tree_description = $xml_ind->description;
print('<b><i>'.$txn.'</i></b> ('.$tree_title.')<br><br>');
print(' <a href="'.$site.'&base='.$base.'&txn='.$txn.'&map=blank" title="Статическая карта (jpg)" target="_blank"> Посмотреть карту (jpeg, в новом окне)</a><br>');
//array of specimens & function for print
$index=array();
foreach($xml_ind->children() as $name=>$ch){
 $index[$name]=trim($ch);
}
//make arrays of specimens, locations and series
$sp_geo = simplexml_load_file('kgbase/base/bases/'.$base.'/'.$index['geodata']);
$sp_ind = simplexml_load_file('kgbase/base/bases/'.$base.'/'.$index['collection']);
$tax_loc=array();//array: taxon-location-hierarhy
$ser=array();//array: location-series-properties
$spec=array();//array: taxon-location-series-specimen-properties
$locs=array();//array location-properties
$spec_xarr=$sp_ind->xpath("//specimen[taxon='$txn']");
foreach($spec_xarr as $xnum=>$specimen){
 $series_xarr = $spec_xarr[$xnum]->xpath("parent::*");
 $series=$series_xarr[0];
 $no_series = $series->series;
 $no_loc = $series->location; trim($no_loc);
 $start = $series->datetime_start;
 $end = $series->datetime_end;
 $collector = $series->collector;
 $cmode =  $series->cmode;
 $habitat =  $series->habitat;
 $no_specimen = $specimen->specimen;
 $taxon = $specimen->taxon; trim($taxon);
 $collection = $specimen->collection;
 $nall = $specimen->nall;
 $nmale = $specimen->nmale;
 $nfemale = $specimen->nfemale;
 $nlarv = $specimen->nlarv;
 $nwork = $specimen->nwork;
 //
 if(!isset($spec["$taxon"])){$spec["$taxon"]=array();}else{}
 if(!isset($tax_loc["$taxon"])){$tax_loc["$taxon"]=array();}else{}
 if(!isset($spec["$taxon"]["$no_loc"])){$spec["$taxon"]["$no_loc"]=array();}else{}
 foreach($sp_geo->location as $eloc){
  $shloc = $eloc->location;trim($shloc);
  if("$no_loc"=="$shloc"){
   $geo_series=$eloc;
  }else{}
 }
 $hr = $geo_series->hierarhy;
 if(!isset($tax_loc["$taxon"]["$no_loc"])){$tax_loc["$taxon"]["$no_loc"]=trim($hr);}else{}
 //
 if(!isset($locs["$no_loc"])){$locs["$no_loc"]=array();}else{}
 $ldesc = $geo_series->description;
 $locs["$no_loc"]['description']=trim($ldesc);
 $locs["$no_loc"]['placemarks']=array();
 foreach($geo_series->placemark as $placemark){
  $no_placemark = $placemark->placemark;
  $locs["$no_loc"]['placemarks']["$no_placemark"]=array();
  $type = $placemark->type;
  $coordinates = $placemark->coordinates;
  $locs["$no_loc"]['placemarks']["$no_placemark"]['type']=trim($type);
  $locs["$no_loc"]['placemarks']["$no_placemark"]['coordinates']=trim($coordinates);
 }
 //
 if(!isset($ser["$no_loc"])){$ser["$no_loc"]=array();}else{}
 if(!isset($ser["$no_loc"]["$no_series"])){$ser["$no_loc"]["$no_series"]=array();}else{}
 $ser["$no_loc"]["$no_series"]['start']=trim($start);
 $ser["$no_loc"]["$no_series"]['end']=trim($end);
 $ser["$no_loc"]["$no_series"]['collector']=trim($collector);
 $ser["$no_loc"]["$no_series"]['cmode']=trim($cmode);
 $ser["$no_loc"]["$no_series"]['habitat']=trim($habitat);
 //
 if(!isset($spec["$taxon"]["$no_loc"]["$no_series"])){$spec["$taxon"]["$no_loc"]["$no_series"]=array();}else{}
 $spec["$taxon"]["$no_loc"]["$no_series"]["$no_specimen"]=array();
 $spec["$taxon"]["$no_loc"]["$no_series"]["$no_specimen"]['nall']=trim($nall);
 $spec["$taxon"]["$no_loc"]["$no_series"]["$no_specimen"]['nmale']=trim($nmale);
 $spec["$taxon"]["$no_loc"]["$no_series"]["$no_specimen"]['nfemale']=trim($nfemale);
 $spec["$taxon"]["$no_loc"]["$no_series"]["$no_specimen"]['nlarv']=trim($nlarv);
 $spec["$taxon"]["$no_loc"]["$no_series"]["$no_specimen"]['nwork']=trim($nwork);
 $spec["$taxon"]["$no_loc"]["$no_series"]["$no_specimen"]['collection']=trim($collection);
 $spec["$taxon"]["$no_loc"]["$no_series"]["$no_specimen"]['det']=array();
 foreach($specimen->determ as $determ){
  $no_det = $determ->determ;
  $spec["$taxon"]["$no_loc"]["$no_series"]["$no_specimen"]['det']["$no_det"]=array();
  $det_taxon = $determ->taxon;
  $det_datetime = $determ->datetime;
  $determinator = $determ->determinator;
  $spec["$taxon"]["$no_loc"]["$no_series"]["$no_specimen"]['det']["$no_det"]['det_taxon']=trim($det_taxon);
  $spec["$taxon"]["$no_loc"]["$no_series"]["$no_specimen"]['det']["$no_det"]['det_datetime']=trim($det_datetime);
  $spec["$taxon"]["$no_loc"]["$no_series"]["$no_specimen"]['det']["$no_det"]['determinator']=trim($determinator);
 }
}
//make kml file
$kml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><kml xmlns="http://www.opengis.net/kml/2.2" xmlns:gx="http://www.google.com/kml/ext/2.2" xmlns:kml="http://www.opengis.net/kml/2.2" xmlns:atom="http://www.w3.org/2005/Atom"></kml>');
$kml_doc = $kml->addChild('Document','&#xA;');
$kml_doc->addChild('name',$txn);
$kml_doc->addChild('description',$txn.' - '.$tree_description);
foreach($locs as $no_loc=>$loc){
 $kml_folder = $kml_doc->addChild('Folder','&#xA;');
 $kml_folder->addChild('name',$no_loc);
 $kml_folder->addChild('description',$loc['description']);
 foreach($loc['placemarks'] as $no_placemark=>$placemark){
  $kml_placemark = $kml_folder->addChild('Placemark','&#xA;');
  $kml_placemark->addChild('name',$no_placemark);
  $kml_placemark->addChild('visibility','1');
  $kml_placemark->addChild('open','1');
  $pl_type = $placemark['type'];
  $pl_coordinates = $placemark['coordinates'];
  if($pl_type=='Point'){
   $kml_point = $kml_placemark->addChild('Point','&#xA;');
   $kml_point->addChild('coordinates',$pl_coordinates);
  }elseif($pl_type=='LineString'){
   $kml_linestring = $kml_placemark->addChild('LineString','&#xA;');
   $kml_linestring->addChild('coordinates',$pl_coordinates);
  }elseif($pl_type=='Polygon'){
   $kml_polygon = $kml_placemark->addChild('Polygon','&#xA;');
   $kml_ob = $kml_polygon->addChild('outerBoundaryIs','&#xA;');
   $kml_linearring = $kml_ob->addChild('LinearRing','&#xA;');
   $kml_linearring->addChild('coordinates',$pl_coordinates);
  }else{}
 }
}
$kml->asXML($kmldir.$txn.'.kml');
print('<a href="'.$kmldir.$txn.'.kml" target="_blank"> Карта (KML файл)</a><br><br>');
//print locations, series &specimens
$sp_arr=$tax_loc["$txn"];
asort($sp_arr);
$padding=10;
foreach($sp_arr as $no_loc=>$hr){
 print('<p style="padding-left: '.$padding.'px; margin-top: -1px;  margin-bottom: -1px;"><b>'.$hr.':</b></p>');
 $ldesc=$locs["$no_loc"]['description'];
 print('<p style="padding-left: '.($padding+10).'px; margin-top: -1px;  margin-bottom: -1px;">'.$ldesc.':</p>');
 $sp=$spec["$txn"]["$no_loc"];
 foreach($sp as $no_series=>$specimens){
  $start = $ser["$no_loc"]["$no_series"]['start'];
  $end = $ser["$no_loc"]["$no_series"]['end'];
  $collector = $ser["$no_loc"]["$no_series"]['collector'];
  $cmode = $ser["$no_loc"]["$no_series"]['cmode'];
  $habitat = $ser["$no_loc"]["$no_series"]['habitat'];
  print('<p style="padding-left: '.($padding+20).'px; margin-top: -1px;  margin-bottom: -1px;"><i>'.$start);
  if($end!==''){
   print(' - '.$end);
  }else{}
  if($habitat!==''){
   print(', '.$habitat);
  }else{}
  if($cmode!==''){
   print(', '.$cmode);
  }else{}
  print(', '.$collector);
  print('</i><br>');
  print('Образцы:</p>');
  //specimens
  foreach($specimens as $no_specimen=>$specimen){
   $nall=$specimen['nall'];
   $nmale=$specimen['nmale'];
   $nfemale=$specimen['nfemale'];
   $nlarv=$specimen['nlarv'];
   $nwork=$specimen['nwork'];
   $collection = $specimen['collection'];
   print('<p style="padding-left: '.($padding+20).'px; margin-top: -1px;  margin-bottom: -1px;"><a name="'.$no_specimen.'"></a>'.$no_specimen.': '.$nall.' ex.: ');
   if($nmale!==''){
    print($nmale.'&#x2642; ');
   }else{}
   if($nfemale!==''){
    print($nfemale.'&#x2640; ');
   }else{}
   if($nlarv!==''){
    print($nlarv.'&#x26aa; ');
   }else{}
   if($nwork!==''){
    print($nwork.'&#x26b2; ');
   }else{}
   print(' <a href="'.$site.'&base='.$base.'&collect='.$collection.'" title="Данные о коллекции '.$collection.'" target="_blank">('.$collection.')</a><br></p><p style="padding-left: '.($padding+30).'px; margin-top: -1px;  margin-bottom: -1px;">Определения:<br>');
   //deterninations
   foreach($specimen['det'] as $no_det=>$determ){
    $det_taxon=$determ['det_taxon'];
    $det_datetime=$determ['det_datetime'];
    $determinator=$determ['determinator'];
    print('<i>'.$det_taxon.' ('.$determinator.', '.$det_datetime.') - '.$no_det.'</i><br>');
   }
   print('</p>');
   //images
   print('<p style="padding-left: '.($padding+40).'px; margin-top: -1px;  margin-bottom: -1px;">Изображения:<br>');
   if(is_dir("kgbase/base/img/$no_specimen")){
    $images=scandir("kgbase/base/img/$no_specimen");
    if(!is_dir("kgbase/base/img/$no_specimen/res")){
     mkdir("kgbase/base/img/$no_specimen/res");
    }else{}
    $imcount=0;
    foreach($images as $ino=>$image){
     if($image!=='.' && $image!=='..' && $image!=='Thumbs.db' && $image!=='res'){
     if($imcount>3){print('<br>');$imcount=0;}else{}
     if(!file_exists("kgbase/base/img/$no_specimen/res/$image")){
      $simage = new SimpleImage();
      $simage->load("kgbase/base/img/$no_specimen/$image");
      $simage->resizeToHeight(100);
      $simage->save("kgbase/base/img/$no_specimen/res/$image");
     }else{}
     print('<a href="kgbase/base/img/'.$no_specimen.'/'.$image.'" target="_blank" title="'.$txn.':'.$no_specimen.'"><img src="kgbase/base/img/'.$no_specimen.'/res/'.$image.'"></a>&nbsp;&nbsp;');$imcount=$imcount+1;
     }else{}
    }
    print('<br>');
   }else{print('No images available:<br>');}
 }
  print('</p>');
 }
}
 ?>