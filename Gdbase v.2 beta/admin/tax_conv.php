<?php
session_start();
//
$page_title='KGBase-GDBase by K.Grebennikov (v.2 2015) - преобразование таксономического дерева из OpenOffice Spreadsheet';
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
 if($c_user['role']=='admin'){
  //authorised user's interface and functions


  include('template/top');
  //default variables
  $docdir = '../trees/ods/';
  $xmldir = '../trees/';
  @$ods = $_FILES['ods'];
  //print form if ods is not loaded
  if(!isset($ods)){
   print('<p align="justify">Для запуска преобразования выберите файл формата OpenOffice Spreadsheet, созданный ранее ("Создание таксономического дерева"), с заполненными таксономическими списками<br>Внимание! Если в список с таким названием уже есть, он будет заменен результатом преобразования</p>');
   print('<form enctype="multipart/form-data" action="tax_conv.php" method="post">
    Выберите ods файл с таксономией:<br>
    <input type="file" min="1" max="9999" name="ods" /><br>
    <input type="submit" name="submit" value="ЗАПУСК" /><br>
   </form>');
  
  //parse ods if it is loaded
  }else{
   //uploading
   $f = $ods[name];$ods_temp = $ods[tmp_name];
   $fdoc = $docdir.$f;
   move_uploaded_file($ods_temp,$fdoc);
   $dirt = $fdoc.'.temp/';
   @mkdir($dirt);
   //unzip
   $zip = new ZipArchive;
   $zip->open($fdoc);
   $zip->extractTo($dirt);
   $zip->close();
   //get content
   $fc = file($dirt.'content.xml');
   $fc_str = implode('',$fc);
   $fc_str_r1 = str_replace(':','__',$fc_str);
   $fc_str_r2 = str_replace('table-','table',$fc_str_r1);
   $content = simplexml_load_string($fc_str_r2);
   $body = $content->office__body;
   $sheet = $body->office__spreadsheet;
   $doc = array();
   foreach($sheet->table__table as $table){
    $tablename = $table['table__name'];
    $doc["$tablename"]=array();
    $nrow=0;
    foreach($table->table__tablerow as $row){
     $doc["$tablename"][$nrow]=array();
     $ncell=0;
     foreach($row->table__tablecell as $cell){
      $text = $cell->text__p;
      $text=trim($text);
      $text=str_replace('&','&amp;',$text);
      $text=str_replace('__',':',$text);
      $doc["$tablename"][$nrow][$ncell] = $text;
      $ncell=$ncell+1;
      $rep = $cell['table__number-columns-repeated'];
      if($rep>0 && $rep<5){
       for($ir=0;$ir<($rep-1);$ir++){
        $doc["$tablename"][$nrow][$ncell] = $text;
        $ncell=$ncell+1;
       }
      }else{}
     } 
     $nrow=$nrow+1;
    }
   }
   //parse taxonomy
   //add new tree to the index
   $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><tree></tree>');
   //make base tree and ranks array
   $ranks=array();
   foreach($doc['ranks'] as $num=>$str){
    $pstr[1]=trim($str[1]);
    if($num==1){
     $taxon=$str[1];
     $element = $xml->addChild($taxon,'&#xA;');
     $element->addChild('subtaxons','&#xA;');
     $ranks[0]['taxons']=$str[0];
     $ranks[0]['taxon']=$str[1];
     $ranks[0]['pseudonym']=$str[2];
    }else{}
    //
    if($num>1 && $str[0]!==''){
     $ranks[($num-1)]['taxons']=$str[0];
     $ranks[($num-1)]['taxon']=$str[1];
     $ranks[($num-1)]['pseudonym']=$str[2];
    }else{}
   }
   //add index to tree
   $doc['index']=array_reverse($doc['index']);
   foreach($doc['index'] as $num=>$str){
    $xml->addChild($str[0],$str[1]);
   }
   //add content items to tree and make content array
   $contents=array();
   $content_tree = $xml->addChild('content','&#xA;');
   foreach($doc['content'] as $num=>$str){
    $content_tree->addChild($str[0],$str[1]);
    $contents['content'][$num]=$str[0];
   }
   //add children of content to tree and to content array
   foreach($doc as $tab_name=>$tab){
    $is_content=substr_count($tab_name,'content ');
    if($is_content==1){
     $child_name=str_replace('content ','',$tab_name);
     $content_child = $content_tree->addChild($child_name,'&#xA;');
     foreach($tab as $num=>$str){
      $content_child->addChild($str[0],$str[1]);
      $contents[$child_name][$num]=$str[0];
     }
    }else{}
   }
   //add to tree content of root taxon
   $root_taxon = $xml->children();
   $root_rank=$ranks[0]['taxon'];
   foreach($doc["$root_rank"] as $num=>$str){
    foreach($contents as $part=>$list){
     if($part=='content'){
      foreach($list as $list_num=>$val){
       $str[$list_num]=trim($str[$list_num]);
       if($num>0 && $str[$list_num]!==''){
        $root_taxon->addChild($val,$str[$list_num]);
       }else{}
      }
     }else{
      $doc["$root_rank $part"][1][1]=trim($doc["$root_rank $part"][1][1]);
      if($doc["$root_rank $part"][1][1]!==''){
       $root_taxon_add = $root_taxon->addChild($part,'&#xA;');
       foreach($doc["$root_rank $part"] as $num=>$str){
        foreach($list as $list_num=>$val){
         $str[$list_num]=trim($str[$list_num]);
         if($num>0 && $str[$list_num]!==''){
          $root_taxon_add->addChild($val,$str[$list_num]);
         }else{}
        }
       }
      }else{}
     }
    }
   }
   //add to tree content of taxons
   foreach($ranks as $level=>$rank){
    if($level!==0){
    $rank_taxons=$rank['taxons'];
    foreach($doc["$rank_taxons"] as $num=>$str){
     $nstr=count($str);
     for($i=0;$i<$nstr;$i++){
      $str[$i]=str_replace('&','&amp;',$str[$i]);//trim each value in cells of the row
      $str[$i]=trim($str[$i]);
     }
     //replace & by entity
     foreach($contents as $cont_type=>$item){
      if($cont_type=='content' && $str[1]!=='parent'){
       @$rtaxon_ar = $xml->xpath("//$str[0][name='$str[1]']");
       $rtaxon=$rtaxon_ar[0];
       if($rtaxon->subtaxons){
        $stx = $rtaxon->subtaxons;
        if($stx->$rank['taxons']){
         $stx_txs = $stx->$rank['taxons'];
         $ctax = $stx_txs->addChild($rank['taxon'],'&#xA;');
         $ctax->addChild('subtaxons','&#xA;');
        }else{
         $stx_txs = $stx->addChild($rank['taxons'],'&#xA;');
         $ctax = $stx_txs->addChild($rank['taxon'],'&#xA;');
         $ctax->addChild('subtaxons','&#xA;');
        }
       }else{}
       foreach($item as $key=>$val){
        if($str[($key+2)]!=='' && $str[($key+2)]!==null){
         $ctax->addChild($val,$str[($key+2)]);
        }else{}
       }
      }else{}
     }
    }
   }else{}
  }
  //add synonyms and may be some others
  foreach($ranks as $level=>$rank){
  if($level!==0){
  $rank_taxons=$rank['taxons'];
   foreach($contents as $cont_type=>$item){
   if($cont_type!=='content'){
    $rank_taxons_add=$rank_taxons.' '.$cont_type;
    $empt_req=trim($doc["$rank_taxons_add"][1][1]);
    if($empt_req!=="" && $empt_req!==null){//if sheet is not empty
     foreach($doc["$rank_taxons_add"] as $num=>$str){
      $nstr=count($str);
      for($i=0;$i<$nstr;$i++){$str[$i]=str_replace('&','&amp;',$str[$i]);//trim each value in cells of the row
       $str[$i]=trim($str[$i]);}//replace & by entity
       if($str[1]!=='name' && $str[1]!==null){
        $where=trim($rank['taxon']);
        $scht_ar = $xml->xpath("..//$where");
        foreach($scht_ar as $scht){
         $scht_name=$scht->name;
         if($scht_name==$str[0]){
          $cont_dir = $scht->addChild($cont_type,'&#xA;');
          foreach($item as $key=>$val){
           if($str[($key+1)]!=='' && $str[($key+1)]!==null){
            $cont_dir->addChild($val,$str[($key+1)]);
           }else{}
          }
        }else{}
       }
      }else{}
     }
    }else{}
   }else{}
  }
 }else{}
 }
  //add pseudonyms to the groups of taxons
  foreach($ranks as $level=>$rank){
   if($level!==0){
    $sitem=trim($rank['taxons']);
    $targets = $xml->xpath("..//$sitem");
    foreach($targets as $target){
     $target->addChild('pseudonym',$rank['pseudonym']);
     $target->addChild('rank',$rank['taxon']);
    }
   }else{}
  }
  //add ranks schema to tree
  $r_tree = $xml->addChild('ranks','&#xA;');
  foreach($ranks as $level=>$rank){
   $r_node = $r_tree->addChild('rank','&#xA;');
   $r_node->addChild('taxons',$rank['taxons']);
   $r_node->addChild('taxon',$rank['taxon']);
   $r_node->addChild('pseudonym',$rank['pseudonym']);
  }
  $xml_res = $xmldir.$f.'.xml';
  $xml->asXML($xml_res);
  //add new tree to the index
  $trees_index = simplexml_load_file($xmldir.'index.xml');
  $new_tree = $trees_index->addChild('tree','&#xA;');
  $new_tree->addChild('file',$f.'.xml');
  $new_tree_desk = $xml->description;
  $new_tree->addChild('description',$new_tree_desk);
  $trees_index->asXML($xmldir.'index.xml');
  //
  print('<p align="justify">OK! Дерево преобразовано и добавлено к списку<br> Результат преобразования в xml тут: <a href="'.$xml_res.'">'.$f.'.xml</a></p>');
  //delete temp files and directories of ods
  unlink($dirt.'META-INF/manifest.xml');
  rmdir($dirt.'/META-INF/');
  unlink($dirt.'/Configurations2/accelerator/current.xml');
  rmdir($dirt.'/Configurations2/accelerator/');
  rmdir($dirt.'/Configurations2/images/Bitmaps/');
  rmdir($dirt.'/Configurations2/images/');
  rmdir($dirt.'/Configurations2/popupmenu/');
  rmdir($dirt.'/Configurations2/statusbar/');
  rmdir($dirt.'/Configurations2/floater/');
  rmdir($dirt.'/Configurations2/progressbar/');
  rmdir($dirt.'/Configurations2/toolbar/');
  rmdir($dirt.'/Configurations2/menubar/');
  rmdir($dirt.'/Configurations2/toolpanel/');
  rmdir($dirt.'/Configurations2/');
  unlink($dirt.'/content.xml');
  unlink($dirt.'/mimetype');
  unlink($dirt.'/meta.xml');
  unlink($dirt.'/styles.xml');
  unlink($dirt.'/settings.xml');
  unlink($dirt.'/manifest.rdf');
  unlink($dirt.'/Thumbnails/thumbnail.png');
  rmdir($dirt.'/Thumbnails/');
  rmdir($dirt.'/');
 }
 //load end of the page
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