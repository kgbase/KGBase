<?php
/*
PHP Class for drawing maps on the jpeg map image from array of points, lines and polygons.
© 2014 K.A. Grebennikov, State Reserve Bogdinsko-Baskunchakskiy
© Erhan Baris - some geodedic functions
Free software, license - GNU GPL v.3
Notes:
Map image in Mercator elliptical projection supported
Geodata is an array of coordinates of KML placemarks (points, lines and polygons).
*/
class GeoSpace{
 //
 public $gsp_src;//path to map image
 public $gsp_map;//path to map file (*.kml with coordinates)
 public $gsp_src_wd;//width of map image
 public $gsp_src_h;//hight of map image
 public $gsp_src_n;//northern limit of the map
 public $gsp_src_s;//southern limit of the map
 public $gsp_src_w;//western limit of the map
 public $gsp_src_e;//eastern limit of the map
 public $geodata;//array with placemarks
 //function returns physical size of the map ($gsp_src) in pixels
 //and geodetic limits from *kml file ($gsp_map)
 public function gsp_getsize($gsp_src,$gsp_map){
  $size=getimagesize($gsp_src);
  $this->gsp_src_wd=$size[0];
  $this->gsp_src_h=$size[1];
  $kml = simplexml_load_file($gsp_map);
  $GroundOverlay = $kml->GroundOverlay;
  $LatLonBox = $GroundOverlay->LatLonBox;
  $north = $LatLonBox->north;$this->gsp_src_n=trim($north);
  $south = $LatLonBox->south;$this->gsp_src_s=trim($south);
  $west = $LatLonBox->west;$this->gsp_src_w=trim($west);
  $east = $LatLonBox->east;$this->gsp_src_e=trim($east);
 }
 /*function drew map on the basic map image
 $name Map's name - written in top left corner of the name
 $font Font of map name (link to ttf font file)
 $fsize Font size
 $gsp_src Path to map image
 $geodata KML placemarks 
    (array: 'name'=> 'type'=>type (Point,Linestring,Polygon), 'coordinates'=>e.g. 10,10,0 11,11.1, 0 ...)
 $dest Destination folder for new map
 $map_name File name of new map
 $pt_size Size of points on the map
 $thick Thickness of lines on the map
 */
 public function gsp_drowmap($name,$font,$fsize,$gsp_src,$geodata,$dest,$map_name,$pt_size,$thick){
  $img=imagecreatefromjpeg($gsp_src);
  $color_fill=imagecolorallocatealpha($img, 255, 0, 0, 0);
  $color_line=imagecolorallocate($img, 255, 0, 0);
  $color_out=imagecolorallocate($img, 0, 0, 0);
  $color_text=imagecolorallocatealpha($img, 128,128,0, 50);
  imagesetthickness($img,$thick);
  //get limits of the map and size of map image
  $gsp_src_wd = $this->gsp_src_wd;
  $gsp_src_h = $this->gsp_src_h;
  $nlim = $this->gsp_src_n;
  $slim = $this->gsp_src_s;
  $wlim = $this->gsp_src_w;
  $elim = $this->gsp_src_e;
  imagefttext($img,$fsize,0,10,($fsize+10),$color_text,$font,$name);
 //-----------------------------------------
 //Php Code by Erhan Baris 19:19, 01.09.2007
 //http://wiki.openstreetmap.org/wiki/Mercator#Elliptical_Mercator
  function merc_x($lon){
   $r_major = 6378137.000;
   return $r_major * deg2rad($lon);
  }
  function merc_y($lat){
   if ($lat > 89.5) $lat = 89.5;
   if ($lat < -89.5) $lat = -89.5;
   $r_major = 6378137.000;
   $r_minor = 6356752.3142;
   $temp = $r_minor / $r_major;
   $es = 1.0 - ($temp * $temp);
   $eccent = sqrt($es);
   $phi = deg2rad($lat);
   $sinphi = sin($phi);
   $con = $eccent * $sinphi;
   $com = 0.5 * $eccent;
   $con = pow((1.0-$con)/(1.0+$con), $com);
   $ts = tan(0.5 * ((M_PI*0.5) - $phi))/$con;
   $y = - $r_major * log($ts);
   return $y;
  }
  function merc($x,$y){
   return array('x'=>merc_x($x),'y'=>merc_y($y));
  }
  //---------------------------------------------------
  //recalculate Mercator's meters in pixels on the image
  $merc_nw=merc($wlim,$nlim);
  $merc_se=merc($elim,$slim);
  $merc_mpp_h=($merc_nw['y']-$merc_se['y'])/$gsp_src_h;
  $merc_mpp_wd=($merc_se['x']-$merc_nw['x'])/$gsp_src_wd;
  //draw placemarks
  foreach($geodata as $no_pl=>$pl){
   //if the placemark is point
   if($pl['type']=='Point'){
    $coord=explode(',',$pl['coordinates']);
    $pl_x=$coord[0];
    $pl_y=$coord[1];
    if($pl_x>$wlim && $pl_x<$elim && $pl_y>$slim && $pl_y<$nlim){
     $pl_merc=merc($pl_x,$pl_y);
     $pl_map_x=($pl_merc['x']-$merc_nw['x'])/$merc_mpp_wd;
     $pl_map_y=($merc_nw['y']-$pl_merc['y'])/$merc_mpp_h;
     imagefilledellipse($img,$pl_map_x,$pl_map_y,$pt_size,$pt_size,$color_fill);
     imageellipse($img,$pl_map_x,$pl_map_y,$pt_size,$pt_size,$color_out);
    }else{print('Sorry, point "'.$no_pl.'" is out of the map :(<br>');}
   }else{}
   //if the placemark is line
   if($pl['type']=='LineString'){
    $pl_points=explode(' ',$pl['coordinates']);
    $pl_limits=array(
     "xmin"=>0,
     "xmax"=>0,
     "ymin"=>0,
     "ymax"=>0, 
     );
    $points=array();
    foreach($pl_points as $no_pt=>$pt){
     $pt_coord=explode(',',$pt);
     $pt_x=$pt_coord[0];
     $pt_y=$pt_coord[1];
     $points[$no_pt]=array();
     $points[$no_pt]['x']=$pt_x;
     $points[$no_pt]['y']=$pt_y;
     if($no_pt==0){
      $pl_limits["xmin"]=$pt_x;
      $pl_limits["xmax"]=$pt_x;
      $pl_limits["ymin"]=$pt_y;
      $pl_limits["ymax"]=$pt_y;
     }else{
      if($pt_x<$pl_limits["xmin"]){$pl_limits["xmin"]=$pt_x;}else{}
      if($pt_x>$pl_limits["xmax"]){$pl_limits["xmax"]=$pt_x;}else{}
      if($pt_y<$pl_limits["ymin"]){$pl_limits["ymin"]=$pt_y;}else{}
      if($pt_y>$pl_limits["ymax"]){$pl_limits["ymax"]=$pt_y;}else{}
     }
    }
    $merc_min=merc($pl_limits["xmin"],$pl_limits["ymin"]);
    $merc_max=merc($pl_limits["xmax"],$pl_limits["ymax"]);
    $delta_x=($merc_max['x']-$merc_min['x'])/$merc_mpp_wd;
    $delta_y=($merc_max['y']-$merc_min['y'])/$merc_mpp_h;
    if($delta_x<$pt_size && $delta_y<$pt_size){
     //draw point if line size lesser than pointer limits
     $pl_map_x=($merc_min['x']-$merc_nw['x'])/$merc_mpp_wd;
     $pl_map_y=($merc_nw['y']-$merc_min['y'])/$merc_mpp_h;
     imagefilledellipse($img,$pl_map_x,$pl_map_y,$pt_size,$pt_size,$color_fill);
     imageellipse($img,$pl_map_x,$pl_map_y,$pt_size,$pt_size,$color_out);
    }else{
     //draw line if it large than pointer limits
     $merc_line_start=merc($points[0]['x'],$points[0]['y']);
     $points_qw=count($points);
     for($i=1;$i<$points_qw;$i++){
      $merc_line_next=merc($points[$i]['x'],$points[$i]['y']);
      $x1=($merc_line_start['x']-$merc_nw['x'])/$merc_mpp_wd;
      $x2=($merc_line_next['x']-$merc_nw['x'])/$merc_mpp_wd;
      $y1=($merc_nw['y']-$merc_line_start['y'])/$merc_mpp_h;
      $y2=($merc_nw['y']-$merc_line_next['y'])/$merc_mpp_h;
      imageline($img,$x1,$y1,$x2,$y2,$color_line);
      $merc_line_start=$merc_line_next;
     }
    }
   }else{}
   //if the placemark is polygon
   if($pl['type']=='Polygon'){
    $pl_points=explode(' ',$pl['coordinates']);
    $pl_limits=array(
     "xmin"=>0,
     "xmax"=>0,
     "ymin"=>0,
     "ymax"=>0, 
     );
    $points=array();
    foreach($pl_points as $no_pt=>$pt){
     $pt_coord=explode(',',$pt);
     $pt_x=$pt_coord[0];
     $pt_y=$pt_coord[1];
     $points[$no_pt]=array();
     $points[$no_pt]['x']=$pt_x;
     $points[$no_pt]['y']=$pt_y;
     if($no_pt==0){
      $pl_limits["xmin"]=$pt_x;
      $pl_limits["xmax"]=$pt_x;
      $pl_limits["ymin"]=$pt_y;
      $pl_limits["ymax"]=$pt_y;
     }else{
      if($pt_x<$pl_limits["xmin"]){$pl_limits["xmin"]=$pt_x;}else{}
      if($pt_x>$pl_limits["xmax"]){$pl_limits["xmax"]=$pt_x;}else{}
      if($pt_y<$pl_limits["ymin"]){$pl_limits["ymin"]=$pt_y;}else{}
      if($pt_y>$pl_limits["ymax"]){$pl_limits["ymax"]=$pt_y;}else{}
     }
    }
    $merc_min=merc($pl_limits["xmin"],$pl_limits["ymin"]);
    $merc_max=merc($pl_limits["xmax"],$pl_limits["ymax"]);
    $delta_x=($merc_max['x']-$merc_min['x'])/$merc_mpp_wd;
    $delta_y=($merc_max['y']-$merc_min['y'])/$merc_mpp_h;
    if($delta_x<$pt_size && $delta_y<$pt_size){
     //draw point if polygon size lesser than pointer limits
     $pl_map_x=($merc_min['x']-$merc_nw['x'])/$merc_mpp_wd;
     $pl_map_y=($merc_nw['y']-$merc_min['y'])/$merc_mpp_h;
     imagefilledellipse($img,$pl_map_x,$pl_map_y,$pt_size,$pt_size,$color_fill);
     imageellipse($img,$pl_map_x,$pl_map_y,$pt_size,$pt_size,$color_out);
    }else{
     //draw polygon if it large than pointer limits
     //make array with nodes of the polygon
     $nodes=array();$nnode=0;
     foreach($points as $point_no=>$point){
      $merc_point=merc($point['x'],$point['y']);
      $merc_point_x=($merc_point['x']-$merc_nw['x'])/$merc_mpp_wd;
      $merc_point_y=($merc_nw['y']-$merc_point['y'])/$merc_mpp_h;
      $nodes[$nnode]=$merc_point_x;$nnode=$nnode+1;
      $nodes[$nnode]=$merc_point_y;$nnode=$nnode+1;
     }
     $nodes_qw=count($nodes)/2;
     imagepolygon($img,$nodes,$nodes_qw,$color_line);
    }
   }else{}
  }
  if(file_exists($dest.$map_name)){unlink($dest.$map_name);}
  imagejpeg($img,$dest.$map_name);
 }
}
?>