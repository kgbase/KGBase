<?php
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
//-----------------------------------------------------------------------------------------------------------------------
//print selected tree of taxons from some point with the data
//-----------------------------------------------------------------------------------------------------------------------
  function show_sub_data($obj_data,$end_items,$ranks,$name,$level){
  //print tree of all observed taxons ($end_items) in order of ranks according $ranks, beginning from taxon with name $name of level $level
   $ranks_count=array();
   $rank_sub = $ranks[$level]['taxon'];
   if(!isset($ranks_count["$rank_sub"])){$ranks_count["$rank_sub"]=1;}else{}
   foreach($end_items["$rank_sub"] as $ssubtax=>$ssubcontent){
    if($ssubcontent['parent']==$name){
     //print taxon data
     $padding=10*($level+1);
     print('<p style="padding-left: '.$padding.'px">');
     print($ranks_count["$rank_sub"].'. ');
     print_taxon($ssubcontent,$rank_sub);
     $taxon_name=$ssubcontent['name'];
     if(isset($obj_data["$taxon_name"])){
      data_print($obj_data["$taxon_name"],$padding);
     }else{}
     $ranks_count["$rank_sub"]++;
     print('</p>');
     $sublevel=$level;
     $subname=$ssubcontent['name'];
     while(isset($ranks[($sublevel+1)])){
      $sublevel++;
      show_sub_data($obj_data,$end_items,$ranks,$subname,$sublevel);
     }
    }else{}
   }
  }
  //
  //
  //
//-----------------------------------------------------------------------------------------------------------------------
//print selected tree of taxons from some point (full empty tree without the data of objects)
//-----------------------------------------------------------------------------------------------------------------------
  function show_sub_taxa($taxons,$ranks,$name,$level){
  //print tree of all taxons ($taxons) in order of ranks according $ranks, beginning from taxon with name $name of level $level
   $ranks_count=array();
   $rank_sub = $ranks[$level]['taxon'];
   if(!isset($ranks_count["$rank_sub"])){$ranks_count["$rank_sub"]=1;}else{}
   foreach($taxons["$rank_sub"] as $ssubtax=>$ssubcontent){
    if($ssubcontent['parent']==$name){
     //print taxon data
     $padding=10*($level+1);
     print('<p style="padding-left: '.$padding.'px">');
     print($ranks_count["$rank_sub"].'. ');
     print_taxon($ssubcontent,$rank_sub);
     $taxon_name=$ssubcontent['name'];
     $ranks_count["$rank_sub"]++;
     print('</p>');
     $sublevel=$level;
     $subname=$ssubcontent['name'];
     while(isset($ranks[($sublevel+1)])){
      $sublevel++;
      show_sub_taxa($taxons,$ranks,$subname,$sublevel);
     }
    }else{}
   }
  }
  //
  //
  //
  
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
     $styles['tribe']['name']=array('<b><i><font color="green">Триба ','item','</font></i></b> ');
     $styles['tribe']['autor']=array('<b>','item','</b>.');
     $styles['tribe']['pseudonym']=array(' (','item',')');
    $styles['subfamily']=array();
     $styles['subfamily']['name']=array('<b><i><font color="green">Подсемейство ','item','</font></i></b> ');
     $styles['subfamily']['autor']=array('<b>','item','</b>.');
     $styles['subfamily']['pseudonym']=array(' (','item',')');
    $styles['family']=array();
     $styles['family']['name']=array('<b><i><font color="green">Семейство ','item','</font></i></b> ');
     $styles['family']['autor']=array('<b>','item','</b>.');
     $styles['family']['pseudonym']=array(' (','item',')');
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
  //
  //
  //
//-----------------------------------------------------------------------------------------------------------------------
//print data of objects of selected taxon
//!!!IMPORTANT - GeoSpace class is needed!
//-----------------------------------------------------------------------------------------------------------------------
  function data_print($data,$padding){
  //print data of taxon ($data=$obj_data['name']) with padding from left = $padding
   //create printable arrays of data
    $padding=$padding+10;
    $arr_loc=array();
    $arr_cond=array();
    foreach($data as $d_n=>$d_o){
     $arr_loc[]=$d_o['location'];
     $arr_cond[]=$d_o['condition'];
    }
    $d_loc=array_unique($arr_loc);asort($d_loc);
    $d_cond=array_unique($arr_cond);asort($d_cond);
    $locs=array();
    $conds=array();
    foreach($d_loc as $n_loc=>$loc){
     $locs[$n_loc]['name']=$loc;
     $locs[$n_loc]['obs']=array();
     foreach($data as $d_n=>$d_o){
      if($d_o['location']==$loc){
       $locs[$n_loc]['obs'][]=$d_o['observer'];
      }else{}
     }
     $locs[$n_loc]['obs']=array_unique($locs[$n_loc]['obs']);asort($locs[$n_loc]['obs']);
    }
    foreach($d_cond as $n_cond=>$cond){
     $conds[$n_cond]['name']=$cond;
     $conds[$n_cond]['dt']=array();
     foreach($data as $d_n=>$d_o){
      if($d_o['condition']==$cond){
       $arr_dt=explode(' ',$d_o['datetime']);
       $conds[$n_cond]['dt'][]=$arr_dt[0];
      }else{}
     }
     $conds[$n_cond]['dt']=array_unique($conds[$n_cond]['dt']);asort($conds[$n_cond]['dt']);
    }
    //print data
    print('<table style="padding-left: '.$padding.'px"><tr><td style="width: 80%">');
    //locations&observers
    print('<p align="justify"><i>Места наблюдений и наблюдатели:</i></p>');
    print('<p align="justify">');
    foreach($locs as $nloc=>$loc){
     print($loc['name']);
     if((count($loc['obs']))==1){
      $name=array_shift($loc['obs']);
      print(' ('.$name.')');
     }else{
      $print_obs=' (';
      foreach($loc['obs'] as $nobs=>$obs){
       $print_obs=$print_obs.$obs.', ';
      }
      $print_obs=substr_replace(-1,2,$print_obs);
      print($print_obs.')');
     }
     print('; ');
    }
    print('</p>');
    //conditions&datetime
    print('<p align="justify"><i>Состояния и их сроки:</i></p>');
    print('<p align="justify">');
    foreach($conds as $ncond=>$cond){
     print($cond['name']);
     if((count($cond['dt']))==1){
      $dt=array_shift($cond['dt']);
      $arr_date=explode(':',$dt);
      $arr_date=array_reverse($arr_date);
      $dt=implode('.',$arr_date);
      print(' ('.$dt.')');
     }else{
      $dt_start=array_shift($cond['dt']);
      $dt_end=array_pop($cond['dt']);
      $arr_date_start=explode(':',$dt_start);
      $arr_date_start=array_reverse($arr_date_start);
      $dt_start=implode('.',$arr_date_start);
      $arr_date_end=explode(':',$dt_end);
      $arr_date_end=array_reverse($arr_date_end);
      $dt_end=implode('.',$arr_date_end);
      print(' ('.$dt_start.' - '.$dt_end.')');
      
     }
     print('; ');
    }
    print('</p></td><td style="width: 20%">');
    //create map
    $map_data=array();$nmap=0;
    foreach($data as $d_n=>$d_o){
     $curr_lon=$d_o['lon'];
     $curr_lat=$d_o['lat'];
     $curr_ele=$d_o['ele'];
     $map_data[$nmap]=array();
     $map_data[$nmap]['type']='Point';
     $map_data[$nmap]['coordinates']=$curr_lon.','.$curr_lat.','.$curr_ele;
     $nmap++;
    }
    $curr_data=array_shift($data);$curr_name=trim($curr_data['name']);
    $map = new GeoSpace;
    $geodata=$map_data;
    $dest = '../map/maps/';
    $gsp_src = '../map/bsm.jpg';
    $gsp_map = '../map/bsm.kml';
    $map_name=$curr_name.'.jpg';
    $curr_data=array_shift($data);
    $name = $curr_name;
    $font = '../GeoSpace/fonts/ARIAL.TTF';
    $fsize=8;
    $map->gsp_src = $gsp_src;
    $map->gsp_map = $gsp_map;
    $map->geodata = $geodata;
    $map->gsp_getsize($gsp_src,$gsp_map);
    $map->gsp_drowmap($name,$font,$fsize,$gsp_src,$geodata,$dest,$map_name,12,3);
    print('<p align="center">Карта-схема мест наблюдения '.$curr_name.'</p>');
    print('<p align="center"><img src="'.$dest.$map_name.'"></p>');
    print('<td></tr></table>');
  }
//-----------------------------------------------------------------------------------------------------------------------
//data of all objects in the base
//-----------------------------------------------------------------------------------------------------------------------
/*
$destination - directory to save xml's
$serv - base address
$db_dir - default database directory
$db_file - default database index
$def - current system time

*/
function all_data($destination,$serv,$db_dir,$db_file,$def){
  print('Вот что есть в нашей базе (данные всех объектов):<br><br>');
  $nob=0;
  $obj_data=array();
  $obj_tree = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><result></result>');
  $obj_map = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><kml xmlns="http://earth.google.com/kml/2.2"></kml>');
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
    $obj_data["$obj_name"][$nob]['name']=$obj_name;
    $obj_data["$obj_name"][$nob]['condition']=$condition;
    $obj_data["$obj_name"][$nob]['location']=$loc;
    $obj_data["$obj_name"][$nob]['datetime']=$datetime;
    $obj_data["$obj_name"][$nob]['lon']=$lon;
    $obj_data["$obj_name"][$nob]['lat']=$lat;
    $obj_data["$obj_name"][$nob]['ele']=$ele;
    $obj_data["$obj_name"][$nob]['observer']=$obs;
    $obj_data["$obj_name"][$nob]['detauth']=$detauth;
    $obj_data["$obj_name"][$nob]['dettime']=$dettime;
    $obj_data["$obj_name"][$nob]['link']=$link;
    $nob=$nob+1;
   }
  }
  
  ksort($obj_data);
  $ntypes=count($obj_data);
  print('<b>'.$nob.'</b> наблюдений <b>'.$ntypes.'</b> объектов:<br>');
  
  //create&upload xml
  $datafiles = array();
  foreach($obj_data as $name=>$objects){
   sort($objects);
   foreach($objects as $num=>$object){
    $obj = $obj_tree->addChild('object','&#xA;');
    $obj->addChild('name',$object['name']);
    $obj->addChild('condition',$object['condition']);
    $obj->addChild('location',$object['location']);
    $obj->addChild('datetime',$object['datetime']);
    $obj->addChild('lon',$object['lon']);
    $obj->addChild('lat',$object['lat']);
    $obj->addChild('ele',$object['ele']);
    $obj->addChild('observer',$object['observer']);
    $obj->addChild('detauth',$object['detauth']);
    $obj->addChild('dettime',$object['dettime']);
    $obj->addChild('link',$object['link']);
   }
  }
  $datafiles['xml']=$destination.$def.'(all).xml';
  $obj_tree->asXML($datafiles['xml']);
  print('<a href="'.$datafiles['xml'].'">Данные выборки (XML)</a><br>');
  //create&upload kml
  $Document = $obj_map->addChild('Document','&#xA;');
  foreach($obj_data as $name=>$objects){
   $Folder = $Document->addChild('Folder','&#xA;');
   $Folder->addChild('name',$name);
   $Folder->addChild('open',1);
   $Style = $Folder->addChild('Style','&#xA;');
   $ListStyle = $Style->addChild('ListStyle','&#xA;');
   $ListStyle->addChild('listItemType','check');
   $ListStyle->addChild('bgColor','00ffffff');
   sort($objects);
   foreach($objects as $num=>$object){
    $Placemark = $Folder->addChild('Placemark','&#xA;');
    $Placemark->addChild('name',$name.'('.($num+1).')');
    $kml_desc=$name.', '.$object['location'].' ('.$object['condition'].'), фотография '.$object['observer'].' ('.$object['datetime'].'), определил '.$object['detauth'].' ('.$object['dettime'].'), <br>&lt;img src="'.$object['link'].'"/&gt;';
    $kml_desc=str_replace('/../','/',$kml_desc);
    $Placemark->addChild('description',$kml_desc);
    $Placemark->addChild('styleUrl','#waypoint');
    $Point = $Placemark->addChild('Point','&#xA;');
    $Point->addChild('extrude',1);
    $Point->addChild('coordinates',$object['lon'].','.$object['lat'].','.$object['ele']);
    $end = $Placemark->addChild('end','&#xA;');
    $kmlt=explode(" ",$object['datetime']);
    $kmlt[0]=str_replace(":","-",$kmlt[0]);
    $kmltime=$kmlt[0]."T".$kmlt[1]."Z";
    $TimeInstant = $end->addChild('end','&#xA;');
    $timePosition = $TimeInstant->addChild('timePosition','&#xA;');
    $timePosition->addChild('time',$kmltime);
   }
  }
  $datafiles['kml']=$destination.$def.'(all).kml';
  $obj_map->asXML($datafiles['kml']);
  print('<a href="'.$datafiles['kml'].'">Карта выборки (KML)</a><br><br>');
  return($datafiles);
}
?>
