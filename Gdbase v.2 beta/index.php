<?php
session_start();
$page_title=$page_title='KGBase-GDBase by K.Grebennikov (v.2 2015) - вход в систему';
include('template/top');
//--------------------------------------------------
//login
//--------------------------------------------------
//user detect
@$user=$_SESSION['username'];
if(isset($user) ){
 $b=file('08rE1qK');
 $c_user=array();
 foreach($b as $n=>$str){
  $str_arr=explode('/',$str);
  if($str_arr[0]==$user){
   $c_user['name']=trim($str_arr[2]);
   $c_user['role']=trim($str_arr[3]);
   $c_user['role_ps']=trim($str_arr[4]);
  }else{}
 }
 print('Здравствуйте, <b>'.$c_user['name'].'</b> Вы - наш <b>'.$c_user['role_ps'].'.</b> Доступные Вам варианты входа в систему вы можете выбрать в меню справа.');
 if($c_user['role']=='admin'){
  include('template/menu_admin');
 }else{}
 if($c_user['role']=='editor'){
  include('template/menu_editor');
 }else{}
 if($c_user['role']=='user'){
  include('template/menu_user');
 }else{}
 //message&form if login is not perform
}else{
@$usr_req=$_POST['usr'];
@$pass_req=$_POST['pass'];
//login form as default
if(!isset($usr_req) && !isset($pass_req)){
 print('К сожалению, Вы пока не вошли в систему. Представьтесь, пожалуйста, в форме справа');
 include('template/form_login');
}else{}
 //log in
 //return to login form if neither login&password is sent
 if(isset($usr_req) && !isset($pass_req)){
  print('К сожалению, Вы не ввели свой пароль. Попробуйте еще раз, пожалуйста.');
 }else{}
 //try to login
 if(isset($usr_req) && isset($pass_req)){
  //get registered users from database text file
  $bc=file('08rE1qK');
  $control=array();
  foreach($bc as $n=>$str){
   $str_arr=explode('/',$str);
   $control["$str_arr[0]"]=$str_arr[1];
  }
  if(isset($control["$usr_req"])){
   if($control["$usr_req"]==$pass_req){
   //register session if password is correct
   $_SESSION['username'] = $usr_req;
   $b=file('08rE1qK');
   $c_user=array();
   foreach($b as $n=>$str){
    $str_arr=explode('/',$str);
    if($str_arr[0]==$usr_req){
     $c_user['name']=trim($str_arr[2]);
     $c_user['role']=trim($str_arr[3]);
     $c_user['role_ps']=trim($str_arr[4]);
    }else{}
   }
   print('Здравствуйте, <b>'.$c_user['name'].'</b> Вы - наш <b>'.$c_user['role_ps'].'</b> Доступные Вам варианты входа в систему вы можете выбрать в меню справа.');
   if($c_user['role']=='admin'){
    include('template/menu_admin');
   }else{}
   if($c_user['role']=='editor'){
    include('template/menu_editor');
   }else{}
   if($c_user['role']=='user'){
    include('template/menu_user');
   }else{}
  }else{
   print('Извините, <b>'.$usr_req.'!</b> Вероятно, Вы ввели неправильный пароль. Попробуйте еще раз, пожалуйста.');
   include('template/form_login');
  }
   }else{
    print('Извините, но мы не знаем, кто такой<b>'.$usr_req.'</b>. Попробуйте войти под известным нам именем, пожалуйста');include('template/form_login');
   }
 }else{}
}
?>