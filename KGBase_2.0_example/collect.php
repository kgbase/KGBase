<?php
//load data about collections
$coll = get_collection_data($db,$collect);
print('<div><b>'.$collect.': '.$coll['description'].'<br>Владелец:</b> '.$coll['owner'].'</b><br><b>Куратор:</b> '.$coll['curator'].'</b><br><b>Контактная информация:</b> '.$coll['contact'].'</div>');
?>