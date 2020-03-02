<?php
if(isset($sect) && $sect!=='all'){
 $sdata=get_section_desc($db,$sect);
 print('<div class="treetitle">'.$sdata['title'].' ('.$sdata['description'].'): <b>'.$txn.'</b></div>');
 $tree_description=' - '.$bases["$sect"]['title'];
}else{$tree_description='';}
//
print(' <div class="geodata">');
$taxon_data=array();
$allt=taxdata_exists($db);
if(isset($allt['specimen']["$txn"])){$taxon_data['coll']=get_taxdata($db,$txn);}else{}
if(isset($allt['reference']["$txn"])){$taxon_data['ref']=get_taxdata_refs($db,$txn);}else{}
if(isset($allt['observation']["$txn"])){$taxon_data['obs']=get_taxdata_obs($db,$txn);}else{}
print('<div>Download geodata for <i>'.$txn.'</i>:<div>');
//links to geodata
//kml
print('<div><a href="'.$rootsite.'kml.php?taxon='.$txn.'" target="_blank">As Keyhole Markup Language (.kml) file.</a></div>');
//geojson
print('<div> As GeoJSON file: <a href="'.$rootsite.'geojson.php?taxon='.$txn.'&geometry=exact" target="_blank">with exact locations geometry</a> or <a href="'.$rootsite.'geojson.php?taxon='.$txn.'&geometry=centroid" target="_blank">with locations, approximated to points</a>. For a description of the structure of these files, <a href="index.php#geojson">see front page</a>.</div>');
print('<div>');
//make interactive map

$leaflet_markers=construct_leaflet_markers($taxon_data,$txn,$site);
print('<style>
      #map { width: 500px; height: 600px; border: 2px solid rgb(42, 86, 29); }
      #basemaps-wrapper { border: 1px solid rgb(42, 86, 29); width: 500px; text-align: center; background: #6c806d; }
      #basemaps { margin-bottom: 2px; }
</style>
<script></script>
<div>Interactive map of records of <i>'.$txn.'</i> in the Lower Volga Region:</div>
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
<div id="map"></div>');
print("<script>
 var map = L.map('map').setView({lon: 46, lat: 48}, 6);
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
    });");
print($leaflet_markers.'</script>
<div class="l_container"><span class="map_legend">black dots - literature references, green dots - specimens in collection(s), yellow dots - observations</span></div>');
$taxon=get_taxon($db,$txn);
print_taxon_header($taxon);
print_taxon_data($taxon_data,$txn,$img_storage,$fdest,$cpecimen_parts,$site,$ibase);
?>