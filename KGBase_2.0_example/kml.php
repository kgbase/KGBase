<?php
include('settings.php');
include('functions.php');
@$taxon=$_GET['taxon'];
@$location=$_GET['location'];
$db = new SQLite3($base);
if(isset($taxon)){
  //prepare taxon data
  $taxon_data=array();
  $allt=taxdata_exists($db);
  if(isset($allt['specimen']["$taxon"])){$taxon_data['coll']=get_taxdata($db,$taxon);}else{}
  if(isset($allt['reference']["$taxon"])){$taxon_data['ref']=get_taxdata_refs($db,$taxon);}else{}
  if(isset($allt['observation']["$taxon"])){$taxon_data['obs']=get_taxdata_obs($db,$taxon);}else{}
  //prepare array op placemarks
  $places=get_taxon_placemarks($taxon_data,$taxon);
  //construct and send to client the knm file
  $current_datetime=date(DATE_ATOM);
  $dataset_name=' '.$base_title.' '.$subtitle." $current_datetime";
  $kml_filename=str_replace(' ','_',$dataset_name.".kml");
  $kml=kml_construct($places,$taxon,$dataset_name);
  $kml_string=$kml->asXML();
  header('Content-type:text/xml;');
  header('Content-Disposition: attachment; filename="'.$kml_filename.'"');
  echo $kml_string;
}else{}
if(isset($location)){
  $geodata=get_locations($db);
  $placemarks=get_placemarks($db);
  $kml_gd=array("$baselocation"=>$geodata["$location"]);
  $kml_pl=array("$baselocation"=>$placemarks["$location"]);
  $kml=construct_kml_simple($kml_gd,$kml_pl);
  $kml_filename="$location.kml";
  $kml_string=$kml->asXML();
  header('Content-type:text/xml;');
  header('Content-Disposition: attachment; filename="'.$kml_filename.'"');
  echo $kml_string;
}else{}
if(!isset($taxon) && !isset($location)){
  print("<div>Sorry, the request is invalid. Correcd request must include one of two variables:<br>
  1. 'taxon' - name of the taxon in the database
  2. 'location' - name of the location in the database</div>");
}else{}