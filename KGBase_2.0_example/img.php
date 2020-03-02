<?php
//script for the image ($img) loading from database ($ibase)
include('settings.php');
@$img=$_GET['img'];
@$txn=$_GET['txn'];
if(isset($txn)){
  $tx=str_replace(' ','_',$txn);
  $filename=$tx.'_'.$img;
}else{
  $filename=$img;
}
$idb = new SQLite3($ibase);$iq="SELECT * FROM img WHERE img.img='".$img."'";$iresult=$idb->query($iq);
while($ires = $iresult->fetchArray(SQLITE3_ASSOC)){$iname=$ires['img'];$content=$ires['src'];}

header('Content-type:image/jpeg;');
header('Content-Disposition: inline; filename="'.$filename.'"');
echo $content;