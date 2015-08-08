<?php
session_start();
//
$page_title='KGBase-GDBase by K.Grebennikov (v.2 2015) - статистика объектов';
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
//-----------------------------------------------------------
if(isset($c_user['name'])){
 if($c_user['role']=='admin'){
  //authorised user's interface and functions
  include('template/top');
  include('tax_functions.php');
  //set default variables for execution
  $c_file=file('../conf');
  $serv_arr=explode('||',$c_file[0]);
  $serv = trim($serv_arr[1]);//base address
  $dmp = '../dump/';//temp directory
  $db_dir = '../photodb/';//default database directory
  $db_file = 'db.xml';//default database index
  $def=date('Y-M-d_H-i-s');//current system time
  //get variables for execution from _POST
  @$item = $_GET['item'];
  @$stat = $_GET['stat'];
  //clear temp directory
  $trash=scandir($dmp);
  foreach($trash as $tr=>$ash){
   if($ash!=='.' && $ash!=='..'){
    unlink($dmp.$ash);
   }
  }
  //---------------------------------------------------------------------------------------
  //construct&print list of objects and their conditions if no action selected
  //---------------------------------------------------------------------------------------
  if(!isset($item) && !isset($stat)){
   print('<b>Есть информация о следующих объектах и их состояниях (получить <a href="objs_list.php?stat=all" target=new>полную выборку</a> всех объектов):</b><br><br>');
   $ob=array();
   $ncond=0;
   $index = simplexml_load_file($db_dir.$db_file);
   $dirs = $index->xpath("..//directory");
   foreach($dirs as $it=>$dir){
    $d_name = $dir->name;
    $direct = simplexml_load_file($db_dir.$d_name.'/'.$d_name.'.xml');
    $imgs = $direct->xpath("..//img");
    foreach($imgs as $iti=>$img){
     $i_objects = $img->objects;
     foreach($i_objects->object as $obj){
   $o_name = $obj->name;
   $o_cond = $obj->condition;
   if(isset($ob["$o_name"])){
    if(!isset($ob["$o_name"]["$o_cond"])){
     $ob["$o_name"]["$o_cond"]=$o_cond;
    }else{}
   }else{
    $ob["$o_name"]=array();
    $ob["$o_name"]["$o_cond"]=$o_cond;
   }
     }
    }
   }
   ksort($ob);
   foreach($ob as $name=>$conds){
    print('<b><a href="objs_list.php?item='.$name.'" target="_blank">'.$name.'</b></a><br>');
   }
   }else{}
  //--------------------------------------------------------------------------------------
  //search selected object (condition) and print statistics
  //--------------------------------------------------------------------------------------
  if(isset($item)){
   //search selected object and create array with the data
   print('Вы искали: <b>'.$item.'</b>. Вот что мы нашли:<br><br>');
   $nob=0;
   $obj_data=array();
   $obj_tree = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><result></result>');
   $obj_map = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><kml xmlns="http://earth.google.com/kml/2.2"></kml>');
   $index = simplexml_load_file($db_dir.$db_file);
   $dirs = $index->xpath("..//directory");
   foreach($dirs as $it=>$dir){
    $d_name = $dir->name;
    $direct = simplexml_load_file($db_dir.$d_name.'/'.$d_name.'.xml');
    $loc = $direct->locations;
    $obs = $direct->observer;
    $objs = $direct->xpath("..//object[name='$item']");
    foreach($objs as $num=>$obj){
     $obj_name = $obj->name;
     $obj_name=trim($obj_name);
     $condition = $obj->condition;
     $dettime = $obj->datetime;
     $detauth = $obj->detauth;
     $node = $obj->xpath("parent::*");$imgs = $node[0]->xpath("parent::*");
     $img=$imgs[0];
     $filename = $img->filename;
     $link=$serv.'/'.$db_dir.'/'.$d_name.'/'.$filename;
     $link=str_replace('/../','/',$link);
     $datetime = $img->datetime;
     $lat = $img->latitude;
     $lon = $img->longitude;
     $ele = $img->ele;
     //
     $obj_data[$nob]['name']=$obj_name;
     $obj_data[$nob]['condition']=$condition;
     $obj_data[$nob]['location']=$loc;
     $obj_data[$nob]['datetime']=$datetime;
     $obj_data[$nob]['lon']=$lon;
     $obj_data[$nob]['lat']=$lat;
     $obj_data[$nob]['ele']=$ele;
     $obj_data[$nob]['observer']=$obs;
     $obj_data[$nob]['detauth']=$detauth;
     $obj_data[$nob]['dettime']=$dettime;
     $obj_data[$nob]['link']=$link;
     $nob=$nob+1;
    }
   }
   print('<b>'.$nob.'</b> Наблюдений:<br>');
   //create&upload xml
   foreach($obj_data as $num=>$object){
    $obj = $obj_tree->addChild('object','&#xA;');
    $obj->addChild('name',$object['name']);
    $obj->addChild('condition',$object['condition']);
    $obj->addChild('location',$object['location']);
    $obj->addChild('datetime',$object['datetime']);
    $obj->addChild('lon',$object['lon']);
    $obj->addChild('lat',$object['lat']);
    $obj->addChild('ele',$object['ele']);
    $obj->addChild('observer',$object['observer']);
    $obj->addChild('detauth',$object['detauth']);
    $obj->addChild('dettime',$object['dettime']);
    $obj->addChild('link',$object['link']);
   }
   $obj_tree->asXML($dmp.$item.'('.$def.').xml');
   print('<a href="'.$dmp.$item.'('.$def.').xml">Данные выборки (XML)</a><br>');
   //create&upload kml
   $Document = $obj_map->addChild('Document','&#xA;');
   $Folder = $Document->addChild('Folder','&#xA;');
   $Folder->addChild('name',$item);
   $Folder->addChild('open',1);
   $Style = $Folder->addChild('Style','&#xA;');
   $ListStyle = $Style->addChild('ListStyle','&#xA;');
   $ListStyle->addChild('listItemType','check');
   $ListStyle->addChild('bgColor','00ffffff');
   sort($obj_data);
   foreach($obj_data as $num=>$object){
    $Placemark = $Folder->addChild('Placemark','&#xA;');
    $Placemark->addChild('name',$item.'('.$num.')');
    $kml_desc=$item.', '.$object['location'].' ('.$object['condition'].'), фотография '.$object['observer'].' ('.$object['datetime'].'), определил '.$object['detauth'].' ('.$object['dettime'].'), <br>&lt;img src="'.$object['link'].'"/&gt;';
    $Placemark->addChild('description',$kml_desc);
    $Placemark->addChild('styleUrl','#waypoint');
    $Point = $Placemark->addChild('Point','&#xA;');
    $Point->addChild('extrude',1);
    $Point->addChild('coordinates',$object['lon'].','.$object['lat'].','.$object['ele']);
    $end = $Placemark->addChild('end','&#xA;');
    $kmlt=explode(" ",$object['datetime']);
     $kmlt[0]=str_replace(":","-",$kmlt[0]);
     $kmltime=$kmlt[0]."T".$kmlt[1]."Z";
    $TimeInstant = $end->addChild('end','&#xA;');
    $timePosition = $TimeInstant->addChild('timePosition','&#xA;');
    $timePosition->addChild('time',$kmltime);
   }
   $obj_map->asXML($dmp.$item.'('.$def.').kml');
   print('<a href="'.$dmp.$item.'('.$def.').kml">Карта выборки (KML)</a><br>');
   print('<a href="map_create.php?item='.$item.'" target="_blank">Карта выборки (jpg, в новом окне)</a><br><br>');
   //make statistics
   //observers
   //print('<b>'.$item.'</b> <i>наблюдали в следующих местах</i>:<br>');
   print('<i>Места наблюдений и наблюдатели</i>:<br>');
   $obs_pers = $obj_tree->xpath("..//observer");
   $obs_arr=array();
   foreach($obs_pers as $num=>$pers){
    if(!isset($obs_arr["$pers"])){
     $obs_arr["$pers"]=$pers;
    }else{}
   }
   ksort($obs_arr);
   //locations
   $loc_list = $obj_tree->xpath("..//location");
   $loc_arr=array();
   foreach($loc_list as $num=>$loc){
    if(!isset($loc_arr["$loc"])){
     $loc_arr["$loc"]=$loc;
    }else{}
   }
   ksort($loc_arr);
   //
   //conditions
   $cond_list = $obj_tree->xpath("..//condition");
   $cond_arr=array();
   foreach($cond_list as $num=>$cond){
    if(!isset($cond_arr["$cond"])){
     $cond_arr["$cond"]=$cond;
    }else{}
   }
   ksort($cond_arr);
   //datetimes
   $time_list = $obj_tree->xpath("..//datetime");
   $time_arr=array();
   foreach($time_list as $num=>$time){
    if(!isset($time_arr["$time"])){
     $time_arr["$time"]=$time;
    }else{}
   }
   ksort($time_arr);
   //
   $lo_arr=array();
   foreach($loc_arr as $numl=>$loc){
    $lo_arr["$loc"]=array();
    foreach($obs_arr as $numo=>$obs){
     $loc_obs_list = $obj_tree->xpath("..//object[location='$loc' and observer='$obs']");
     foreach($loc_obs_list as $n=>$scho){
   $sch_obs = $scho->observer;
   if(!isset($lo_arr["$loc"]["$sch_obs"])){
    $lo_arr["$loc"]["$sch_obs"]='';
   }else{}
     }
    }
   }
   foreach($lo_arr as $kl=>$loc){
    print($kl.' (');
    $keys = array_keys($loc);
    sort($keys);
    $num_keys=count($keys);
    print($keys[0]);
    if($num_keys>1){
     print(', '.$keys[($num_keys-1)]);
    }else{}
    print('); ');
   }
   print('<br>');
   //
   $cd_arr=array();
   foreach($cond_arr as $numc=>$cond){
    $cd_arr["$cond"]=array();
    foreach($time_arr as $numt=>$time){
     $cond_time_list = $obj_tree->xpath("..//object[condition='$cond' and datetime='$time']");
     foreach($cond_time_list as $n=>$scho){
   $sch_time = $scho->datetime;
   if(!isset($cd_arr["$cond"]["$sch_time"])){
    $cd_arr["$cond"]["$sch_time"]='';
   }else{}
     }
    }
   }
   print('<i>Состояния и их сроки</i>:<br>');
   foreach($cd_arr as $kc=>$cond){
    print(''.$kc.' (');
    $keys = array_keys($cond);
    sort($keys);
    $num_keys=count($keys);
    //first time to "human" mode
    $timef_a=explode(' ',$keys[0]);
    $datef_a=explode(':',$timef_a[0]);
    $datef_a=array_reverse($datef_a);
    $datef=implode('.',$datef_a);
    //last time to "human" mode
    $timel_a=explode(' ',$keys[($num_keys-1)]);
    $datel_a=explode(':',$timel_a[0]);
    $datel_a=array_reverse($datel_a);
    $datel=implode('.',$datel_a);
    print($datef);
    if($num_keys>1 && $datef!==$datel){
     print(' - '.$datel);
    }else{}
    print('); ');
   }
   print('<br><a href="objs_viewer.php?item='.$item.'&page=1" target="_blank">Посмотреть все изображения объекта в базе</a><br>');
  }else{}
  //
  if(isset($stat) && $stat=='all'){
   //search all objects and create array with the data
   all_data($dmp,$serv,$db_dir,$db_file,$def);
  }else{}
  //-------------------------------------------------------------------------------------------------------------
  //load end of the page
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