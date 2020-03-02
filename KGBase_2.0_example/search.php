<?php
@$base=$_GET['base'];
@$perf=$_GET['perf'];//perform the search
@$sloc=$_GET['sloc'];//search location
@$sarea=$_GET['sarea'];//search area
@$stn=$_GET['stn'];//search from time (date)
@$stx=$_GET['stx'];//search to time (date)
@$spers=$_GET['spers'];//search collector
@$gr=$_GET['gr'];//search group
@$sp=$_GET['sp'];//search species
//
//SEARCH IN DATABASES
//
if(isset($sect) && $sect!=='all'){
 $sdata=get_section_desc($db,$sect);
//--------------------------------------------------------------------------------------
//if there is no command to perform the search - print form
if(!isset($perf)){
 print('<div class="text_header"><b>Search in the database: </b>'.$sdata['title'].'<br>');
 print('Search terms (empty fields will be omitted):</div><div>
        <form class="sform" action="'.$site.'" method="get">
        <input type="hidden" name="mode" value="inc">
        <input type="hidden" name="sect" value="'.$sect.'">
        <input type="hidden" name="search" value="yes">
        <input type="hidden" name="perf" value="yes">
        <i>Search by place of collecting (observation, litetature referense)</i><br>
        Name of the region (country, district etc.) contains:<br>
        <input type="text" name="sarea" size="50"><br>
        Description of the location contains:<br>
        <input type="text" name="sloc" size="50"><br>
        <i>Search by time of collection (observation) and name of collector (observer)</i><br>
        Material collected ( observation made) not earlier (year[xxxx])<br>
        <input type="text" name="stn" size="4"><br>
        Material collected ( observation made) not later (year[xxxx])<br>
        <input type="text" name="stx" size="4"><br>
        Name of the collector (observer) contains:<br>
        <input type="text" name="spers" size="50"><br>');
 print('<input type="submit" value="Perform the search"></form></div>');
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
  //show query data
  print('<div class="search_result">Search request parameters: <b>'.$sdata['title'].'</b>:<br><br>');
  if($sloc!==''){
   print('Description of the location contains: <b>'.$sloc.'</b><br>');
  }
  if($sarea!==''){
   print('Name of the region (country, district etc.) contains: <b>'.$sarea.'</b><br>');
  }
  if($stn!==''){
   print('Material collected ( observation made) not earlier: <b>'.$stn.'</b><br>');
  }
  if($stx!==''){
   print('Material collected ( observation made) not later: <b>'.$stx.'</b><br>');
  }
  if($spers!==''){
   print('Name of the collector (observer) contains: <b>'.$spers.'</b><br>');
  }
  print('</div>');
  $sect_taxons=get_sect_tax($db,$sect);
  $sect_rank=get_sect_ranks($sect_taxons,$r_order);
  $sect_taxonomy=array();
  $sect_taxonomy['ranks']=$sect_rank;
  $sect_taxonomy['taxons']=$sect_taxons;
  $taxdata=taxdata_exists($db);
  $names=array();
  foreach($sect_taxons as $rank=>$taxa){
    foreach($taxa as $taxon=>$tdata){
      if(isset($taxdata['specimen']["$taxon"]) || isset($taxdata['reference']["$taxon"]) || isset($taxdata['observation']["$taxon"])){
       $names[]=$taxon;
      }else{}
    }
  }
  //select names of taxa of the section that have some data
  $geodata=get_locations($db);
  $coll_result=coll_to_array_select($db,$names,$geodata);
  $ref_result=ref_to_array_select($db,$names,$geodata);
  $obs_result=obs_to_array_select($db,$names,$geodata);
  $search_data=search_data($coll_result,$ref_result,$obs_result,$sloc,$sarea,$stn,$stx,$spers);
  if(isset($search_data['coll']) || isset($search_data['refs']) || isset($search_data['obs'])){
    $tax_result=construct_tax_result($search_data,$sect_taxonomy);
    $series_data=get_series_data($db);
    $end_taxons=$tax_result['taxons'];
    $end_ranks=$tax_result['ranks'];
    $sel_taxons=$tax_result['sel_taxons'];
    $counter=array();
    foreach($end_taxons as $trank=>$txns){
      $counter["$trank"]=array();
      $counter["$trank"]['all']=count($txns);
      $counter["$trank"]['val']=0;
      foreach($txns as $curr_tax){
        if($curr_tax['status']=='valid'){
          $counter["$trank"]['val']=$counter["$trank"]['val']+1;
        }else{}
      }
    }
    print('<div><b>Search results:</b><br>');
    foreach($end_ranks as $rank){
      $r_n=trim($rank['taxon']);
      $r_ps=$rank['pseudonym'];
      $r_count=$counter["$r_n"]['all'];
      $r_count_v=$counter["$r_n"]['val'];
      print($r_ps.': all '.$r_count.', valid - '.$r_count_v.'<br>');
    }
    print('<br></div>');
    $rrank=$end_ranks[0]['taxon'];
    $rtaxon = reset($end_taxons["$rrank"]);
    print_taxon($rtaxon,$end_ranks[0]['taxon'],$styles);
    $rname=$rtaxon['name'];
    $data_restruct=search_res_restruct($search_data);
    show_subtree_result($series_data,$geodata,$data_restruct,$cpecimen_parts,$end_taxons,$end_ranks,$rname,1,$sel_taxons,$site,$styles);
  }else{print('Sorry. We did not find anything. :( <a href="'.$site.'&sect='.$sect.'&search=yes">Look for something else.</a><br>');}
 }else{print('Oh! You did not said what you were looking for - all the fields in the form were empty. :( <a href="'.$site.'&base='.$sect.'&search=yes">Clarify search terms (back)</a><br>');}
}else{}
}else{}
//
//SEARCH OF TAXON
//
if(isset($sect) && $sect=='all'){
  /*
//--------------------------------------------------------------------------------------
//if there is no command to perform the search - print form
 if(!isset($perf)){
  $macro_arr=get_macrotax($db);
  $macro=implode('","',$macro_arr);
  $macro='"'.$macro.'"';
  $taxdata=taxdata_exists($db);
  $names=taxdata_exists_plain($taxdata);
  $alltax=implode('","',$names);
  $alltax='"'.$alltax.'"';
  print('<div><b>Для поиска данных по интересующему Вас таксону во всех базах данных воспользуйтесь одной из форм ниже: введите название либо группы таксонов, либо конкретного вида (подвида):</b></div>');
  print('<table class="search_forms" align="center"><tr><td width="50%">');
  
  
  print('<div><br></div>');
  print('<div><b>Поиск группы тасонов: </b></div>');
  print('<div align="justify">Начните вводить название - автодополнение покажет возможные варианты, имеющиеся в базе данных (таксоны, включающие в себя дочерние таксоны). Поиск ведется только в таксономических списках, которые могут включать не все таксоны, по которым имеются данные - см. форму справа. Если автодополнение не работает - у Вас отключено выполнение javascript.</div>');
  print('  <script>
  $( function() {
    var MacroTags = [
      '.$macro.'
    ];
    $( "#macro" ).autocomplete({
      source: MacroTags
    });
  } );
  </script>');
  print('<div><form action="'.$site.'&sect=all&search=yes" method="post">
        <input type="hidden" name="perf" value="yes">
        <i>Название группы таксонов</i>:<br>
        <input id="macro" type="text" name="gr" size="50"><br>');
  print('<input type="submit" value="Начать поиск"></form></div>');
  //
  print('<div><br></div></td>');
  
  
  
  print('<td width="50%"><div><b>Поиск данных о таксоне: </b></div>');
  print('<div align="justify">Начните вводить название - автодополнение покажет возможные варианты, имеющиеся в базе данных (таксоны, для которых имеются данные о коллекционных образцах, литературных указаниях либо наблюдениях). Если автодополнение не работает - у Вас отключено выполнение javascript.</div>');
  print('  <script>
  $( function() {
    var SpTags = [
      '.$alltax.'
    ];
    $( "#sp" ).autocomplete({
      source: SpTags
    });
  } );
  </script>');
  print('<div><form action="'.$site.'&sect=all&search=yes" method="post">
        <input type="hidden" name="perf" value="yes">
        <i>Название таксона</i>:<br>
        <input id="sp" type="text" name="sp" size="50"><br>');
  print('<input type="submit" value="Начать поиск"></form></div>');
  print('</td></tr></table>');
  
  
 }else{}
 //--------------------------------------------------------------------------------------
 //perform the search
 if(isset($perf)){
  //search the group
  if(isset($gr)){
    $macro_arr=get_macrotax($db);
    $taxdata=taxdata_exists($db);
    $names=taxdata_exists_plain($taxdata);
    $sect_taxons=get_asc_tax($db,$gr);
    $sect_rank=get_sect_ranks($sect_taxons,$r_order);
    $sect_taxonomy=array();
    $sect_taxonomy['ranks']=$sect_rank;
    $sect_taxonomy['taxons']=$sect_taxons;
   //show the results if something is found
   if(array_search($gr,$macro_arr)!==false){
     //calculate number of taxons of each rank - totat and valid separately
     $counter=array();
     foreach($sect_taxonomy['taxons'] as $trank=>$txns){
       $counter["$trank"]=array();
       $counter["$trank"]['all']=count($txns);
       $counter["$trank"]['val']=0;
       foreach($txns as $curr_tax){
        if($curr_tax['status']=='valid'){
         $counter["$trank"]['val']=$counter["$trank"]['val']+1;
        }else{}
      }
    }
    $end_taxons=$sect_taxonomy['taxons'];
    $end_ranks=$sect_taxonomy['ranks'];
    $sel_taxons=$names;
    //
    print('<div class="counter"><b>По группе <strong>'.$gr.'</strong> есть следующие данные:</b><div>');
    foreach($end_ranks as $rank){
     $r_n=trim($rank['taxon']);
     $r_ps=$rank['pseudonym'];
     $r_count=$counter["$r_n"]['all'];
      $r_count_v=$counter["$r_n"]['val'];
      print($r_ps.': всего '.$r_count.', достоверно идентифицированных - '.$r_count_v.'<br>');
    }
    print('</div></div>');
    $rrank=$end_ranks[0]['taxon'];
    $rtaxon = reset($end_taxons["$rrank"]);
    print_taxon($rtaxon,$end_ranks[0]['taxon'],$styles);
    $rname=$rtaxon['name'];
    show_subtree($db,$taxdata,$cpecimen_parts,$end_taxons,$end_ranks,$rname,1,$sel_taxons,$site,$styles); 
   }else{
    print('<div>Группа "'.$gr.'" среди имеющихся данных не найдена. 
    Вы можете <a href="'.$site.'&sect=all&search=yes">поискать что-нибудь еще</a>.</div>');
   } 
  }else{}
  //search the species
  if(isset($sp)){
   $txn=$sp;
   $kmldir = 'kml/';
   $taxdata=taxdata_exists($db);
   $names=taxdata_exists_plain($taxdata);
   $nlocs=count($taxdata['tax_loc']["$txn"]);
   if(array_search($sp,$names)!==false){
     $taxdata=get_taxdata($db,$sp);
     include('tax_list.php');
   }else{
     print('<div>Таксон "'.$txn.'" среди имеющихся данных не найден. Вы можете <a href="'.$site.'&sect=all&search=yes">поискать что-нибудь еще</a>.</div>');
   }
   }else{}
  }else{}
  */
}else{}
?>