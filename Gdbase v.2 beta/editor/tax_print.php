<?php
session_start();
//
$page_title='KGBase-GDBase by K.Grebennikov (v.2 2015) - просмотр таксономических списков';
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
 if($c_user['role']=='admin' || $c_user['role']=='editor'){
  //authorised user's interface and functions
  include('template/top');
  include('../GeoSpace/gsp.php');
  include('tax_functions.php');
  //set global variables
  $print_dir='../print/';
  $xmldir = '../trees/';
  @$view=$_GET['view'];
  @$mode=$_GET['mode'];
  $index = simplexml_load_file($xmldir.'index.xml');
  $dmp = '../dump/';//temp directory
  $db_dir = '../photodb/';//default database directory
  $db_file = 'db.xml';//default database index
  $def=date('Y-M-d_H-i-s');//current system time
  $mapdir=('../map/maps/');
 //
 //if tree is NOT indicated, show the list of trees
 //
  if(!isset($view)){
   print('<p align="justify">');
   print('Есть следующие таксономические списки:<br></p>');
   foreach($index->tree as $tree){
    $tree_name = $tree->description; trim($tree_name);
    $tree_file = $tree->file; trim($tree_file);
    print('<p align="justify"><b> - '.$tree_name.'</b></p>');
    
    print('<p align="justify"><a href="tax_print.php?view='.$tree_file.'">Список объектов</a>&nbsp;&nbsp;');
    print('&nbsp;&nbsp;<a href="tax_print.php?view='.$tree_file.'&mode=all">Просмотреть список</a></p>');
  }
 }else{}
 //
 //if tree is indicated, show the tree: all blank tree or tree of actual objects with data
 //
 if(isset($view) && !isset($mode)){
  $nob=0;
  $obj_data=array();
  $index = simplexml_load_file($db_dir.$db_file);
  $dirs = $index->xpath("..//directory");
  foreach($dirs as $it=>$dir){
   $d_name = $dir->name;
   $direct = simplexml_load_file($db_dir.$d_name.'/'.$d_name.'.xml');
   $loc = $direct->locations;
   $obs = $direct->observer;
   $objs = $direct->xpath("..//object");
   foreach($objs as $num=>$obj){
    $obj_name = $obj->name;
    $obj_name=trim($obj_name);
    $condition = $obj->condition;
    $dettime = $obj->datetime;
    $detauth = $obj->detauth;
    $node = $obj->xpath("parent::*");$imgs = $node[0]->xpath("parent::*");
    $img=$imgs[0];
    $filename = $img->filename;
    $link=$serv.'/'.$db_dir.$d_name.'/'.$filename;
    $link=str_replace('/../','/',$link);
    $datetime = $img->datetime;
    $lat = $img->latitude;
    $lon = $img->longitude;
    $ele = $img->ele;
    //
    $obj_data["$obj_name"][$nob]['name']=trim($obj_name);
    $obj_data["$obj_name"][$nob]['condition']=trim($condition);
    $obj_data["$obj_name"][$nob]['location']=trim($loc);
    $obj_data["$obj_name"][$nob]['datetime']=trim($datetime);
    $obj_data["$obj_name"][$nob]['lon']=trim($lon);
    $obj_data["$obj_name"][$nob]['lat']=trim($lat);
    $obj_data["$obj_name"][$nob]['ele']=trim($ele);
    $obj_data["$obj_name"][$nob]['observer']=trim($obs);
    $obj_data["$obj_name"][$nob]['detauth']=trim($detauth);
    $obj_data["$obj_name"][$nob]['dettime']=trim($dettime);
    $obj_data["$obj_name"][$nob]['link']=trim($link);
    $nob=$nob+1;
   }
  }
  
  ksort($obj_data);
  $ntypes=count($obj_data);
  //+++TAXONOMY+++
  $alltaxnum=0;
  $file=$xmldir.$view;
  $xml = simplexml_load_file($file);
  //
  $tree_desc = $xml->description;
  print('<p>Вы выбрали таксономическое дерево:<br>');
  print($tree_desc.'</p>');
  print('<p>В базе данных есть наблюдения следующих таксонов:</p>');
  //---get all ranks---
  $ranks=array();$n_ranks=0;
  $r_tree = $xml->ranks;
  foreach($r_tree->rank as $t_rank){
   $t_taxons=trim($t_rank->taxons);
   $t_taxon=trim($t_rank->taxon);
   $t_pseudonym=trim($t_rank->pseudonym);
   $ranks[$n_ranks]=array();
   $ranks[$n_ranks]['taxons']=$t_taxons;
   $ranks[$n_ranks]['taxon']=$t_taxon;
   $ranks[$n_ranks]['pseudonym']=$t_pseudonym;
   $n_ranks++;
  }
  //---get all taxons---
  //make arrays with taxons
  $taxons=array();
   foreach($ranks as $level=>$rank){
   $t=trim($rank['taxon']);
   $taxons["$t"]=array();
   $tax_arr = $xml->xpath("//$t");
   foreach($tax_arr as $tax){
    $tax_name = trim($tax->name);
    $taxons["$t"][$tax_name]=array();
    //make array with taxon data, including parent taxon
    $par_txs_ar = $tax->xpath("parent::*");//go to taxons node
    $par_node_name = trim($par_txs_ar[0]->getName());
    if($par_node_name=='tree'){
     $taxons["$t"][$tax_name]['parent']=$par_node_name;
     //properties of root taxon
     foreach($tax->children() as $children){
      $ch = $children->children();
      $chname = trim($ch->getName());
      if($chname=="" || $chname==null){
       //root data
       $it_name = $children->getName();
       $taxons["$t"][$tax_name][$it_name]=trim($children);
      }else{
       //synonyms and others
       $schname = $children->getName();
       $taxons["$t"][$tax_name][$schname]=array();
       foreach($children->children() as $s_children){
        $it_name = $s_children->getName();
        $taxons["$t"][$tax_name][$schname][$it_name]=trim($s_children);
       }
      }
     }
    }else{
     //if the taxon is not root
     $par_stx = $par_txs_ar[0]->xpath("parent::*");//go to "subtaxons" node
     $par_taxa = $par_stx[0]->xpath("parent::*");//go to parent taxon
     $par_name = trim($par_taxa[0]->name);
     $taxons["$t"][$tax_name]['parent']=$par_name;
     //properties of non-root taxons
     foreach($tax->children() as $children){
      $ch = $children->children();
      $chname = trim($ch->getName());
      if($chname=="" || $chname==null){
       //root data
       $it_name = $children->getName();
       $taxons["$t"][$tax_name][$it_name]=trim($children);
      }else{
       //synonyms and others
       $schname = $children->getName();
       $taxons["$t"][$tax_name][$schname]=array();
       foreach($children->children() as $s_children){
        $it_name = $s_children->getName();
        $taxons["$t"][$tax_name][$schname][$it_name]=trim($s_children);
       }
      }
     }
    }
   }
   ksort($taxons["$t"]);
  }
  //---search actual taxons of last level---
  $end_items=array();
  $l_rank_num=$n_ranks-1;
  $l_rank_name=$ranks[$l_rank_num]['taxon'];
  $end_items["$l_rank_name"]=array();
  foreach($taxons["$l_rank_name"] as $n_tx=>$taxon){
   if(isset($obj_data["$n_tx"])){
    $end_items["$l_rank_name"]["$n_tx"]=$taxon;
    $next_taxon=$taxon['parent'];
    $rank_lev=$l_rank_num-1;
    while($rank_lev>=0){
     $curr_rank_name=$ranks[$rank_lev]['taxon'];
     if(!isset($end_items["$curr_rank_name"])){
      $end_items["$curr_rank_name"]=array();
     }else{}
     $curr_taxon=$taxons["$curr_rank_name"]["$next_taxon"];
     $end_items["$curr_rank_name"]["$next_taxon"]=$curr_taxon;
     $next_taxon=$curr_taxon['parent'];
     $rank_lev--;
    }
   }else{}
  }
  foreach($end_items as $end_item=>$item_cont){
   ksort($item_cont);
   $end_items["$end_item"]=$item_cont;
  }
  $root_rank = $ranks[0]['taxon'];
  $root_tax = array_shift($end_items["$root_rank"]);
  print('<p>');
  print_taxon($root_tax,$root_rank);
  print('</p>');
  $name=$root_tax['name'];
  show_sub_data($obj_data,$end_items,$ranks,$name,1);
 }else{}
 //
 //full empty tree
 //
 if(isset($view) && isset($mode)){
  //+++TAXONOMY+++
  $alltaxnum=0;
  $file=$xmldir.$view;
  $xml = simplexml_load_file($file);
  //
  $tree_desc = $xml->description;
  print('<p>Вы выбрали таксономическое дерево:<br>');
  print($tree_desc.'</p>');
  print('<p>Иерархический список таксонов дерева:</p>');
  //---get all ranks---
  $ranks=array();$n_ranks=0;
  $r_tree = $xml->ranks;
  foreach($r_tree->rank as $t_rank){
   $t_taxons=trim($t_rank->taxons);
   $t_taxon=trim($t_rank->taxon);
   $t_pseudonym=trim($t_rank->pseudonym);
   $ranks[$n_ranks]=array();
   $ranks[$n_ranks]['taxons']=$t_taxons;
   $ranks[$n_ranks]['taxon']=$t_taxon;
   $ranks[$n_ranks]['pseudonym']=$t_pseudonym;
   $n_ranks++;
  }
  //---get all taxons---
  //make arrays with taxons
  $taxons=array();
   foreach($ranks as $level=>$rank){
   $t=trim($rank['taxon']);
   $taxons["$t"]=array();
   $tax_arr = $xml->xpath("//$t");
   foreach($tax_arr as $tax){
    $tax_name = trim($tax->name);
    $taxons["$t"][$tax_name]=array();
    //make array with taxon data, including parent taxon
    $par_txs_ar = $tax->xpath("parent::*");//go to taxons node
    $par_node_name = trim($par_txs_ar[0]->getName());
    if($par_node_name=='tree'){
     $taxons["$t"][$tax_name]['parent']=$par_node_name;
     //properties of root taxon
     foreach($tax->children() as $children){
      $ch = $children->children();
      $chname = trim($ch->getName());
      if($chname=="" || $chname==null){
       //root data
       $it_name = $children->getName();
       $taxons["$t"][$tax_name][$it_name]=trim($children);
      }else{
       //synonyms and others
       $schname = $children->getName();
       $taxons["$t"][$tax_name][$schname]=array();
       foreach($children->children() as $s_children){
        $it_name = $s_children->getName();
        $taxons["$t"][$tax_name][$schname][$it_name]=trim($s_children);
       }
      }
     }
    }else{
     //if the taxon is not root
     $par_stx = $par_txs_ar[0]->xpath("parent::*");//go to "subtaxons" node
     $par_taxa = $par_stx[0]->xpath("parent::*");//go to parent taxon
     $par_name = trim($par_taxa[0]->name);
     $taxons["$t"][$tax_name]['parent']=$par_name;
     //properties of non-root taxons
     foreach($tax->children() as $children){
      $ch = $children->children();
      $chname = trim($ch->getName());
      if($chname=="" || $chname==null){
       //root data
       $it_name = $children->getName();
       $taxons["$t"][$tax_name][$it_name]=trim($children);
      }else{
       //synonyms and others
       $schname = $children->getName();
       $taxons["$t"][$tax_name][$schname]=array();
       foreach($children->children() as $s_children){
        $it_name = $s_children->getName();
        $taxons["$t"][$tax_name][$schname][$it_name]=trim($s_children);
       }
      }
     }
    }
   }
   ksort($taxons["$t"]);
  }
  $root_rank = $ranks[0]['taxon'];
  $root_tax = array_shift($taxons["$root_rank"]);
  print('<p>');
  print_taxon($root_tax,$root_rank);
  print('</p>');
  $name=$root_tax['name'];
  show_sub_taxa($taxons,$ranks,$name,1);
  
 }else{}
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
