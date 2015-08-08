<?php
session_start();
//
$page_title='KGBase-GDBase by K.Grebennikov (v.2 2015) - резервное копирование данных';
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
  @$action=$_POST['action'];
  @$issue=$_POST['issue'];
  @$confirm=$_POST['confirm'];
  $db_dir = '../photodb/';//default database directory
  $db_file = 'db.xml';//default database index
  $back_dir = '../backup';//backup directory
  $curr_time=date('Y-m-d_h-i-s');
  //if no action selected, try to make list of backups and forms for actions
  if(!isset($action)){
   //make list of existing backups
   $back_list = array();
   $back_arr = scandir($back_dir);
   foreach($back_arr as $n_arr=>$back){
    if($back!=='.' && $back!=='..' && $back!=='temp'){
     $back_list[]=$back;
    }else{}
   }
   sort($back_list);
   $back_num=count($back_list);
   //display list of existing backups
   if($back_num>0){
    foreach($back_list as $n_list=>$back){
     print('<p><a href="'.$back_dir.'/'.$back.'" target="_blank">'.$back.'</a><p>');
     print('<p><form enctype="multipart/form-data" action="gdb_dump.php" method="post">
            <input name="action" type="hidden" value="restore" />
            <input name="issue" type="hidden" value="'.$back.'" />
            <input type="submit" name="submit" value="Восстановить данные из копии" /></form></p>');
    }
   }else{
    print('<p>Резервных копий данных пока нет</p>');
   }
   //print form for new backup creation
   print('<p></p><p><form enctype="multipart/form-data" action="gdb_dump.php" method="post">
    <input name="action" type="hidden" value="make" />
    <input type="submit" name="submit" value="Создать новую резервную копию данных" /></form></p>');
  }else{}
  //perform selected action
  if(isset($action)){
   //make new backup
   if($action=='make'){
    $index = simplexml_load_file($db_dir.$db_file);
    $zip=new ZipArchive;
    if($zip->open($back_dir.'/'.$curr_time.'.zip', ZipArchive::CREATE) === true){
     $zip->addFile($db_dir.$db_file,$db_file);
     $construct = $index->construct;
     foreach($construct->directory as $dir){
      $dir_name = $dir->name;$dir_name=trim($dir_name);
      $zip->addFile($db_dir.$dir_name.'/'.$dir_name.'.xml',$dir_name.'.xml');
      $zip->addFile($db_dir.$dir_name.'/'.$dir_name.'.kml',$dir_name.'.kml');
     }
     $zip->close();
    }else{echo 'unable to create backup!';}
    print('<p>Новая копия данных ('.$curr_time.') успешно создана. <a href="gdb_dump.php">Вернуться к списку</a></p>');
   }else{}
   //restore selected backup (!no cancel!)
   if($action=='restore' && isset($issue)){
    if(!isset($confirm)){
     print('<p>Вы уверены, что хотите восстановить данные из копии '.$issue.'? Все не сохраненные данные будут безвозвратно потеряны! Для подтверждения нажмите кнопку ниже. Или <a href="gdb_dump.php">вернитесь</a> и сохраните существующие данные.</p>');
     print('<p><form enctype="multipart/form-data" action="gdb_dump.php" method="post">
            <input name="action" type="hidden" value="restore" />
            <input name="issue" type="hidden" value="'.$issue.'" />
            <input name="confirm" type="hidden" value="true" />
            <input type="submit" name="submit" value="Я понимаю, что я делаю" /></form></p>');
    }else{}
    if(isset($confirm) && $confirm=='true'){
     //unzip
     $rest_back=$back_dir.'/'.$issue;
     $temp_dir=$back_dir.'/temp/'.$issue;
     $zip = new ZipArchive;
     $zip->open($rest_back);
     $zip->extractTo($temp_dir);
     $zip->close();
     //restore old data & delete existing data
     if(copy($temp_dir.'/'.$db_file,$db_dir.$db_file)){
     }else{print('<p>Не удалось скопировать индекс базы данных</p>');}
     $index = simplexml_load_file($db_dir.$db_file);
     $construct = $index->construct;
     foreach($construct->directory as $dir){
      $dir_name = $dir->name;$dir_name=trim($dir_name);
      if(file_exists($db_dir.$dir_name)){
       if(copy($temp_dir.'/'.$dir_name.'.xml',$db_dir.$dir_name.'/'.$dir_name.'.xml')){
       }else{print('<p>Не удалось скопировать '.$dir_name.'.xml</p>');}
       if(copy($temp_dir.'/'.$dir_name.'.kml',$db_dir.$dir_name.'/'.$dir_name.'.kml')){
       }else{print('<p>Не удалось скопировать '.$dir_name.'.kml</p>');}
      }else{print('<p>Директории '.$dir_name.' не существует в базе. Ее восстановление невозможно</p>');}
     }
     //delete temp
     $all_temp=scandir($temp_dir);
     foreach($all_temp as $tr=>$ash){
      if($ash!=='.' && $ash!=='..'){
       unlink($temp_dir.'/'.$ash);
      }else{}
     }
     rmdir($temp_dir);
     print('<p>Данные успешно восстановлены из резервной копии '.$issue.'. <a href="gdb_dump.php">Назад к списку копий.</a></p>');
    }else{}
    if(isset($confirm) && $confirm!=='true'){
     print('<p>Что-то пошло не так. Вы можете <a href="gdb_dump.php">попробовать еще раз</a>.</p>');
    }else{}
   }else{}
  }
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