<?php
@$base=$_GET['base'];
@$perf=$_POST['perf'];//perform the search
@$sloc=$_POST['sloc'];//search location
@$sarea=$_POST['sarea'];//search area
@$stn=$_POST['stn'];//search from time (date)
@$stx=$_POST['stx'];//search to time (date)
@$spers=$_POST['spers'];//search collector
include('functions.php');
//--------------------------------------------------------------------------------------
//if there is no command to perform the search - print form
if(!isset($perf)){
 $base_index=simplexml_load_file('kgbase/base/bases/'.$base.'/index.xml');
 $base_colls=simplexml_load_file('kgbase/base/bases/'.$base.'/collections.xml');
 $base_title=$base_index->title;
 print('<p><b>Поиск в базе данных: </b>'.$base_title.'</p>');
 print('<p>Критерии поиска (пустые поля не будут учтены):
        <form action="'.$site.'&base='.$base.'&search=yes" method="post">
        <input type="hidden" name="perf" value="yes">
        Название области сбора содержит:<br>
        <input type="text" name="sarea" size="50"><br>
        Описание места сбора содержит:<br>
        <input type="text" name="sloc" size="50"><br>
        Материал собран не ранее (год[xxxx])<br>
        <input type="text" name="stn" size="4"><br>
        Материал собран не позднее (год[xxxx])<br>
        <input type="text" name="stx" size="4"><br>
        Имя сборщика содержит:<br>
        <input type="text" name="spers" size="50"><br>');
 print('<input type="submit" value="Начать поиск"></form></p>');
}else{}
//--------------------------------------------------------------------------------------
//perform the search
if(isset($perf)){
 //check the values that must be numeric and convert to numeric if possible or set as empty another case
 if($stn!==''){
  if(is_numeric($stn)){
   $stn = (int) $stn;
  }else{$stn!=='';}
 }else{}
 if($stx!==''){
  if(is_numeric($stx)){
   $stx = (int) $stx;
  }else{$stx!=='';}
 }else{}
 if($sloc!=='' || $sarea!=='' || $stn!=='' || $stx!=='' || $spers!==''){
  $base_index=simplexml_load_file('kgbase/base/bases/'.$base.'/index.xml');
  $base_coll=$base_index->collection;
  $all_coll=simplexml_load_file('kgbase/base/bases/'.$base.'/'.$base_coll);
  $base_geo=$base_index->geodata;
  $all_geo=simplexml_load_file('kgbase/base/bases/'.$base.'/'.$base_geo);
  $base_tax=$base_index->taxonomy;
  $all_tax=simplexml_load_file('kgbase/base/bases/'.$base.'/'.$base_tax);
  $base_title=$base_index->title;
  //show query data
  print('<p>Вы искали в базе данных: <b>'.$base_title.'</b>:<br><br>');
  if($sloc!==''){
   print('Описание места сбора содержит: <b>'.$sloc.'</b><br>');
  }
  if($sarea!==''){
   print('Название области сбора содержит: <b>'.$sarea.'</b><br>');
  }
  if($stn!==''){
   print('Материал собран не ранее: <b>'.$stn.'</b><br>');
  }
  if($stx!==''){
   print('Материал собран не позднее: <b>'.$stx.'</b><br>');
  }
  if($spers!==''){
   print('Имя сборщика содержит: <b>'.$spers.'</b><br>');
  }
  print('</p>');
  $coll_result=xml_coll_to_array($all_coll,$all_geo);//get list of locations and list of specimens
  $coll_search_result=search_in_data($coll_result,$sloc,$sarea,$stn,$stx,$spers);//get the list of specimens according search pref.
  $num_result=count($coll_search_result);
  //show the results if something is found (list is not empty)
  if($num_result>0){
   $end_data=construct_taxonomy($coll_search_result,$all_tax);
   $end_taxons=$end_data['taxons'];
   $end_ranks=$end_data['ranks'];
   $sel_taxons=$end_data['sel_taxons'];//!!!check this array!!!
   print('<p><b>Вот что мы нашли:</b><br>');
   foreach($end_ranks as $rank){
    $r_n=$rank['taxon'];
    $r_ps=$rank['pseudonym'];
    $r_count=count($end_taxons["$r_n"]);
    print($r_ps.': '.$r_count.'<br>');
   }
   print('</p>');
   $rrank=$end_ranks[0]['taxon'];
   $rtaxon = reset($end_taxons["$rrank"]);
   print_taxon($rtaxon,$end_ranks[0]['taxon']);
   $rname=$rtaxon['name'];
   $locations=sort_locations($coll_result['locations'],$coll_search_result);
   show_sub_taxa_data($end_taxons,$end_ranks,$rname,1,$sel_taxons,$site,$base,$locations,$coll_search_result);
  }else{print('Извините, ничего не нашлось :( <a href="'.$site.'&base='.$base.'&search=yes">Поискать что-нибудь другое</a><br>');}
 }else{print('Ой! Вы не сказали, что Вы ищете - все поля формы остались пустыми :( <a href="'.$site.'&base='.$base.'&search=yes">Уточнить условия поиска (обратно)</a><br>');}
}else{}
?>