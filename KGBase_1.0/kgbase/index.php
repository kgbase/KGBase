<?php
$site='http://localhost/kgbase/index.php?mode=inc';
//get variables from link
@$base=$_GET['base'];//try to get base name
@$txn=$_GET['txn'];//try to get name of taxon
@$collect=$_GET['collect'];//try to get name of collection
@$map=$_GET['map'];//try to get name of map
@$search=$_GET['search'];//try to get request for search
@$redir=$_GET['redir'];//target of redirection
$base_title='KGBase 2.0 of K.Grebennikov (2015): База данных ';
if(isset($search)){
 $base_title=$base_title.': поиск: ';
}else{}
if(isset($base)){
 $base_title=$base_title.': '.$base;
 if(isset($txn)){
  $base_title=$base_title.': '.$txn;
  if(isset($spec)){$base_title=$base_title.': '.$spec;}else{}
 }else{}
}else{}
$page_title=$base_title;

include('kgbase/templates/top');
//if base's name not set, print common notes
//
//
if(!isset($base)){
 //list of bases
 $bodytext=file('kgbase/templates/index');
 foreach($bodytext as $body=>$text){
  print('<p>'.$text.'</p>');
 }
 $bases=scandir('kgbase/base/bases');
 foreach($bases as $base_n=>$base_dir){
  if($base_dir!=='.' && $base_dir!=='..'){
   $base_ind = simplexml_load_file('kgbase/base/bases/'.$base_dir.'/index.xml');
   $base_title = $base_ind->title;
   $base_desc = $base_ind->description;
   print('<a href="'.$site.'&base='.$base_dir.'" target="_blank">'.$base_title.'</a><br>'.$base_desc.'<br>');
   print('<a href="'.$site.'&base='.$base_dir.'&search=yes" target="_blank">Поиск по базе</a><br><br>');
  }else{}
 }
}else{}
//if set only base's name, print tree of taxons
//
//
if(isset($base) && !isset($txn) && !isset($collect) && !isset($map) && !isset($search)){
 include('tree_list.php');
}else{}
//if set base's name and taxon's name, print the data of taxons
//
//
if(isset($base) && isset($txn) && !isset($collect) && !isset($map)  && !isset($search)){
 include('tax_list.php');
}else{}
//if set base name and collection name, print data of collection
//
//
if(isset($base) && !isset($txn) && isset($collect) && !isset($map) && !isset($search)){
 include('collect.php');
}else{}
//if set base's name, taxon's name and map name, print the map of distribution for taxon
//
//
if(isset($base) && isset($txn) && !isset($collect) && isset($map) && !isset($search)){
 include('map.php');
}else{}
//if the search is requested, perform search
//
//
if(isset($base) && !isset($txn) && !isset($collect) && !isset($map) && isset($search)){
 include('search.php');
}
include('kgbase/templates/bottom');
?>