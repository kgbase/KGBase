<?php
include('functions.php');
$xmldir = 'kgbase/base/';
 $xml_ind = simplexml_load_file('kgbase/base/bases/'.$base.'/index.xml');
 $tree_description = $xml_ind->description;
 print('<p>'.$tree_description.'<br><br>');
 print('<a href="kgbase/base/bases/'.$base.'/'.$base.'.zip" target="_blank">Архив с файлами базы</a></p>');
 print('<p><a href="'.$site.'&base='.$base.'&search=yes">Поиск по базе</a></p>');
 //---------------------------------------------------
 //array of specimens & function for print
 $index=array();
 foreach($xml_ind->children() as $name=>$ch){
  $index[$name]=trim($ch);
 }
 //make arrays of specimens, locations and series
 //some string are commented: to printing collection and other data directly with taxonomic tree was planned firstly
 $sp_ind = simplexml_load_file('kgbase/base/bases/'.$base.'/'.$index['collection']);
 $spec=array();//array: taxons of exists specimens
 foreach($sp_ind->series as $series){
  foreach($series->specimen as $specimen){
   $taxon = $specimen->taxon; $taxon=trim($taxon);
   if(!isset($spec["$taxon"])){$spec[]=$taxon;}else{}
  }
 }
 ksort($spec);
 //-------------------------------------------------------
 //-------------------------------------------------------
 $file=$xmldir.$tree.'.xml';
 $xml = simplexml_load_file('kgbase/base/bases/'.$base.'/'.$index['taxonomy']);
 $all_taxonomy=convert_taxonomy($xml);
 $ranks=$all_taxonomy['ranks'];
 $taxons=$all_taxonomy['taxons'];
 //---print taxons
 //count taxons
 print('Всего таксонов (по рангам):<br>');
 foreach($ranks as $lev=>$rank){
  $tax_count=count($taxons[$rank['taxon']]);
  $ranks[$lev]['count']=1;
  print('- '.$rank['pseudonym'].': '.$tax_count.'.<br>');
 }
 //root
 $rtaxon = array_shift($taxons[$ranks[0]['taxon']]);
 print_taxon($rtaxon,$ranks[0]['taxon']);
 $rname=$rtaxon['name'];
 show_sub_taxa($taxons,$ranks,$rname,1,$spec,$site,$base);
?>