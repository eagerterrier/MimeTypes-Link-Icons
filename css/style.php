<?php header("Content-type: text/css");
$getvars = explode('&',base64_decode($_GET['cssvars']));
foreach($getvars as $k=>$v){
	$data = explode('=',$v);
	$$data[0]=$data[1];
}

echo ".mtli_attachment {  display:inline-block;  height:".$mtli_height."px;  background-position: top ".$mtli_leftorright."; background-attachment: scroll; background-repeat: no-repeat; padding-".$mtli_leftorright.": ".($mtli_height*1.2)."px; }";
$mtli_available_mime_types = array('ai','asf','bib','csv','deb','doc','docx','djvu','dmg','dwg','dwf','flac','gif','gz','indd','iso','jpg','log','m4v','midi','mkv','mov','mp3','mp4','mpeg','mpg','odp','ods','odt','oga','ogg','ogv','pdf','png','ppt','pptx','psd','ra','ram','rm','rpm','rv','skp','spx','tar','tex','tgz','txt','vob','wmv','xls','xlsx','xml','xpi','zip');
foreach($mtli_available_mime_types as $k=>$type){ 
	echo '.mtli_'.$type.' { background-image: url(../images/'.$type.'-icon-'.$mtli_height.'x'.$mtli_height.'.'.$mtli_image_type.'); }';
 } 
?>
