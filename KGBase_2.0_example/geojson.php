<?php
include('settings.php');
include('functions.php');
@$taxon=$_GET['taxon'];
@$geometry=$_GET['geometry'];
if(!isset($taxon)){$taxon='all';}
if(!isset($geometry)){$geometry='centroid';}
$db = new SQLite3($base);
//convert locations of DB to geojson format
$locations=geojson_get_location_data($db);
$geojson_locations=array();
foreach($locations as $location_ID=>$location_data){
  $geojson_locations["$location_ID"]=geojson_location_data_transform($location_data);
}
//get data (specimens, observations, references) from DB - for the specified taxon or for all
$specimens=geojson_get_specimens_data($db,$taxon);
$observations=geojson_get_observations_data($db,$taxon);
$references=geojson_get_references_data($db,$taxon);
$geojson_data=array();
$spec_num=count($specimens);if($spec_num>0){$geojson_data['spec']=$specimens;}
$obs_num=count($observations);if($obs_num>0){$geojson_data['obs']=$observations;}
$ref_num=count($references);if($ref_num>0){$geojson_data['ref']=$references;}
//encode and send to client the geojson file
$current_datetime=date(DATE_ATOM);
if(isset($taxon) && $taxon!=='all'){
  $dataset_name=$base_title.' '.$subtitle.' '.$taxon." $current_datetime";
}else{
  $dataset_name=$base_title.' '.$subtitle." $current_datetime";
}
$json_filename=str_replace(' ','_',$dataset_name.".geojson");
$geojson=geojson_data_encode($dataset_name,$geojson_locations,$geojson_data,$geometry); $txn_path=str_replace(' ','_',$txn);
header('Content-type:text/plain;');
header('Content-Disposition: attachment; filename="'.$json_filename.'"');
echo $geojson;

// ------------------------------------------------------ FUNCTIONS ---------------------------------------------------------//
//encode geojson from the data
function geojson_data_encode($dataset_name,$geojson_locations,$geojson_data,$geometry='centroid'){
  $geojson_array=array();
  $geojson_array['type']='FeatureCollection';
  $geojson_array['name']=$dataset_name;
  $geojson_array['crs']=array('type'=>'name','properties'=>array('name'=>'urn:ogc:def:crs:OGC:1.3:CRS84',),);
  $geojson_array['features']=array();
  $f_num=0;
  $npol=1;
  foreach($geojson_data as $data_sect){
    foreach($data_sect as $item_ID=>$item_data){
      //properties
      $geojson_array['features'][$f_num]['type']='Feature';
      $geojson_array['features'][$f_num]['properties']=array();
      foreach($item_data as $data_title=>$data_value){
        $geojson_array['features'][$f_num]['properties']["$data_title"]=$data_value;
      }
      //geodata
      $loc_data_ID=$item_data['location_ID'];
      $item_location=$geojson_locations["$loc_data_ID"];
      $geojson_array['features'][$f_num]['properties']['err_degrees']=$item_location['err_degrees'];
      $geojson_array['features'][$f_num]['properties']['err_meters']=$item_location['err_meters'];
      if($geometry=='centroid'){
        $geojson_array['features'][$f_num]['geometry']=array('type'=>'Point','coordinates'=>$item_location['centroid'],);
      }else{
        $geojson_array['features'][$f_num]['geometry']=array('type'=>$item_location['type'],'coordinates'=>$item_location['geojson_coordinates'],);
      }
      $f_num++;
    }
  }
  //encode end return
  $geojson=json_encode($geojson_array, JSON_PRETTY_PRINT);
  return($geojson);
}
// -------------------------------------------------------------------------------------------------------------------------//
//get observations data for geojson
function geojson_get_references_data($db,$taxon='all'){
  $references=array();
  if($taxon=='all'){
    $ref_qu="select location.location,location.hierarhy,reference.reference,reference.name,taxon.rank,taxon.status,reference.ref,reference.citedas from reference join location on reference.location=location.location join taxon on reference.name=taxon.name order by reference.name";
  }else{
    $ref_qu="select location.location,location.hierarhy,reference.reference,reference.name,taxon.rank,taxon.status,reference.ref,reference.citedas from reference join location on reference.location=location.location join taxon on reference.name=taxon.name where taxon.name='$taxon' order by reference.name";
  }
  $ref_result=$db->query($ref_qu);
	while($ref_res = $ref_result->fetchArray(SQLITE3_ASSOC)){
    $ref_id=trim($ref_res['reference']);
    $references["$ref_id"]['ID']=$ref_id;
    $references["$ref_id"]['record_type']='reference';
    $references["$ref_id"]['taxon_name']=$ref_res['name'];
    $references["$ref_id"]['taxon_rank']=$ref_res['rank'];
    $references["$ref_id"]['taxon_status']=$ref_res['status'];
    $references["$ref_id"]['location_ID']=$ref_res['location'];
    $hr_arr=explode(',',$ref_res['hierarhy']);
    $references["$ref_id"]['country']=trim($hr_arr[0]);
    if(isset($hr_arr[1])){$references["$ref_id"]['region']=trim($hr_arr[1]);}else{$references["$ref_id"]['region']='';}
    if(isset($hr_arr[2])){$references["$ref_id"]['district']=trim($hr_arr[2]);}else{$references["$ref_id"]['district']='';}
    $references["$ref_id"]['collector']='';
    $references["$ref_id"]['collection']='';
    $references["$ref_id"]['date']='';
    $references["$ref_id"]['observer']='';
    $references["$ref_id"]['source']=$ref_res['ref'];
    $references["$ref_id"]['source_name']=$ref_res['citedas'];
  }
  return($references);
}
// -------------------------------------------------------------------------------------------------------------------------//
//get observations data for geojson
function geojson_get_observations_data($db,$taxon='all'){
  $observations=array();
  if($taxon=='all'){
    $obs_qu="select location.location,location.hierarhy,obs.obs,obs.datetime,obs.observer,obs.name,taxon.rank,taxon.status from obs join location on obs.location=location.location join taxon on obs.name=taxon.name order by obs.name";
  }else{
    $obs_qu="select location.location,location.hierarhy,obs.obs,obs.datetime,obs.observer,obs.name,taxon.rank,taxon.status from obs join location on obs.location=location.location join taxon on obs.name=taxon.name where taxon.name='$taxon' order by obs.name";
  }
  $obs_result=$db->query($obs_qu);
	while($obs_res = $obs_result->fetchArray(SQLITE3_ASSOC)){
    $obs_id=trim($obs_res['obs']);
    $observations["$obs_id"]['ID']=$obs_id;
    $observations["$obs_id"]['record_type']='observation';
    $observations["$obs_id"]['taxon_name']=$obs_res['name'];
    $observations["$obs_id"]['taxon_rank']=$obs_res['rank'];
    $observations["$obs_id"]['taxon_status']=$obs_res['status'];
    $observations["$obs_id"]['location_ID']=$obs_res['location'];
    $hr_arr=explode(',',$obs_res['hierarhy']);
    $observations["$obs_id"]['country']=trim($hr_arr[0]);
    if(isset($hr_arr[1])){$observations["$obs_id"]['region']=trim($hr_arr[1]);}else{$observations["$obs_id"]['region']='';}
    if(isset($hr_arr[2])){$observations["$obs_id"]['district']=trim($hr_arr[2]);}else{$observations["$obs_id"]['district']='';}
    $observations["$obs_id"]['collector']='';
    $observations["$obs_id"]['collection']='';
    $date_data_arr=explode(' ',$obs_res['datetime']);
    $observations["$obs_id"]['date']=str_replace(':','-',$date_data_arr[0]);
    $observations["$obs_id"]['observer']=$obs_res['observer'];
    $observations["$obs_id"]['source']='';
    $observations["$obs_id"]['source_name']='';
  }
  return($observations);
}
// -------------------------------------------------------------------------------------------------------------------------//
//get specimens data for geojson
function geojson_get_specimens_data($db,$taxon='all'){
  $specimens=array();
  if($taxon=='all'){
    $spec_qu="select location.location,location.hierarhy,specimen.specimen,specimen.taxon,taxon.rank,taxon.status,specimen.collection,series.collector,series.datetime_start from specimen join taxon on specimen.taxon=taxon.name join series on specimen.series=series.series join location on series.location=location.location order by specimen.taxon";
  }else{
    $spec_qu="select location.location,location.hierarhy,specimen.specimen,specimen.taxon,taxon.rank,taxon.status,specimen.collection,series.collector,series.datetime_start from specimen join taxon on specimen.taxon=taxon.name join series on specimen.series=series.series join location on series.location=location.location where taxon.name='$taxon' order by specimen.taxon";
  }
  $spec_result=$db->query($spec_qu);
	while($spec_res = $spec_result->fetchArray(SQLITE3_ASSOC)){
    $spec_id=trim($spec_res['specimen']);
    $specimens["$spec_id"]['ID']=$spec_id;
    $specimens["$spec_id"]['record_type']='specimen';
    $specimens["$spec_id"]['taxon_name']=$spec_res['taxon'];
    $specimens["$spec_id"]['taxon_rank']=$spec_res['rank'];
    $specimens["$spec_id"]['taxon_status']=$spec_res['status'];
    $specimens["$spec_id"]['location_ID']=$spec_res['location'];
    $hr_arr=explode(',',$spec_res['hierarhy']);
    $specimens["$spec_id"]['country']=trim($hr_arr[0]);
    if(isset($hr_arr[1])){$specimens["$spec_id"]['region']=trim($hr_arr[1]);}else{$specimens["$spec_id"]['region']='';}
    if(isset($hr_arr[2])){$specimens["$spec_id"]['district']=trim($hr_arr[2]);}else{$specimens["$spec_id"]['district']='';}
    $specimens["$spec_id"]['collector']=$spec_res['collector'];
    $specimens["$spec_id"]['collection']=$spec_res['collection'];
    $date_data_arr=explode(' ',$spec_res['datetime_start']);
    $specimens["$spec_id"]['date']=str_replace(':','-',$date_data_arr[0]);
    $specimens["$spec_id"]['observer']='';
    $specimens["$spec_id"]['source']='';
    $specimens["$spec_id"]['source_name']='';
  }
  return($specimens);
}
// -------------------------------------------------------------------------------------------------------------------------//
//trnsform $location_data (all placemarks of the location) to the geojso format and calculate centroid with potential error
function geojson_location_data_transform($location_data){
  $location_data2=$location_data;
  $geojson_data=array();
  //type of geometry and coordinates in geojson format
  $lpacemark_num=count($location_data);
  if($lpacemark_num==1){
    //if the location has only 1 placemark
    $pl_keys=array_keys($location_data);
    $pl=$location_data[$pl_keys[0]];
    $geojson_data['type']=$pl['type'];
    if($pl['type']=='Point'){
      $coord_array=explode(',',$pl['coordinates']);
      $curr_lon=(float)$coord_array[0];
      $curr_lat=(float)$coord_array[1];
      $geojson_data['geojson_coordinates']=array($curr_lon,$curr_lat,);
      $point_lon=(float)$coord_array[0];
      $point_lat=(float)$coord_array[1];
    }else{
      $points_array=explode(' ',$pl['coordinates']);
      $points_geojson_arr=array();
      foreach($points_array as $point){
        $coord_array=explode(',',$point);
        $curr_lon=(float)$coord_array[0];
        $curr_lat=(float)$coord_array[1];
        $points_geojson_arr[]=array($curr_lon,$curr_lat,);
      }
      $geojson_data['geojson_coordinates']=$points_geojson_arr;
      if($pl['type']=='Polygon'){
        //
        //NONE: https://tools.ietf.org/html/rfc7946#section-3.1.6
        //"A linear ring MUST follow the _right-hand rule_ with respect to the area it bounds"
        //BUT, "For backwards compatibility, parsers SHOULD NOT reject Polygons that do not follow the right-hand rule"
        //now, some polygons genereted here may cause errors in some application, but, according RFC, it's not critical error
        //Issue: add a right-hand test of polygons, and array (coordinates) reversion if it falls 
        //
        $geojson_data['geojson_coordinates']=array($points_geojson_arr,);
      }else{
        $geojson_data['geojson_coordinates']=$points_geojson_arr;
      }
    }
  }else{
    $geom_elements=array();
    //if the location has several placemarks
    //make array of placemarks in geojson format
    foreach($location_data as $placemark){
      if($placemark['type']=='Point'){
        $coord_array=explode(',',$placemark['coordinates']);
        $pt_lon=(float)$coord_array[0];
        $pt_lat=(float)$coord_array[1];
        $geom_elements['points'][]=array($pt_lon,$pt_lat);
      }elseif($placemark['type']=='LineString'){
        $points_array=explode(' ',$placemark['coordinates']);
        $points_geojson_arr=array();
        foreach($points_array as $point){
          $coord_array=explode(',',$point);
          $ln_lon=(float)$coord_array[0];
          $ln_lat=(float)$coord_array[1];
          $points_geojson_arr[]=array($ln_lon,$ln_lat,);
        }
        $geom_elements['lines'][]=$points_geojson_arr;
      }else{
        $points_array=explode(' ',$placemark['coordinates']);
        $points_geojson_arr=array();
        foreach($points_array as $point){
          $coord_array=explode(',',$point);
          $poly_lon=(float)$coord_array[0];
          $poly_lat=(float)$coord_array[1];
          $points_geojson_arr[]=array($poly_lon,$poly_lat,);
        }
        $geom_elements['polygons'][]=$points_geojson_arr;
      }
    }
    //
    $geom_num=count($geom_elements);
    if($geom_num==1){
      //if all of placemarks in the location has one the same type
      if(isset($geom_elements['points'])){
        //MultiPoint
        //$mitlipoint=implode(", ",$geom_elements['points']);
        $geojson_data['type']='MultiPoint';
        $geojson_data['geojson_coordinates']=$geom_elements['points'];
      }elseif($geom_elements['lines']){
        //Issue: add MultiLineString parsing
      }else{
        //Issue: add MultiPolygon parsing
      }
    }else{
      //if placemarks in the location has several types
      //Issue: add geometry collection parsing
    }
  }
  //centroid (calculate limits and the center of the location)
  $nlimit='0';$slimit='90';$wlimit='90';$elimit='0';
  foreach($location_data2 as $pl_name=>$pl){
    $coordinates=$pl['coordinates'];
    $type=$pl['type'];
    if($type!=='Point'){
      $coord=explode(' ',$coordinates);
      foreach($coord as $item){
        $data=explode(',',$item);
        if($data[0]>$elimit){$elimit=$data[0];}else{}
        if($data[0]<$wlimit){$wlimit=$data[0];}else{}
        if($data[1]>$nlimit){$nlimit=$data[1];}else{}
        if($data[1]<$slimit){$slimit=$data[1];}else{}
      }
    }else{
      $point_data=explode(',',$coordinates);
      if($point_data[0]>$elimit){$elimit=$point_data[0];}else{}
      if($point_data[0]<$wlimit){$wlimit=$point_data[0];}else{}
      if($point_data[1]>$nlimit){$nlimit=$point_data[1];}else{}
      if($point_data[1]<$slimit){$slimit=$point_data[1];}else{}
    }
  }
  //calculate the limits
  $dlat=$nlimit-$slimit;
  $dlon=$elimit-$wlimit;
  $deltasum=$dlat+$dlon;
  if($deltasum>0){
    //if the location is not a single point - 
    //calculate border, center and potential errors
    $clon=(float)$elimit-($dlon/2);
    $clat=(float)$nlimit-($dlat/2);
    //maximal difference between borders of the location - in longitude or latitude
    if($dlat>$dlon){$max=$dlat;}else{$max=$dlon;}
    $lat_dist=$clat+$max;
    //potential error ($max/2) in meters:
    //modified code from https://www.geodatasource.com/developers/php
    //LGPLv3
    $theta = 0.001;
    $dist = sin(deg2rad($clat)) * sin(deg2rad($lat_dist)) +  cos(deg2rad($clat)) * cos(deg2rad($lat_dist)) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    $meters = $dist * 60 * 1.1515 * 0.8684 * 1000;
    //
    $geojson_data['err_degrees']=round($max,4);
    $geojson_data['err_meters']=round($meters,-1);
    $geojson_data['centroid']=array($clon,$clat,);
  }else{
    //if the location IS a single point -
    //the coordinate potential error in angular degrees is accepted here 
    //as the last unit of the coordinate value digit capacity
    //as 1 degree of a meridian on the ground approximately corresponds to 100 kilometers ((40008/360)=111,13=~100 km), 
    //the error of coordinates in meters is accepted here error in degrees, multiplied by 100000 meters
    if(isset($point_lon) && isset($point_lat)){
      $float_arr=explode('.',$point_lon);
      $fl_acc=strlen($float_arr[1]);
      $geojson_data['err_degrees']=1/pow(10,$fl_acc);
      $geojson_data['err_meters']=1/pow(10,$fl_acc)*100000;
      //$geojson_data['centroid']='[ '.$point_lon.', '.$point_lat.' ]';
      $geojson_data['centroid']=array($point_lon,$point_lat,);
    }else{
      //a hypothetical case where the location is a single point, but the coordinates for it have not been set before
      print('<div>Something wrong with the location :(</div>');
    }
  }
  return($geojson_data);
}
//get all location data fron the database ($db)
function geojson_get_location_data($db){
  $locations=array();
  $loc_qu="select * from placemark";
  $loc_result=$db->query($loc_qu);
	while($loc_res = $loc_result->fetchArray(SQLITE3_ASSOC)){
    $loc_ID=trim($loc_res['location']);
    $loc_pl=trim($loc_res['placemark']);
    $loc_type=trim($loc_res['type']);
    $loc_coord=trim($loc_res['coordinates']);
    $locations["$loc_ID"]["$loc_pl"]=array('type'=>$loc_type,'coordinates'=>$loc_coord,);
  }
  return($locations);
}