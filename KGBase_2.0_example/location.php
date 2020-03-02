<?php
$geodata=get_locations($db);
$placemarks=get_placemarks($db);
if(isset($geodata["$baselocation"])){
	//get&print geodata for the location
	$geodata=get_locations($db);
	$placemarks=get_placemarks($db);
	$kml_gd=array("$baselocation"=>$geodata["$baselocation"]);
	$kml_pl=array("$baselocation"=>$placemarks["$baselocation"]);
	$map_calc=calc_limits_ne($placemarks["$baselocation"]);
	print('<div class="text_as_header"><b>Lоcation '.$baselocation.':</b></div>');
	print('<div><b>Country, province, etc.</b>: '.$geodata["$baselocation"]['h'].'</div>');
	print('<div><b>Description</b>: '.$geodata["$baselocation"]['d'].'</div>');
	print('<div><b>Souce of geodata</b>: '.$geodata["$baselocation"]['s'].'</div>');
	print('<div><b>Georefereced by</b>: '.$geodata["$baselocation"]['a'].'</div>');
	print('<div id="geodata"><b><a href="'.$rootsite.'kml.php?location='.$baselocation.'" target="_blank">Download geodata (.kml file)</a></b></div>');
	$leaflet_map=leaflet_prepare_map($placemarks["$baselocation"]);
  print($leaflet_map);
	$prevs_loc=get_sqlite_prevs(array('0'=>$baselocation),$ibase);
  if(isset($prevs_loc["$baselocation"])){
		print('<div><br><b>Images:</b></div><div class="img_container">');
  	foreach($prevs_loc["$baselocation"] as $ino=>$image){
  		print('<div><a href="img.php?img='.$ino.'" target="_blank" title="'.$baselocation.'"><img src="data:image/jpeg;base64,'.base64_encode($image).'"></a></div>');
  	}
		print('</div>');
  }else{}
	//get&print biodiversity data for series
	$series_data=get_series_data($db);
	$coll_result=coll_to_array_location($db,$baselocation);
	$ref_result=ref_to_array_location($db,$baselocation);
	$obs_result=obs_to_array_location($db,$baselocation);
	$search_data=array();
	$loc_names=array();
	if($coll_result!==null){
		$search_data['coll']=$coll_result['coll'];
		foreach($search_data['coll'] as $coll_item){
			$tname=$coll_item['taxon'];
			if(!isset($loc_names["$tname"])){
				$loc_names["$tname"]=true;
			}else{}
		}
	}else{}
	if($ref_result!==null){
		$search_data['refs']=$ref_result['refs'];
		foreach($search_data['refs'] as $refs_item){
			$tname=$refs_item['name'];
			if(!isset($loc_names["$tname"])){
				$loc_names["$tname"]=true;
			}else{}
		}
	}else{}
	if($obs_result!==null){
		$search_data['obs']=$obs_result['obs'];
		foreach($search_data['obs'] as $obs_item){
			$tname=$obs_item['name'];
			if(!isset($loc_names["$tname"])){
				$loc_names["$tname"]=true;
			}else{}
		}
	}else{}
	ksort($loc_names);
	$data_restruct=search_res_restruct($search_data);
	print('<div class="text_as_header"><b>Literature references, specimens in collection(s), observations related to the location:</b><div>');
	foreach($loc_names as $lname=>$lval){
		print('<div><b><i>'.$lname.'</i></b></div>');
		print_taxon_search_location($baselocation,$series_data,$data_restruct,$cpecimen_parts,$lname,$site);
	}
}else{
  print("<div>Sorry, the location $baselocation is absent in the database</div>");
}
?>