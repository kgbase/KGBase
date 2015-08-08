<?php
$page_title='KGBase by K.Grebennikov (2014) - Создание таксономического дерева';
include('template/top');
//default variables
$tree_dir = 'trees_teplates/';
$templ_folder = 'tax_template/';
$templ_index = 'templates.xml';
$serial = 'serial.xml';
$def=date('Y-M-d_h-i-s');
//include library for ods
include('ods-kg.php');
//get user set variables
@$act = $_GET['act'];
@$ID = $_GET['ID'];
@$write = $_POST['write'];
//tree selection
if(!isset($act)){
 print('<p>Создание таксономического дерева: Шаг №0: Выберите шаблон дерева:</p>');
 $templ = simplexml_load_file($templ_folder.$templ_index);
 $ttrees = $templ->ttrees;
 $description = $ttrees->description;
 print('<p><b>'.$description.':</b></p>');
 foreach($ttrees->ttree as $ttree){
  $tree_ID = $ttree->ID;
  $tree_description = $ttree->description;
  print('<p><a href="tax_build.php?act=cr&ID='.$tree_ID.'">'.$tree_description.'</a></p>');
 }
}else{}
//ranks selection
if(isset($act) && $act == "cr"){
 print('<p>Создание таксономического дерева: Шаг №1: Выберите таксоны Вашего будущего дерева:</p>');
 $templ = simplexml_load_file($templ_folder.$templ_index);
 $ttrees = $templ->ttrees;
 $folder = $ttrees->folder;
 $description = $ttrees->description;
 foreach($ttrees->ttree as $ttree){
  $tree_ID = $ttree->ID;
  if($tree_ID == $ID){
   $tree_desc = $ttree->description;
   print('<p><b>'.$tree_desc.':</b></p>');
   $tree_file = $ttree->file;
   $serial_file = simplexml_load_file($templ_folder.$serial);
   $ranks = simplexml_load_file($templ_folder.$folder.$tree_file);
   //text fields of form
   print('<form enctype="multipart/form-data" action="tax_build.php?act=wr&ID='.$ID.'" method="post">');
   print('Название дерева:<br>');
   print('<input name="nt_name" type="text" size="50" value="New Tree (time mask)" /><br>');
   print('');
   print('');
   print('Описание дерева:<br>');
   print('<input name="nt_desc" type="text" size="100" value="Description of new tree" /><br>');
   print('Автор дерева:<br>');
   print('<input name="nt_author" type="text" size="30" value="Author" /><br>');
   print('Ранги:<br>');
   //array with ranks from selected tree
   $rank_nodes = array();
   $rank_names = array();
   $rank_ps = array();
   $rank_item = $ranks->children(); $rank_name = $rank_item->getName();
   $rank_nodes[0] = $rank_name; $rank_nodes_n = 1;
   $rank_names[0] = $rank_item->$rank_name->rank; $rank_names_n = 1;
   $rank_ps[0] = $rank_item->$rank_name->pseudonym; $rank_ps_n = 1;
   while($rank_name !== 'rank'){
     $rank_item = $rank_item->children(); $rank_name = $rank_item->getName();
     $rank_nodes[$rank_nodes_n] = $rank_name; $rank_nodes_n = $rank_nodes_n+1;
     $rank_names[$rank_names_n] = $rank_item->$rank_name->rank; $rank_names_n = $rank_names_n+1;
     $rank_ps[$rank_ps_n] = $rank_item->$rank_name->pseudonym; $rank_ps_n = $rank_ps_n+1;
   }
   for($i_n=0;$i_n<($rank_nodes_n-1);$i_n++){
    print('<input name="ranks['.$rank_nodes[$i_n].'][rank]" type="checkbox" value="');
    print($rank_names[$i_n].'">'.$rank_names[$i_n].' ('.$rank_ps[$i_n].')<br>');
    print('<input name="ranks['.$rank_nodes[$i_n].'][pseudonym]" type="hidden" value="');
    print($rank_ps[$i_n].'">');
   }
   print('<input type="hidden" name="write" value="is">');
   print('<input type="submit" value="Создать шаблон">');
   print('</form>');
  }else{}
 }
}else{}
//write
if(isset($act) && $act == "wr"){
 if(isset($write) && $write == "is"){
  print('Шаблон готов:<br><br>');
  $serial_file = simplexml_load_file($templ_folder.$serial);
  @$post = $_POST;
  if($post['nt_name'] == 'New Tree (time mask)'){
   $tree_name = $def;
  }else{$tree_name = $post['nt_name'];}
  $tree = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><tree></tree>');
  //make empty tree
  foreach($post['ranks'] as $taxons=>$taxon){
   if(isset($taxon['rank'])){//if the taxon selected
    if(!isset($xtaxon)){//if there is first (root) rank
     $xtaxon = $taxon['rank'];
     $x_taxon = $x_taxon = $tree->addChild($xtaxon,'&#xA;');
     $child = $x_taxon->addChild('subtaxons','&#xA;');
     $serial_var = $serial_file->taxon->children();
     foreach($serial_var as $key=>$value){
     $value_child = $value->children();
     $child_name = $value_child->getName();
     if($child_name !== ""){
      $z_taxon = $x_taxon->addChild($key,'&#xA;');
      foreach($value as $xkey=>$xvalue){
       $z_taxon->addChild($xkey,'&#xA;');}
     }else{$x_taxon->addChild($key,'&#xA;');}
    }
    $tree->addChild('pseudonym',$taxon['pseudonym']);
    $tree->addChild('rank',$taxon['rank']);
    }else{//if there is one of child taxons
     $taxons = $child->addChild($taxons,'&#xA;');
     $x_taxon = $taxons->addChild($taxon['rank'],'&#xA;');
     $child = $x_taxon->addChild('subtaxons','&#xA;');
     //
     $serial_var = $serial_file->taxon->children();
     foreach($serial_var as $key=>$value){
     $value_child = $value->children();
     $child_name = $value_child->getName();
     if($child_name !== ""){
      $z_taxon = $x_taxon->addChild($key,'&#xA;');
      foreach($value as $xkey=>$xvalue){
       $z_taxon->addChild($xkey,'&#xA;');}
     }else{$x_taxon->addChild($key,'&#xA;');}
     }
    $taxons->addChild('pseudonym',$taxon['pseudonym']);
    $taxons->addChild('rank',$taxon['rank']);
    }
   
   
   //rank added
   //do nothing if the taxon is not selected
   }else{}
  }
  //add tree properties
  $tree->addChild('name',$tree_name);
  $tree->addChild('description',$post['nt_desc']);
  $tree->addChild('author',$post['nt_author']);
  //add class "content" to the tree
  $content = $tree->addChild('content','&#xA;');
  foreach($serial_var as $key=>$value){
   $value_child = $value->children();
   $child_name = $value_child->getName();
   if($child_name !== ""){
    $x_content = $content->addChild($key,'&#xA;');
    foreach($value as $xkey=>$xvalue){
     $x_content_text = $x_content->$xkey;
     $x_content->addChild($xkey,$xvalue);}
     }else{
     $content_text = $content->$key;
     $content->addChild($key,$value);
     }
  }
  //add tree to trees index
  $tree_index = simplexml_load_file($tree_dir.'trees.xml');
  $tree_add = $tree_index->addChild('tree','&#xA;');
  $tree_add->addChild('name',$tree_name);
  $tree_add->addChild('description',$post['nt_desc']);
  $tree_add->addChild('author',$post['nt_author']);
  $tree_index->asXML($tree_dir.'trees.xml');
  //write tree ods
  $tree_ods = newOds();
  //make "index" tab
  $tree_ods->addSheet('index');
  $tree_ods->addCell('index',1,1,'name','string');
  $tree_ods->addCell('index',1,2,$tree_name,'string');
  $tree_ods->addCell('index',2,1,'description','string');
  $tree_ods->addCell('index',2,2,$post['nt_desc'],'string');
  $tree_ods->addCell('index',3,1,'author','string');
  $tree_ods->addCell('index',3,2,$post['nt_author'],'string');
  $tree_ods->addCell('index',4,1,'rank','string');
  $tree_ods->addCell('index',5,1,'pseudonym','string');
  //make "content" tab
  $tree_ods->addSheet('content');
  $n_row = 1;
  foreach($content as $key=>$value){
   $value_child = $value->children();
   $child_name = $value_child->getName();
   if($child_name !== ""){
    $tree_ods->addSheet('content '.$key);$x_n_row = 1;
    foreach($value as $xkey=>$xvalue){
     $tree_ods->addCell('content '.$key,$x_n_row,1,$xkey,'string');
     $tree_ods->addCell('content '.$key,$x_n_row,2,$xvalue,'string');
     $x_n_row = $x_n_row+1;}
     }else{
     $tree_ods->addCell('content',$n_row,1,$key,'string');
     $tree_ods->addCell('content',$n_row,2,$value,'string');
     $n_row = $n_row+1;
     }
  }
  
  
  //make root taxon tab
  
  $root_tax = $tree->children();
  $root_name = $root_tax->getName();
  $tree_ods->addSheet($root_name);
  $root_tax_content = $root_tax->children();
  $rt_row=1;
  foreach($root_tax_content as $rkey=>$rvalue){
   if($rkey!=="subtaxons"){
   $rvalue_child = $rvalue->children();
   $rchild_name = $rvalue_child->getName();
    if($rchild_name!==""){/**/
     $tree_ods->addSheet($root_name.' '.$rkey);
     $tree_ods->addCell($root_name.' '.$rkey,1,1,$rchild_name,'string');
     $x_rt_row = 2;
     foreach($rvalue as $rxkey=>$rxvalue){
      $tree_ods->addCell($root_name.' '.$rkey,1,$x_rt_row,$rxkey,'string');
      $x_rt_row = $x_rt_row+1;
     }
    }else{
     $tree_ods->addCell($root_name,1,$rt_row,$rkey,'string');
     $rt_row = $rt_row+1;}
   }else{}
  }
  
  
  //make tabs
  $tree_ods->addSheet('ranks');
  $tree_ods->addCell('ranks',1,1,'taxons','string');
  $tree_ods->addCell('ranks',1,2,'taxon','string');
  $tree_ods->addCell('ranks',1,3,'pseudonym','string');
  $tree_item = $tree->children();
   $root_rank = $tree_item->rank;
   $tree_ods->addCell('ranks',2,1,'root','string');
   $tree_ods->addCell('ranks',2,2,$root_rank,'string');
   $tree_ods->addCell('index',4,2,$root_rank,'string');
   $root_pseudo = $tree_item->pseudonym;
   $tree_ods->addCell('ranks',2,1,'','string');
   $tree_ods->addCell('ranks',2,3,$root_pseudo,'string');
   $tree_ods->addCell('index',5,2,$root_pseudo,'string');
  $tree_item_count = $tree_item->count();
  $tree_item_name = $tree_item->getName();
  $n_rank_tab = 3;
  while($tree_item_count>0){
   $tree_item = $tree_item->children();
   $tree_item_count = $tree_item->count();
   $tree_item_name = $tree_item->getName();
   if($tree_item_name == 'subtaxons' && $tree_item_count>0){
    $subtaxons = $tree_item->children();
    $st_name = $subtaxons->getName();
    $tree_ods->addCell('ranks',$n_rank_tab,1,$st_name,'string');
    $rank = $subtaxons->$st_name->rank;
    $tree_ods->addCell('ranks',$n_rank_tab,2,$rank,'string');
    $pseudonym = $subtaxons->$st_name->pseudonym;
    $tree_ods->addCell('ranks',$n_rank_tab,3,$pseudonym,'string');
    $n_rank_tab = $n_rank_tab+1;
    if(($subtaxons->count())>0){
    $subtaxon = $subtaxons->children();
    $sstaxon = $subtaxon->children();
     $tree_ods->addSheet($st_name);
     $nt_row = 1;
     foreach($sstaxon as $key=>$value){
      $value_child = $value->children();
      $child_name = $value_child->getName();
      if($child_name!=="" && $key!=="subtaxons"){
       $tree_ods->addSheet($st_name.' '.$key);
       $tree_ods->addCell($st_name.' '.$key,1,1,$rank,'string');
       $x_nt_row = 2;
       foreach($value as $xkey=>$xvalue){
        $tree_ods->addCell($st_name.' '.$key,1,$x_nt_row,$xkey,'string');
        $x_nt_row = $x_nt_row+1;
       }
      }else{
       if($nt_row == 1){
        $tree_ods->addCell($st_name,1,1,'p_rank','string');
        $tree_ods->addCell($st_name,1,2,'parent','string');
        $nt_row = 3;
       }else{
       $tree_ods->addCell($st_name,1,$nt_row,$key,'string');
       $nt_row = $nt_row+1;}
      }
     }
    }else{}
   }else{}
  }
  
  //write ods
  saveOdsZip($tree_ods,$tree_dir,$tree_name);
  print('<a href="'.$tree_dir.$tree_name.'.ods" target="_blank">Шаблон дерева '.$tree_name.' в формате Open Document Spreadsheet (.ods)</a><br>');
 }else{
  print('Формат шаблона не задан :( <a href="tax_build.php">Начать сначала</a><br><br>');
 }
}else{}
//-------------------------------------------------------------------------------------------------------------
//load end of the page
print('</table>
</body>
</html>');
?>
