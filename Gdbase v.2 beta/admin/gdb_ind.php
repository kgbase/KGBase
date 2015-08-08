<?php
session_start();
//
$page_title='KGBase-GDBase by K.Grebennikov (v.2 2015) - индексирование загруженных данных';
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
if(isset($c_user['name'])){
 if($c_user['role']=='admin'){
  //authorised user's interface and functions
  include('template/top');
  //set default variables for execution
  $db_dir = '../photodb/';//default database directory
  $db_file = 'db.xml';//default database index
  $start = 0;//flag: 0 (default) - don't EXECUTE, 1 - do EXECUTE
  $def=date('Y-M-d_h-i-s');//default directory for upload (by time)
  include('classSimpleImage.php');
  //get variables for execution from _POST
  @$updir = $_POST['updir'];
  @$deltat = $_POST['deltat'];
  @$gmt = $_POST['gmt'];$duts=$gmt*3600;
  @$obs_author = $_POST['obs_author'];
  @$cloc = $_POST['cloc'];
  @$sdesc = $_POST['sdesc'];
  @$resw = $_POST['resw'];
  if(!isset($updir)){
   print('<p align="justify">Выберите в форме ниже директорию из списка и заполните необходимые текстовые поля</p>');
   print('<p align="justify"></p>');
   //list of indexed directories
   $dir_ind=array();
   $index = simplexml_load_file($db_dir.$db_file);
   $construct = $index->construct;
   foreach($construct->directory as $dir){
    $dir_name = $dir->name;
    $dir_ind[]=trim($dir_name);
   }
   sort($dir_ind);
   //list of actually exists in ../photodb directories
   $dir_ex=array();
   $db_content=scandir($db_dir);
   foreach($db_content as $n_cont=>$cont){
    if(is_dir($db_dir.$cont) && $cont!=='.' && $cont!=='..'){
     $dir_ex[]=$cont;
    }
   }
   sort($dir_ex);
   //make diff - list of exists, but not indexed directories
   $dir_diff = array_diff($dir_ex, $dir_ind);
   sort($dir_diff);
   //print form
   $c_dirs=count($dir_diff);
   if($c_dirs>0){
    print('<p><form enctype="multipart/form-data" action="gdb_ind.php" method="post"><select name="updir">');
    foreach($dir_diff as $n_diff=>$dir_n){
     print('<option value="'.$dir_n.'" size="60">'.$dir_n.'</option>');
    }
    print('</select><br>');
    print('Поправка GMT (часы):<br>
      <input type="text" name="gmt" size="10" value="0"><br>
      Поправка времени (трек - снимок, секунды):<br>
      <input type="text" name="deltat" size="10" value="0"><br>
      Размер уменьшенных копий (ширина, пикселей):<br>
      <input type="text" name="resw" size="10" value="300"><br>
      Автор наблюдений (фотограф):<br>
      <input name="obs_author" type="text" size="30" value="Author" /><br>
      Место наблюдений (общее):<br>
      <input name="cloc" type="text" size="100" value="Location" /><br>
      Описание:<br>
      <input name="sdesc" type="text" size="120" value="Description" /><br>
      <input type="submit" name="submit" value="ПОЕХАЛИ" /><br>
    </form></p>');
    print('<p align="justify">Для того, чтобы проиндексировать директорию, разместите ее в '.$serv.'photodb/ (директория должна содержать фотографии и файл формата GPX с треком).</p>');
   }else{
    print('<p align="justify">К сожалению, нет не проиндексированных директорий!</p>');
    print('<p align="justify">Для того, чтобы проиндексировать директорию, разместите ее в '.$serv.'photodb/ (директория должна содержать фотографии и файл формата GPX с треком).</p>');
   }
  }else{
   //MODULE:---add directory to index---
   $time_add = date("Y:m:d H:i:s");
   $db_ind = simplexml_load_file($db_dir.$db_file);
   $db_body = $db_ind->construct;
   $dir_db = $db_body->addChild('directory','&#xA;');
   $dir_db->addChild('name',$updir);
   $dir_db->addChild('time',$time_add);
   $dir_db->addChild('author',$obs_author);
   $dir_db->addChild('location',$cloc);
   $dir_db->addChild('description',$sdesc);
   $db_ind->asXML($db_dir.$db_file);
   print('Директория <b>'.$updir.'</b> внесена в индекс<br><b>'.$time_add.'</b><br>Автор: <b>'.$obs_author.'</b><br>Место: <b>'.$cloc.'</b><br>Описание: <b>'.$sdesc.'</b><br>');
   //---RESULT: index with new directory ---
   //MODULE:---make array of files---
   
   $files=array();
   $files['foto']=array();
   $content=scandir($db_dir.$updir);
   foreach($content as $n_file=>$curr_file){
    $file_ext=substr($curr_file,-3);
    if($file_ext=='GPX' || $file_ext=='gpx'){
     $files['gpx']=$curr_file;
    }else{}
    if($file_ext=='JPG' || $file_ext=='jpg'){
     $files['foto'][]=$curr_file;
    }else{}
   }
   sort($files['foto']);
   $num_foto=count($files['foto']);
   print('В директории '.$serv.$db_dir.$updir.'/ '.$num_foto.' фотографий<br>');
   print('GPX-файл: '.$files['gpx'].'<br>');
   //---RESULT: array: $files [gpx]=>...gpx, ['foto']=>[0]some1.jpg,[1]=>some2.jpg ---
   //MODULE:---creation preview files---
   mkdir($db_dir.$updir.'/prev/');
   foreach($files['foto'] as $n_foto=>$foto){
    $image = new SimpleImage();
    $image->load($db_dir.$updir.'/'.$foto);
    $image->resizeToWidth($resw);
    $image->save($db_dir.$updir.'/prev/'.$foto);
   }
  //---RESULT: new directory /prev with preview of photo files ---
  //MODULE:---exif export module---
  //create table of images with data from exif
  $exif=array();$nexif=0;
  foreach($files['foto'] as $jk => $jv){
   $jfile=$db_dir.$updir.'/'.$jv;
   $ex=exif_read_data($jfile);
   $exif[$nexif]['name']=$ex[FileName];
   $exif[$nexif]['datetime']=$ex[DateTimeOriginal];
   $exif[$nexif]['UTS']=strtotime($ex[DateTimeOriginal]);
   $nexif=$nexif+1;
  }
  //---RESULT:associate array:(No)file name|file datatime|file Unix timestamp---
  //-------------------------------------
  //MODULE: GPX parsing module
  $gpxfile=$db_dir.$updir.'/'.$files['gpx'];
  $gpx = simplexml_load_file($gpxfile);
  $gpxtab=array();$ngpx=0;
  foreach($gpx->trk->trkseg as $trkseg){
    foreach($trkseg->trkpt as $trkpt){
    $gpxtab[$ngpx]['lat']=$trkpt[lat];$gpxtab[$ngpx]['lon']=$trkpt[lon];
     foreach($trkpt->time as $time){
   $t=substr($time,0,19);
   $t=str_replace("T"," ",$t);
   $t=str_replace("-",":",$t);
   $gpxtab[$ngpx]['datetime']=$t;
   $gpxtab[$ngpx]['UTS']=strtotime($t);
   }
     foreach($trkpt->ele as $ele){
   $gpxtab[$ngpx]['ele']=$ele;
     }
   $ngpx=$ngpx+1;}
  }
  //---RESULT:associate array:(No)latitude|longitude|elevation|datetime|Unix timestamp---
  //-----------------------------------------------------
  //MODULE:---exif and GPX data comparison------
  for($iex=0;$iex<$nexif;$iex++){
   for($igpx=1;$igpx<($ngpx-2);$igpx++){
    $delta1=($gpxtab[($igpx-1)]['UTS']+$duts)-($exif[$iex]['UTS']+$deltat);
    if($delta1==0){$delta1=-1;}else{}
    $delta2=($gpxtab[$igpx]['UTS']+$duts)-($exif[$iex]['UTS']+$deltat);
    if($delta2==0){$delta1=1;}else{}
    if($delta1<0 && $delta2>0){
     if($delta2<(0-$delta1)){$exif[$iex]['lat']=$gpxtab[($igpx-1)]['lat'];
           $exif[$iex]['lon']=$gpxtab[($igpx-1)]['lon'];
           $exif[$iex]['ele']=$gpxtab[($igpx-1)]['ele'];
           $exif[$iex]['GPX datetime']=$gpxtab[($igpx-1)]['datetime'];
     }else{$exif[$iex]['lat']=$gpxtab[$igpx]['lat'];
     $exif[$iex]['lon']=$gpxtab[$igpx]['lon'];
     $exif[$iex]['ele']=$gpxtab[$igpx]['GPX datetime'];
     $exif[$iex]['GPX datetime']=$gpxtab[$igpx]['datetime'];}
    }else{}
   }
  if(!isset($exif[$iex]['lat'])){$exif[$iex]['lat']=0;}else{}
  if(!isset($exif[$iex]['lon'])){$exif[$iex]['lon']=0;}else{}
  if(!isset($exif[$iex]['ele'])){$exif[$iex]['ele']=0;}else{}
  if(!isset($exif[$iex]['GPX datetime'])){$exif[$iex]['GPX datetime']="2000:01:01 00:00:00";
  print('<b>Ошибочка!</b> Фото "'.$exif[$iex]['name'].'" снято за пределами трека :(. Ищите его на координатах 0,0 2000:01:01 00:00:00 или поправьте трек ;)<br>');}else{}
  }
  //---RESULT:associate array:(No)file name|file datetime|file Unix timestamp|GPX datetime|latitude|longitude|elevation ---
  //----------------------------------------------------------
  //MODULE:---data files create&upload---
  //kml file create&upload
  $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><kml xmlns="http://earth.google.com/kml/2.2"></kml>');
  $Document = $xml->addChild('Document','&#xA;');
  $Document->addChild('description',$obs_author.': '.$cloc.': '.$sdesc);
  $Folder = $Document->addChild('Folder','&#xA;');
  $Folder->addChild('name',$updir);
  $Folder->addChild('open',1);
  $Style = $Folder->addChild('Style','&#xA;');
  $ListStyle = $Style->addChild('ListStyle','&#xA;');
  $ListStyle->addChild('listItemType','check');
  $ListStyle->addChild('bgColor','00ffffff');
  for($ikml=-1;$ikml<($nexif-1);$ikml++){
   $Placemark = $Folder->addChild('Placemark','&#xA;');
   $Placemark->addChild('name',$exif[($ikml+1)]['name']);
   $kml_desc=$obs_author.': '.$cloc.': '.$sdesc.'<br>('.$exif[($ikml+1)]['datetime'].')<br>&lt;img src="'.$serv.'/'.$db_dir.$updir.'/'.$exif[($ikml+1)]['name'].'"/&gt;';
   $kml_desc=str_replace('/../','/',$kml_desc);
   $Placemark->addChild('description',$kml_desc);
   $Placemark->addChild('styleUrl','#waypoint');
   $Point = $Placemark->addChild('Point','&#xA;');
   $Point->addChild('extrude',1);
   $Point->addChild('coordinates',$exif[($ikml+1)]['lon'].','.$exif[($ikml+1)]['lat'].','.$exif[($ikml+1)]['ele']);
   $end = $Placemark->addChild('end','&#xA;');
   $kmlt=explode(" ",$exif[($ikml+1)]['datetime']);
    $kmlt[0]=str_replace(":","-",$kmlt[0]);
    $kmltime=$kmlt[0]."T".$kmlt[1]."Z";
   $TimeInstant = $end->addChild('end','&#xA;');
   $timePosition = $TimeInstant->addChild('timePosition','&#xA;');
   $timePosition->addChild('time',$kmltime);
  }
  $kmlf=$updir.".kml";
  $xml->asXML($db_dir.$updir.'/'.$kmlf);
  print('<br>Созданные файлы:<br>KML (файл привязки изображений): <b><a href="'.$db_dir.$updir.'/'.$kmlf.'">'.$kmlf.'</a></b><br>');
  //xml index create&upload
  $xml_ind = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><folder></folder>');
  $xml_ind->addChild('ID',$updir);
  $xml_ind->addChild('TimeAdd',$time_add);
  $xml_ind->addChild('observer',$obs_author);
  $xml_ind->addChild('locations',$cloc);
  $xml_ind->addChild('header',$sdesc);
  $images = $xml_ind->addChild('images','&#xA;');
  for($ixml=-1;$ixml<($nexif-1);$ixml++){
   $img = $images->addChild('img','&#xA;');
   $img->addAttribute('imgno',($ixml+1));
   $img->addChild('filename',$exif[($ixml+1)]['name']);
   $img->addChild('datetime',$exif[($ixml+1)]['datetime']);
   $img->addChild('latitude',$exif[($ixml+1)]['lat']);
   $img->addChild('longitude',$exif[($ixml+1)]['lon']);
   $img->addChild('ele',$exif[($ixml+1)]['ele']);
   $objects = $img->addChild('objects','&#xA;');
  }
  $xmlf = $updir.'.xml';
  $xml_ind->asXML($db_dir.$updir.'/'.$xmlf);
  print('XML (индекс загруженных файлов): <b><a href="'.$db_dir.$updir.'/'.$xmlf.'">'.$xmlf.'</a></b><br>');
  print('<a href="gdb_editor.php?dir='.$updir.'">Смотреть и редактировать загруженные данные</a><br>');
  /*
  //
  */
  //execution end
  }
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