<?php
//-----------------------------
// get all literature sources from DB
function get_lit($db){
	$lit=array();
	$litq="SELECT * FROM literature";
	$litresult=$db->query($litq);
	while($litres = $litresult->fetchArray(SQLITE3_ASSOC)){
		$ref=$litres['ref'];
		$lit["$ref"]=array('text'=>$litres['text'],'file'=>$litres['file'],);	
	}
	ksort($lit);
	return($lit);
}
//get all (default) references from db
function get_ref($db,$litsrc='all'){
	$refs=array();
	if($litsrc=='all'){$addq='';}else{$addq=" WHERE reference.ref='".$litsrc."'";}
	$refq="SELECT * FROM reference".$addq;
	$refresult=$db->query($refq);
	while($refres = $refresult->fetchArray(SQLITE3_ASSOC)){
		$ref=$refres['ref'];
		$loc=$refres['location'];
		$name=$refres['name'];
		$refs["$ref"]["$name"]["$loc"]=$refres;
	}
	foreach($refs as $rkey=>$rval){
		ksort($refs["$rkey"]);
	}
	return($refs);
}
//calculate limits, center and zoom (for Google maps) from array of placemar (for NE part of the Earth), $mpd = meter per degree
function calc_limits_ne($placemarks,$mpd=10000){
	$nlimit='0';$slimit='90';$wlimit='90';$elimit='0';
	foreach($placemarks as $pl){
		$coordinates=$pl['coordinates'];
		$coord=explode(' ',$coordinates);
		foreach($coord as $item){
			$data=explode(',',$item);
			if($data[0]>$elimit){$elimit=$data[0];}else{}
			if($data[0]<$wlimit){$wlimit=$data[0];}else{}
			if($data[1]>$nlimit){$nlimit=$data[1];}else{}
			if($data[1]<$slimit){$slimit=$data[1];}else{}
		}
	}
	$dtat=$nlimit-$slimit;
	$dlon=$elimit-$wlimit;
	if($dlon>$dlat){$dmax=$dlon*2;}else{$dmax=$dlat*2;}
	$dm=$dmax*$mpd;
	if($dm<1000){$zoom=16;}
	if($dm>1000 && $dm<2000){$zoom=15;}
	if($dm>2000 && $dm<4000){$zoom=14;}
	if($dm>4000 && $dm<8000){$zoom=13;}
	if($dm>8000 && $dm<16000){$zoom=12;}
	if($dm>16000 && $dm<32000){$zoom=11;}
	if($dm>32000 && $dm<64000){$zoom=10;}
	if($dm>64000 && $dm<128000){$zoom=9;}
	if($dm>128000 && $dm<250000){$zoom=8;}
	if($dm>250000 && $dm<500000){$zoom=7;}
	if($dm>500000 && $dm<1000000){$zoom=6;}
	if($dm>1000000 && $dm<2000000){$zoom=5;}
	if($dm>2000000 && $dm<5000000){$zoom=4;}
	if($dm>5000000){$zoom=3;}
	$clon=$elimit-($dlon/2);
	$clat=$nlimit-($dlat/2);
	$center='lat: '.$clon.', lng: '.$clat;
	$calc=array('nlimit'=>$nlimit,'slimit'=>$slimit,'wlimit'=>$wlimit,'elimit'=>$elimit,'center'=>$center,'zoom'=>$zoom,);
	return($calc);
}
//show data for selected location
function print_taxon_search_location($baselocation,$series_data,$data_restruct,$cpecimen_parts,$taxon_name,$site){
  if(isset($data_restruct["$taxon_name"])){
    $tdata=$data_restruct["$taxon_name"];
    $out='<div class="search_result">';
    //if references exists
    if(isset($tdata['refs'])){
      $out=$out.'<div><b><i>Literature references:</i></b> ';
      $locs["$baselocation"]['items']=array();
      foreach($tdata['refs'] as $refn=>$data){
        $locs["$baselocation"]['items']["$refn"]=$data['ref'].': '.$data['page'].' ("'.$data['text'].'")';
      }
      foreach($locs as $chr=>$cloc){
        $content=implode('; ',$cloc['items']);
				$out=$out.$content.'.</div>';
      }
    }else{}
    //if collectiod data exists
    if(isset($tdata['coll'])){
      $out=$out.'<div><b><i>Specimens in collection(s):</i></b> ';
      $locs["$baselocation"]['items']=array();
      foreach($tdata['coll'] as $colln=>$data){
        $nser=$data['series'];
        if($data['datetime_end']=='' && $data['datetime_end']==null){
          $timestart=datetime_convert_date($data['datetime_start'],$data['datetime_end']);
        }else{
          $timestart=datetime_convert_date($data['datetime_start']);
        }
        $sdata=$series_data["$nser"]["$taxon_name"];$ndata=count($sdata);
        if($ndata>0){
          $ser_arr=array();
          foreach($sdata as $part=>$number){
            $ser_arr[]=$number.$cpecimen_parts["$part"];
          }
          $sdprint=implode(', ',$ser_arr);
          $sdprint=' - '.$sdprint;
        }else{
          $sdprint='';
        }
        $locs["$baselocation"]['items']["$nser"]=$data['collector'].' ('.$timestart.$sdprint.')';
      }
      foreach($locs as $chr=>$cloc){
				$content=implode('; ',$cloc['items']);
				$out=$out.$content.'.</div>';
      }
    }else{}
    //if observation data exists
   if(isset($tdata['obs'])){
      $out=$out.'<div><b><i>Observations:</i></b> ';
      $locs["$baselocation"]['items']=array();
      foreach($tdata['obs'] as $obsn=>$data){
        $timestart=datetime_convert_date($data['datetime']);
        $locs["$baselocation"]['items']["$obsn"]=$data['observer'].' ('.$timestart.')';
      }
      foreach($locs as $chr=>$cloc){
        $out=$out.'<div><b>'.$chr.'</b>';
				$content=implode('; ',$cloc['items']);
				$out=$out.$content.'.</div>';
      }
    }else{}
    $out=$out.'<div><span class="link_button"><a href="'.$site.'&sect=all&txn='.$taxon_name.'" target="_blank"><b>complete data (in new window)</b></a></span></div></div>'."\r\n";
    $out=$out.'</div>';
    print($out);
  }else{}
}
//get data of collection specimens for location
function coll_to_array_location($db,$baselocation){
  $coll_result=array();
  $coll_result['coll']=array();
  $collq="select location.location,series.series,specimen.taxon,
                 specimen.specimen,specimen.collection,
                 series.cmode,series.habitat,series.datetime_start,
                 series.datetime_end,series.collector from location
                 join series on series.location='".$baselocation."' and series.location=location.location
                 left join specimen on specimen.series=series.series";
  $collresult=$db->query($collq);
  $collitems=array();
  while($collres = $collresult->fetchArray(SQLITE3_ASSOC)){
    $sp=$collres['specimen'];
		if($sp!=='' && $sp!==null){
    	$coll_tax=$collres['taxon'];
    	$coll_result['coll'][$sp]=$collres;
		}else{}
  }
  $sum=count($coll_result['coll']);
  if($sum>0){return($coll_result);
  }else{return(null);}
}
//get data of literature references for location
function ref_to_array_location($db,$baselocation){
  $ref_result=array();
  $ref_result['refs']=array();
  $refq="select location.location,reference.reference,reference.name,
               reference.ref,reference.page,reference.citedas,
          	   reference.text from reference
               join location on reference.location='".$baselocation."'
			         and reference.location=location.location";
  $refresult=$db->query($refq);
  $refitems=array();
  while($refres = $refresult->fetchArray(SQLITE3_ASSOC)){
    $rf=$refres['reference'];
    $ref_tax=$refres['name'];
    $ref_result['refs'][$rf]=$refres;
  }
  $sum=count($ref_result['refs']);
  if($sum>0){return($ref_result);
  }else{return(null);}
}
//get data of observations for location
function obs_to_array_location($db,$baselocation){
  $obs_result=array();
  $obs_result['obs']=array();
  $obsq="select location.location,obs.obs,obs.name,obs.datetime,
               obs.observer from obs
               join location on obs.location='".$baselocation."'
			         and obs.location=location.location";
  $obsresult=$db->query($obsq);
  $obsitems=array();
  while($obsres = $obsresult->fetchArray(SQLITE3_ASSOC)){
    $obs=$obsres['obs'];
    $obs_tax=$obsres['name'];
    $obs_result['obs'][$obs]=$obsres;
  }
  $sum=count($obs_result['obs']);
  if($sum>0){return($obs_result);
  }else{return(null);}
}
//get all placemarks data from db by locations
function get_placemarks($db){
  $placemarks=array();
  $plquery="SELECT location.location,placemark.placemark,placemark.type,placemark.coordinates
                   FROM location LEFT JOIN placemark ON placemark.location=location.location
	                 ORDER BY location.location,placemark.placemark";
  $plresult=$db->query($plquery);
  while($plres=$plresult->fetchArray(SQLITE3_ASSOC)){
    $loc=$plres['location'];
    $pl=$plres['placemark'];
    if(!isset($placemarks["$loc"])){$placemarks["$loc"]=array();}else{}
    $placemarks["$loc"]["$pl"]=array('type'=>$plres['type'],'coordinates'=>$plres['coordinates'],);
  }
  return($placemarks);
}
//construct simple kml file from the array from get_$placemarks and get_locations functions
function construct_kml_simple($geodata,$placemarks,$title=''){
  $time=date("Y-m-d H:i:s");
  $kml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><kml xmlns="http://www.opengis.net/kml/2.2" xmlns:gx="http://www.google.com/kml/ext/2.2" xmlns:kml="http://www.opengis.net/kml/2.2" xmlns:atom="http://www.w3.org/2005/Atom"></kml>');
  $kml_doc = $kml->addChild('Document','&#xA;');
  $kml_doc->addChild('name',$time.$title);
  foreach($geodata as $loc=>$ldesc){
    $loc_d=$ldesc['d'];
    $kml_folder = $kml_doc->addChild('Folder','&#xA;');
    $kml_folder->addChild('name',$loc_d);
    $pl=$placemarks["$loc"];
    foreach($pl as $pln=>$pldata){
      $kml_placemark = $kml_folder->addChild('Placemark','&#xA;');
      $kml_placemark->addChild('name',$pln);
      $kml_placemark->addChild('visibility','1');
      $kml_placemark->addChild('open','1');
      $pl_type = $pldata['type'];
      $pl_coordinates = $pldata['coordinates'];
      if($pl_type=='Point'){
        $kml_point = $kml_placemark->addChild('Point','&#xA;');
        $kml_point->addChild('coordinates',$pl_coordinates);
      }elseif($pl_type=='LineString'){
        $kml_linestring = $kml_placemark->addChild('LineString','&#xA;');
        $kml_linestring->addChild('coordinates',$pl_coordinates);
      }elseif($pl_type=='Polygon'){
        $kml_polygon = $kml_placemark->addChild('Polygon','&#xA;');
        $kml_ob = $kml_polygon->addChild('outerBoundaryIs','&#xA;');
        $kml_linearring = $kml_ob->addChild('LinearRing','&#xA;');
        $kml_linearring->addChild('coordinates',$pl_coordinates);
      }else{}
    }
  }
  return($kml);
}
//show locations as a tree, based on their hierarchy
function show_geotree($hierarchy,$level,$name,$site){
  foreach($hierarchy['parts'][$level] as $subname=>$parent){
    if($parent==$name){
      $padding=10*($level+1);
      print('<p style="padding-left: '.$padding.'px; margin-top: -1px;  margin-bottom: -1px;"><b>'.$subname.'</b>');
      if(isset($hierarchy['loci']["$subname"])){
        print('<br><span class="search_result">');
        foreach($hierarchy['loci']["$subname"] as $locus=>$dec){
          print($locus.': '.$dec.' <span class="link_button"><a href="'.$site.'&baselocation='.$locus.'" target="_blank">detailed data (in new window)</a></span><br>');
        }
        print('</span>');
      }
      print('</p>');
      $sublevel=$level;
      $ssubname=$subname;
      while(isset($hierarchy['parts'][($sublevel+1)])){
        $sublevel++;
        show_geotree($hierarchy,$sublevel,$ssubname,$site);
      }
    }else{}
  }
}
//show all descendant taxons of $name as tree with short data
function show_subtree_result($series_data,$geodata,$data_restruct,$cpecimen_parts,$taxons,$ranks,$name,$level,$sel_taxons,$site,$styles){
 $ranks_count=array();
 $rank_sub = $ranks[$level]['taxon'];
 if(!isset($ranks_count["$rank_sub"])){$ranks_count["$rank_sub"]=1;}else{}
 foreach($taxons["$rank_sub"] as $ssubtax=>$ssubcontent){
  if($ssubcontent['parent']==$name){
   //print taxon data
   $padding=10*($level+1);
   print('<p style="padding-left: '.$padding.'px; margin-top: -1px;  margin-bottom: -1px;">');
   $taxon_status=$ssubcontent['status'];
   if($taxon_status=='valid'){print($ranks_count["$rank_sub"].'. ');}else{}
   print_taxon($ssubcontent,$rank_sub,$styles);
   $taxon_name=$ssubcontent['name'];
   $taxon_name=trim($taxon_name);
   if(array_search($taxon_name,$sel_taxons)!==false){
    print_taxon_search_result($series_data,$geodata,$data_restruct,$cpecimen_parts,$taxon_name,$site);
   }else{}
   if($taxon_status=='valid'){$ranks_count["$rank_sub"]++;}else{}
   print('</p>'."\r\n");
   $sublevel=$level;
   $subname=$ssubcontent['name'];
   while(isset($ranks[($sublevel+1)])){
    $sublevel++;
    show_subtree_result($series_data,$geodata,$data_restruct,$cpecimen_parts,$taxons,$ranks,$subname,$sublevel,$sel_taxons,$site,$styles);
   }
  }else{}
 }
}
//show short explication of result of the search
function print_taxon_search_result($series_data,$geodata,$data_restruct,$cpecimen_parts,$taxon_name,$site){
  if(isset($data_restruct["$taxon_name"])){
    $tdata=$data_restruct["$taxon_name"];
    $out='<div class="search_result">';
    //if references exists
    if(isset($tdata['refs'])){
      $out=$out.'<div><b><i>Literature references:</i></b></div>';
      $locs=array();
      foreach($tdata['refs'] as $refn=>$data){
        $loc=$data['location'];
        $hr=$geodata["$loc"]['h'];
        $ds=$geodata["$loc"]['d'];
        if(!isset($locs["$hr"])){
          $locs["$hr"]=array();
        }else{}
        if(!isset($locs["$hr"]["$loc"])){
          $locs["$hr"]["$loc"]=array('d'=>$ds,'items'=>array());
        }
        $locs["$hr"]["$loc"]['items']["$refn"]=$data['ref'].': '.$data['page'].' ("'.$data['text'].'")';
      }
      ksort($locs);
      foreach($locs as $chr=>$clocs){
        $out=$out.'<div><b>'.$chr.'</b>';
        foreach($clocs as $cloc){
          $content=implode('; ',$cloc['items']);
          $out=$out.'<div>'.$cloc['d'].' ('.$content.').</div>';
        }
        $out=$out.'</div>';
      }
    }else{}
    //if collectiod data exists
    if(isset($tdata['coll'])){
      $out=$out.'<div><b><i>Specimens im collection(s):</i></b></div>';
      $locs=array();
      foreach($tdata['coll'] as $colln=>$data){
        $loc=$data['location'];
        $nser=$data['series'];
        $hr=$geodata["$loc"]['h'];
        $ds=$geodata["$loc"]['d'];
        if(!isset($locs["$hr"])){
          $locs["$hr"]=array();
        }else{}
        if(!isset($locs["$hr"]["$loc"])){
          $locs["$hr"]["$loc"]=array('d'=>$ds,'items'=>array());
        }
        if($data['datetime_end']=='' && $data['datetime_end']==null){
          $timestart=datetime_convert_date($data['datetime_start'],$data['datetime_end']);
        }else{
          $timestart=datetime_convert_date($data['datetime_start']);
        }
        $sdata=$series_data["$nser"]["$taxon_name"];$ndata=count($sdata);
        if($ndata>0){
          $ser_arr=array();
          foreach($sdata as $part=>$number){
            $ser_arr[]=$number.$cpecimen_parts["$part"];
          }
          $sdprint=implode(', ',$ser_arr);
          $sdprint=' - '.$sdprint;
        }else{
          $sdprint='';
        }
        $locs["$hr"]["$loc"]['items']["$nser"]=$data['collector'].' ('.$timestart.$sdprint.')';
      }
      ksort($locs);
      foreach($locs as $chr=>$clocs){
        $out=$out.'<div><b>'.$chr.'</b>';
        foreach($clocs as $cloc){
          $content=implode('; ',$cloc['items']);
          $out=$out.'<div>'.$cloc['d'].' ('.$content.').</div>';
        }
        $out=$out.'</div>';
      }
    }else{}
    //if observation data exists
   if(isset($tdata['obs'])){
      $out=$out.'<div><b><i>Observations:</i></b></div>';
      $locs=array();
      foreach($tdata['obs'] as $obsn=>$data){
        $loc=$data['location'];
        $hr=$geodata["$loc"]['h'];
        $ds=$geodata["$loc"]['d'];
        if(!isset($locs["$hr"])){
          $locs["$hr"]=array();
        }else{}
        if(!isset($locs["$hr"]["$loc"])){
          $locs["$hr"]["$loc"]=array('d'=>$ds,'items'=>array());
        }
        $timestart=datetime_convert_date($data['datetime']);
        $locs["$hr"]["$loc"]['items']["$obsn"]=$data['observer'].' ('.$timestart.')';
      }
      ksort($locs);
      foreach($locs as $chr=>$clocs){
        $out=$out.'<div><b>'.$chr.'</b>';
        foreach($clocs as $cloc){
          $content=implode('; ',$cloc['items']);
          $out=$out.'<div>'.$cloc['d'].' ('.$content.').</div>';
        }
        $out=$out.'</div>';
      }
    }else{}
    
    $out=$out.'<div><span class="link_button"><a href="'.$site.'&sect=all&txn='.$taxon_name.'" target="_blank"><b>complete data (in new window)</b></a></span></div></div>'."\r\n";
    $out=$out.'</div>';
    print($out);
  }else{}
}
//get data of collection series
function get_series_data($db){
  $series=array();
  $squery='SELECT specimen.specimen,specimen.series,specimen.taxon,
                  specpart.number,specpart.part
                  FROM specimen LEFT JOIN specpart ON
                  specpart.specimen=specimen.specimen
                  ORDER BY specimen.taxon,specimen.specimen';
  $sresult=$db->query($squery);
  while($sres = $sresult->fetchArray(SQLITE3_ASSOC)){
    $cseries=$sres['series'];
    $taxon=$sres['taxon'];
    $part=$sres['part'];
    $number=$sres['number'];
    if(!isset($series["$cseries"]["$taxon"])){$series["$cseries"]["$taxon"]=array();}else{}
		if($part!=='' && $part!==null){
	    if(!isset($series["$cseries"]["$taxon"]["$part"])){
	      $series["$cseries"]["$taxon"]["$part"]=$number;
	    }else{
 	     $series["$cseries"]["$taxon"]["$part"]=$series["$cseries"]["$taxon"]["$part"]+$number;
 	   }
		}else{}
  }
  return($series);
}
//convert result of the search to array of taxa
function search_res_restruct($search_data){
  $restruct=array();
  if(isset($search_data['coll'])){
    foreach($search_data['coll'] as $sp=>$data){
      $name=$data['taxon'];
      if(!isset($restruct["$name"]['coll'])){
        $restruct["$name"]['coll']=array();
      }else{}
      $restruct["$name"]['coll']["$sp"]=$data;
    }
  }else{}
  if(isset($search_data['refs'])){
    foreach($search_data['refs'] as $sp=>$data){
      $name=$data['name'];
      if(!isset($restruct["$name"]['refs'])){
        $restruct["$name"]['refs']=array();
      }else{}
      $restruct["$name"]['refs']["$sp"]=$data;
    }
  }else{}
  if(isset($search_data['obs'])){
    foreach($search_data['obs'] as $sp=>$data){
      $name=$data['name'];
      if(!isset($restruct["$name"]['obs'])){
        $restruct["$name"]['obs']=array();
      }else{}
      $restruct["$name"]['obs']["$sp"]=$data;
    }
  }else{}
  return($restruct);
}
//construct taxonomic tree based on result of the search in the section data
function construct_tax_result($search_data,$sect_taxonomy){
  $sel_taxons=array();
  $all_taxonomy=$sect_taxonomy;
  if(isset($search_data['coll'])){
    foreach($search_data['coll'] as $sp=>$data){
      $taxon=$data['taxon'];
      $taxon=trim($taxon);
      if(array_search($taxon,$sel_taxons)==false){
        $sel_taxons[]=$taxon;
      }
    }
  }
  if(isset($search_data['refs'])){
    foreach($search_data['refs'] as $sp=>$data){
      $taxon=$data['name'];
      $taxon=trim($taxon);
      if(array_search($taxon,$sel_taxons)==false){
        $sel_taxons[]=$taxon;
      }
    }
  }
  if(isset($search_data['obs'])){
    foreach($search_data['obs'] as $sp=>$data){
      $taxon=$data['name'];
      $taxon=trim($taxon);
      if(array_search($taxon,$sel_taxons)==false){
        $sel_taxons[]=$taxon;
      }
    }
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
  $end_items=array();
  for($ir=$n_ranks-1;$ir>=0;$ir--){
    $r_name=$ranks[$ir]['taxon'];
    foreach($taxons["$r_name"] as $name=>$content){
      if(isset($sel_struct["$name"])){
        if(!isset($end_items["$r_name"])){
          $end_items["$r_name"]=array();
        }else{}
        $end_items["$r_name"]["$name"]=$taxons["$r_name"]["$name"];
        $item_parent=$end_items["$r_name"]["$name"]['parent'];
        $parent_lev=$ir-1;
        while(isset($ranks[$parent_lev])){
          $par_r_name=$ranks[$parent_lev]['taxon'];
          if(!isset($end_items["$par_r_name"])){
            $end_items["$par_r_name"]=array();
          }else{}
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
    }else{}
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
}
//get descriptions and hierarchy of all locations in thr db
function get_locations($db){
 $geodata=array();
 $geoq="select * from location";
 $georesult=$db->query($geoq);
 while($geores = $georesult->fetchArray(SQLITE3_ASSOC)){
  $gloc=$geores['location'];
  $ghr=$geores['hierarhy'];
  $gdesc=$geores['description'];
  $gsrc=$geores['source'];
  $gdaut=$geores['author'];
  $geodata["$gloc"]=array();
  $geodata["$gloc"]['h']=$ghr;
  $geodata["$gloc"]['d']=$gdesc;
  $geodata["$gloc"]['s']=$gsrc;
  $geodata["$gloc"]['a']=$gdaut;
 }
 return($geodata);
}
//get all specimens data in the section
function coll_to_array_select($db,$names,$geodata){
 $coll_result=array();
 $coll_result['locations']=array();
 $coll_result['specimens']=array();
 $collq="select location.location,series.series,specimen.taxon,
                 specimen.specimen,specimen.collection,
                 series.cmode,series.habitat,series.datetime_start,
                 series.datetime_end,series.collector from location
                 left join series on series.location=location.location
                 left join specimen on specimen.series=series.series";
 $collresult=$db->query($collq);
 $collitems=array();
 while($collres = $collresult->fetchArray(SQLITE3_ASSOC)){
  $sp=$collres['specimen'];
  $coll_tax=$collres['taxon'];
  $coll_loc=$collres['location'];
  if(in_array($coll_tax,$names)){
    $coll_result['specimens'][$sp]=$collres;
    if(!isset($coll_result['locations']["$coll_loc"])){
     $coll_result['locations']["$coll_loc"]=$geodata["$coll_loc"];
   }else{}
  }else{}
 }
 return($coll_result);
}
//get all refefences data in the section
function ref_to_array_select($db,$names,$geodata){
 $ref_result=array();
 $ref_result['locations']=array();
 $ref_result['references']=array();
 $refq="select location.location,reference.reference,reference.name,
               reference.ref,reference.page,reference.citedas,
          	   reference.text from reference
               left join location on reference.location=location.location";
 $refresult=$db->query($refq);
 $refitems=array();
 while($refres = $refresult->fetchArray(SQLITE3_ASSOC)){
  $rf=$refres['reference'];
  $ref_tax=$refres['name'];
  $ref_loc=$refres['location'];
  if(in_array($ref_tax,$names)){
    $ref_result['references'][$rf]=$refres;
    if(!isset($ref_result['locations']["$ref_loc"])){
     $ref_result['locations']["$ref_loc"]=$geodata["$ref_loc"];
   }else{}
  }else{}
 }
 return($ref_result);
}
//get all observations data in the section
function obs_to_array_select($db,$names,$geodata){
 $obs_result=array();
 $obs_result['locations']=array();
 $obs_result['observations']=array();
 $obsq="select location.location,obs.obs,obs.name,obs.datetime,
               obs.observer from obs
               left join location on obs.location=location.location";
 $obsresult=$db->query($obsq);
 $obsitems=array();
 while($obsres = $obsresult->fetchArray(SQLITE3_ASSOC)){
  $obs=$obsres['obs'];
  $obs_tax=$obsres['name'];
  $obs_loc=$obsres['location'];
  if(in_array($obs_tax,$names)){
    $obs_result['observations'][$obs]=$obsres;
    if(!isset($obs_result['locations']["$obs_loc"])){
     $obs_result['locations']["$obs_loc"]=$geodata["$obs_loc"];
   }else{}
  }else{}
 }
 return($obs_result);
}
//returns result of the searching in section data according the query searching (see variables in the form in search.php)
function search_data($coll_result,$ref_result,$obs_result,$sloc,$sarea,$stn,$stx,$spers){
  $coll_search_result=array();
  $colln=count($coll_result['specimens']);
  $refn=count($ref_result['references']);
  $obsn=count($obs_result['observations']);
  if($stn=='' && $stx=='' && $spers==''){
    //if the search performs only in location properties
    //if some collectiom data exists
    if($colln>0){
      foreach($coll_result['specimens'] as $sp=>$data){
        if($sloc!==''){
          $loc_num=$data['location'];
          $loc=$coll_result['locations'][$loc_num]['d'];
          if(stristr($loc,$sloc)){
            $coll_search_result['coll'][$sp]=$data;
          }else{}
        }else{}
        if($sarea!==''){
          $loc_num=$data['location'];
          $area=$coll_result['locations'][$loc_num]['h'];
          if(stristr($area,$sarea)){
            $coll_search_result['coll'][$sp]=$data;
          }else{}
        }else{}
      }
    }else{}
    //if some reference data exits
    if($refn>0){
      foreach($ref_result['references'] as $sp=>$data){
        if($sloc!==''){
          $loc_num=$data['location'];
          $loc=$ref_result['locations'][$loc_num]['d'];
          if(stristr($loc,$sloc)){
            $coll_search_result['refs'][$sp]=$data;
          }else{}
        }else{}
        if($sarea!==''){
          $loc_num=$data['location'];
          $area=$ref_result['locations'][$loc_num]['h'];
          if(stristr($area,$sarea)){
            $coll_search_result['refs'][$sp]=$data;
          }else{}
        }else{}
      }
    }else{}
    //if some observation data exists
    if($obsn>0){
      foreach($obs_result['observations'] as $sp=>$data){
        if($sloc!==''){
          $loc_num=$data['location'];
          $loc=$obs_result['locations'][$loc_num]['d'];
          if(stristr($loc,$sloc)){
            $coll_search_result['obs'][$sp]=$data;
          }else{}
        }else{}
        if($sarea!==''){
          $loc_num=$data['location'];
          $area=$obs_result['locations'][$loc_num]['h'];
          if(stristr($area,$sarea)){
            $coll_search_result['obs'][$sp]=$data;
          }else{}
        }else{}
      }
    }else{}
  }else{
    // if the search performs and(or) in series/observation properties - references are excluded from the result
    if($colln>0){
      foreach($coll_result['specimens'] as $sp=>$data){
        if($sloc!==''){
          $loc_num=$data['location'];
          $loc=$coll_result['locations'][$loc_num]['d'];
          if(stristr($loc,$sloc)){
            $coll_search_result['coll'][$sp]=$data;
          }else{}
        }else{}
        if($sarea!==''){
          $loc_num=$data['location'];
          $area=$coll_result['locations'][$loc_num]['h'];
          if(stristr($area,$sarea)){
            $coll_search_result['coll'][$sp]=$data;
          }else{}
        }else{}
        //if year in the query exists
        //start year only
        if($stn!=='' && $stx==''){
          $stn_item_array=explode(':',$data['datetime_start']);
          $stn_item= (int) $stn_item_array[0];
          if($stn_item>=$stn){
            $coll_search_result['coll'][$sp]=$data;
          }else{}
        }else{}
        //end year only
        if($stn=='' && $stx!==''){
          if($data['datetime_end']!==''){
            $stx_item_array=explode(':',$data['datetime_end']);
            $stx_item= (int) $stx_item_array[0];
            if($stx_item<=$stx){
              $coll_search_result['coll'][$sp]=$data;
            }else{}
          }else{
            $stx_item_array=explode(':',$data['datetime_start']);
            $stx_item=$stx_item_array[0];
            if($stx_item<=$stx){
              $coll_search_result['coll'][$sp]=$data;
            }else{}
          }
        }else{}
        //start and end year both
        if($stn!=='' && $stx!==''){
          if($data['datetime_end']!==''){
            $stn_item_array=explode(':',$data['datetime_start']);
            $stn_item= (int) $stn_item_array[0];
            $stx_item_array=explode(':',$data['datetime_end']);
            $stx_item= (int) $stx_item_array[0];
            if($stn_item>=$stn && $stx_item<=$stx){
              $coll_search_result['coll'][$sp]=$data;
            }else{}
          }else{
            $stn_item_array=explode(':',$data['datetime_start']);
            $stn_item=$stn_item_array[0];
            if($stn_item>=$stn && $stn_item<=$stx){
              $coll_search_result['coll'][$sp]=$data;
            }else{}
          }
        }else{}
        if($spers!==''){
          $pers_item=$data['collector'];
            if(stristr($pers_item,$spers)){
              $coll_search_result['coll'][$sp]=$data;
            }else{}
        }else{}
      }
    }else{}
    //if some observation data exists
    if($obsn>0){
      foreach($obs_result['observations'] as $sp=>$data){
        if($sloc!==''){
          $loc_num=$data['location'];
          $loc=$obs_result['locations'][$loc_num]['d'];
          if(stristr($loc,$sloc)){
            $coll_search_result['obs'][$sp]=$data;
          }else{}
        }else{}
        if($sarea!==''){
          $loc_num=$data['location'];
          $area=$obs_result['locations'][$loc_num]['h'];
          if(stristr($area,$sarea)){
            $coll_search_result['obs'][$sp]=$data;
          }else{}
        }else{}
        //start year only
        if($stn!=='' && $stx==''){
          $stn_item_array=explode(':',$data['datetime']);
          $stn_item= (int) $stn_item_array[0];
          if($stn_item>=$stn){
            $coll_search_result['obs'][$sp]=$data;
          }else{}
        }else{}
        //end year only
        if($stn=='' && $stx!==''){
          $stn_item_array=explode(':',$data['datetime']);
          $stn_item= (int) $stn_item_array[0];
          if($stn_item<=$stx){
            $coll_search_result['obs'][$sp]=$data;
          }else{}
        }else{}
        //start and end year both
        if($stn!=='' && $stx!==''){
          $stn_item_array=explode(':',$data['datetime']);
          $stn_item= (int) $stn_item_array[0];
          if($stn_item>=$stn && $stn_item<=$stn){
            $coll_search_result['obs'][$sp]=$data;
          }else{}
        }else{}
        if($spers!==''){
          $pers_item=$data['observer'];
            if(stristr($pers_item,$spers)){
              $coll_search_result['obs'][$sp]=$data;
            }else{}
        }else{}
      }
    }else{}
  }
  return($coll_search_result);
}
//show all descendant taxons of $name as tree with short data
function show_subtree($db,$taxdata,$cpecimen_parts,$taxons,$ranks,$name,$level,$spec,$site,$styles){
 $ranks_count=array();
 $rank_sub = $ranks[$level]['taxon'];
 if(!isset($ranks_count["$rank_sub"])){$ranks_count["$rank_sub"]=1;}else{}
 foreach($taxons["$rank_sub"] as $ssubtax=>$ssubcontent){
  if($ssubcontent['parent']==$name){
   //print taxon data
   $padding=10*($level+1);
   print('<p style="padding-left: '.$padding.'px; margin-top: -1px;  margin-bottom: -1px;">');
   $taxon_status=$ssubcontent['status'];
   if($taxon_status=='valid'){print($ranks_count["$rank_sub"].'. ');}else{}
   
   print_taxon($ssubcontent,$rank_sub,$styles);
   $taxon_name=$ssubcontent['name'];
   $taxon_name=trim($taxon_name);
   $selector=str_replace(' ','_',$taxon_name);
   $selector=str_replace('.','',$selector);
   $selector=str_replace('(','',$selector);
   $selector=str_replace(')','',$selector);
   $selector=str_replace('?','',$selector);
   if(array_search($taxon_name,$spec)!==false){
     print('<script type="text/javascript">
             $(document).ready(function(){
              $("#'.$selector.'.shdata_toggle").click(function () {
               $("#'.$selector.'.taxon_short_data").slideToggle("slow");
              });
             });
            </script>');
     print(' <span class="shdata_toggle" id="'.$selector.'">show/hide short data</span>'."\r\n");
     $taxon_data=array();
     if(isset($taxdata['specimen']["$taxon_name"])){$taxon_data['coll']=get_taxdata($db,$taxon_name);}else{}
     if(isset($taxdata['reference']["$taxon_name"])){$taxon_data['ref']=get_taxdata_refs($db,$taxon_name);}else{}
     if(isset($taxdata['observation']["$taxon_name"])){$taxon_data['obs']=get_taxdata_obs($db,$taxon_name);}else{}
     print_taxon_short_data($taxon_data,$taxon_name,$cpecimen_parts,$site);
   }else{}
   if($taxon_status=='valid'){$ranks_count["$rank_sub"]++;}else{}
   
   print('</p>'."\r\n");
   $sublevel=$level;
   $subname=$ssubcontent['name'];
   while(isset($ranks[($sublevel+1)])){
    $sublevel++;
    show_subtree($db,$taxdata,$cpecimen_parts,$taxons,$ranks,$subname,$sublevel,$spec,$site,$styles);
   }
  }else{}
 }
}
//construct & return kml file from geodata
function kml_construct($places,$txn,$tree_description){
  $kml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><kml xmlns="http://www.opengis.net/kml/2.2" xmlns:gx="http://www.google.com/kml/ext/2.2" xmlns:kml="http://www.opengis.net/kml/2.2" xmlns:atom="http://www.w3.org/2005/Atom"></kml>');
  $kml_doc = $kml->addChild('Document','&#xA;');
  $kml_doc->addChild('name',$txn);
  $kml_doc->addChild('description',$txn.$tree_description);
  if(isset($places['coll'])){
    $kml_folder_c = $kml_doc->addChild('Folder','&#xA;');
    $kml_folder_c->addChild('name','specimens');
    foreach($places['coll'] as $no_placemark=>$placemark){
      $kml_placemark = $kml_folder_c->addChild('Placemark','&#xA;');
      $kml_placemark->addChild('name',$no_placemark);
      $kml_placemark->addChild('visibility','1');
      $kml_placemark->addChild('open','1');
      $pl_type = $placemark['type'];
      $pl_coordinates = $placemark['coordinates'];
      if($pl_type=='Point'){
        $kml_point = $kml_placemark->addChild('Point','&#xA;');
        $kml_point->addChild('coordinates',$pl_coordinates);
      }elseif($pl_type=='LineString'){
        $kml_linestring = $kml_placemark->addChild('LineString','&#xA;');
        $kml_linestring->addChild('coordinates',$pl_coordinates);
      }elseif($pl_type=='Polygon'){
        $kml_polygon = $kml_placemark->addChild('Polygon','&#xA;');
        $kml_ob = $kml_polygon->addChild('outerBoundaryIs','&#xA;');
        $kml_linearring = $kml_ob->addChild('LinearRing','&#xA;');
        $kml_linearring->addChild('coordinates',$pl_coordinates);
      }else{}
    }
  }else{}
  if(isset($places['ref'])){
    $kml_folder_r = $kml_doc->addChild('Folder','&#xA;');
    $kml_folder_r->addChild('name','references');
    foreach($places['ref'] as $no_placemark=>$placemark){
      $kml_placemark = $kml_folder_r->addChild('Placemark','&#xA;');
      $kml_placemark->addChild('name',$no_placemark);
      $kml_placemark->addChild('visibility','1');
      $kml_placemark->addChild('open','1');
      $pl_type = $placemark['type'];
      $pl_coordinates = $placemark['coordinates'];
      if($pl_type=='Point'){
        $kml_point = $kml_placemark->addChild('Point','&#xA;');
        $kml_point->addChild('coordinates',$pl_coordinates);
      }elseif($pl_type=='LineString'){
        $kml_linestring = $kml_placemark->addChild('LineString','&#xA;');
        $kml_linestring->addChild('coordinates',$pl_coordinates);
      }elseif($pl_type=='Polygon'){
        $kml_polygon = $kml_placemark->addChild('Polygon','&#xA;');
        $kml_ob = $kml_polygon->addChild('outerBoundaryIs','&#xA;');
        $kml_linearring = $kml_ob->addChild('LinearRing','&#xA;');
        $kml_linearring->addChild('coordinates',$pl_coordinates);
      }else{}
    }
  }else{}
  if(isset($places['obs'])){
    $kml_folder_o = $kml_doc->addChild('Folder','&#xA;');
    $kml_folder_o->addChild('name','observations');
    foreach($places['obs'] as $no_placemark=>$placemark){
      $kml_placemark = $kml_folder_o->addChild('Placemark','&#xA;');
      $kml_placemark->addChild('name',$no_placemark);
      $kml_placemark->addChild('visibility','1');
      $kml_placemark->addChild('open','1');
      $pl_type = $placemark['type'];
      $pl_coordinates = $placemark['coordinates'];
      if($pl_type=='Point'){
        $kml_point = $kml_placemark->addChild('Point','&#xA;');
        $kml_point->addChild('coordinates',$pl_coordinates);
      }elseif($pl_type=='LineString'){
        $kml_linestring = $kml_placemark->addChild('LineString','&#xA;');
        $kml_linestring->addChild('coordinates',$pl_coordinates);
      }elseif($pl_type=='Polygon'){
        $kml_polygon = $kml_placemark->addChild('Polygon','&#xA;');
        $kml_ob = $kml_polygon->addChild('outerBoundaryIs','&#xA;');
        $kml_linearring = $kml_ob->addChild('LinearRing','&#xA;');
        $kml_linearring->addChild('coordinates',$pl_coordinates);
      }else{}
    }
  }else{}
  return($kml);
}
//select all placemarks from taxon data
function get_taxon_placemarks($taxon_data,$txn){
  $places=array();
  if(isset($taxon_data['coll'])){
   $places['coll']=array();
   foreach($taxon_data['coll']['locs'] as $location){
      foreach($location['placemarks'] as $plname=>$pl){
       $places['coll']["$plname"]=array('type'=>$pl['type'],'coordinates'=>$pl['coordinates'],);
      }
    }
  }else{}
  if(isset($taxon_data['ref'])){
    $places['ref']=array();
   foreach($taxon_data['ref']['locs'] as $location){
     foreach($location['placemarks'] as $plname=>$pl){
       $places['ref']["$plname"]=array('type'=>$pl['type'],'coordinates'=>$pl['coordinates'],);
     }
  }
  }else{}
  if(isset($taxon_data['obs'])){
   $places['obs']=array();
    foreach($taxon_data['obs']['spec']["$txn"] as $location){
     foreach($location as $oname=>$odata){
       $places['obs']["$oname"]=array('type'=>'Point','coordinates'=>$odata['coordinates'],);
     }
   }
  }else{}
  return($places);
}
//print SHORT data of references, preserved specimens, observations of taxon
function print_taxon_short_data($taxon_data,$txn,$cpecimen_parts,$site){
  $selector=str_replace(' ','_',$txn);
  $selector=str_replace('.','',$selector);
  $outsh='<div class="taxon_short_data" id="'.$selector.'">';
  if(isset($taxon_data['ref'])){
    $tax_loc=$taxon_data['ref']['tax_loc'];
    $lit=$taxon_data['ref']['lit'];
    $locs=$taxon_data['ref']['locs'];
    $spec=$taxon_data['ref']['spec'];
    $sp_arr=$tax_loc["$txn"];
    asort($sp_arr);
    $padding=10;
    $refprint=array();$nterm=0;
    foreach($sp_arr as $no_loc=>$hr){
     if(!isset($curr_hr_ref) || (isset($curr_hr_ref) && $curr_hr_ref!==$hr)){
       $curr_hr_ref=$hr;
       $refprint[$nterm]=array('hr'=>$hr,'locs'=>array(),);
       $nterm++;
     }else{}
     $ldesc=$locs["$no_loc"]['description'];
     $refprint[($nterm-1)]['locs']["$no_loc"]=array('ldesc'=>$ldesc,'refs'=>array(),);
     $sp=$spec["$txn"]["$no_loc"];
     foreach($sp as $refn=>$rdata){
       $rtitle=$rdata['ref'];
       $refprint[($nterm-1)]['locs']["$no_loc"]['refs']["$rtitle"]=$rtitle;
     }
    }
    //print short references data
    $outsh=$outsh.'<div id="taxon_ref"><div class="dataheader"><b>Literature references:</b></div><div class="short_ref_data" id="'.$txn.'">';
    foreach($refprint as $hritem){
      $hrc=$hritem['hr'];
      $outsh=$outsh.'<p style="padding-left: '.$padding.'px; margin-top: -1px;  margin-bottom: -1px;"><b>'.$hrc.':</b> ';
      $hrlocs=array();
      foreach($hritem['locs'] as $location){
        $lseries=array();
        foreach($location['refs'] as $lser){
          $lseries[]=$lser;
        }
        $lseries_str=implode('; ',$lseries);
        $hrlocs[]=$location['ldesc'].' ('.$lseries_str.')';
      }
      $hrlocs_str=implode('; ',$hrlocs);
      $outsh=$outsh.$hrlocs_str.'.</p>'."\r\n";
    } 
    $outsh=$outsh.'</div>'."\r\n";
    $outsh=$outsh.'</div>'."\r\n";
  }else{}
  
  if(isset($taxon_data['coll'])){
    //prepare short collection data
    $tax_loc=$taxon_data['coll']['tax_loc'];
    $ser=$taxon_data['coll']['ser'];
    $locs=$taxon_data['coll']['locs'];
    $spec=$taxon_data['coll']['spec'];
    //print locations, series &specimens
    $sp_arr=$tax_loc["$txn"];
    asort($sp_arr);
    $padding=10;
    $locprint=array();$nterm=0;
    foreach($sp_arr as $no_loc=>$hr){
     if(!isset($curr_hr_coll) || (isset($curr_hr_coll) && $curr_hr_coll!==$hr)){
       $curr_hr_coll=$hr;
       $locprint[$nterm]=array('hr'=>$hr,'locs'=>array(),);
       $nterm++;
     }else{}
     $ldesc=$locs["$no_loc"]['description'];
     $locprint[($nterm-1)]['locs']["$no_loc"]=array('ldesc'=>$ldesc,'series'=>array(),);
     $sp=$spec["$txn"]["$no_loc"];
     foreach($sp as $no_series=>$specimens){
      $start = $ser["$no_series"]['start'];
      $end = $ser["$no_series"]['end'];
      $dtt=datetime_convert_date($start,$end);
      $collector = $ser["$no_series"]['collector'];
      $locprint[($nterm-1)]['locs']["$no_loc"]['series']["$no_series"]['sdesc']=$dtt.', '.$collector;
      $locprint[($nterm-1)]['locs']["$no_loc"]['series']["$no_series"]['smat']=array();
      //specimens
      $nterm_all=0;
      $prt=array();$coll=array();$colls=array();
      foreach($specimens as $no_specimen=>$specimen){
       $nall=$specimen['nall'];
       $nterm_all=$nterm_all+$nall;
       $collection = $specimen['collection'];
       if(!isset($coll["$collection"])){$coll["$collection"]=true;$colls[]=$collection;}else{}
       //parts of the specimen as they in settings.php are signed
       foreach($specimen['parts'] as $part=>$pnum){
        if(!isset($prt["$part"])){
          $prt["$part"]=0;
        }else{}
        $prt["$part"]=$prt["$part"]+$pnum;
       }
     }
     $locprint[($nterm-1)]['locs']["$no_loc"]['series']["$no_series"]['all']=$nterm_all;
     $prtstr='';
     foreach($prt as $part=>$pnum){
       $prtstr=$prtstr.$pnum.$cpecimen_parts["$part"];
     }
     $collstr=implode(', ',$colls);
     $locprint[($nterm-1)]['locs']["$no_loc"]['series']["$no_series"]['smat']='('.$prtstr.', '.$collstr.')';
    }
   }
   //print short collection data
   $outsh=$outsh.'<div id="taxon_coll"><div class="dataheader"><b>Specimens in collection(s):</b></div><div class="short_coll_data" id="'.$txn.'">';
   foreach($locprint as $hritem){
     $hrc=$hritem['hr'];
     $outsh=$outsh.'<p style="padding-left: '.$padding.'px; margin-top: -1px;  margin-bottom: -1px;"><b>'.$hrc.':</b> ';
     $hrlocs=array();
     foreach($hritem['locs'] as $location){
       $lseries=array();
       foreach($location['series'] as $lser){
         $lseries[]=$lser['sdesc'].' '.$lser['smat'];
       }
       $lseries_str=implode(', ',$lseries);
       $hrlocs[]=$location['ldesc'].', '.$lseries_str;
     }
     $hrlocs_str=implode('; ',$hrlocs);
     $outsh=$outsh.$hrlocs_str.'.</p>'."\r\n";
   } 
   $outsh=$outsh.'</div>'."\r\n";
   $outsh=$outsh.'</div>'."\r\n";
  }else{}
  if(isset($taxon_data['obs'])){
    $tax_loc=$taxon_data['obs']['tax_loc'];
    $locs=$taxon_data['obs']['locs'];
    $spec=$taxon_data['obs']['spec'];
    //print locations, series &specimens
    $sp_arr=$tax_loc["$txn"];
    asort($sp_arr);
    $padding=10;
    $obsprint=array();$nterm=0;
    foreach($sp_arr as $no_loc=>$hr){
     if(!isset($curr_hr_obs) || (isset($curr_hr_obs) && $curr_hr_obs!==$hr)){
       $curr_hr_obs=$hr;
       $obsprint[$nterm]=array('hr'=>$hr,'locs'=>array(),);
       $nterm++;
     }else{}
     $ldesc=$locs["$no_loc"]['description'];
     $obsprint[($nterm-1)]['locs']["$no_loc"]=array('ldesc'=>$ldesc,'observ'=>array(),);
     $sp=$spec["$txn"]["$no_loc"];
     foreach($sp as $obsn=>$odata){
       $dtt=datetime_convert_date($odata['datetime']);
       $c_arr=explode(',',$odata['coordinates']);
       $coord=$c_arr[0].' E '.$c_arr[1].' N';
       if(isset($odata['condition']) && $odata['condition']!=='' && $odata['condition']!==null){
         $cond=' ('.$odata['condition'].')';
       }else{$cond='';}
       $obsprint[($nterm-1)]['locs']["$no_loc"]['observ']["$obsn"]=array('coord'=>$coord,'date'=>$dtt,'observer'=>$odata['observer'],'condition'=>$cond,);
     }
    }
    //print short observations data
    $outsh=$outsh.'<div id="taxon_obs"><div class="dataheader"><b>Observations:</b></div><div class="short_obs_data" id="'.$txn.'">';
    foreach($obsprint as $hritem){
      $hrc=$hritem['hr'];
      $outsh=$outsh.'<p style="padding-left: '.$padding.'px; margin-top: -1px;  margin-bottom: -1px;"><b>'.$hrc.':</b> ';
      $hrlocs=array();
      foreach($hritem['locs'] as $location){
        $lseries=array();
        foreach($location['observ'] as $lser){
          $lseries[]=$lser['coord'].' ('.$lser['date'].') - '.$lser['observer'].$lser['condition'];
        }
        $lseries_str=implode('; ',$lseries);
        $hrlocs[]=$location['ldesc'].' ('.$lseries_str.')';
      }
      $hrlocs_str=implode('; ',$hrlocs);
      $outsh=$outsh.$hrlocs_str.'.</p>'."\r\n";
    } 
    $outsh=$outsh.'</div>'."\r\n";
    $outsh=$outsh.'</div>'."\r\n"."\r\n";
  }else{}
  $outsh=$outsh.'<div><span class="link_button"><a href="'.$site.'&sect=all&txn='.$txn.'" target="_blank"><b>complete data (in new window)</b></a></span></div></div>'."\r\n";
  print($outsh);
}
//print data of references, preserved specimens, observations of taxon
function print_taxon_data($taxon_data,$txn,$img_storage,$fdest,$cpecimen_parts,$site,$ibase){
  $out='<div id="taxon_data">'."\r\n";
  if(isset($taxon_data['ref'])){
    $out=$out.'<div id="taxon_ref"><div class="dataheader"><b>Literature references:</b></div>'."\r\n";
    $tax_loc=$taxon_data['ref']['tax_loc'];
    $lit=$taxon_data['ref']['lit'];
    $locs=$taxon_data['ref']['locs'];
    $spec=$taxon_data['ref']['spec'];
    //print locations, series &specimens
    $sp_arr=$tax_loc["$txn"];
    asort($sp_arr);
    $padding=10;
    foreach($sp_arr as $no_loc=>$hr){
     if(!isset($curr_hr_ref) || (isset($curr_hr_ref) && $curr_hr_ref!==$hr)){
       $out=$out.'<p style="padding-left: '.$padding.'px; margin-top: -1px;  margin-bottom: -1px;"><b>'.$hr.':</b></p>'."\r\n";
       $curr_hr_ref=$hr;
     }else{}
     $ldesc=$locs["$no_loc"]['description'];
     $out=$out.'<p style="padding-left: '.($padding+10).'px; margin-top: -1px;  margin-bottom: -1px;">'.$ldesc.' <span class="link_button"><a href="'.$site.'&baselocation='.$no_loc.'" target="_blank">Location data (in new window)</a></span>:</p>'."\r\n";
     $sp=$spec["$txn"]["$no_loc"];
     foreach($sp as $refn=>$rdata){
       $rtitle=$rdata['ref'];
       $out=$out.'<p style="padding-left: '.($padding+20).'px; margin-top: -1px;  margin-bottom: -1px;"><b>Name in the source:</b> "'.$rdata['citedas'].'".<br>'."\r\n";
       $out=$out.'<b>Original reference:</b> "'.$rdata['text'].'".<br>';
       $out=$out.'<script type="text/javascript">
             $(document).ready(function(){
              $("#'.$refn.'.showsrc").click(function () {
               $("#'.$refn.'.lit_src").slideToggle("fast");
              });
             });
            </script>';
       $out=$out.'<b>Source:</b> <a href="'.$lit["$rtitle"]['file'].'" target="_blank">'.$rdata['ref'].'</a>: '.$rdata['page'].' <span class="showsrc" id="'.$refn.'"> <b>complete reference (show / hide)</b> </span><span class="lit_src" id="'.$refn.'">'.$lit["$rtitle"]['text'].'</span></p>'."\r\n";
     }
    }
    $out=$out.'</div>'."\r\n";
  }else{}
  if(isset($taxon_data['coll'])){
    $out=$out.'<div id="taxon_coll"><div class="dataheader"><b>Specimens in collection(s):</b></div>'."\r\n";
    $tax_loc=$taxon_data['coll']['tax_loc'];
    $ser=$taxon_data['coll']['ser'];
    $locs=$taxon_data['coll']['locs'];
    $spec=$taxon_data['coll']['spec'];
    $specind=array();
    foreach($spec["$txn"] as $loci=>$locd){
      foreach($locd as $si=>$sd){
        foreach($sd as $spi=>$spd){
          $specind[]=$spi;
        }
      }
    }
    $prevs_sp=get_sqlite_prevs($specind,$ibase);
    //print locations, series &specimens
    $sp_arr=$tax_loc["$txn"];
    asort($sp_arr);
    $padding=10;
    foreach($sp_arr as $no_loc=>$hr){
     if(!isset($curr_hr_coll) || (isset($curr_hr_coll) && $curr_hr_coll!==$hr)){
       $out=$out.'<p style="padding-left: '.$padding.'px; margin-top: -1px;  margin-bottom: -1px;"><b>'.$hr.':</b></p>'."\r\n";
       $curr_hr_coll=$hr;
     }else{}
     $ldesc=$locs["$no_loc"]['description'];
     $out=$out.'<p style="padding-left: '.($padding+10).'px; margin-top: -1px;  margin-bottom: -1px;">'.$ldesc.' <span class="link_button"><a href="'.$site.'&baselocation='.$no_loc.'" target="_blank">Location data (in new window)</a></span>:</p>'."\r\n";
     $sp=$spec["$txn"]["$no_loc"];
     foreach($sp as $no_series=>$specimens){
      $start = $ser["$no_series"]['start'];
      $end = $ser["$no_series"]['end'];
      $collector = $ser["$no_series"]['collector'];
      $cmode = $ser["$no_series"]['cmode'];
      $habitat = $ser["$no_series"]['habitat'];
      $out=$out.'<p style="padding-left: '.($padding+20).'px; margin-top: -1px;  margin-bottom: -1px;"><i>'.$start;
      if($end!==''){
       $out=$out.' - '.$end;
      }else{}
      if($habitat!==''){
       $out=$out.'; habitat: '.$habitat;
      }else{}
      if($cmode!==''){
       $out=$out.'; collection method: '.$cmode;
      }else{}
      $out=$out.'; collector: '.$collector;
      $out=$out.'</i><br>';
      $out=$out.'Specimens:</p>'."\r\n";
      //specimens
      foreach($specimens as $no_specimen=>$specimen){
       $nall=$specimen['nall'];
       $collection = $specimen['collection'];
       $out=$out.'<p style="padding-left: '.($padding+20).'px; margin-top: -1px;  margin-bottom: -1px;"><a name="'.$no_specimen.'"></a>'.$no_specimen.': '.$nall.' ex.: ';
       //parts of the specimen as they in settings.php are signed
       foreach($specimen['parts'] as $part=>$pnum){
        $out=$out.$pnum.$cpecimen_parts["$part"].' ';
       }
       $out=$out.' <a href="'.$site.'&collect='.$collection.'" title="Collection data '.$collection.'" target="_blank">('.$collection.')</a><br></p><p style="padding-left: '.($padding+30).'px; margin-top: -1px;  margin-bottom: -1px;">Determinations:<br>';
       //determinations
       foreach($specimen['det'] as $no_det=>$determ){
        $det_taxon=$determ['det_taxon'];
        $det_datetime=$determ['det_datetime'];
        $determinator=$determ['determinator'];
        $out=$out.'<i>'.$det_taxon.' ('.$determinator.', '.$det_datetime.')</i><br>';
       }
       $out=$out.'</p>'."\r\n";
       //images
       if(isset($prevs_sp["$no_specimen"])){
        $out=$out.'<p style="padding-left: '.($padding+40).'px; margin-top: -1px;  margin-bottom: -1px;">Images:</p><div class="img_container">';
        foreach($prevs_sp["$no_specimen"] as $ino=>$image){
          $out=$out.'<div><a href="img.php?img='.$ino.'&txn='.$txn.'" target="_blank" title="'.$txn.':'.$no_specimen.'"><img src="data:image/jpeg;base64,'.base64_encode($image).'"></a></div>';
        }
        $out=$out.'</div>';
       }else{}
     }
     $out=$out.'</div></p>'."\r\n";
    }
   }
   $out=$out.'</div>'."\r\n";
  }else{}
  if(isset($taxon_data['obs'])){
    $out=$out.'<div id="taxon_obs"><div class="dataheader"><b>Observations:</b></div>'."\r\n";
    $tax_loc=$taxon_data['obs']['tax_loc'];
    $locs=$taxon_data['obs']['locs'];
    $spec=$taxon_data['obs']['spec'];
    $obsind=array();
    foreach($spec["$txn"] as $loci=>$locd){
      foreach($locd as $obsi=>$obsd){
          $obsind[]=$obsi;
      }
    }
    $prevs_obs=get_sqlite_prevs($obsind,$ibase);
    //print locations, series &specimens
    $sp_arr=$tax_loc["$txn"];
    asort($sp_arr);
    $padding=10;
    foreach($sp_arr as $no_loc=>$hr){
     if(!isset($curr_hr_obs) || (isset($curr_hr_obs) && $curr_hr_obs!==$hr)){
       $out=$out.'<p style="padding-left: '.$padding.'px; margin-top: -1px;  margin-bottom: -1px;"><b>'.$hr.':</b></p>'."\r\n";
       $curr_hr_obs=$hr;
     }else{}
     $ldesc=$locs["$no_loc"]['description'];
     $out=$out.'<p style="padding-left: '.($padding+10).'px; margin-top: -1px;  margin-bottom: -1px;">'.$ldesc.' <span class="link_button"><a href="'.$site.'&baselocation='.$no_loc.'" target="_blank">Location data (in new window)</a></span>:</p>'."\r\n";
     $sp=$spec["$txn"]["$no_loc"];
     foreach($sp as $obsn=>$odata){
       $c_arr=explode(',',$odata['coordinates']);
       $out=$out.'<p style="padding-left: '.($padding+20).'px; margin-top: -1px;  margin-bottom: -1px;">'.$c_arr[0].' E '.$c_arr[1].' N ('.$odata['datetime'].') - '.$odata['observer'].' ('.$odata['condition'].')<br>';
       if(isset($prevs_obs["$obsn"])){
        $out=$out.'<div class="img_container">';
        foreach($prevs_obs["$obsn"] as $ino=>$image){
          $out=$out.'<div><a href="img.php?img='.$ino.'" target="_blank" title="'.$txn.':'.$no_specimen.'"><img src="data:image/jpeg;base64,'.base64_encode($image).'"></a></div>';
        }
        $out=$out.'<br>';
       }else{}
       $out=$out.'</div>'."\r\n";
       $out=$out.'<p style="padding-left: '.($padding+30).'px; margin-top: -1px;  margin-bottom: -1px;">Determinations:</p>'."\r\n";
       foreach($odata['det'] as $odetn=>$odetd){
         $out=$out.'<p style="padding-left: '.($padding+30).'px; margin-top: -1px;  margin-bottom: -1px;">'.$odetd['name'].' - '.$odetd['determinator'].' det., '.$odetd['datetime'].'</p>'."\r\n";
       }
     }
    }
    $out=$out.'</div>'."\r\n";
  }else{}
  $out=$out.'</div>'."\r\n";
  print($out);
}
//get data of taxon from taxonomy
function get_taxon($db,$txn){
  $data=array();
  //taxon
  $taxq="SELECT * FROM taxon WHERE taxon.name='".$txn."'";
  $taxres=$db->query($taxq);
  $taxr=$taxres->fetchArray(SQLITE3_ASSOC);
  foreach($taxr as $tkey=>$tval){
    if($tval!=='' && $tval!==null){
      $data["$tkey"]=$tval;
    }else{}
  }
  //additional data
  $taxdq="SELECT * FROM tdata WHERE tdata.name='".$txn."'";
  $taxdres=$db->query($taxdq);
  $taxdr=$taxdres->fetchArray(SQLITE3_ASSOC);
  foreach($taxdr as $tdkey=>$tdval){
    if($tdval!=='' && $tdval!==null){
      $data["$tdkey"]=$tdval;
    }else{}
  }
  //synonyms
  $data['syn']=array();
  $synq="SELECT * FROM taxon WHERE taxon.ssyn='".$txn."'";
  $synres=$db->query($synq);
  while($synr=$synres->fetchArray(SQLITE3_ASSOC)){
   foreach($synr as $snkey=>$snval){
     $sname=$synr['name'];
     if($snval!=='' && $snval!==null){
       $data['syn']["$sname"]["$snkey"]=$snval;
     }else{}
   }
  }
  return($data);
}
//print common taxon data
function print_taxon_header($taxon){
  $tprint='<div class="tax_as_header">';
  $tprint=$tprint.'<div><b><i>'.$taxon['name'].'</i> '.$taxon['abbr'].'</b></div>'."\r\n";
  //data of original description - if exists
  if(isset($taxon['orig'])){
    $tprint=$tprint.'<div class="taxon_orig"><i>'.$taxon['orig'].'</i> '.$taxon['auct'].' '.$taxon['year'];
    if(isset($taxon['ref'])){
      $tprint=$tprint.' ('.$taxon['ref'].': '.$taxon['page'];
      if(isset($taxon['origin'])){
        $tprint=$tprint.' - '.$taxon['origin'];
      }else{}
      $tprint=$tprint.')';
    }else{}
    $tprint=$tprint.'</div>'."\r\n";
  }else{}
  ///data of synonyms - if exists
  if(isset($taxon['syn'])){
    foreach($taxon['syn'] as $synonym){
      $tprint=$tprint.'<div class="synonym">=<i>'.$synonym['name'].'</i> '.$synonym['abbr'];
       if(isset($taxon['ref'])){
         $tprint=$tprint.' (<i>'.$synonym['orig'].'</i> '.$synonym['ref'].': '.$synonym['page'];
         if(isset($synonym['origin'])){
           $tprint=$tprint.' - '.$synonym['origin'];
         }else{}
         $tprint=$tprint.')';
       }else{}
      $tprint=$tprint.'</div>'."\r\n";
    }
  }else{}
  //additional data
  if(isset($taxon['general'])){
    $tprint=$tprint.'<div class="add_data"><b>General description:</b> '.$taxon['general'].'</div>'."\r\n";
  }
  if(isset($taxon['mdescript'])){
    $tprint=$tprint.'<div class="add_data"><b>Morphology:</b> '.$taxon['mdescript'].'</div>'."\r\n";
  }
  if(isset($taxon['ecology'])){
    $tprint=$tprint.'<div class="add_data"><b>Ecology:</b> '.$taxon['ecology'].'</div>'."\r\n";
  }
  if(isset($taxon['biology'])){
    $tprint=$tprint.'<div class="add_data"><b>Biology:</b> '.$taxon['biology'].'</div>'."\r\n";
  }
  if(isset($taxon['distribution'])){
    $tprint=$tprint.'<div class="add_data"><b>Distribution:</b> '.$taxon['distribution'].'</div>'."\r\n";
  }
  $tprint=$tprint.'</div>'."\r\n";
  print($tprint);
}
//get the arrays of names of the taxons that have some data in DB
function taxdata_exists($db){
  $taxdata=array('specimen'=>array(),'reference'=>array(),'observation'=>array(),);
  $collq="SELECT distinct(specimen.taxon) FROM specimen ORDER BY specimen.taxon";
  $refq="SELECT distinct(reference.name) FROM reference ORDER BY reference.name";
  $obsq="SELECT distinct(obs.name) FROM obs ORDER BY obs.name";
  //
  $collres=$db->query($collq);
  $refres=$db->query($refq);
  $obsres=$db->query($obsq);
  //
  while($collr=$collres->fetchArray(SQLITE3_ASSOC)){
    $cname=$collr['taxon'];
    $taxdata['specimen']["$cname"]=true;
  }
  while($refr=$refres->fetchArray(SQLITE3_ASSOC)){
    $rname=$refr['name'];
    $taxdata['reference']["$rname"]=true;
  }
  while($obsr=$obsres->fetchArray(SQLITE3_ASSOC)){
    $oname=$obsr['name'];
    $taxdata['observation']["$oname"]=true;
  }
  return($taxdata);
}
//converse arrays of names of the taxons that have some data in DB to plain array
function taxdata_exists_plain($taxdata){
  $names=array();
  foreach($taxdata['specimen'] as $cname=>$val){
    $names[]=$cname;
  }
  foreach($taxdata['reference'] as $rname=>$val){
    if(!isset($taxdata['specimen']["$rname"]) && !isset($taxdata['observation']["$rname"])){
      $names[]=$rname;
    }else{}
  }
  foreach($taxdata['observation'] as $oname=>$val){
    if(!isset($taxdata['specimen']["$oname"]) && !isset($taxdata['reference']["$oname"])){
      $names[]=$oname;
    }else{}
  }
  return($names);
}
//get all collection data for specified taxon - see get_taxdata($db,$txn) in functions

//get all literature references data for specified taxon
function get_taxdata_refs($db,$txn){
  //get references
  $refs=array();
  $refq="SELECT * FROM reference WHERE reference.name='".$txn."'";
  $rresult=$db->query($refq);
  while($rres=$rresult->fetchArray(SQLITE3_ASSOC)){
    $rnum=$rres['reference'];
    $refs["$rnum"]=array('ref'=>$rres['ref'],'location'=>$rres['location'],'name'=>$rres['name'],'citedas'=>$rres['citedas'],'text'=>$rres['text'],'page'=>$rres['page'],);
  }
  //prepare lists for queries
  $ref_arr=array();
  $loc_arr=array();
  foreach($refs as $ref=>$rdata){
    $ref_arr[]=$rdata['ref'];
    $loc_arr[]=$rdata['location'];
  }
  $ref_str=implode('" OR literature.ref="',$ref_arr);$ref_str='(literature.ref="'.$ref_str.'")';
  $loc_str=implode('" OR location.location="',$loc_arr);$loc_str='(location.location="'.$loc_str.'")';
  $pl_str=implode('" OR placemark.location="',$loc_arr);$pl_str='(placemark.location="'.$pl_str.'")';
  //get literature
  $lit=array();
  $litq="SELECT * FROM literature WHERE ".$ref_str;
  $litres=$db->query($litq);
  while($litr=$litres->fetchArray(SQLITE3_ASSOC)){
    $litref=$litr['ref'];
    $lit["$litref"]=array('text'=>$litr['text'],'file'=>$litr['file'],);
  }
  //get locations
  $ql="SELECT * from location WHERE ".$loc_str;
  $lresult=$db->query($ql);
  $locs=array();
  $hrs=array();
  while($lres = $lresult->fetchArray(SQLITE3_ASSOC)){
   $nloc=$lres['location'];
   $hrs["$nloc"]=$lres['hierarhy'];
   $locs["$nloc"]=array('description'=>$lres['description'],'placemarks'=>array(),);
  }
  //get placemarks
  $pll="SELECT * from placemark WHERE ".$pl_str;
  $plresult=$db->query($pll);
  while($plres = $plresult->fetchArray(SQLITE3_ASSOC)){
   $pln=$plres['placemark'];
   $lpln=$plres['location'];
   $locs["$lpln"]['placemarks']["$pln"]=array('type'=>$plres['type'],'coordinates'=>$plres['coordinates'],);
  }
  //create final arrays
  $tax_loc=array("$txn"=>$hrs,);
  $spec=array("$txn"=>array(),);
  foreach($locs as $flocn=>$flocd){
   $spec["$txn"]["$flocn"]=array();
   foreach($refs as $fsern=>$fserd){
    $ffloc=$fserd['location'];
    if($ffloc==$flocn){
     $spec["$txn"]["$flocn"]["$fsern"]=$fserd; 
    }else{}
   }
  }
  $data=array('tax_loc'=>$tax_loc,'spec'=>$spec,'lit'=>$lit,'locs'=>$locs,);
  return($data);
}
//get all observations data for specified taxon
function get_taxdata_obs($db,$txn){
  //get observations
  $obs=array();
  $obsq="SELECT * FROM obs WHERE obs.name='".$txn."'";
  $oresult=$db->query($obsq);
  while($ores=$oresult->fetchArray(SQLITE3_ASSOC)){
    $obsn=$ores['obs'];
    $obs["$obsn"]=array('location'=>$ores['location'],
                        'coordinates'=>$ores['coordinates'],
                        'datetime'=>$ores['datetime'],
                        'observer'=>$ores['observer'],
                        'name'=>$ores['name'],
                        'condition'=>$ores['condition'],
                        'file'=>$ores['file'],
                        'det'=>array(),);
  }
  //prepare lists for queries
  $loc_arr=array();
  $obs_arr=array();
  foreach($obs as $onum=>$odata){
    $loc_arr[]=$odata['location'];
    $obs_arr[]=$onum;
  }
  $obs_str=implode("' OR obsdet.obs='",$obs_arr);$obs_str="(obsdet.obs='".$obs_str."')";
  $loc_str=implode("' OR location.location='",$loc_arr);$loc_str="(location.location='".$loc_str."')";
  //get locations
  $ql="SELECT * from location WHERE ".$loc_str;
  $lresult=$db->query($ql);
  $locs=array();
  $hrs=array();
  while($lres = $lresult->fetchArray(SQLITE3_ASSOC)){
   $nloc=$lres['location'];
   $hrs["$nloc"]=$lres['hierarhy'];
   $locs["$nloc"]=array('description'=>$lres['description'],);
   $pl_qu="SELECT * from placemark WHERE location='$nloc'";
   $pl_result=$db->query($pl_qu);
   while($pl_data = $pl_result->fetchArray(SQLITE3_ASSOC)){
     $nplace=$pl_data['placemark'];
     $locs["$nloc"]['placemarks']["$nplace"]=array('type'=>$pl_data['type'],'coordinates'=>$pl_data['coordinates'],);
   }
  }
  //get determinations
  $qobd="SELECT * FROM obsdet WHERE ".$obs_str;
  $obdres=$db->query($qobd);
  while($obdr = $obdres->fetchArray(SQLITE3_ASSOC)){
    $obsdn=$obdr['obsdet'];
    $obsnum=$obdr['obs'];
    $obs["$obsnum"]['det']["$obsdn"]=array('name'=>$obdr['name'],'determinator'=>$obdr['determinator'],'datetime'=>$obdr['datetime'],);
  }
  //create final arrays
  $tax_loc=array("$txn"=>$hrs,);
  $spec=array("$txn"=>array(),);
  
  foreach($locs as $flocn=>$flocd){
   $spec["$txn"]["$flocn"]=array();
   foreach($obs as $fsern=>$fserd){
    $ffloc=$fserd['location'];
    if($ffloc==$flocn){
     $spec["$txn"]["$flocn"]["$fsern"]=$fserd; 
    }else{}
   }
  }
  $data=array('tax_loc'=>$tax_loc,'spec'=>$spec,'locs'=>$locs,);
  return($data);
}
//get all data of collection
function get_collection_data($db,$collect){
  $collq="SELECT * FROM collections WHERE collection='".$collect."'";
  $collresult=$db->query($collq);
  $collres = $collresult->fetchArray(SQLITE3_ASSOC);
  return($collres);
}
//gets array of references and returns array of previews from image database
function get_sqlite_prevs($ind,$ibase){
 $idb = new SQLite3($ibase);
 $indimpl=implode("' OR img.refer='",$ind);
 $indstr="img.refer='".$indimpl."'";
 $iq="SELECT img.refer,prev.img,prev.src 
       FROM img JOIN prev ON img.img=prev.img
       AND (".$indstr.")
       ORDER BY img.refer";
 $iresult=$idb->query($iq);
 $prevs=array();
  while($ires = $iresult->fetchArray(SQLITE3_ASSOC)){
  $iref=$ires['refer'];
  $iname=$ires['img'];
  $icontent=$ires['src'];
  if(!isset($prevs["$iref"])){
   $prevs["$iref"]=array();
  }else{}
  $prevs["$iref"]["$iname"]=$icontent;
 }
 return($prevs);
}
//
//select from collection data the data for taxonomic data of the section
function select_in_data($coll_result,$sect_taxonomy){
 $coll_sel_result=array();
 $tterms=array();
 foreach($sect_taxonomy['taxons'] as $srank=>$sitems){
  foreach($sitems as $sn=>$sd){
   $tterms["$sn"]=true;
  }
 }
 foreach($coll_result['specimens'] as $colln=>$colldata){
  $collname=$colldata['taxon'];
  if(isset($tterms["$collname"])){
   $coll_sel_result["$colln"]=$colldata;
  }else{}
 }
 return($coll_sel_result);
}
//
//recursive function for selection "ascendant" names for the main group ($select)
function asc_select($allt,$select,$ends=array()){
  $start=($allt["$select"]);
  $ends["$select"]=true;
  foreach($start as $skey=>$val){
    $ends["$skey"]=true;
    if(isset($allt["$skey"])){
      $ends=asc_select($allt,$skey,$ends);
    }else{}
  }
  return($ends);
}
//
//returns array of "ascendant" taxons for the mane group ($gr)
function get_asc_tax($db,$gr){
 $gr=trim($gr);
 $strucq="SELECT taxon.parent,taxon.name FROM taxon WHERE taxon.parent NOT LIKE ''";
 $strucresult=$db->query($strucq);
 $strucitems=array();
 while($strucres = $strucresult->fetchArray(SQLITE3_ASSOC)){
  $name=trim($strucres['name']);
  $parent=trim($strucres['parent']);
  if(!isset($strucitems["$parent"])){
   $strucitems["$parent"]=array();
  }else{}
  $strucitems["$parent"]["$name"]=true;
 }
 $ascterms=asc_select($strucitems,$gr);
 //
 $tq="SELECT * FROM taxon WHERE prank NOT NULL";
 $tresult=$db->query($tq);
 $titems=array();
 while($tres = $tresult->fetchArray(SQLITE3_ASSOC)){
  $prename=$tres['name'];
  if(isset($ascterms["$prename"])){
   $titems[]=$tres;
  }
 }
 //get synonyms
 $synq="select taxon.ssyn,taxon.name,taxon.abbr from taxon 
 where taxon.ssyn NOT NULL and taxon.ssyn not like ''";
 $sysresult=$db->query($synq);
 $synitems=array();
 while($synres = $sysresult->fetchArray(SQLITE3_ASSOC)){
  $ssyn=$synres['ssyn'];
  $synname=$synres['name'];
  $synabbr=$synres['abbr'];
  if(!isset($synitems["$ssyn"])){
   $synitems["$ssyn"]=array();
  }else{}
  $synitems["$ssyn"]["$synname"]=$synabbr;
 }
 //create array of taxons
 $taxons=array();
 foreach($titems as $titem){
  $trank=$titem['rank'];
  $tparent=$titem['parent'];
  $tname=$titem['name'];
  $tautor=$titem['abbr'];
  $tstatus=$titem['status'];
  if(!isset($taxons["$trank"])){
   $taxons["$trank"]=array();
  }else{}
  $taxons["$trank"]["$tname"]=array();
  $taxons["$trank"]["$tname"]['parent']=$tparent;
  $taxons["$trank"]["$tname"]['name']=$tname;
  $taxons["$trank"]["$tname"]['autor']=$tautor;
  $taxons["$trank"]["$tname"]['status']=$tstatus;
  if(isset($synitems["$tname"])){
   $syns=array();
   foreach($synitems["$tname"] as $nsyn=>$nabbr){
    $syns[]='<i>'.$nsyn.'</i> '.$nabbr;
   }
   $taxons["$trank"]["$tname"]['syn']=implode(', =',$syns);
  }else{}
 }
 return($taxons);
}
//gets from database all taxonomic names that are'nt prents for any taxon
function get_species($db,$macro_arr){
 $allq="SELECT distinct(taxon.name) FROM taxon WHERE taxon.parent NOT LIKE '' ORDER BY taxon.name";
 $allresult=$db->query($allq);
 $allitems=array();
 while($allres = $allresult->fetchArray(SQLITE3_ASSOC)){
  if(!in_array($allres['name'],$macro_arr)){
   $allitems[]=$allres['name'];
  }
 }
 return($allitems);
}
//gets from database all taxonomic names that are prents for some lesser taxons
function get_macrotax($db){
 $macroq="SELECT distinct(taxon.parent) FROM taxon WHERE taxon.parent NOT LIKE '' ORDER BY taxon.parent";
 $macroresult=$db->query($macroq);
 $macroitems=array();
 while($macrores = $macroresult->fetchArray(SQLITE3_ASSOC)){
  $macroitems[]=$macrores['parent'];
 }
 return($macroitems);
}
//retuns hierarhical array (0...n) for taxonomic ranks among selected taxons (according $r_order in settings.php)
function get_sect_ranks($taxons,$r_order){
 $secrank=array();$srnum=0;
 $ex_ranks=array();
 foreach($taxons as $curr_rank=>$tdata){
  $ex_ranks["$curr_rank"]=true;
 }
 foreach($r_order as $r_rank=>$r_pseud){
  if(isset($ex_ranks["$r_rank"])){
   $secrank[$srnum]=array();
   if($srnum==0){
    $secrank[$srnum]['taxons']='';
   }else{
    $secrank[$srnum]['taxons']=$r_rank;
   }
   $secrank[$srnum]['taxon']=$r_rank;
   $secrank[$srnum]['pseudonym']=$r_pseud;
   $srnum++;
  }else{}
 }
 return($secrank);
}
//-----------------------------------------------------------------------------------------------------------------------
//get db data of collection to array for search
//-----------------------------------------------------------------------------------------------------------------------
function coll_to_array($db){
 $coll_result=array();
 $coll_result['locations']=array();
 $coll_result['specimens']=array();
 //parse geodata
 $geoq="select location.location,location.description,location.hierarhy from location";
 $georesult=$db->query($geoq);
 $geoitems=array();
 while($geores = $georesult->fetchArray(SQLITE3_ASSOC)){
  $gloc=$geores['location'];
  $ghr=$geores['hierarhy'];
  $gdesc=$geores['description'];
  $coll_result['locations']["$gloc"]=array();
  $coll_result['locations']["$gloc"]['h']=$ghr;
  $coll_result['locations']["$gloc"]['d']=$gdesc;
  
 }
 //parse collection
 //!!!REFACTORING IS NEEDED: TOO BIG DATA RETURNS FROM DB (also search_in_data function needs to correct)
 $collq="select location.location,series.series,specimen.taxon,
                 specimen.specimen,specimen.collection,
                 series.cmode,series.habitat,series.datetime_start,
                    series.datetime_end,series.collector from location
                 left join series on series.location=location.location
                    left join specimen on specimen.series=series.series";
 $collresult=$db->query($collq);
 $collitems=array();
 while($collres = $collresult->fetchArray(SQLITE3_ASSOC)){
  $sp=$collres['specimen'];
  $coll_result['specimens'][$sp]=$collres;
 }
 return($coll_result);
}
//
//returns array of placemarks for taxon
function get_place_by_tax($db,$txn){
 $tpq="select specimen.series,specimen.specimen,specimen.taxon,
       series.location,
       placemark.placemark,placemark.type,placemark.coordinates
       from specimen 
       join series
       on series.series=specimen.series
       and specimen.taxon='".$txn."'
       left join placemark
       on placemark.location=series.location";
 $tpqresult=$db->query($tpq);
 $geodata=array();
 while($tpqres = $tpqresult->fetchArray(SQLITE3_ASSOC)){
  $placemark=$tpqres['placemark'];
  $type=$tpqres['type'];;
  $coordinates=$tpqres['coordinates'];;
  if(!isset($geodata["$placemark"])){$geodata["$placemark"]=array();}else{}
  $geodata["$placemark"]['type']=trim($type);
  $geodata["$placemark"]['coordinates']=trim($coordinates);
 }
 return($geodata);
}
//
function get_sect_tax($db,$sect){
 //!!!REFACTORING IS NEEDED: 2 QUERYES TO 1 (section join taxon -> select columns)
 //get all taxon from section
 $stq="SELECT * FROM section WHERE section='".$sect."'";
 $stresult=$db->query($stq);
 $stitems=array();
 while($stres = $stresult->fetchArray(SQLITE3_ASSOC)){
  //print($stres['name'].'<br>');
  $sname=$stres['name'];
  $stitems["$sname"]=true;
 }
 //
 $tq="SELECT * FROM taxon WHERE prank NOT NULL";
 $tresult=$db->query($tq);
 $titems=array();
 while($tres = $tresult->fetchArray(SQLITE3_ASSOC)){
  $prename=$tres['name'];
  if(isset($stitems["$prename"])){
   $titems[]=$tres;
  }
 }
 //get synonyms
 $synq="select taxon.ssyn,taxon.name,taxon.abbr from taxon 
 where taxon.ssyn NOT NULL and taxon.ssyn not like ''";
 $sysresult=$db->query($synq);
 $synitems=array();
 while($synres = $sysresult->fetchArray(SQLITE3_ASSOC)){
  $ssyn=$synres['ssyn'];
  $synname=$synres['name'];
  $synabbr=$synres['abbr'];
  if(!isset($synitems["$ssyn"])){
   $synitems["$ssyn"]=array();
  }else{}
  $synitems["$ssyn"]["$synname"]=$synabbr;
 }
 //create array of taxons
 $taxons=array();
 foreach($titems as $titem){
  $trank=$titem['rank'];
  $tparent=$titem['parent'];
  $tprank=$titem['prank'];
  $tname=$titem['name'];
  $tautor=$titem['abbr'];
  $tstatus=$titem['status'];
  if(!isset($taxons["$trank"])){
   $taxons["$trank"]=array();
  }else{}
  $taxons["$trank"]["$tname"]=array();
  $taxons["$trank"]["$tname"]['parent']=$tparent;
  $taxons["$trank"]["$tname"]['prank']=$tprank;
  $taxons["$trank"]["$tname"]['name']=$tname;
  $taxons["$trank"]["$tname"]['autor']=$tautor;
  $taxons["$trank"]["$tname"]['status']=$tstatus;
  if(isset($synitems["$tname"])){
   $syns=array();
   foreach($synitems["$tname"] as $nsyn=>$nabbr){
    $syns[]='<i>'.$nsyn.'</i> '.$nabbr;
   }
   $taxons["$trank"]["$tname"]['syn']=implode(', =',$syns);
  }else{}
 }
 return($taxons);
}
//
//retuns all unique names of taxons in collection
function get_allspec_names($db){
 $query="SELECT distinct(taxon) FROM specimen";
 $tnames=$db->query($query);
 $names=array();
 while($tres = $tnames->fetchArray(SQLITE3_ASSOC)){
  $lname=$tres['taxon'];
  $names["$lname"]=$lname;
 }
 return($names);
}
//
//returms description (all rows) of the section
function get_section_desc($db,$sect){
 $query="SELECT * FROM sections WHERE section='".$sect."'";
 $result=$db->query($query);
 $res = $result->fetchArray(SQLITE3_ASSOC);
 return($res);
}
//
//returns 
function get_sections_all($db){
 $query="SELECT * FROM sections";
 $result=$db->query($query);
 $bases=array();
 while($res = $result->fetchArray(SQLITE3_ASSOC)){
  $base=$res['section'];
  $bases["$base"]=array('title'=>$res['title'],'description'=>$res['description'],);
 }
 return($bases);
}
//
//returns collection data for taxon
function get_taxdata($db,$txn){
 //get specimens
 $spms=array();
 $series=array();
 $query="SELECT * FROM specimen WHERE taxon='".$txn."'";
 $result=$db->query($query);
 while($spm = $result->fetchArray(SQLITE3_ASSOC)){
  $num=$spm['specimen'];
  $ser=$spm['series'];
  if(!isset($seies["$ser"])){
   $series["$ser"]=array();
  }else{}
  $spms["$num"]=array('series'=>$ser,
                      'nall'=>$spm['parts'],
                      'parts'=>array(),
                      'collection'=>$spm['collection'],
                      'det'=>array(),);
 }
 //prepare lists for queries
 $spm_arr=array();
 foreach($spms as $spmsn=>$spmsd){
  $spm_arr[]=$spmsn;
 }
 $spm_l=implode("' OR specimen='",$spm_arr);
 $spm_l="(specimen='".$spm_l."')";
 $ser_arr=array();
 foreach($series as $sern=>$serd){
  $ser_arr[]=$sern;
 }
 //get parts of specimen
 $qpars="SELECT * FROM specpart WHERE ".$spm_l;
 $qpresult=$db->query($qpars);
 while($qpres = $qpresult->fetchArray(SQLITE3_ASSOC)){
  $pspec=$qpres['specimen'];
  $pcase=$qpres['part'];
  $pnum=$qpres['number'];
  $spms["$pspec"]['parts']["$pcase"]=$pnum;
 }
 $ser_l=implode("' OR series='",$ser_arr);
 $ser_l="(series='".$ser_l."')";
 //get determ
 $qt="SELECT * from determ WHERE ".$spm_l;
 $tresult=$db->query($qt);
 while($tres = $tresult->fetchArray(SQLITE3_ASSOC)){
  $ndt=$tres['determ'];
  $nsp=$tres['specimen'];
  $spms["$nsp"]['det']["$ndt"]=array('det_taxon'=>$tres['taxon'],'det_datetime'=>$tres['datetime'],'determinator'=>$tres['determinator'],);
 }
 //get series
 $qs="SELECT * from series WHERE ".$ser_l;
 $sresult=$db->query($qs);
 $locs=array();
 while($sres = $sresult->fetchArray(SQLITE3_ASSOC)){
  $nser=$sres['series'];
  $nloc=$sres['location'];
  if(!isset($locs["$nloc"])){
   $locs["$nloc"]=array();
  }else{}
  $series["$nser"]=array('location'=>$nloc,'start'=>$sres['datetime_start'],'end'=>$sres['datetime_end'],'collector'=>$sres['collector'],'cmode'=>$sres['cmode'],'habitat'=>$sres['habitat'],);
 }
 //prepare lists for query
 $loc_arr=array();
 foreach($locs as $ln=>$ld){
  $loc_arr[]=$ln;
 }
 $loc_l=implode("' OR location='",$loc_arr);
 $loc_l="(location='".$loc_l."')";
 //get locations
 $ql="SELECT * from location WHERE ".$loc_l;
 $lresult=$db->query($ql);
 $hrs=array();
 while($lres = $lresult->fetchArray(SQLITE3_ASSOC)){
  $nloc=$lres['location'];
  $hrs["$nloc"]=$lres['hierarhy'];
  $locs["$nloc"]=array('description'=>$lres['description'],'placemarks'=>array(),);
 }
 //get placemarks
 $pll="SELECT * from placemark WHERE ".$loc_l;
 $plresult=$db->query($pll);
 while($plres = $plresult->fetchArray(SQLITE3_ASSOC)){
  $pln=$plres['placemark'];
  $lpln=$plres['location'];
  $locs["$lpln"]['placemarks']["$pln"]=array('type'=>$plres['type'],'coordinates'=>$plres['coordinates'],);
 }
 //create final arrays
 $tax_loc=array("$txn"=>$hrs,);
 $ser=$series;
 $locs=$locs;
 $spec=array("$txn"=>array(),);
 foreach($locs as $flocn=>$flocd){
  $spec["$txn"]["$flocn"]=array();
  foreach($series as $fsern=>$fserd){
   $ffloc=$fserd['location'];
   if($ffloc==$flocn){
    $spec["$txn"]["$flocn"]["$fsern"]=array();
    foreach($spms as $fspmn=>$fspmd){
     $ffser=$fspmd['series'];
     if($ffser==$fsern){
      $spec["$txn"]["$flocn"]["$fsern"]["$fspmn"]=$fspmd;
     }else{}
    }
   }else{}
  }
 }
 //output
 $data=array('tax_loc'=>$tax_loc,'ser'=>$ser,'locs'=>$locs,'spec'=>$spec,);
 return($data);
}
//-----------------------------------------------------------------------------------------------------------------------
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
//TD: refactoring
//function must return 2 arrays (<div></div>):
//1 - print for all subtaxa; 2 - print for valid subtaxa
function show_sub_taxa($taxons,$ranks,$name,$level,$spec,$site,$sect,$styles){
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
   print_taxon($ssubcontent,$rank_sub,$styles);
   $taxon_name=$ssubcontent['name'];
   $taxon_name=trim($taxon_name);
   if(array_search($taxon_name,$spec)!==false){
    print(' <a href="'.$site.'&sect='.$sect.'&txn='.$taxon_name.'" target="_blank">коллекция</a>');
   }else{}
   $ranks_count["$rank_sub"]++;
   print('</p>');
   $sublevel=$level;
   $subname=$ssubcontent['name'];
   while(isset($ranks[($sublevel+1)])){
    $sublevel++;
    show_sub_taxa($taxons,$ranks,$subname,$sublevel,$spec,$site,$sect,$styles);
   }
  }else{}
 }
}
//-----------------------------------------------------------------------------------------------------------------------
//print data of selected taxon ($styles - from settings.php)
//-----------------------------------------------------------------------------------------------------------------------
function print_taxon($taxon,$style,$styles){
//print data of taxon from the tree ($taxon=$taxons['taxon level']['taxon name']) with style $styles[$style] as below
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
//construct taxonomic array of results from whole tree
//-----------------------------------------------------------------------------------------------------------------------
function construct_taxonomy($coll_search_result,$sect_taxonomy){
//arguments: numeric array of exists taxons (list), array: array1(ranks) array2 (taxons in order of ranks)
 $sel_taxons=array();
 $all_taxonomy=$sect_taxonomy;
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
 //print_r($sel_struct);print('<br>--<br>');
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
  }
//-----------------------------------------------------------------------------------------------------------------------
//print selected tree of taxons from some point (with short collection data of each taxon)
//-----------------------------------------------------------------------------------------------------------------------
function show_sub_taxa_data($taxons,$ranks,$name,$level,$spec,$site,$sect,$locations,$specimens,$styles){
 //this function in common as show_sub_taxa (u.s.), but prints also data of specimens from 2 last arguments 
 $ranks_count=array();
 $rank_sub = $ranks[$level]['taxon'];
 if(!isset($ranks_count["$rank_sub"])){$ranks_count["$rank_sub"]=1;}else{}
 foreach($taxons["$rank_sub"] as $ssubtax=>$ssubcontent){
  if($ssubcontent['parent']==$name){
   //print taxon data
   $padding=10*($level+1);
   print('<p style="padding-left: '.$padding.'px; margin-top: -1px;  margin-bottom: -1px;">');
   print($ranks_count["$rank_sub"].'. ');
   print_taxon($ssubcontent,$rank_sub,$styles);
   $taxon_name=$ssubcontent['name'];
   $taxon_name=trim($taxon_name);
   if(array_search($taxon_name,$spec)!==false){
    print(' <a href="'.$site.'&sect='.$sect.'&txn='.$taxon_name.'" target="_blank">коллекция</a>');
    short_taxon_data($taxon_name,$locations,$padding,$site,$sect);
   }else{}
   $ranks_count["$rank_sub"]++;
   print('</p>');
   $sublevel=$level;
   $subname=$ssubcontent['name'];
   while(isset($ranks[($sublevel+1)])){
    $sublevel++;
    show_sub_taxa_data($taxons,$ranks,$subname,$sublevel,$spec,$site,$sect,$locations,$specimens,$styles);
   }
  }else{}
 }
}
//-----------------------------------------------------------------------------------------------------------------------
//print short collection data of taxon
//-----------------------------------------------------------------------------------------------------------------------
function short_taxon_data($taxon,$locations,$padding,$site,$sect){
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
     print('<a href="'.$site.'&sect='.$sect.'&txn='.$taxon.'#'.$n_sp.'" target="_blank">'.$n_sp.'</a><i>: ');
     $date_data=datetime_convert_date($sp['datetime_start'],$sp['datetime_end']);
     print($date_data.'; ');
     if($sp['habitat']!==''){
      print($sp['habitat'].'; ');
     }else{}
     if($sp['cmode']!==''){
      print($sp['cmode'].'; ');
     }else{}
     print($sp['collector'].' - </i>');
     print('<a href="'.$site.'&sect='.$sect.'&collect='.$sp['collection'].'" target="_blank">'.$sp['collection'].'</a>.');
     print('</p>');
    }else{}
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
//-----------------------------------------------------------------------------------------------------------------------
//make array of markes for OSM map based on taxon data
//-----------------------------------------------------------------------------------------------------------------------
function construct_leaflet_markers($taxon_data,$txn,$site){
  $leaflet_locs=array();
  if(isset($taxon_data['coll']['locs'])){foreach($taxon_data['coll']['locs'] as $location=>$loc_data){$leaflet_locs['coll']["$location"]=$loc_data;}}
  if(isset($taxon_data['ref']['locs'])){foreach($taxon_data['ref']['locs'] as $location=>$loc_data){$leaflet_locs['ref']["$location"]=$loc_data;}}
  if(isset($taxon_data['obs']['locs'])){foreach($taxon_data['obs']['locs'] as $location=>$loc_data){$leaflet_locs['obs']["$location"]=$loc_data;}}
  $markers=array();
  foreach($leaflet_locs as $loc_type=>$locations){
    if($loc_type=='coll'){$color='green';}elseif($loc_type=='ref'){$color='black';}elseif($loc_type=='obs'){$color='yellow';}
    foreach($locations as $location=>$loc_data){
    $loc_desc=$loc_data['description'];
    $locname_esc=str_replace("'","&#x27;",$location);
    $locdesc_esc=str_replace("'","&#x27;",$loc_desc);
    foreach($loc_data['placemarks'] as $placemark){
      $pl_type=$placemark['type'];
      $pl_coord=$placemark['coordinates'];
      if($pl_type=='Point'){
        $coord_arr=explode(',',$pl_coord);
        $lon=$coord_arr[0];$lat=$coord_arr[1];
        $loc_link='<a href="'.$site."&baselocation=$locname_esc".'" target="blank">'."$locdesc_esc</a>";
        $markers[]="L.circleMarker({lon: $lon, lat: $lat}, {radius: 8, color: '$color', fillColor: '$color',}).bindPopup("."'".$loc_link."'".").addTo(map);";
      }else{
        $nlimit='0';$slimit='90';$wlimit='90';$elimit='0';
        $points_arr=explode(' ',$pl_coord);
        foreach($points_arr as $point){
          $point_arr=explode(',',$pl_coord);
          if($point_arr[0]>$elimit){$elimit=$point_arr[0];}else{}
          if($point_arr[0]<$wlimit){$wlimit=$point_arr[0];}else{}
          if($point_arr[1]>$nlimit){$nlimit=$point_arr[1];}else{}
          if($point_arr[1]<$slimit){$slimit=$point_arr[1];}else{}
        }
        $dtat=$nlimit-$slimit;
        $dlon=$elimit-$wlimit;
        $clon=$elimit-($dlon/2);
        $clat=$nlimit-($dlat/2);
        $loc_link='<a href="'.$site."&baselocation=$locname_esc".'" target="blank">'."$locdesc_esc</a>";
        $markers[]="L.circleMarker({lon: $clon, lat: $clat}, {radius: 8, color: '$color', fillColor: '$color',}).bindPopup("."'".$loc_link."'".").addTo(map);";
      }
    }
    }
  }
  $leaflet_markers=implode("\r\n",$markers);
  return($leaflet_markers);
}
//-----------------------------------------------------------------------------------------------------------------------
//mprepare map (leaflet.js) from array of placemarks
//-----------------------------------------------------------------------------------------------------------------------
function leaflet_prepare_map($placemarks,$w=500,$h=600,$dzoom=10){
  $map='';
  //determine: is only one point in array or not
  $is_point=true;
  $pl_quantity=count($placemarks);
  if($pl_quantity==1){
    $pl_keys=array_keys($placemarks);
    if($placemarks[$pl_keys[0]]['type']!=='Point'){$is_point=false;}else{}
  }else{$is_point=false;}
    //contruct map
  if($is_point){
    $lonlat=explode(',',$placemarks[$pl_keys[0]] ['coordinates']);
    $lon=$lonlat[0];$lat=$lonlat[1];
    $map=$map.'<style>
      #map { width: '.$w.'px; height: '.$h.'px; border: 2px solid rgb(42, 86, 29); }
      #basemaps-wrapper { border: 1px solid rgb(42, 86, 29); width: 500px; text-align: center; background: #6c806d; }
      #basemaps { margin-bottom: 2px; }
</style>
<script></script>
<div>The location on the map:</div>
<div id="basemaps-wrapper">
  <select id="basemaps">
    <option value="Topographic">Topographic</option>
    <option value="Streets">Streets</option>
    <option value="NationalGeographic">National Geographic</option>
    <option value="Oceans">Oceans</option>
    <option value="Gray">Gray</option>
    <option value="DarkGray">Dark Gray</option>
    <option value="Imagery">Imagery</option>
    <option value="ImageryClarity">Imagery (Clarity)</option>
    <option value="ImageryFirefly">Imagery (Firefly)</option>
    <option value="ShadedRelief">Shaded Relief</option>
    <option value="Physical">Physical</option>
  </select>
</div>
<div id="map"></div>';
    $map=$map."<script>
 var map = L.map('map').setView({lon: $lon, lat: $lat}, $dzoom);
 var layer = L.esri.basemapLayer('Topographic').addTo(map);
 var layerLabels;
 function setBasemap (basemap) {
    if (layer) {
      map.removeLayer(layer);
    }
    layer = L.esri.basemapLayer(basemap);
    map.addLayer(layer);
    if (layerLabels) {
      map.removeLayer(layerLabels);
    }
    if (
      basemap === 'ShadedRelief' ||
      basemap === 'Oceans' ||
      basemap === 'Gray' ||
      basemap === 'DarkGray' ||
      basemap === 'Terrain'
    ) {
      layerLabels = L.esri.basemapLayer(basemap + 'Labels');
      map.addLayer(layerLabels);
    } else if (basemap.includes('Imagery')) {
      layerLabels = L.esri.basemapLayer('ImageryLabels');
      map.addLayer(layerLabels);
    }
  }
  document
    .querySelector('#basemaps')
    .addEventListener('change', function (e) {
      var basemap = e.target.value;
      setBasemap(basemap);
    });";
    $map=$map."L.control.scale().addTo(map);
          L.circleMarker({lon: $lon, lat: $lat}, {radius: 15, color: 'green', fillColor: 'green',}).addTo(map);
          </script>";
  }else{
    //defime limits of the map and constuct the markers
    $nlimit='0';$slimit='90';$wlimit='90';$elimit='0';
    $vectors=array();
    foreach($placemarks as $pl_name=>$pl){
      $coordinates=$pl['coordinates'];
      $type=$pl['type'];
      if($type!=='Point'){
        $coord=explode(' ',$coordinates);
        $latlon=array();
        foreach($coord as $item){
          $data=explode(',',$item);
          $latlon[]='['.$data[1].', '.$data[0].']';
          if($data[0]>$elimit){$elimit=$data[0];}else{}
          if($data[0]<$wlimit){$wlimit=$data[0];}else{}
          if($data[1]>$nlimit){$nlimit=$data[1];}else{}
          if($data[1]<$slimit){$slimit=$data[1];}else{}
        }
        if($type=='LineString'){$vtype='polyline';}else{$vtype='polygon';}
        $path_str=implode(',',$latlon);
        $latlngs="[$path_str]";
        $vectors[$pl_name]="var latlngs = $latlngs; var $vtype = L.$vtype(latlngs, {color: 'green'}).addTo(map);";
      }else{
        $point_data=explode(',',$coordinates);
        if($point_data[0]>$elimit){$elimit=$point_data[0];}else{}
        if($point_data[0]<$wlimit){$wlimit=$point_data[0];}else{}
        if($point_data[1]>$nlimit){$nlimit=$point_data[1];}else{}
        if($point_data[1]<$slimit){$slimit=$point_data[1];}else{}
        $point_lon=$point_data[0];$point_lat=$point_data[1];
        $vectors[$pl_name]="L.circleMarker({lon: $point_lon, lat: $point_lat}, {radius: 15, color: 'green', fillColor: 'green',}).addTo(map);";
      }
    }
    $vector_block=implode("\r\n",$vectors);
    //calculate the limits
    $dlat=$nlimit-$slimit;
    $dlon=$elimit-$wlimit;
    $north=$nlimit+$dlat;
    $south=$slimit-$dlat;
    $west=$wlimit-$dlon;
    $east=$elimit+$dlon;
    $clon=$elimit-($dlon/2);
    $clat=$nlimit-($dlat/2);
    $rectangle="var bounds = [[$slimit, $wlimit], [$nlimit, $elimit]]; L.rectangle(bounds, {color: 'white', opacity: 0.0}).addTo(map); map.fitBounds(bounds);";
    //create map
    $map=$map.'<style>
      #map { width: '.$w.'px; height: '.$h.'px; border: 2px solid rgb(42, 86, 29); }
      #basemaps-wrapper { border: 1px solid rgb(42, 86, 29); width: 500px; text-align: center; background: #6c806d; }
      #basemaps { margin-bottom: 2px; }
</style>
<script></script>
<div>The location on the map:</div>
<div id="basemaps-wrapper">
  <select id="basemaps">
    <option value="Topographic">Topographic</option>
    <option value="Streets">Streets</option>
    <option value="NationalGeographic">National Geographic</option>
    <option value="Oceans">Oceans</option>
    <option value="Gray">Gray</option>
    <option value="DarkGray">Dark Gray</option>
    <option value="Imagery">Imagery</option>
    <option value="ImageryClarity">Imagery (Clarity)</option>
    <option value="ImageryFirefly">Imagery (Firefly)</option>
    <option value="ShadedRelief">Shaded Relief</option>
    <option value="Physical">Physical</option>
  </select>
</div>
<div id="map"></div>';
    $map=$map."<script>
 var map = L.map('map').setView({lon: $clon, lat: $clat}, $dzoom);
 var layer = L.esri.basemapLayer('Topographic').addTo(map);
 var layerLabels;
 function setBasemap (basemap) {
    if (layer) {
      map.removeLayer(layer);
    }
    layer = L.esri.basemapLayer(basemap);
    map.addLayer(layer);
    if (layerLabels) {
      map.removeLayer(layerLabels);
    }
    if (
      basemap === 'ShadedRelief' ||
      basemap === 'Oceans' ||
      basemap === 'Gray' ||
      basemap === 'DarkGray' ||
      basemap === 'Terrain'
    ) {
      layerLabels = L.esri.basemapLayer(basemap + 'Labels');
      map.addLayer(layerLabels);
    } else if (basemap.includes('Imagery')) {
      layerLabels = L.esri.basemapLayer('ImageryLabels');
      map.addLayer(layerLabels);
    }
  }
  document
    .querySelector('#basemaps')
    .addEventListener('change', function (e) {
      var basemap = e.target.value;
      setBasemap(basemap);
    });
    L.control.scale().addTo(map);";
    $map=$map.$rectangle."\r\n".$vector_block."\r\n";
    $map=$map."</script>";
  }
  return($map);
}
?>