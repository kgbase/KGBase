<?php
session_start();
//
$page_title='KGBase-GDBase by K.Grebennikov (v.2 2015) - управление директориями данных';
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
//--------------------------------------------------------------------
//set title and load top of the page
//set&get global variables
$ifile='../photodb/db.xml';
@$dlmode=$_GET['dlmode'];
@$targ=$_POST['target'];
@$do=$_POST['do'];
@$confirm=$_POST['confirm'];
//----------------------------------------------------------------------------------------------------------------
//list of directories - if no action selected
//-----------------------------------------------------------------------------------------------------------------
if(isset($c_user['name'])){
 if($c_user['role']=='admin'){
  //authorised user's interface and functions
  include('template/top');
  if(!isset($do)){
   if(!isset($dlmode)){$dlmode='num';}else{}
   $index = simplexml_load_file($ifile);
   print('<p><b>Зарегистрированные данные:</b><br>
    <a href="'.$ifile.'">XML-индекс базы</a></p>');
   print('<a href="gdb_manager.php?dlmode=alph">Директории по алфавиту</a><br>');
   print('<a href="gdb_manager.php?dlmode=num">Директории по дате добавления (по умолчанию)</a><br>');
   $construct = $index->construct;
   $dirs=array();
   if($dlmode=='alph'){
    foreach($construct->directory as $dir){
     $name = $dir->name;
     $dirs["$name"]=$dir;
    }
   }else{
    foreach($construct->directory as $dir){
     $time = $dir->time;
     $dirs["$time"]=$dir;
    }
   }
   ksort($dirs);
   foreach($dirs as $ind=>$dir){
    $name = $dir->name;
    $time = $dir->time;
    $aut =  $dir->author;
    $loc =  $dir->location;
    $desc =  $dir->description;
    print('<b>'.$name.'</b><br>');
    print('<i>Добавлено: </i>'.$time.'<br>');
    print('<i>Автор:</i> '.$aut.'<br>');
    print('<i>Место:</i> '.$loc.'<br>');
    print('<i>Описание: </i>'.$desc.'<br>');
    //statistic data of dir
    $dir_xml = simplexml_load_file('../photodb/'.$name.'/'.$name.'.xml');
    $dir_imgs = $dir_xml->xpath("..//img");
    $dir_objects = $dir_xml->xpath("..//object");
    $num_imgs=count($dir_imgs);
    $num_objects=count($dir_objects);
    print('<br><u><b>'.$num_imgs.'</b></u> изображений,  указано <u><b>'.$num_objects.'</b></u> объектов<br>');
    //links
    print('<a href="../photodb/'.$name.'/'.$name.'.xml">XML-данные</a>&nbsp;&nbsp;');
    print('<a href="../photodb/'.$name.'/'.$name.'.kml">KML-файл</a><br>');
    print('<form enctype="multipart/form-data" action="gdb_manager.php" method="post">
    <input name="do" type="hidden" value="del" />
    <input name="target" type="hidden" value="'.$name.'" />
    <input type="submit" name="submit" value="Удалить (без возврата!)" /></form>');
    print('<a href="gdb_editor.php?dir='.$name.'" target="_blank">Смотреть и редактировать</a><br><hr><br>');
   }
  }else{}
  //--------------------------------------------------------------------------------------------------------
  //delete folder with all data from the database
  //--------------------------------------------------------------------------------------------------------

  if(isset($do) && $do=='del'){
  if(isset($confirm) && $confirm=='yes'){
   //delete target folder
   $tfiles=scandir('../photodb/'.$targ.'/');
   //search prev!!!
   if(file_exists('../photodb/'.$targ.'/prev/')){
    $prev_tfiles=scandir('../photodb/'.$targ.'/prev/');
    foreach($prev_tfiles as $nom=>$file){
     if($file!=='.' && $file!=='..'){
   unlink('../photodb/'.$targ.'/prev/'.$file);
     }else{}
    }
    rmdir('../photodb/'.$targ.'/prev/');
   }else{}
   foreach($tfiles as $nom=>$file){
    if($file!=='.' && $file!=='..' && $file!=='/prev/'){
     @unlink('../photodb/'.$targ.'/'.$file);
    }else{}
   }
   rmdir('../photodb/'.$targ.'/');
   //delete target from index
   $dom_xml = new DomDocument;
   $dom_xml->load($ifile);
   $dirs = $dom_xml->getElementsByTagName("directory");
   foreach($dirs as $it=>$dir){
    $dir_node = simplexml_import_dom($dir);
    $name = $dir_node->name;
    if($name==$targ){
     $construct = $dom_xml->getElementsByTagName("construct")->item(0);
     $del = $construct->getElementsByTagName("directory")->item($it);
     $construct->removeChild($del);
    }else{}
   }
   $dom_xml->save($ifile);
   print('<p>Данные и файлы из директории "<b>'.$targ.'</b>" успешно удалены</p>');
   $index = simplexml_load_file($ifile);
   print('<br>Можно <a href="gdb_manager.php">вернуться к списку директорий</a><br>');
   
   print('<p><b>Или удалить другие директории:</b></p>');
   $construct = $index->construct;
   foreach($construct->directory as $dir){
    $name = $dir->name;
    $time = $dir->time;
    $aut =  $dir->author;
    $loc =  $dir->location;
    $desc =  $dir->description;
    print('<b>'.$name.'</b><br>');
    print('<i>Добавлено:</i>'.$time.'<br>');
    print('<i>Автор:</i>'.$aut.'<br>');
    print('<i>Место:</i>'.$loc.'<br>');
    print('<i>Описание:</i>'.$desc.'<br>');
    print('<form enctype="multipart/form-data" action="gdb_manager.php" method="post">
    <input name="do" type="hidden" value="del" />
    <input name="target" type="hidden" value="'.$name.'" />
    <input type="submit" name="submit" value="Удалить (без возврата!)" /></form>');
   }
  }else{
   $index = simplexml_load_file($ifile);
   print('<p><b>Вы собираетесь удалить директорию (<u>!со всеми данными, безвозвратно)!</u>:</b></p>');
   $construct = $index->construct;
   $dir_sch = $index->xpath("..//directory[name='$targ']");
   $dir=$dir_sch[0];
    $name = $dir->name;
    $time = $dir->time;
    $aut =  $dir->author;
    $loc =  $dir->location;
    $desc =  $dir->description;
    print('<b>'.$name.'</b><br>');
    print('<i>Добавлено:</i>'.$time.'<br>');
    print('<i>Автор:</i>'.$aut.'<br>');
    print('<i>Место:</i>'.$loc.'<br>');
    print('<i>Описание:</i>'.$desc.'<br>');
    print('<form enctype="multipart/form-data" action="gdb_manager.php" method="post">
    <input name="do" type="hidden" value="del" />
    <input name="confirm" type="hidden" value="yes" />
    <input name="target" type="hidden" value="'.$targ.'" />
    <input type="submit" name="submit" value="Да, удалить" /></form><br>');
    print('<br><b>Нет!</b> <a href="gdb_manager.php">вернуться к списку директорий</a><br>');
  }
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