<?php
$taxdata=taxdata_exists($db);
$sel_taxons=taxdata_exists_plain($taxdata);
$sdata=get_section_desc($db,$sect);
print('<div class="treetitle">'.$sdata['title'].' ('.$sdata['description'].'):</div>');
print('<div class="tree">');
//
$taxons=get_sect_tax($db,$sect);
$counter=array();
foreach($taxons as $trank=>$txns){
  $counter["$trank"]=array();
  $counter["$trank"]['all']=count($txns);
  $counter["$trank"]['val']=0;
  foreach($txns as $curr_tax){
    if($curr_tax['status']=='valid'){
      $counter["$trank"]['val']=$counter["$trank"]['val']+1;
    }else{}
  }
}
print('<div class="counter"><b>In the database "<strong>'.$sdata['title'].'"</strong> there are these taxa:</b><div>');
foreach($counter as $crank=>$cdata){
  $r_ps=$r_order["$crank"];
  $r_count=$cdata['all'];
  $r_count_v=$cdata['val'];
  print($r_ps.': all '.$r_count.', valid - '.$r_count_v.'<br>');
}
print('</div></div>');

$names_all=array();
foreach($taxons as $txr=>$trd){
 ksort($taxons["$txr"]);
 foreach($trd as $trdn=>$trdd){
  $names_all[]=$trdn;
 }
}
sort($names_all);
$ranks=array();$rnum=0;
foreach($r_order as $rname=>$rpse){
 if(isset($taxons["$rname"])){
  $ranks[$rnum]=array('taxon'=>$rname,'pseudonym'=>$rpse);
  $rnum++;
 }else{}
}
$allspnames=get_allspec_names($db);
$spec=array();
foreach($names_all as $treename){
 if(isset($allspnames["$treename"])){
  $spec["$treename"]=$treename;
 }else{}
}
$rtaxon = array_shift($taxons[$ranks[0]['taxon']]);
print_taxon($rtaxon,$ranks[0]['taxon'],$styles);
$rname=$rtaxon['name'];
show_subtree($db,$taxdata,$cpecimen_parts,$taxons,$ranks,$rname,1,$sel_taxons,$site,$styles);
print('</div>');
?>