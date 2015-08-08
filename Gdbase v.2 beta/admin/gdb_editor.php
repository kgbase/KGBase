<?php
session_start();
//
$page_title='KGBase-GDBase by K.Grebennikov (v.2 2015) - редактирование данных';
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
  if(isset($dir)){@$itdir=$dir.'/';}else{}
  @$item=$_GET['item'];
  @$do=$_POST['do'];
  @$object=$_POST['object'];
  @$confirm=$_POST['confirm'];
  include('classSimpleImage.php');
  //---------------------------------------------------------------------------------------------------------------
  //load list of directories if nothing selected
  //---------------------------------------------------------------------------------------------------------------
  if(!isset($do) && !isset($dir)){
   $index = simplexml_load_file($ifile);
   print('<p><b>Зарегистрированные данные:</b><br>
    <a href="'.$ifile.'">XML-индекс базы</a></p>');
   $construct = $index->construct;
   foreach($construct->directory as $dir_d){
    $name = $dir_d->name;
    $time = $dir_d->time;
    $aut =  $dir_d->author;
    $loc =  $dir_d->location;
    $desc =  $dir_d->description;
    print('<b>'.$name.'</b><br>');
    print('<i>Добавлено:</i>'.$time.'<br>');
    print('<i>Автор:</i>'.$aut.'<br>');
    print('<i>Место:</i>'.$loc.'<br>');
    print('<i>Описание:</i>'.$desc.'<br>');
    //statistic data of dir
    $dir_xml = simplexml_load_file('../photodb/'.$name.'/'.$name.'.xml');
    $dir_imgs = $dir_xml->xpath("..//img");
    $dir_objects = $dir_xml->xpath("..//object");
    $num_imgs=count($dir_imgs);
    $num_objects=count($dir_objects);
    print('<u><b>'.$num_imgs.'</b></u> изображений,  указано <u><b>'.$num_objects.'</b></u> объектов<br>');
    //links
    print('<a href="../photodb/'.$name.'/'.$name.'.xml" target="_blank">XML-данные</a>&nbsp;&nbsp;');
    print('<a href="../photodb/'.$name.'/'.$name.'.kml" target="_blank">KML-файл</a><br>');
    print('<a href="gdb_editor.php?dir='.$name.'" target="_blank">Смотреть и редактировать</a><br><br>');
   }
  }else{}
  //------------------------------------------------------------------------------------------------------------------
  //load list of images
  //------------------------------------------------------------------------------------------------------------------
  if(!isset($do) && isset($dir) && $dir!==null && !isset($item)){
   $index = simplexml_load_file($db.$itdir.$dir.'.xml');
   $ID = $index->ID;
   $TimeAdd = $index->TimeAdd;
   $observer = $index->observer;
   $locations = $index->locations;
   $header = $index->header;
   print('<p><b>'.$ID.'</b><br>
      <i>Добавлено</i>: '.$TimeAdd.'<br>
      <i>Наблюдатель</i>:'.$observer.'<br>
      <i>Место</i>: '.$locations.'<br>
      <i>Описание</i>: '.$header.'</p>');
   $images = $index->images;
   foreach($images->img as $img){
    //get image properties
    $filename = $img->filename;
    $file_path=$db.$itdir.$filename;
    $file_prev=$db.$itdir.'prev/'.$filename;
    $datetime = $img->datetime;
    $latitude = $img->latitude;
    $longitude = $img->longitude;
    $ele = $img->ele;
    $objects = $img->objects;
    //make image preview if it's not exists
    if(!file_exists($file_prev)){
     $image = new SimpleImage();
     $image->load($file_path);
     $image->resizeToWidth(500);
     $image->save($file_prev);
    }else{}
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
     print('<br>Название</i>: '.$name.'<br>
     <i>Состояние</i>: '.$condition.'<br>');
    }
    //links
    print('<b><a href="gdb_editor.php?dir='.$dir.'&item='.$filename.'" target="_blank">Смотреть и редактировать</a></b><br></p>');
    //form: delete image with data from directory
    print('<form enctype="multipart/form-data" action="gdb_editor.php?dir='.$dir.'&item='.$filename.'" method="post">
    <input name="do" type="hidden" value="del" />
    <input type="submit" name="submit" value="Удалить изображение (с данными, без возврата!)" /></form><br><hr>');
   }
  }else{}

  //--------------------------------------------------------------------------------------------------------------
  //view selected image and select the action
  //--------------------------------------------------------------------------------------------------------------

  if(!isset($do) && isset($dir) && isset($item)){
   $index = simplexml_load_file($db.$itdir.$dir.'.xml');
   //common folder properties
   $ID = $index->ID;
   $TimeAdd = $index->TimeAdd;
   $observer = $index->observer;
   $locations = $index->locations;
   $header = $index->header;
   print('<p><i>Наблюдатель</i>:'.$observer.'<br>
      <i>Место</i>: '.$locations.'</p>');
   //get image properties
   $imgs = $index->xpath("..//img[filename='$item']");
   $img=$imgs[0];
   $filename = $img->filename;
   $file_path=$db.$itdir.$filename;
   $file_prev=$db.$itdir.'prev/'.$filename;
   $datetime = $img->datetime;
   $latitude = $img->latitude;
   $longitude = $img->longitude;
   $ele = $img->ele;
   $objects = $img->objects;
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
    //form: delete object
    print('<form enctype="multipart/form-data" action="gdb_editor.php?dir='.$dir.'&item='.$item.'" method="post">
    <input name="do" type="hidden" value="del" />
    <input name="object" type="hidden" value="'.$name.'" />
    <input type="submit" name="submit" value="Удалить объект" /></form>');
    //form end
   }
   //create kml with point if not exists
   if(!file_exists($file_path.'.kml')){
    $kml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><kml xmlns="http://earth.google.com/kml/2.2"></kml>');
    $Folder = $kml->addChild('Folder','&#xA;');
    $Placemark = $Folder->addChild('Placemark','&#xA;');
    $Placemark->addChild('name',$item);
    $kml_img_path=$serv.$file_path;
    $kml_img_path=str_replace('/../','/',$kml_img_path);
    $Placemark->addChild('description',$datetime.'<br>&lt;img src="'.$kml_img_path.'"/&gt;');
    $Point = $Placemark->addChild('Point','&#xA;');
    $coord=$longitude.','.$latitude.','.$ele;
    $Point->addChild('coordinates',$coord);
    $kml->asXML($file_path.'.kml');
   }else{}
   print('<br><a href="'.$file_path.'.kml">KML-файл с координатами точки</a><br>');
   print('<a href="map_create.php?dir='.$dir.'&item='.$item.'" target="_blank">Посмотреть точку на карте (jpg)</a><br>');
   //form: add new object
   print('<hr><br><b><u>Добавить объект</u></b>:<br>');
   
   $objects = simplexml_load_file('../gdbase/objects.xml');
   $conditions = simplexml_load_file('../gdbase/conditions.xml');
   print('<form enctype="multipart/form-data" action="gdb_editor.php?dir='.$dir.'&item='.$filename.'" method="post">');
   print('<input name="do" type="hidden" value="add" />');
   print('Название объекта:<br>Выбрать');
   print('<select name="oob">');
   print('<option value="Нет объекта" size="30">Нет объекта</option>');
   $list_obj=array();
   foreach($objects->object as $l_obj){
    $name = $l_obj->name;
    $list_obj["$name"]=$l_obj;
   }
   ksort($list_obj);
   foreach($list_obj as $nomen=>$obj){
    $ob_name = $obj->name;
    print('<option value="'.$ob_name.'" size="30">'.$ob_name.'</option>');
   }
   print('</select>&nbsp;Или ввести новый:');
   print('<input name="nob" type="text" size="30" value="Нет объекта" />');
   print('<br>Состояние объекта:<br>Выбрать');
   print('<select name="ocond">');
   print('<option value="Не известно" size="30">Не известно</option>');
   $list_cond=array();
   foreach($conditions->condition as $l_cond){
    $name = $l_cond->name;
    $list_cond["$name"]=$l_cond;
   }
   ksort($list_cond);
   foreach($list_cond as $nomen=>$cond){
    $cond_name = $cond->name;
    print('<option value="'.$cond_name.'" size="30">'.$cond_name.'</option>');
   }
   print('</select>&nbsp;Или ввести новый:');
   print('<input name="ncond" type="text" size="30" value="Не известно" />');
   print('<br><input type="submit" name="submit" value="Добавить" /></form>');
   print('<br><hr></p>');
  }else{}

  //----------------------------------------------------------------------------------------------------------
  //edit objects data of the image
  //----------------------------------------------------------------------------------------------------------
  if(isset($do) && isset($dir) && isset($item)){
   //-------------------------------------------
   //add new object from form
   if($do=='add'){
    $oob=$_POST['oob'];
    $nob=$_POST['nob'];
    $ocond=$_POST['ocond'];
    $ncond=$_POST['ncond'];
    //If user attempts to add new object
    if($nob!=='Нет объекта' && $nob!==''){
     $objects = simplexml_load_file('../gdbase/objects.xml');
     //search "new" object in index, add to index if it's really new
     $srch = $objects->xpath("..//object[name='$nob']");
     $srch_n = count($srch);
     if($srch_n==0){
   $object = $objects->addChild('object','&#xA;');
   $object->addChild('name',$nob);
   $objects->asXML('../gdbase/objects.xml');
   print('Новый объект <b>'.$nob.'</b> добавлен к списку объектов<br>');
     }else{
     print('К сведению: объект <b>'.$nob.'</b> уже есть в списке объектов<br>');
     }
    }else{}
    //If user attempts to add new condition
    if($ncond!=='Не известно' && $ncond!==''){
     $conditions = simplexml_load_file('../gdbase/conditions.xml');
     //search "new" condition in index, add to index if it's really new
     $srch = $conditions->xpath("..//object[name='ncond']");
     $srch_n = count($srch);
     if($srch_n==0){
   $condition = $conditions->addChild('condition','&#xA;');
   $condition->addChild('name',$ncond);
   $conditions->asXML('../gdbase/conditions.xml');
   print('Новое состояние <b>'.$ncond.'</b> добавлено к списку состояний<br>');
     }else{
     print('К сведению: состояние <b>'.$ncond.'</b> уже есть в списке состояний<br>');
     }
    }else{}
    //Add object to image
    $time_add = date("Y:m:d H:i:s");
    $index = simplexml_load_file($db.$itdir.$dir.'.xml');
    $imgs = $index->xpath("..//img[filename='$item']");
    $objs=$imgs[0];
    $img = $objs->objects;
    //search "new" object name in objects that already attached to the image
    //
    if($nob!=='Нет объекта' && $nob!==$oob){
     foreach($img->object as $l_obj){
   $name = $l_obj->name;
   if($name==$nob){$add_restr='no';}else{}
     }
     if(!isset($add_restr)){
   $new_obj = $img->addChild('object','&#xA;');
   $new_obj->addChild('name',$nob);
   $new_obj->addChild('datetime',$time_add);
   $new_obj->addChild('detauth',$detauth);
   print('Объект с именем <b>'.$nob.'</b> добавлен к изображению во время <i>'.$time_add.'</i> автором с именем <b>'.$detauth.'</b><br>');
     }else{
   print('Объект с именем <b>'.$nob.'</b> Не может быть добавлен к изображению - он уже отмечен на нем. Если Вы хотите изменить состояние объекта - сначала удалите существующий объект, затем создайте новый объект с тем же именем и новым состоянием.</b><br>');
     }
    }elseif($nob=='Нет объекта' && $nob!==$oob){
     $list_obj=array();
     foreach($img->object as $l_obj){
   $name = $l_obj->name;
   if($name==$oob){$add_restr='no';}else{}
     }
     if(!isset($add_restr)){
   $new_obj = $img->addChild('object','&#xA;');
   $new_obj->addChild('name',$oob);
   $new_obj->addChild('datetime',$time_add);
   $new_obj->addChild('detauth',$detauth);
   print('Объект с именем <b>'.$oob.'</b> добавлен к изображению во время <i>'.$time_add.'</i> автором с именем <b>'.$detauth.'</b><br>');
     }else{
   print('Объект с именем <b>'.$oob.'</b> Не может быть добавлен к изображению - он уже отмечен на нем. Если Вы хотите изменить состояние объекта - сначала удалите существующий объект, затем создайте новый объект с тем же именем и новым состоянием.</b><br>');
     }
    }elseif($nob=='Нет объекта' && $nob==$oob){
     print('Не задано имя объекта. Объект к изображению добавлен не будет<br>');
    }else{}
    //Add condition to the object
    if(isset($new_obj)){
     if($ncond!=='Не известно' && $ncond!==$ocond){
   $new_obj->addChild('condition',$ncond);
   print('Состояние <b>"'.$ncond.'"</b> добавлено к объекту<br>');
     }elseif($ncond=='Не известно' && $ncond!==$ocond){
   $new_obj->addChild('condition',$ocond);
   print('Состояние <b>"'.$ocond.'"</b> добавлено к объекту<br>');
     }elseif($ncond=='Не известно' && $ncond==$ocond){
   $new_obj->addChild('condition',$ocond);
   print('Состояние <b>"'.$ocond.'"</b> добавлено к объекту<br>');
     }else{
   $new_obj->addChild('condition',$ocond);
   print('Состояние <b>"'.$ocond.'"</b> добавлено к объекту<br>');
     }
    }else{}
    //Dump data to index and redirect backwards
    $index->asXML($db.$itdir.$dir.'.xml');
    print('<b><a href="gdb_editor.php?dir='.$dir.'&item='.$item.'">Вернуться к изображению</a></b><br>');
    print('<b><a href="gdb_editor.php?dir='.$dir.'">Вернуться к списку изображений</a></b><br>');
   }else{}
   //delete image || object from image
   if($do=='del'){
    //--delete image (if object not set)
    if(!isset($object)){
     //-confirm delete image 
     if(!isset($confirm)){
   $index = simplexml_load_file($db.$itdir.$dir.'.xml');
   $imgs = $index->xpath("..//img[filename='$item']");
   $img=$imgs[0];
   $filename = $img->filename;
   $file_path=$db.$itdir.$filename;
   $file_prev=$db.$itdir.'prev/'.$filename;
   $datetime = $img->datetime;
   $latitude = $img->latitude;
   $longitude = $img->longitude;
   $ele = $img->ele;
   $objects = $img->objects;
   print('<p><b>Вы собираетесь удалить изображение (<u>!со всеми данными, безвозвратно)!</u>:</b></p>');
   //print image with properties
   print('<p><hr><br><i><b>Файл</b></i>: '.$filename.'<br>
    <a href="'.$file_path.'" target="_blank"><img src="'.$file_prev.'"></a><br>
    <i>Время снимка</i>: '.$datetime.'<br>
    <i>Долгота</i>: '.$longitude.', <i>широта</i>: '.$latitude.', <i>высота (н.у.м.)</i>: '.$ele.'<br>
    Объекты:<br>');
   print('<form enctype="multipart/form-data" action="gdb_editor.php?dir='.$dir.'&item='.$filename.'" method="post">
   <input name="do" type="hidden" value="del" />
   <input name="confirm" type="hidden" value="yes" />
   <input type="submit" name="submit" value="Удалить (без возврата!)" /></form><br>');
   print('<br><b>Нет!<br><a href="gdb_editor.php?dir='.$dir.'">вернуться к списку изображений</a><br><hr><br>');
     //-delete image
     }else{
   //delete the image and data about the image from xml
   $dom_xml = new DomDocument;
   $dom_xml->load($db.$itdir.$dir.'.xml');
   $imgs = $dom_xml->getElementsByTagName("img");
   foreach($imgs as $it=>$img){
    $img_node = simplexml_import_dom($img);
    $img_name = $img_node->filename;
    if($img_name==$item){
     $file_path=$db.$itdir.$item;
     unlink($file_path);
     $all_img = $dom_xml->getElementsByTagName("images")->item(0);
     $del = $all_img->getElementsByTagName("img")->item($it);
     $all_img->removeChild($del);
    }else{}
   }
   $dom_xml->save($db.$itdir.$dir.'.xml');
   ////delete point with the image from kml
   $dom_kml = new DomDocument;
   $dom_kml->load($db.$itdir.$dir.'.kml');
   $places = $dom_kml->getElementsByTagName("Placemark");
   foreach($places as $it=>$place){
    $place_node = simplexml_import_dom($place);
    $place_name = $place_node->name;
    if($place_name==$item){
     $folder = $dom_kml->getElementsByTagName("Folder")->item(0);
     $del = $folder->getElementsByTagName("Placemark")->item($it);
     $folder->removeChild($del);
    }else{}
   }
   $dom_kml->save($db.$itdir.$dir.'.kml');
   print('Изображение <b>'.$item.'</b> и все связанные с ним данные успешно удалены из директории <b>'.$dir.'</b>.<br><br>');
   print('<a href="gdb_editor.php?dir='.$dir.'">вернуться к списку изображений директории</a><br><br>');
    }
    //---delete object (if object set)
    }else{
     //-confirm delete object 
     if(!isset($confirm)){
   $index = simplexml_load_file($db.$itdir.$dir.'.xml');
   $imgs = $index->xpath("..//img[filename='$item']");
   $img=$imgs[0];
   $filename = $img->filename;
   $file_path=$db.$itdir.$filename;
   $file_prev=$db.$itdir.'prev/'.$filename;
   $objects = $img->objects;
   print('<p><b>Вы собираетесь удалить объект, связанный с изображением:</b></p>');
   
   //print image with properties
   print('<p><hr><br><i><b>Файл</b></i>: '.$filename.'<br>
    <a href="'.$file_path.'" target="_blank"><img src="'.$file_prev.'"></a><br>
    Объект:<br>');
   $sch_obs = $img->xpath("..//object[name='$object']");
   $obj=$sch_obs[0];
   $name = $obj->name;
   $condition = $obj->condition;
   $datetime = $obj->datetime;
   $detauth = $obj->detauth;
   print('<br><i>Название</i>: '.$name.'<br>
     <i>Состояние</i>: '.$condition.'<br>
     <i>Добавлено</i>: '.$datetime.'<br>
     <i>Автором</i>: '.$detauth.'<br>');
   print('<form enctype="multipart/form-data" action="gdb_editor.php?dir='.$dir.'&item='.$filename.'" method="post">
   <input name="do" type="hidden" value="del" />
   <input name="confirm" type="hidden" value="yes" />
   <input name="object" type="hidden" value="'.$name.'" />
   <input type="submit" name="submit" value="Удалить (без возврата!)" /></form><br>');
   print('<br><b>Нет!<br><a href="gdb_editor.php?dir='.$dir.'&item='.$filename.'">вернуться к изображению</a><br><hr><br>');
     //-delete object
     }else{
   $dom_xml = new DomDocument;
   $dom_xml->load($db.$itdir.$dir.'.xml');
   $imgs = $dom_xml->getElementsByTagName("img");
   foreach($imgs as $it=>$img){
    $img_node = simplexml_import_dom($img);
    $img_name = $img_node->filename;
    if($img_name==$item){
     $dom_objs_all = $img->getElementsByTagName("objects")->item(0);
     $dom_objs = $dom_objs_all->getElementsByTagName("object");
     foreach($dom_objs as $itt=>$dom_obj){
      $obj_node = simplexml_import_dom($dom_obj);
      $obj_name = $obj_node->name;
      if($obj_name==$object){
    $del = $dom_objs_all->getElementsByTagName("object")->item($itt);
    $dom_objs_all->removeChild($del);
      }else{}
     }
    }else{}
   }
   $dom_xml->save($db.$itdir.$dir.'.xml');
   print('Объект к именем <b>'.$object.'</b> успешно удален с изображения <b>'.$item.'</b> в директории <b>'.$dir.'</b>.<br>');
   print('<a href="gdb_editor.php?dir='.$dir.'&item='.$item.'">вернуться к изображению</a><br><hr><br>');
     }
    }
   }else{}
  }else{}
  //-------------------------------------------------------------------------------------------------------------
  //load and of the page
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
