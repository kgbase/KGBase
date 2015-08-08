<?php
//load data about collections
$coll_tree = simplexml_load_file('kgbase/base/bases/'.$base.'/collections.xml');
foreach($coll_tree->collection as $coll_item){
 $coll_name = $coll_item->name;
 if($coll_name==$collect){
  $coll_description = $coll_item->description;
  print('<b>'.$collect.':</b> '.$coll_description.'<br><br>');
  $coll_owner = $coll_item->owner;
  print('Владелец: '.$coll_owner.'<br><br>');
  $coll_curator = $coll_item->curator;
  print('Куратор: '.$coll_curator.'<br><br>');
  $coll_contact = $coll_item->contact;
  print('Контактная информация: '.$coll_contact.'<br><br>');
 }else{}
}
?>