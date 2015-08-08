<?php
$page_title='KGBase by K.Grebennikov (2014) - Пакетное рекурсивное создание миниатюр изображений базы:';
include('../templates/top');
include('classSimpleImage.php');
@$ih=$_POST['ih'];
@$select=$_POST['select'];
@$checkbox=$_POST['checkbox'];
//print form if no action selected
if(!isset($ih)){
 print('<form enctype="multipart/form-data" action="resimg.php" method="post">
    Размер создаваемых миниатюр по высоте:<br>
    <input name="ih" type="text" size="50" value="100" /><br>
    Перезаписывать существующие миниатюры?<br>
    Да
    <input type="radio" name="select" value="rewrite" />
    Нет
    <input type="radio" name="select" value="skip" checked="checked" /><br>
    Подтвердите запуск:<br>
    <input type="checkbox" name="checkbox" value="confirm" /><br>
    <input type="submit" name="submit" value="ЗАПУСК" /><br>
 </form>');
}else{}
if(isset($ih) && $checkbox!=='confirm'){
 print('Вы не подтвердили запуск <a href="resimg.php">Назад</a>');
}else{}
if(isset($ih) && $checkbox=='confirm'){
 $baseimg=scandir('../base/img');
 foreach($baseimg as $no=>$imgdir){
 if($imgdir!=='.' && $imgdir!=='..'){
  if(!is_dir("../base/img/$imgdir/res")){
   $imgs=scandir("../base/img/$imgdir");
   mkdir("../base/img/$imgdir/res");
   print("директория ../base/img/$imgdir/res успешно создана</br>");
   foreach($imgs as $noi=>$image){
    if($image!=='.' && $image!=='..' && $image!=='Thumbs.db' && $image!=='res'){
      $simage = new SimpleImage();
      $simage->load("../base/img/$imgdir/$image");
      $simage->resizeToHeight($ih);
      $simage->save("../base/img/$imgdir/res/$image");
    }else{}
   }
   print("--- миниатюры для ../base/img/$imgdir успешно созданы</br>");
  }elseif(is_dir("../base/img/$imgdir/res") && $select=='rewrite'){
   $trash=scandir("../base/img/$imgdir/res");
   foreach($trash as $tr=>$ash){
    if($ash!=='.' && $ash!=='..'){
     unlink("../base/img/$imgdir/res/$ash");
    }else{}
   }
   rmdir("../base/img/$imgdir/res");
   print("директория ../base/img/$imgdir/res и все ее содержимое успешно удалены</br>");
   $imgs=scandir("../base/img/$imgdir");
   mkdir("../base/img/$imgdir/res");
   print("директория ../base/img/$imgdir/res успешно создана</br>");
   foreach($imgs as $noi=>$image){
    if($image!=='.' && $image!=='..' && $image!=='Thumbs.db' && $image!=='res'){
      $simage = new SimpleImage();
      $simage->load("../base/img/$imgdir/$image");
      $simage->resizeToHeight($ih);
      $simage->save("../base/img/$imgdir/res/$image");
    }else{}
   }
   print("--- миниатюры для ../base/img/$imgdir успешно созданы</br>");
  }elseif(is_dir("../base/img/$imgdir/res") && $select=='skip'){
   print("!директория ../base/img/$imgdir/res уже существует!</br>");
  }else{}
 }else{}
 }
}else{}

//-------------------------------------------------------------------------------------------------------------
//load end of the page
include('../templates/bottom');
?>