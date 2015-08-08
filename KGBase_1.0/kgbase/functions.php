<?php
//error_reporting (E_ALL);
//-----------------------------------------------------------------------------------------------------------------------
//clear selected directory
//-----------------------------------------------------------------------------------------------------------------------
function clear_dir($destination){
 $trash=scandir($destination);
 foreach($trash as $tr=>$ash){
  if($ash!=='.' && $ash!=='..'){
   unlink($destination.$ash);
  }else{}
 }
}
//----------------------------------------------------------------------------------------------------------------------
//print short list of specimens
//----------------------------------------------------------------------------------------------------------------------
  function show_obj_data($obj_curr,$obj_padding){
   $obj_locs = array();
   foreach($obj_curr as $obj_n=>$obj){
    if(!in_array($obj['location'],$obj_locs)){
     $obj_locs[]=$obj['location'];
    }else{}
   }
   sort($obj_locs);
   print('<p style="padding-left: '.$obj_padding.'px"; margin-top: -1px;  margin-bottom: -1px;>');
   foreach($obj_locs as $loc_n=>$loc){
    print($loc.': ');
    $dateauth=array();
    foreach($obj_curr as $obj_n=>$obj){
     if($obj['location']==$loc){
     $curr_dateauth = $obj['date'].' ('.$obj['collector'].')';
      if(!in_array($curr_dateauth,$dateauth)){
       $dateauth[]=$curr_dateauth;
      }else{}
     }else{}
    }
    $str_dateauth=implode(', ',$dateauth);
    print($str_dateauth.'.<br>');
   }
   print('</p>');
  }
//-----------------------------------------------------------------------------------------------------------------------
//print selected tree of taxons from some point (full empty tree without the data of collection et c.)
//-----------------------------------------------------------------------------------------------------------------------
  function show_sub_taxa($taxons,$ranks,$name,$level,$spec,$site,$base){
  //print tree of all taxons ($taxons) in order of ranks according $ranks, beginning from taxon with name $name of level $level
   $ranks_count=array();
   $rank_sub = $ranks[$level]['taxon'];
   if(!isset($ranks_count["$rank_sub"])){$ranks_count["$rank_sub"]=1;}else{}
   foreach($taxons["$rank_sub"] as $ssubtax=>$ssubcontent){
    if($ssubcontent['parent']==$name){
     //print taxon data
     $padding=10*($level+1);
     print('<p style="padding-left: '.$padding.'px; margin-top: -1px;  margin-bottom: -1px;">');
     print($ranks_count["$rank_sub"].'. ');
      print_taxon($ssubcontent,$rank_sub);
      $taxon_name=$ssubcontent['name'];
      $taxon_name=trim($taxon_name);
      if(array_search($taxon_name,$spec)!==false){
       print(' <a href="'.$site.'&base='.$base.'&txn='.$taxon_name.'" target="_blank">коллекция</a>');
      }else{}
     $ranks_count["$rank_sub"]++;
     print('</p>');
     $sublevel=$level;
     $subname=$ssubcontent['name'];
     while(isset($ranks[($sublevel+1)])){
      $sublevel++;
      show_sub_taxa($taxons,$ranks,$subname,$sublevel,$spec,$site,$base);
     }
    }else{}
   }
  }
//-----------------------------------------------------------------------------------------------------------------------
//print data of selected taxon
//-----------------------------------------------------------------------------------------------------------------------
  function print_taxon($taxon,$style){
  //print data of taxon from the tree ($taxon=$taxons['taxon level']['taxon name']) with style $styles[$style] as below
   $styles=array();
    $styles['ssp.']=array();
     $styles['ssp.']['name']=array('<i><font color="grey">','item','</font></i> ');
     $styles['ssp.']['autor']=array('item','.');
    $styles['sp.']=array();
     $styles['sp.']['name']=array('<b><i>','item','</b></i> ');
     $styles['sp.']['autor']=array('item');
     $styles['sp.']['pseudonym']=array(' (','item',')');
     $styles['sp.']['description']=array('. ','item');
    $styles['tribe']=array();
     $styles['tribe']['name']=array('<b><i><font color="green">','item','</font></i></b> ');
     $styles['tribe']['autor']=array('<b>','item','</b>.');
     $styles['tribe']['pseudonym']=array(' (','item',')');
    $styles['subfamily']=array();
     $styles['subfamily']['name']=array('<b><i><font color="green">','item','</font></i></b> ');
     $styles['subfamily']['autor']=array('<b>','item','</b>.');
     $styles['subfamily']['pseudonym']=array(' (','item',')');
    $styles['family']=array();
     $styles['family']['name']=array('<b><i><font color="green">Семейство ','item','</font></i></b> ');
     $styles['family']['autor']=array('<b>','item','</b>.');
     $styles['family']['pseudonym']=array(' (','item',')');
    $styles['superfamily']=array();
     $styles['superfamily']['name']=array('<b><i><font color="green">Семейство ','item','</font></i></b> ');
     $styles['superfamily']['autor']=array('<b>','item','</b>.');
     $styles['superfamily']['pseudonym']=array(' (','item',')');
    $styles['suborder']=array();
     $styles['suborder']['name']=array('<b><i><font color="green">Подотряд ','item','</font></i></b> ');
     $styles['suborder']['autor']=array('<b>','item','</b>.');
     $styles['suborder']['pseudonym']=array(' (','item',')');
    $styles['order']=array();
     $styles['order']['name']=array('<b><i><font color="green">Отряд ','item','</font></i></b> ');
     $styles['order']['autor']=array('<b>','item','</b>.');
     $styles['order']['pseudonym']=array(' (','item',')');
    $styles['subclass']=array();
     $styles['subclass']['name']=array('<b><i><font color="green">Подкласс ','item','</font></i></b> ');
     $styles['subclass']['autor']=array('<b>','item','</b>.');
     $styles['subclass']['pseudonym']=array(' (','item',')');
    $styles['class']=array();
     $styles['class']['name']=array('<b><i><font color="green">Класс ','item','</font></i></b> ');
     $styles['class']['autor']=array('<b>','item','</b>.');
     $styles['class']['pseudonym']=array(' (','item',')');
    $styles['phylum']=array();
     $styles['phylum']['name']=array('<b><font color="red">','item','</font></b> ');
     $styles['phylum']['pseudonym']=array(' (','item',')');
    $styles['kingdom']=array();
     $styles['kingdom']['name']=array('<b><font size="6">','item','</font></b> ');
     $styles['kingdom']['pseudonym']=array(' <font size="6">(','item',')</font>');
   foreach($styles["$style"] as $key=>$val){
    $item=$taxon["$key"];
    foreach($val as $skey=>$sval){
     if($sval!=='item' && $item!==null){
      print($sval);
     }elseif($sval=='item' && $item!==null){
      print($item);
     }else{}
    }
   }
  }
//-----------------------------------------------------------------------------------------------------------------------
//convert xml-tree of collection to array for search
//-----------------------------------------------------------------------------------------------------------------------
  function xml_coll_to_array($all_coll,$all_geo){
   $coll_result=array();
   $coll_result['locations']=array();
   $coll_result['specimens']=array();
   //parse geodata
   foreach($all_geo->location as $location){
    $loc=$location->location;$loc=trim($loc);
    $hier=$location->hierarhy;
    $desc=$location->description;
    $coll_result['locations'][$loc]=array();
    $coll_result['locations'][$loc]['h']=trim($hier);
    $coll_result['locations'][$loc]['d']=trim($desc);
   }
   //parse collection
   foreach($all_coll->series as $series){
    $loc=$series->location;
    $cmode=$series->cmode;
    $habitat=$series->habitat;
    $datetime_start=$series->datetime_start;
    $datetime_end=$series->datetime_end;
    $collector=$series->collector;
    foreach($series->specimen as $specimen){
     $sp=$specimen->specimen;$sp=trim($sp);
     $taxon=$specimen->taxon;
     $collection=$specimen->collection;
     $coll_result['specimens'][$sp]=array();
     $coll_result['specimens'][$sp]['taxon']=trim($taxon);
     $coll_result['specimens'][$sp]['location']=trim($loc);
     $coll_result['specimens'][$sp]['cmode']=trim($cmode);
     $coll_result['specimens'][$sp]['habitat']=trim($habitat);
     $coll_result['specimens'][$sp]['datetime_start']=trim($datetime_start);
     $coll_result['specimens'][$sp]['datetime_end']=trim($datetime_end);
     $coll_result['specimens'][$sp]['collector']=trim($collector);
     $coll_result['specimens'][$sp]['collection']=trim($collection);
    }
   }
   return($coll_result);
  }
//-----------------------------------------------------------------------------------------------------------------------
//return array with result of search - found specimens
//-----------------------------------------------------------------------------------------------------------------------
  function search_in_data($coll_result,$sloc,$sarea,$stn,$stx,$spers){
   $coll_search_result=array();
   foreach($coll_result['specimens'] as $sp=>$data){
    $res_num=0;
    if($sloc!==''){
     $loc_num=$data['location'];
     $loc=$coll_result['locations'][$loc_num]['d'];
     if(stristr($loc,$sloc)  && !isset($coll_search_result[$sp])){
      $res_num++;
     }else{}
    }else{$res_num++;}
    if($sarea!==''){
     $loc_num=$data['location'];
     $area=$coll_result['locations'][$loc_num]['h'];
     if(stristr($area,$sarea)  && !isset($coll_search_result[$sp])){
      $res_num++;
     }else{}
    }else{$res_num++;}
    if($stn!==''){
     $stn_item_array=explode(':',$data['datetime_start']);
     $stn_item= (int) $stn_item_array[0];
     if($stn_item>=$stn){
      $res_num++;
     }else{}
    }else{$res_num++;}
    if($stx!==''){
     if($data['datetime_end']!==''){
      $stx_item_array=explode(':',$data['datetime_end']);
      $stx_item= (int) $stx_item_array[0];
      if($stx_item<=$stx){
       $res_num++;
      }else{}
     }else{
      $stx_item_array=explode(':',$data['datetime_start']);
      $stx_item=$stx_item_array[0];
      if($stx_item<=$stx){
       $res_num++;
      }else{}
     }
    }else{$res_num++;}
    if($spers!==''){
     $pers_item=$data['collector'];
     if(stristr($pers_item,$spers) && !isset($coll_search_result[$sp])){
      $res_num++;
     }else{}
    }else{$res_num++;}
    if($res_num==5){
     $coll_search_result[$sp]=$data;
    }
   }
   return($coll_search_result);
  }
//-----------------------------------------------------------------------------------------------------------------------
//convert taxonomic xml-tree to arrays of ranks and taxons
//-----------------------------------------------------------------------------------------------------------------------
  function convert_taxonomy($xml){
   $taxonomy=array();
   //make list of ranks
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
    $taxonomy['ranks']=$ranks;
    $taxonomy['taxons']=$taxons;
    return($taxonomy);
   }
//-----------------------------------------------------------------------------------------------------------------------
//construct taxonomic array of results from whole tree
//-----------------------------------------------------------------------------------------------------------------------
  function construct_taxonomy($coll_search_result,$all_tax){
  //arguments: numeric array of exists taxons (list), array: array1(ranks) array2 (taxons in order of ranks)
   $sel_taxons=array();
   $all_taxonomy=convert_taxonomy($all_tax);
   foreach($coll_search_result as $sp=>$data){
    $taxon=$data['taxon'];
    $taxon=trim($taxon);
    if(array_search($taxon,$sel_taxons)==false){
     $sel_taxons[]=$taxon;
    }else{}
   }
   sort($sel_taxons);
   $ranks=$all_taxonomy['ranks'];
   $taxons=$all_taxonomy['taxons'];
   $n_ranks=count($ranks);
   $sel_struct=array();
   //make array rank=>name of exists taxons
   for($ir=0;$ir<$n_ranks;$ir++){
    $r_name=$ranks[$ir]['taxon'];
    foreach($taxons["$r_name"] as $name=>$content){
     if(array_search($name,$sel_taxons)!==false){
      $sel_struct["$name"]=$r_name;
     }else{}
    }
   }
   //make "final" array
   $end_items=array();
   for($ir=$n_ranks-1;$ir>=0;$ir--){
    $r_name=$ranks[$ir]['taxon'];
    foreach($taxons["$r_name"] as $name=>$content){
     if(isset($sel_struct["$name"])){
      if(!isset($end_items["$r_name"])){
       $end_items["$r_name"]=array();
      }
      $end_items["$r_name"]["$name"]=$taxons["$r_name"]["$name"];
      $item_parent=$end_items["$r_name"]["$name"]['parent'];
      $parent_lev=$ir-1;
      while(isset($ranks[$parent_lev])){
       $par_r_name=$ranks[$parent_lev]['taxon'];
       if(!isset($end_items["$par_r_name"])){
        $end_items["$par_r_name"]=array();
       }
       $parent_name=
       $end_items["$par_r_name"]["$item_parent"]=$taxons["$par_r_name"]["$item_parent"];
       $item_parent=$end_items["$par_r_name"]["$item_parent"]['parent'];
       $parent_lev--;
      }
     }else{}
    }
   }
   //make new array of taxons - really exists in query
   $ranks_new=array();
   for($ir=0;$ir<$n_ranks;$ir++){
    $r_name=$ranks[$ir]['taxon'];
    if(isset($end_items["$r_name"])){
     $ranks_new[$ir]=$ranks[$ir];
    }
   }
   foreach($end_items as $level=>$taxon_level){
    ksort($taxon_level);
    $end_items["$level"]=$taxon_level;
   }
   $end_items=array_reverse($end_items);
   $end_data=array();
   $end_data['ranks']=$ranks_new;
   $end_data['taxons']=$end_items;
   $end_data['sel_taxons']=$sel_taxons;
   return($end_data);
   return($end_data);
  }
//-----------------------------------------------------------------------------------------------------------------------
//print selected tree of taxons from some point (with short collection data of each taxon)
//-----------------------------------------------------------------------------------------------------------------------
  function show_sub_taxa_data($taxons,$ranks,$name,$level,$spec,$site,$base,$locations,$specimens){
   //this function in common as show_sub_taxa (u.s.), but prints also data of specimens from 2 last arguments (calls short_taxon_data function)
   $ranks_count=array();
   $rank_sub = $ranks[$level]['taxon'];
   if(!isset($ranks_count["$rank_sub"])){$ranks_count["$rank_sub"]=1;}else{}
   foreach($taxons["$rank_sub"] as $ssubtax=>$ssubcontent){
    if($ssubcontent['parent']==$name){
     //print taxon data
     $padding=10*($level+1);
     print('<p style="padding-left: '.$padding.'px; margin-top: -1px;  margin-bottom: -1px;">');
     print($ranks_count["$rank_sub"].'. ');
     print_taxon($ssubcontent,$rank_sub);
     $taxon_name=$ssubcontent['name'];
     $taxon_name=trim($taxon_name);
     if(array_search($taxon_name,$spec)!==false){
      print(' <a href="'.$site.'&base='.$base.'&txn='.$taxon_name.'" target="_blank">коллекция</a>');
      short_taxon_data($taxon_name,$locations,$padding,$site,$base);
     }else{}
     $ranks_count["$rank_sub"]++;
     print('</p>');
     $sublevel=$level;
     $subname=$ssubcontent['name'];
     while(isset($ranks[($sublevel+1)])){
      $sublevel++;
      show_sub_taxa_data($taxons,$ranks,$subname,$sublevel,$spec,$site,$base,$locations,$specimens);
     }
    }else{}
   }
  }
//-----------------------------------------------------------------------------------------------------------------------
//print short collection data of taxon
//-----------------------------------------------------------------------------------------------------------------------
  function short_taxon_data($taxon,$locations,$padding,$site,$base){
   $curr_h='';
   foreach($locations as $item){
    if(array_search($taxon,$item['tax'])!==false && $item['h']!==$curr_h){
     print('<p style="padding-left: '.($padding+10).'px; margin-top: -1px;  margin-bottom: -1px;"><b>'.$item['h'].':</b><p>');
     $curr_h=$item['h'];
    }else{}
    $curr_d='';
    foreach($item['d'] as $loc_item){
    if(array_search($taxon,$loc_item['tax'])!==false && $item['d']!==$curr_d){
     print('<p style="padding-left: '.($padding+20).'px; margin-top: -1px;  margin-bottom: -1px;">'.$loc_item['d'].':<p>');
     $curr_d=$loc_item['d'];
    }else{}
     foreach($loc_item['sp'] as $n_sp=>$sp){
      if($sp['taxon']==$taxon){
       print('<p style="padding-left: '.($padding+20).'px; margin-top: -1px;  margin-bottom: -1px;">');
       print('<a href="'.$site.'&base='.$base.'&txn='.$taxon.'#'.$n_sp.'" target="_blank">'.$n_sp.'</a><i>: ');
       $date_data=datetime_convert_date($sp['datetime_start'],$sp['datetime_end']);
       print($date_data.'; ');
       if($sp['habitat']!==''){
        print($sp['habitat'].'; ');
       }else{}
       if($sp['cmode']!==''){
        print($sp['cmode'].'; ');
       }else{}
       print($sp['collector'].' - </i>');
       print('<a href="'.$site.'&base='.$base.'&collect='.$sp['collection'].'" target="_blank">'.$sp['collection'].'</a>.');
       print('</p>');
      }
     }
    }
   }
  }
//-----------------------------------------------------------------------------------------------------------------------
//convert datetime
//-----------------------------------------------------------------------------------------------------------------------
  function datetime_convert_date($start,$end=''){
   //start
   $start_arr=explode(' ',$start);
   if(count($start_arr)==2){
    $start_item=$start_arr[0];
   }else{$start_item=$start;}
   $start_item_arr=explode(':',$start_item);
   if(count($start_item_arr)==3){
    $start_fin_arr=array_reverse($start_item_arr);
    $start_fin=implode('.',$start_fin_arr);
   }elseif(count($start_item_arr)==2){
    $start_fin='??.'.$start_item_arr[1].$start_item_arr[0];
   }elseif(count($$start_item_arr)==1){
    $start_fin='??.??.'.$start_item_arr[1].$start_item;
   }else{}
   //end
   if($end!==''){
    $end_arr=explode(' ',$end);
    if(count($end_arr)==2){
     $end_item=$end_arr[0];
    }else{$end_item=$end;}
    $end_item_arr=explode(':',$end_item);
    if(count($end_item_arr)==3){
     $end_fin_arr=array_reverse($end_item_arr);
     $end_fin=implode('.',$end_fin_arr);
    }elseif(count($end_item_arr)==2){
     $end_fin='??.'.$end_item_arr[1].$end_item_arr[0];
    }elseif(count($$end_item_arr)==1){
     $end_fin='??.??.'.$end_item_arr[1].$end_item;
    }else{$end_fin='';}
   }
   //joint
   if($end_fin=='' || $end_fin==$start_fin){
    $date_data=$start_fin;
   }else{
    
    
    
    $date_data=$start_fin.' - '.$end_fin;
   }
   return($date_data);
  }
//-----------------------------------------------------------------------------------------------------------------------
//make structured array of locations for the list of specimens
//-----------------------------------------------------------------------------------------------------------------------
  function sort_locations($locations,$specimens){
   //prepare array of locations
   $loc_struct=array();//array key=>val of hierarchies
   foreach($locations as $location){
    $loc_struct[]=$location['h'];
   }
   $loc_struct=array_unique($loc_struct);
   sort($loc_struct);
   $loc_fin=array();//final array key=>(hierarchy=>val)(descriptions(key=>description))
   foreach($locations as $l_num=>$location){
    $loc_desc=$location['d'];
    $loc_hier=$location['h'];
    $pos_hier=array_search($loc_hier,$loc_struct);
    if(!isset($loc_fin[$pos_hier])){
     $loc_fin[$pos_hier]=array();
     $loc_fin[$pos_hier]['h']=$loc_hier;
     $loc_fin[$pos_hier]['d']=array();
    }else{}
    foreach($specimens as $n_sp=>$specimen){
     if($specimen['location']==$l_num){
      if(!isset($loc_fin[$pos_hier]['d']["$l_num"])){
       $loc_fin[$pos_hier]['d']["$l_num"]=array();
      }else{}
      $loc_fin[$pos_hier]['d']["$l_num"]['d']=$loc_desc;
      if(!isset($loc_fin[$pos_hier]['d']["$l_num"]['sp'])){
       $loc_fin[$pos_hier]['d']["$l_num"]['sp']=array();
      }else{}
      $loc_fin[$pos_hier]['d']["$l_num"]['sp']["$n_sp"]=$specimen;
      if(!isset($loc_fin[$pos_hier]['d']["$l_num"]['tax'])){
       $loc_fin[$pos_hier]['d']["$l_num"]['tax']=array();
      }else{}
      $loc_fin[$pos_hier]['d']["$l_num"]['tax'][]=$specimen['taxon'];
      if(!isset($loc_fin[$pos_hier]['tax'])){
       $loc_fin[$pos_hier]['tax']=array();
      }else{}
      $loc_fin[$pos_hier]['tax'][]=$specimen['taxon'];
     }else{}
    }
   }
   //exclude empty items from hierarchy
   foreach($loc_fin as $num_fin=>$loc){
    $num_descs=count($loc['d']);
    if($num_descs==0){
     unset($loc_fin[$num_fin]);
    }else{}
   }
   //
   foreach($loc_fin as $num_fin=>$loc){
    foreach($loc['tax'] as $nt=>$tax){
     $loc_fin[$num_fin]['tax'][$nt]=trim($tax);
    }
    
   }
   return($loc_fin);
  }
?>