<?php
session_start();
$page_title='KGBase-GDBase by K.Grebennikov (v.2 2015) - выход из системы';
include('template/top');
//-----------------------------------------------------------------------------------------------
@$user=$_POST['usroff'];
if(isset($user) && $_SESSION['username']==$user){
 print('Увы! <b>'.$user.'</b> покинул(а) нас!<br> Но Вы всегда можете вернуться!</a>.');
 unset($_SESSION['username']);
 include('template/form_login');
}else{
 print('Чтобы выйти из системы, сначала нужно войти :)');
 include('template/form_login');
}
?>