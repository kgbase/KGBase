<?php
//base link for scripts
$site='http(s)://your_site/some_dir/index.php?mode=inc';
//absolute path to installation
$rootsite='http(s)://your_site/some_dir';
//main database with the data
$base='ants_lv_en.db';
//database with images
$ibase='img/img.db';
//title and subtitle of the database
$base_title='KGBase';
$subtitle="Ants of Lower Volga";
//order of taxonomic ranks (descendant): rank - pseudonym (other if the language of the interface is not English)
$r_order=array('domain'=>'domain',
               'kingdom'=>'kingdom',
               'phylum'=>'phylum',
               'class'=>'class',
               'order'=>'order',
               'suborder'=>'suborder',
               'superfamily'=>'superfamily',
               'family'=>'family',
               'subfamily'=>'subfamily',
               'tribe'=>'tribe',
               'subtribe'=>'subtribe',
               'genus'=>'genus',
               'species'=>'species',
               'subspecies'=>'subspecies',);
//destination of folders with files (images, books ect.) - DEPRECATED
$fdest=array('img'=>'img/','lit'=>'lit/','obs'=>'obs/','maps'=>'map/blank/');
//styles for taxon's data printing: rank - section(db or output array field) - parts of the string ('item'==data)
$styles=array();
 $styles['subspecies']=array();
  $styles['subspecies']['name']=array('<i><font color="grey">','item','</font></i> ');
  $styles['subspecies']['autor']=array('item','.');
 $styles['species']=array();
  $styles['species']['name']=array('<b><i>','item','</b></i> ');
  $styles['species']['autor']=array('item');
  $styles['species']['syn']=array(' (=','item',')');
  $styles['species']['pseudonym']=array(' (','item',')');
  $styles['species']['description']=array('. ','item');
 $styles['tribe']=array();
  $styles['tribe']['name']=array('<b><i><font color="green">','item','</font></i></b> ');
  $styles['tribe']['autor']=array('<b>','item','</b>.');
  $styles['tribe']['pseudonym']=array(' (','item',')');
 $styles['subfamily']=array();
  $styles['subfamily']['name']=array('<b><i><font color="green">','item','</font></i></b> ');
  $styles['subfamily']['autor']=array('<b>','item','</b>.');
  $styles['subfamily']['pseudonym']=array(' (','item',')');
 $styles['family']=array();
  $styles['family']['name']=array('<b><i><font color="green">Family ','item','</font></i></b> ');
  $styles['family']['autor']=array('<b>','item','</b>.');
  $styles['family']['pseudonym']=array(' (','item',')');
 $styles['superfamily']=array();
  $styles['superfamily']['name']=array('<b><i><font color="green">Superfamily ','item','</font></i></b> ');
  $styles['superfamily']['autor']=array('<b>','item','</b>.');
  $styles['superfamily']['pseudonym']=array(' (','item',')');
 $styles['suborder']=array();
  $styles['suborder']['name']=array('<b><i><font color="green">Suborder ','item','</font></i></b> ');
  $styles['suborder']['autor']=array('<b>','item','</b>.');
  $styles['suborder']['pseudonym']=array(' (','item',')');
 $styles['order']=array();
  $styles['order']['name']=array('<b><i><font color="green">Order ','item','</font></i></b> ');
  $styles['order']['autor']=array('<b>','item','</b>.');
  $styles['order']['pseudonym']=array(' (','item',')');
 $styles['subclass']=array();
  $styles['subclass']['name']=array('<b><i><font color="green">Subclass ','item','</font></i></b> ');
  $styles['subclass']['autor']=array('<b>','item','</b>.');
  $styles['subclass']['pseudonym']=array(' (','item',')');
 $styles['class']=array();
  $styles['class']['name']=array('<b><i><font color="green">Class ','item','</font></i></b> ');
  $styles['class']['autor']=array('<b>','item','</b>.');
  $styles['class']['pseudonym']=array(' (','item',')');
 $styles['phylum']=array();
  $styles['phylum']['name']=array('<b><font color="red">','item','</font></b> ');
  $styles['phylum']['pseudonym']=array(' (','item',')');
 $styles['kingdom']=array();
  $styles['kingdom']['name']=array('<b><font size="6">','item','</font></b> ');
  $styles['kingdom']['pseudonym']=array(' <font size="6">(','item',')</font>');
//subscription of parts of the specimens - as they will be displayed on the pages
$cpecimen_parts=array('male'=>'&#x2642;','female'=>'&#x2640;','larva'=>'&#x26aa;','worker'=>'&#x263f;','hermaphrodite'=>'&#x26a5;',);
//database (sqlite), one folder with files (file name is the reference), folder with multiple folder (folder name is the reference)  - DEPRECATED
$img_storage='sqlite';
?>