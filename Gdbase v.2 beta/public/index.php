<?php
session_start();
//
$page_title='KGBase-GDBase by K.Grebennikov (v.2 2015) - главная страница администратора';
$main_page=true;
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
//
//-----------------------------------------------------------------
//GDBase Data management system (GeoData Base of digital photo images)
//GDBase - система управления данными (база геоданных цифровых фотоизображений)
//------------------------------------------------------------------
//copyrights:
//eng: State reserve "Bogdinsko-Baskunchaksky",2014 (glagolev1974@mail.ru),
//Konstantin A. Grebennikov, 2014 (kgrebennikov@gmail.com)
//v.2 - 2015
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
 if($c_user['role']=='admin' || $c_user['role']=='editor' || $c_user['role']=='user'){
  include('template/top');
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