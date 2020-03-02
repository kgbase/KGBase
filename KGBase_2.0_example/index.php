<?php
include('settings.php');
include('functions.php');
$db = new SQLite3($base);
//get variables from link
@$sect=$_GET['sect'];//try to get base name
@$txn=$_GET['txn'];//try to get name of taxon
@$collect=$_GET['collect'];//try to get name of collection
@$map=$_GET['map'];//try to get command to redirect at map page
@$msrc=$_GET['msrc'];//name of source map (blank)
@$search=$_GET['search'];//try to get request for search
@$baselocation=$_GET['baselocation'];//try to get name of location
@$litsrc=$_GET['litsrc'];//try to get reference to literature source
//generate title of the page
$bases=get_sections_all($db);
if(!isset($sect) && !isset($collect) && !isset($baselocation) && !isset($litsrc)){$base_title=$base_title.': '.$subtitle.': About';}else{}
if(isset($sect) && !isset($txn) && !isset($collect) && !isset($map) && !isset($search)){$base_title=$base_title.': '.$subtitle.': List of species';}else{}
if(isset($search)){$base_title=$base_title.': '.$subtitle.': search';}else{}
if(isset($map)){$base_title=$base_title.': map';}else{}
//if(isset($sect)){$base_title=$base_title.': '.$bases["$sect"]['title'];}else{}
if(isset($txn)){$base_title=$base_title.': '.$subtitle.': '.$txn;}else{}
if(isset($msrc)){$base_title=$base_title.': '.$map_src["$msrc"];}else{}
if(isset($litsrc)){
  $base_title=$base_title.': '.$subtitle.': literature';
  if($litsrc!=='all'){$base_title=$base_title.': '.$litsrc;}
}else{}
if(isset($baselocation)){
  if($baselocation=='all'){
    $base_title=$base_title.': all locations';
  }else{
    $base_title=$base_title.': '.$subtitle.': location: '.$baselocation;
  }
}else{}
$page_title=$base_title;

include('templates/top');
//if base's name not set, print common notes
if(!isset($sect) && !isset($collect) && !isset($baselocation) && !isset($litsrc)){
  //about the database
 $bodytext=file('templates/index');
 foreach($bodytext as $body=>$text){
  //print('<p>'.$text.'</p>');
 }
 foreach($bases as $base_n=>$base_d){
  if($base_n!=='all'){
   $base_title = $base_d['title'];
   $base_desc = $base_d['description'];
   //print('<a href="'.$site.'&sect='.$base_n.'" target="_blank">'.$base_title.'</a><br>'.$base_desc.'<br>');
   //print('<a href="'.$site.'&sect='.$base_n.'&search=yes" target="_blank">Поиск по базе</a><br><br>');
  }else{}
 }
 include('templates/intro.html');
}else{}
//if set only base's name, print tree of taxons
//
//
if(isset($sect) && !isset($txn) && !isset($collect) && !isset($map) && !isset($search)){
 include('tree_list.php');
}else{}
//if set base's name and taxon's name, print the data of taxons
//
//
if(isset($sect) && isset($txn) && !isset($collect) && !isset($map)  && !isset($search)){
  include('tax_list.php');
}else{}
//if set base name and collection name, print data of collection
//
//
if(isset($collect) && !isset($map) && !isset($search)){
 include('collect.php');
}else{}
//if set base's name, taxon's name and map name, print the map of distribution for taxon
//
//
//if(isset($sect) && !isset($collect) && isset($map) && !isset($search)){
 //include('map.php');
//}else{}
//if the search is requested, perform search
//
//
if(isset($sect) && !isset($txn) && !isset($collect) && !isset($map) && isset($search)){
 include('search.php');
}
//
if(isset($baselocation)){
 include('location.php');
}
if(isset($litsrc)){
 include('lit.php');
}
include('templates/bottom');
?>