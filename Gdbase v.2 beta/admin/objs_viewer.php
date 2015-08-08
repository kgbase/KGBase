<?php
session_start();
//
@$item = $_GET['item'];
if(isset($item)){
 $page_title='KGBase-GDBase by K.Grebennikov (v.2 2015) - Просмотр фотографий объекта '.$item;
}else{
 @$img = $_GET['img'];
 $page_title='KGBase-GDBase by K.Grebennikov (v.2 2015) - Просмотр данных изображения '.$img;
}
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
//-----------------------------------------------------------
//set title and load top of the page
if(isset($c_user['name'])){
 if($c_user['role']=='admin'){
  //set default variables for execution
  $c_file=file('../conf');
  $serv_arr=explode('||',$c_file[0]);
  $serv = trim($serv_arr[1]);//base address
  $db_dir = '../photodb/';//default database directory
  $db_file = 'db.xml';//default database index
  $def=date('Y-M-d_H-i-s');//current system time
  $perp=9;//images per 1 page
  $img_per=3;//images in row
  //get variables for execution from _POST
  @$item = $_GET['item'];
  @$page = $_GET['page'];
  @$dir = $_GET['dir'];
  @$img = $_GET['img'];
  include('template/top');
  //---------------------------------------------------------------------------------------
  //search&display all images of selected object
  //---------------------------------------------------------------------------------------
  if(isset($item) && !isset($dir) && !isset($img)){
   print($serv.'---<br>');
   if(!isset($page)){$page=1;}else{}
   print('В базе есть изображения <b>'.$item.'</b>:<br><br>');
   $nob=0;
   $obj_data=array();
   $obj_tree = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><result></result>');
   $index = simplexml_load_file($db_dir.$db_file);
   $dirs = $index->xpath("..//directory");
   foreach($dirs as $it=>$dir_c){
    $d_name = $dir_c->name;
    $direct = simplexml_load_file($db_dir.$d_name.'/'.$d_name.'.xml');
    $loc = $direct->locations;
    $obs = $direct->observer;
    $objs = $direct->xpath("..//object[name='$item']");
    foreach($objs as $num=>$obj){
     $obj_name = $obj->name;
     $obj_name=trim($obj_name);$item=trim($item);
     $condition = $obj->condition;
     $dettime = $obj->datetime;
     $detauth = $obj->detauth;
     $node = $obj->xpath("parent::*");$imgs = $node[0]->xpath("parent::*");
     $img_c=$imgs[0];
     $filename = $img_c->filename;
     $link=$serv.'/'.$db_dir.$d_name.'/'.$filename;
     $prev=$serv.'/'.$db_dir.$d_name.'/prev/'.$filename;
     $datetime = $img_c->datetime;
     $lat = $img_c->latitude;
     $lon = $img_c->longitude;
     $ele = $img_c->ele;
     //
     $obj_data[$nob]['dir']=$d_name;
     $obj_data[$nob]['img']=$filename;
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
     $obj_data[$nob]['prev']=$prev;
     $nob=$nob+1;
    }
   }
   print('Всего - <b>'.$nob.'</b> изображений<br>');
   if(isset($obj_data[(($page*$perp)-($perp+1))])){
    print('<a href="objs_viewer.php?item='.$item.'&page='.($page-1).'">Предыдущая страница</a>&nbsp;&nbsp;&nbsp;');
   }
   print('Страница <b>'.$page.'</b>');
   if(isset($obj_data[(($page*$perp)+1)])){
    print('&nbsp;&nbsp;&nbsp;<a href="objs_viewer.php?item='.$item.'&page='.($page+1).'">Следующая страница</a>');
   }
   print('<hr><br><font size="2">');
   print('<table>');
   print('<tr>');$ncell=1;
   if($page==1){
   $startit=($page*$perp)-($perp+1);
   }else{$startit=($page*$perp)-$perp;}
   $endit=$page*$perp;
   for($i=$startit;$i<$endit;$i++){
    if($ncell==($img_per+1)){
     print('</tr><tr>');$ncell=1;
    }else{}
    if(isset($obj_data[$i])){
     $object=$obj_data[$i];
     print('<td>');
     print('№ '.($i+1).':<br>');
     print('<a href="'.$obj_data[$i]['link'].'" target="_blank"><img src="'.$obj_data[$i]['prev'].'"></a><br>');
     print('Место: <b>'.$obj_data[$i]['location'].'</b><br>');
     print('Время: <b>'.$obj_data[$i]['datetime'].'</b><br>');
     print('Состояние: <b>'.$obj_data[$i]['condition'].'</b><br>');
     print('Наблюдатель: <b>'.$obj_data[$i]['observer'].'</b><br>');
     print('Определил: <b>'.$obj_data[$i]['detauth'].'</b><br>');
     print('<a href="objs_viewer.php?dir='.$obj_data[$i]['dir'].'&img='.$obj_data[$i]['img'].'" target="_blank">подробные данные</a><br>');
     print('</td>');
     $ncell=$ncell+1;
    }else{}
   }
   print('</table>');
   //include('template/right');
  }else{}
  if(isset($dir) && isset($img)){
   $index = simplexml_load_file($db_dir.$db_file);
   $dirs = $index->xpath("..//directory[name='$dir']");
   $dir_d=$dirs[0];
   $d_name = $dir_d->name;
   $direct = simplexml_load_file($db_dir.$d_name.'/'.$d_name.'.xml');
   $imgs = $direct->xpath("..//img[filename='$img']");
   $image = $imgs[0];
   //get image properties
   $filename = $image->filename;
   $file_path=$db_dir.$dir.'/'.$filename;
   $file_path=str_replace('/../','/',$file_path);
   $file_prev=$db_dir.$dir.'/prev/'.$filename;
   $file_prev=str_replace('/../','/',$file_prev);
   $datetime = $image->datetime;
   $latitude = $image->latitude;
   $longitude = $image->longitude;
   $ele = $image->ele;
   $objects = $image->objects;
   //print image with properties
   print('<p><hr><br><i><b>Файл</b></i>: '.$filename.'<br>
    <a href="'.$file_path.'" target="_blank"><img src="'.$file_prev.'"></a><br>
    <i>Время снимка</i>: '.$datetime.'<br>
    <i>Долгота</i>: '.$longitude.', <i>широта</i>: '.$latitude.', <i>высота (н.у.м.)</i>: '.$ele.'<br>
    Объекты:<br>');
    //get & print objects on image
   $list_obj=array();
   foreach($objects->object as $l_obj){
    $name = $l_obj->name;
    $list_obj["$name"]=$l_obj;
   }
   ksort($list_obj);
   foreach($list_obj as $nomen=>$obj){
    $name = $obj->name;
    $condition = $obj->condition;
    $datetime = $obj->datetime;
    $detauth = $obj->detauth;
    print('<br><i>Название</i>: '.$name.'<br>
     <i>Состояние</i>: '.$condition.'<br>
     <i>Добавлено</i>: '.$datetime.'<br>
     <i>Автором</i>: '.$detauth.'<br>');
   }
   //create kml with point if not exists
   if(!file_exists($file_path.'.kml')){
    $kml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><kml xmlns="http://earth.google.com/kml/2.2"></kml>');
    $Folder = $kml->addChild('Folder','&#xA;');
    $Placemark = $Folder->addChild('Placemark','&#xA;');
    $Placemark->addChild('name',$filename);
    $kml_img_path=$serv.$file_path;
    $kml_img_path=str_replace('/../','/',$kml_img_path);
    $Placemark->addChild('description',$datetime.'<br>&lt;img src="'.$kml_img_path.'"/&gt;');
    $Point = $Placemark->addChild('Point','&#xA;');
    $coord=$longitude.','.$latitude.','.$ele;
    $Point->addChild('coordinates',$coord);
    $kml->asXML($file_path.'.kml');
   }else{}
   print('<br><a href="'.$file_path.'.kml">KML-файл с координатами точки</a><br>');
   
   //for administrators and editors only!
   print('<b><a href="gdb_editor.php?dir='.$dir.'&item='.$img.'" target="_blank">Редактировать</a></b><br></p>');
   //
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