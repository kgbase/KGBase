<?php
$lit=get_lit($db);
$litnum=count($lit);
$locations=get_locations($db);
$allrefs=get_ref($db);
if($litsrc=='all'){
	if($litnum>0){
		print('<div class="text_as_header"><b>These publications are in the database:</b></div>');
		print('<div class="search_result">');
		foreach($lit as $ref=>$data){
			print('<div>'.$data['text']);
			if(isset($allrefs["$ref"])){
				print(' <span class="link_button"><b><a href="'.$site.'&litsrc='.$ref.'">see references</a></b></span>');
			}else{}
			if($data['file']!=='' && $data['file']!==null){
				print(' <span class="link_button"><b><a href="'.$data['file'].'" target="_blank">Link (in new window)</a></b></span>');
			}else{}
			print('</div>');
		}
		print('</div>');
	}else{
		print('<div class="text_as_header"><b>there is no publications in the database.</b></div>');
	}
}else{
	if(isset($allrefs["$litsrc"])){
		$refs=$allrefs["$litsrc"];
		$srcdata=$lit["$litsrc"];
		print('<div class="text_as_header"><b>The publication:<br>'.$srcdata['text'].'</b><br>');
		if($srcdata['file']!=='' && $srcdata['file']!==null){
				print('<span class="link_button"><b><a href="'.$srcdata['file'].'" target="_blank">Link (in new window)</a></b></span><br>');
		}else{}
		print('<b>Contains the references:</b></div>');
		print('<div class="search_result">');
		//load references
		foreach($refs as $name=>$items){
			print('<div><b><i>'.$name.'</i></b></div>');
			foreach($items as $loc=>$ldata){
				print('<div class="search_result">');
				print('<div><b>'.$locations["$loc"]['h'].'</b>, '.$locations["$loc"]['d'].' <span class="link_button"><a href="'.$site.'&baselocation='.$loc.'" target="_blank">detailed location data (in new window)</a></span></div>');
				print('<div>Page '.$ldata['page'].': "'.$ldata['text'].'", as <i>'.$ldata['citedas'].'</i></div>');
				print('</div>');
				print('<div><span class="link_button"><a href="'.$site.'&sect=all&txn='.$name.'" target="_blank"><b>complete data (in new window)</b></a></span></div>');
			}
		}
		print('</div>');
	}else{
		print('<div><b>There us no references for this publication.</b></div>');
	}
}
?>