<?php
session_start();
//
$page_title='KGBase-GDBase by K.Grebennikov (v.2 2015) - создание пользовательской карты';
$main_page=false;
$c_file=file("../conf");
$serv_arr=explode('||',$c_file[0]);
$serv = trim($serv_arr[1]);//base address
$rwr_arr=explode('||',$c_file[1]);
$rwr=trim($rwr_arr[1]);
//
@$user=$_SESSION['username'];
$b=file("../$rwr");
$c_user=array();
foreach($b as $n=>$str){
 $str_arr=explode('/',$str);
 if($str_arr[0]==$user){
  $c_user['name']=trim($str_arr[2]);
  $c_user['role']=trim($str_arr[3]);
  $c_user['role_ps']=trim($str_arr[4]);
 }else{}
}
//-----------------------------------------------------------------
//GDBase Data management system (GeoData Base of digital photo images)
//GDBase - система управления данными (база геоданных цифровых фотоизображений)
//------------------------------------------------------------------
//copyrights:
//eng: State reserve "Bogdinsko-Baskunchaksky",2014 (glagolev1974@mail.ru),
//Konstantin A. Grebennikov, 2014 (kgrebennikov@gmail.com)
//
//rus: ФГБУ "Государственный природный заповедник "Богдинско-Баскунчакский" ,2014 (glagolev1974@mail.ru),
//Гребенников Константин Алексеевич, 2014 (kgrebennikov@gmail.com)
//
//This program is free software - License GPL v.3 (license.txt, http://www.gnu.org/licenses/gpl-3.0.html)
// Свободное программное обеспечение, распространяется на условиях
//стандартной общественной лицензии GPL v.3 (license.txt, http://www.gnu.org/licenses/gpl-3.0.html)
//
// 2014, Akhtubinsk, Russian Federation
//
//In the program some modules written by other authors are used:
//SimpleImage by Simon Jarvis, 2006.
//Authority, rights and license agreements - see the modules (SimpleImage.php).
//--------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------
//set title and load top of the page
if(isset($c_user['name'])){
 if($c_user['role']=='admin'){
  //authorised user's interface and functions
  include('template/top');
  //set global variables
  $detauth=$c_user['name'];//name of current user as name of determinator
  $db='../photodb/';
  $ifile=$db.'db.xml';
  @$dir=$_GET['dir'];
  $db_dir = '../photodb/';//default database directory
  $db_file = 'db.xml';//default database index
  if(isset($dir)){@$itdir=$dir.'/';}else{}
  @$item=$_GET['item'];
  include('classSimpleImage.php');
  //-------------------------------------------------------------------------
  //GeoSpace class include and base map set
  $conf_geo_file=file("../conf_geo");
  $geoclass_arr=explode('||',$conf_geo_file[0]);
  $geoclass = trim($geoclass_arr[1]);//GeoSpace class
  $image_arr=explode('||',$conf_geo_file[1]);
  $gsp_src = trim($image_arr[1]);//map image
  $map_arr=explode('||',$conf_geo_file[2]);
  $gsp_map = trim($map_arr[1]);//map data
  include($geoclass);
  //-------------------------------------------------------------------------
  //create map for sigle location (image)
  if(isset($dir) && isset($item)){
   $dest = '../userdata/'.$user.'/';
   $map_name=$dir.'_'.$item.'.jpg';
   $index = simplexml_load_file($db.$itdir.$dir.'.xml');
   $item_data_arr = $index->xpath("..//img[filename='$item']");
   $item_data = $item_data_arr[0];
   $datetime = $item_data->datetime;
   $latitude = $item_data->latitude;
   $longitude = $item_data->longitude;
   $ele = $item_data->ele;
   $geodata = array(0=>array("type" => "Point","coordinates" => $longitude.','.$latitude.','.$ele));
   $map = new GeoSpace;
   $date=date("F j, Y, g:i a");
   $name = $dir.': '.$item.' created: '.$date;
   $font = '../GeoSpace/fonts/ARIAL.TTF';
   $fsize=14;
   $map->gsp_src = $gsp_src;
   $map->gsp_map = $gsp_map;
   $map->geodata = $geodata;
   $map->gsp_getsize($gsp_src,$gsp_map);
   $map->gsp_drowmap($name,$font,$fsize,$gsp_src,$geodata,$dest,$map_name,20,3);
   print('<img src="'.$dest.$map_name.'">');
  }else{}
  //-------------------------------------------------------------------------
  //create map for object (all records)
  if(!isset($dir) && isset($item)){
   //get data of object from entire base
   $nob=0;
   $obj_data=array();
   $index = simplexml_load_file($db_dir.$db_file);
   $dirs = $index->xpath("..//directory");
   foreach($dirs as $it=>$dir){
    $d_name = $dir->name;
    $direct = simplexml_load_file($db_dir.$d_name.'/'.$d_name.'.xml');
    $loc = $direct->locations;
    $obs = $direct->observer;
    $objs = $direct->xpath("..//object[name='$item']");
    foreach($objs as $num=>$obj){
     $node = $obj->xpath("parent::*");$imgs = $node[0]->xpath("parent::*");
     $img=$imgs[0];
     $filename = $img->filename;
     $lat = $img->latitude;
     $lon = $img->longitude;
     $ele = $img->ele;
     //
     $obj_data[$nob]['lon']=trim($lon);
     $obj_data[$nob]['lat']=trim($lat);
     $obj_data[$nob]['ele']=trim($ele);
     $nob=$nob+1;
    }
   }
   //create map
   $dest = '../userdata/'.$user.'/';
   $map_name=$item.'.jpg';
   $geodata=array();print('<br>');
   $geo_cnt=0;
   foreach($obj_data as $no=>$obj){
    $geodata[$geo_cnt]=array();
    $geodata[$geo_cnt]["type"]="Point";
    $geodata[$geo_cnt]["coordinates"]=$obj['lon'].','.$obj['lat'].','.$obj['ele'];
    $geo_cnt++;
   }
   $map = new GeoSpace;
   $date=date("F j, Y, g:i a");
   $name = $item.' - created: '.$date;
   $font = '../GeoSpace/fonts/ARIAL.TTF';
   $fsize=14;
   $map->gsp_src = $gsp_src;
   $map->gsp_map = $gsp_map;
   $map->geodata = $geodata;
   $map->gsp_getsize($gsp_src,$gsp_map);
   $map->gsp_drowmap($name,$font,$fsize,$gsp_src,$geodata,$dest,$map_name,20,3);
   print('<img src="'.$dest.$map_name.'">');
  }else{}
  //-------------------------------------------------------------------------
  if(!isset($dir) && !isset($item)){
   print('Вы не сказали, карту чего хотите видеть :(');
  }else{}
  include('template/right');
 }else{
  include('template/top');
  print('К сожалению, <b>'.$c_user['name'].'</b>, Вы - <b>'.$c_user['role_ps'].'</b>, а не администратор системы. Попробуйте выйти и войти еще раз или свяжитесь с администратором системы для изменения Вашего статуса.');
  include('template/form_logoff');
 }
}else{
 include('template/top');
 print('Здравствуйте! Мы Вас не знаем. Если Вы наш пользователь - представьтесь, пожалуйста');
 include('template/form_login');
}
?>
