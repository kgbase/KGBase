<?php
session_start();
//
$page_title='KGBase-GDBase by K.Grebennikov (v.2 2015) - сообщения пользователей об ошибках';
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
  $err_dir='../errlog';
  @$act=$_POST['act'];
  @$target=$_POST['target'];
  if(!isset($act)){
   //array of requested errors
   $errata=array();$c_err=0;
   $errors=scandir($err_dir);
   foreach($errors as $n_err=>$error){
    if($error!=='.' && $error!=='..'){
     $errata[$c_err]=array();
     $err = simplexml_load_file($err_dir.'/'.$error);
     $err_cont = $err->content;
     $dir = $err_cont->dir;
     $filename = $err_cont->filename;
     $name = $err_cont->name;
     $requestor = $err_cont->requestor;
     $comment = $err_cont->comment;
     $time = $err_cont->time;
     $errata[$c_err]['error']=$error;
     $errata[$c_err]['dir']=trim($dir);
     $errata[$c_err]['filename']=trim($filename);
     $errata[$c_err]['name']=trim($name);
     $errata[$c_err]['requestor']=trim($requestor);
     $errata[$c_err]['comment']=trim($comment);
     $errata[$c_err]['time']=trim($time);
     $c_err++;
    }
   }
   //print list of errors
   $count_err=count($errata);
   if($count_err>0){
    foreach($errata as $n_err=>$err){
     print('<p align=justify> - '.$err['time'].':<br>
            Запрос на удаление объекта <b>'.$err['name'].'</b> 
            на изображении <b>'.$err['filename'].'</b> <br>в директории <b>'.$err['dir'].'</b><br>
            от: <b>'.$err['requestor'].'</b><br> Комментарий: ');
     if($err['comment']!==''){
      print($err['comment'].'<br>');
     }else{print('Не комментария.<br>');}
    print('<a href="gdb_editor.php?dir='.$err['dir'].'&item='.$err['filename'].'" target="_blank">Перейти к редактированию</a> <form enctype="multipart/form-data" action="gdb_err.php" method="post"><input name="act" type="hidden" value="del" /><input name="target" type="hidden" value="'.$err['error'].'" /><input type="submit" name="submit" value="Удалить запрос" /></form></p>');
    }
   }else{print('<p>Сообщений об ошибках пока нет.</p>');}
  }else{}
  //
  if(isset($act) && $act=='del'){
   unlink($err_dir.'/'.$target);
   print('<p> Сообщение об ошибке '.$target.' успешно удалено</p>');
   print('<p><a href="gdb_err.php">Вернуться к списку сообщений об ошибках</a></p>');
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
