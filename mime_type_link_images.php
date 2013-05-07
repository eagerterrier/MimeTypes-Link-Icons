<?php
/**
 * @package MimeTypeLinkImages
 * @version 2.2.3
 */
/*
Plugin Name: Mime Type Link Images
Plugin URI: http://blog.eagerterrier.co.uk/2010/10/holy-cow-ive-gone-and-made-a-mime-type-wordpress-plugin/
Description: This will add file type icons next to links automatically. Change options in the <a href="options-general.php?page=mime_type_link_images.php">settings page</a>
Author: Toby Cox
Version: 2.2.3
Author URI: http://eagerterrier.co.uk
Contributor: Keith Parker
Contributor URI: http://infas.net
Contributor: Juliette Reinders Folmer
Contributor URI: http://adviesenzo.nl
*/


// constants
define('mtli_version', '2.2.3', true);

$mtli_options = get_option('mimetype_link_icon_options'); 

global $mtli_available_sizes;
global $mtli_available_image_types;
global $mtli_available_mime_types;
global $add_attachment_style;
global $mtli_css;
global $fileSizeStyles;

$mtli_available_sizes = array(16,24,48,64,128);
$mtli_available_image_types = array('gif','png');
$mtli_available_mime_types = array('ai','asf','bib','csv','deb','doc','docx','djvu','dmg','dwg','dwf','epub','flac','gif','gz','indd','iso','jpg','log','m4v','midi','mkv','mov','mp3','mp4','mpeg','mpg','odp','ods','odt','oga','ogg','ogv','pdf','png','ppt','pptx','psd','ra','ram','rm','rpm','rv','skp','spx','tar','tex','tgz','txt','vob','wmv','xls','xlsx','xml','xpi','zip');
$mtli_default_true = array('pdf');

function mtli_set_option($option_name, $option_value) {
	// first get the existing options in the database
	$mtli_options = get_option('mimetype_link_icon_options');
	// set the value
	$mtli_options[$option_name] = $option_value;
	// write the new options to the database
	update_option('mimetype_link_icon_options', $mtli_options);
}


// get an Ultimate GA option from the WordPress options database table
// if the option does not exist (yet), the default value is returned
function mtli_get_option($option_name) {

  // get options from the database
  $mtli_options = get_option('mimetype_link_icon_options'); 

  
  if (!$mtli_options || !array_key_exists($option_name, $mtli_options)) {
    // no options in database yet, or not this specific option 
    // create default options array

    $mtli_default_options=array();
    $mtli_default_options['internal_domains']  = $_SERVER['SERVER_NAME'];
    if (preg_match('@www\.(.*)@i', $mtli_default_options['internal_domains'], $parts)>=1) {
      $mtli_default_options['internal_domains'] .= ','.$parts[1];
    }
    $mtli_default_options['image_size']             = '16';  
    $mtli_default_options['image_type']     	    = 'png'; 
    $mtli_default_options['leftorright']     	    = 'left'; 
    $mtli_default_options['show_file_size']         = false;

    global $mtli_available_mime_types, $mtli_default_true;
    foreach( $mtli_available_mime_types as $type ) {
		$mtli_default_options['enable_' . $type]	= ( in_array( $type, $mtli_default_true ) === false ? false : true );
	}

    $mtli_default_options['enable_async']     	    = false;  
    $mtli_default_options['enable_hidden_class']    = true;  
    $mtli_default_options['hidden_classname'] 		= 'wp-caption';  

    // add default options to the database (if options already exist, 
    // add_option does nothing
    add_option('mimetype_link_icon_options', $mtli_default_options, 
               'Settings for MimeType Link Icon plugin');

    // return default option if option is not in the array in the database
    // this can happen if a new option was added to the array in an upgrade
    // and the options haven't been changed/saved to the database yet
    $result = $mtli_default_options[$option_name];

  } else {
    // option found in database
    $result = $mtli_options[$option_name];
  }
  

  return $result;
}

function mtli_admin() {

  if (function_exists('add_options_page')) {

    add_options_page('MimeType Link Icons' /* page title */, 
                     'MimeType Icons' /* menu title */, 
                     8 /* min. user level */, 
                     basename(__FILE__) /* php file */ , 
                     'mtli_options' /* function for subpanel */);
  }

}

function mtli_get_wp_path(){
	if ( version_compare( get_bloginfo( 'version' ) , '3.0' , '<' ) && is_ssl() ) {
		 $wp_content_url = str_replace( 'http://' , 'https://' , get_option( 'siteurl' ) );
	} else {
		 $wp_content_url = get_option( 'siteurl' );
	}
	$wp_content_url .= '/wp-content';
	return $wp_content_url;
}

function mtli_options() {
	mtli_admin_css();
	global $mtli_available_sizes;
	global $mtli_available_image_types;
	global $mtli_available_mime_types;
  	$wp_content_url = mtli_get_wp_path();
	if (isset($_POST['info_update'])) {

		?><div class="updated"><p><strong><?php 
		
		// process submitted form
		$mtli_options = get_option('mimetype_link_icon_options');
		$mtli_options['image_size']		= $_POST['image_size'];
		$mtli_options['image_type']		= $_POST['image_type'];
		$mtli_options['leftorright']	= $_POST['leftorright'];
		$mtli_options['show_file_size']	= ( isset( $_POST['show_file_size'] ) && $_POST['show_file_size']=="true"	? true : false);
		
		global $mtli_available_mime_types, $mtli_default_true;
		foreach( $mtli_available_mime_types as $type ) {
			if( isset( $_POST['enable_' . $type] ) ) {
				$mtli_options['enable_' . $type]	= ($_POST['enable_' . $type]=="true"		? true : false);
			}
			// Not set, get default ? or should this revert back to the already saved version if there is one first ?
			else {
				$mtli_options['enable_' . $type]	= ( in_array( $type, $mtli_default_true ) === false ? false : true );
			}
		}

		$mtli_options['enable_async']	= ( isset($_POST['enable_async']) && $_POST['enable_async']=="true"	? true : false);
		$mtli_options['enable_hidden_class']	= ( isset($_POST['enable_hidden_class']) && $_POST['enable_hidden_class']=="true" 	? true : false);
		if( isset($_POST['hidden_classname']) && !empty($_POST['hidden_classname']) ) {
			$mtli_options['hidden_classname']		=  $_POST['hidden_classname'];
		}

		update_option('mimetype_link_icon_options', $mtli_options);

		_e('Options saved', 'mtli')
		?></strong></p></div><?php
	} 
	?>
	<div class="wrap">
		<form method="post">
			<h2>MimeTypes Link Icons</h2> 
				
			<div class="whitebg">
			<fieldset class="options" name="general">
				<legend><?php _e('General settings', 'mtli') ?></legend>
				<table width="100%" cellspacing="2" cellpadding="5" class="editform form-table">
					<tr>
						<th nowrap valign="top" width="33%">
							<?php _e('Image Size', 'mtli') ?>
						</th>
						<td>
							<select name="image_size" id="image_size"> 
								<?php foreach($mtli_available_sizes as $k=> $v){ ?>
									<option value="<?php echo $v; ?>" <?php if(mtli_get_option('image_size')==$v) echo ' selected'; ?>><?php echo $v."x".$v; ?></option>
								<?php } ?>
							</select>
							<br />
						</td>
					</tr>
					<tr>
						<th nowrap valign="top" width="33%">
							<?php _e('Image Type', 'mtli') ?>
						</th>
						<td>
							<select name="image_type" id="image_type"> 
								<?php foreach($mtli_available_image_types as $k=>$v){ ?>
									<option value="<?php echo $v;?>" <?php if($v==mtli_get_option('image_type')) echo ' selected';?>><?php echo $v;?></option>
								<?php } ?>
							</select>
						</td>
					</tr>
					<tr>
						<th nowrap valign="top" width="33%">
							<?php _e('Display images on left or right', 'mtli') ?>
						</th>
						<td>
							<select name="leftorright" id="leftorright"> 
								<option value="left" <?php if('left'==mtli_get_option('leftorright')) echo ' selected';?>>Left</option>
								<option value="right" <?php if('right'==mtli_get_option('leftorright')) echo ' selected';?>>Right</option>
							</select>
						</td>
					</tr>
				</table>
			</fieldset>
			<fieldset class="options" name="general">
				<legend><?php _e('Image Settings', 'mtli') ?></legend>
				<table width="100%" cellspacing="2" cellpadding="5" class="editform form-table">
					<?php foreach($mtli_available_mime_types as $k=>$mime_type){ ?> 
					<tr>
						<th nowrap valign="top" width="33%">
							<?php _e('Add images to '.$mime_type.' uploads', 'mtli') ?>
						</th>
						<td style="width:24px;"><img src="<?php echo $wp_content_url.'/plugins/mimetypes-link-icons/images/'.$mime_type.'-icon-24x24.png';?>"</td>
						<td>
							<input type="checkbox" name="enable_<?php echo $mime_type;?>" id="enable_<?php echo $mime_type;?>" value="true" <?php if (mtli_get_option('enable_'.$mime_type)) echo "checked"; ?> /> 
							<br />
						</td>
					</tr>
					<?php } ?>
				</table>
			</fieldset>
			<fieldset class="options" name="general">
				<legend><?php _e('Enable/Disable classname?', 'mtli') ?></legend>
				<table width="100%" cellspacing="2" cellpadding="5" class="editform form-table">
					<tr>
						<td><input type="checkbox" name="enable_hidden_class" id="enable_hidden_class" value="true" <?php if (mtli_get_option('enable_hidden_class')) echo "checked"; ?> /> Tick this box to have a <i>classname</i> that will disable the mime type links (ie: around an image or caption).</td>
					</tr>
					<!-- <tr>
						<td> </td>
					</tr> -->
					<tr>
						<td>You can change the classname by editing the field below.</td>
					</tr>
					<tr>
						<td><input type="text" name="hidden_classname" id="hidden_classname" value="<?php echo mtli_get_option('hidden_classname');?>" /> </td>
					</tr>
				</table>
			</fieldset>
			<fieldset class="options" name="general">
				<legend><?php _e('Show File Size?', 'mtli') ?></legend>
				<table width="100%" cellspacing="2" cellpadding="5" class="editform form-table">
					<tr>
						<td><input type="checkbox" name="show_file_size" id="show_file_size" value="true" <?php if (mtli_get_option('show_file_size')) echo "checked"; ?> /> Tick this box to display the <i>file size</i> of the attachment.</td>
					</tr>
					<tr>
						<td> </td>
					</tr>
				</table>
			</fieldset>
			<fieldset class="options" name="general">
				<legend><?php _e('Enable Asynchronous Replacement?', 'mtli') ?></legend>
				<table width="100%" cellspacing="2" cellpadding="5" class="editform form-table">
					<tr>
						<td>Some themes or plugins may conflict with this plugin. If you find you are having trouble you can switch on asynchronous replacement which (instead of PHP) uses JavaScript to find your PHP links.</td>
					</tr>
					<tr>
						<td><input type="checkbox" name="enable_async" id="enable_async" value="true" <?php if (mtli_get_option('enable_async')) echo "checked"; ?> /> Tick box to enable <i>asyncronous replacement</i>.</td>
					</tr>
				</table>
			</fieldset>
			<div class="submit">
				<input type="submit" name="info_update" value="<?php _e('Update options', 'mtli') ?>" /> 
			</div>
			</div>
		</form>
	</div>
<?php

}


// Hook function for init action to do some initialization
function mtli_init() {
	// load texts for localization
	load_plugin_textdomain('mtli');
}


// This just echoes the chosen line, we'll position it later
function mimetype_to_icon($content) {
	global $mtli_available_sizes;
	global $mtli_available_image_types;
	global $mtli_available_mime_types;
	global $add_attachment_style;
	global $mtli_css;
	if ( ! function_exists( 'is_ssl' ) ) {
		function is_ssl() {
			if ( isset($_SERVER['HTTPS']) ) {
				if ( 'on' == strtolower($_SERVER['HTTPS']) )
					return true;
				if ( '1' == $_SERVER['HTTPS'] )
					return true;
			} elseif ( isset($_SERVER['SERVER_PORT']) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {
				return true;
			}
			return false;
		}
	}
	
	$wp_content_url = mtli_get_wp_path();
	$add_attachment_style = false;
	$mtli_css = '';
	foreach($mtli_available_mime_types as $k=>$mime_type){
		if(mtli_get_option('enable_'.$mime_type)){
			//if($content = preg_replace('/href="([^"]+\.pdf)"/','href="\1"  class="mtli_attachment mtli_'.$mime_type.'"',$content, -1, $howmany))
			if(mtli_get_option('show_file_size')===true){
				$extrabit = 'mtli_filesize ';
			} else {
				$extrabit = '';
			}
			if(preg_match_all('/href="([^"#]+\.'.$mime_type.')(?:#[^" ]+"|")/', $content, $matches)!==false){
				$howmany=0;
				$content = preg_replace('/href="([^"#]+\.'.$mime_type.')(#[^" ]+"|")/','href="\\1\\2 rel="'.$extrabit.'" class="mtli_attachment mtli_'.$mime_type.'"',$content, -1, $howmany);
				$content = mtli_determine_file_paths($matches, $content);
				if($howmany>0){
					$add_attachment_style = true;
					$mtli_css .= '.mtli_'.$mime_type.' { background-image: url('.$wp_content_url.'/plugins/mimetypes-link-icons/images/'.$mime_type.'-icon-'.mtli_get_option('image_size').'x'.mtli_get_option('image_size').'.'.mtli_get_option('image_type').'); }';
				}
			} elseif(preg_match_all("/href='([^'#]+\.".$mime_type.")(?:#[^' ]+'|')/",$content, $matches)!==false){
				$howmany=0;
				$content = preg_replace("/href='([^'#]+\.".$mime_type.")(#[^' ]+'|')/","href='\\1\\2 rel='".$extrabit."' class='mtli_attachment mtli_".$mime_type."'",$content, -1, $howmany);
				$content = mtli_determine_file_paths($matches, $content);
				if($howmany>0){
					$add_attachment_style = true;
					$mtli_css .= '.mtli_'.$mime_type.' { background-image: url('.$wp_content_url.'/plugins/mimetypes-link-icons/images/'.$mime_type.'-icon-'.mtli_get_option('image_size').'x'.mtli_get_option('image_size').'.'.mtli_get_option('image_type').'); }';
				}
			}
		}
		
	}
	
	return $content;
}

function mtli_determine_file_paths($matches, $content){
	global $fileSizeStyles;
	if(mtli_get_option('show_file_size')===true){
		foreach($matches as $k=>$match){
			if($k>0){
				foreach($match as $key=>$thismatch){
					if($this_filesize = mtli_get_size($thismatch)){
						$content = preg_replace('/('.str_replace('/','\/',$thismatch).'(#[^" ]+"|")) rel="mtli_filesize /', '\\1 rel="mtli_filesize'.str_replace('.','',$this_filesize).' ', $content);
					}
				}
			}
		}
	}
	if(mtli_get_option('hidden_classname')){
		foreach($matches as $k=>$match){
			if($k>0){
				foreach($match as $key=>$thismatch){
					if($this_filesize = mtli_get_size($thismatch)){
						$content = preg_replace('/('.str_replace('/','\/',$thismatch).'(#[^" ]+"|")) rel="mtli_filesize /', '\\1 rel="mtli_filesize'.str_replace('.','',$this_filesize).' ', $content);
					}
				}
			}
		}
	}
	return $content;
}

function mtli_get_size($filePath){
	global $fileSizeStyles;
	if(!$filePath){
		return false;
	}
	if(strpos($filePath,'http://')!==false && strpos($filePath,$_SERVER['SERVER_NAME'])===false){ // We have an external file to size up
		$fileSize = mtli_get_external_filesize($filePath);
	} else {
		$uploads = wp_upload_dir();
		$filePath = explode('#',$filePath);
		$filePath = explode('?',$filePath[0]);
		$filePath = $filePath[0];
		$filePath = str_replace($uploads['baseurl'],$uploads['basedir'], $filePath);
		$fileSize = filesize($filePath);
	}
	$fileSize = mtli_return_nice_size($fileSize);
	$fileSizeStyles .= 'a[rel~="mtli_filesize'.str_replace('.','',$fileSize).'"]:after {content:" ('.$fileSize.')"}';
	return $fileSize;
}

function mtli_get_external_filesize($remoteFile){
	$ch = curl_init($remoteFile);
	curl_setopt($ch, CURLOPT_NOBODY, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); //not necessary unless the file redirects (like the PHP example we're using here)
	$data = curl_exec($ch);
	curl_close($ch);
	if ($data === false) {
		return false;
	}
	
	$contentLength = 'unknown';
	$status = 'unknown';
	if (preg_match('/^HTTP\/1\.[01] (\d\d\d)/', $data, $matches)) {
	  $status = (int)$matches[1];
	}
	if (preg_match('/Content-Length: (\d+)/', $data, $matches)) {
	  $contentLength = (int)$matches[1];
	}
	return $contentLength;
}

function mtli_return_nice_size($bytes){
	if($bytes<1024){
		return $bytes.'b';
	} elseif($bytes<1048576){
		return number_format(($bytes/1024),2).'Kb';
	} elseif($bytes<1073741824){
		return number_format(($bytes/1048576),2).'Mb';
	} else {
		return number_format(($bytes/1073741824),2).'Gb';
	}
}
// Now we set that function up to execute when the the_content action is called

//add_action('load_template', 'mimetype_to_icon');


function mtli_add_jquery(){
	wp_enqueue_script('jquery');
}

function mtli_display_css($content){
	global $mtli_available_mime_types;
	global $add_attachment_style;
	global $fileSizeStyles;
	$mtli_css = "<style type='text/css'> .mtli_attachment {  display:inline-block;  height:".mtli_get_option('image_size')."px;  background-position: top ".mtli_get_option('leftorright')."; background-attachment: scroll; background-repeat: no-repeat; padding-".mtli_get_option('leftorright').": ".(mtli_get_option('image_size')*1.2)."px; }";
	$wp_content_url = mtli_get_wp_path();
	foreach($mtli_available_mime_types as $k=>$mime_type){ 
		if(mtli_get_option('enable_'.$mime_type)){
			$mtli_css .= '.mtli_'.$mime_type.' { background-image: url('.$wp_content_url.'/plugins/mimetypes-link-icons/images/'.$mime_type.'-icon-'.mtli_get_option('image_size').'x'.mtli_get_option('image_size').'.'.mtli_get_option('image_type').'); }';
		}
	}
	if($fileSizeStyles){
		$mtli_css.=$fileSizeStyles;
	}
	$mtli_css.="</style>";
	if($add_attachment_style===true || mtli_get_option('enable_async')){
		return $content.$mtli_css;
	} else {
		return $content;
	}
}

function mtli_show_file_size_css($content){
	global $fileSizeStyles;
	$mtli_css = '';
	if($fileSizeStyles){
		$mtli_css = "<style type='text/css'>".$fileSizeStyles."</style>";
	}
	return $content.$mtli_css;
}

function mtli_add_async_replace($content){
	$wp_content_url = mtli_get_wp_path();
	$mtli_js_array = 'var mtli_js_array = new Array(';
	global $mtli_available_mime_types;
	foreach($mtli_available_mime_types as $k=>$mime_type){ 
		if(mtli_get_option('enable_'.$mime_type)){
			$mtli_js_array .= "'".$mime_type."',";
		}
	}
	$mtli_js_array = substr($mtli_js_array,0,-1).');';
	echo '<script type="text/javascript">'.$mtli_js_array.'</script>';
	wp_enqueue_script( 'mtli_str_replace', $wp_content_url.'/plugins/mimetypes-link-icons/js/mtli_str_replace.js', array( 'jquery' ), '', true );
}

function mtli_add_hidden_class(){
	echo '<script type="text/javascript">var mtli_hidethings = true; var mtli_avoid="'.mtli_get_option('hidden_classname').'";</script>';
}
function mtli_no_hidden_class(){
	echo '<script type="text/javascript">var mtli_hidethings = false;</script>';
}
function mtli_add_css_to_head(){
	$wp_content_url = mtli_get_wp_path();
	wp_register_style('mtli_css_head',$wp_content_url.'/plugins/mimetypes-link-icons/css/style.php?cssvars='.base64_encode('mtli_height='.mtli_get_option('image_size').'&mtli_image_type='.mtli_get_option('image_type').'&mtli_leftorright='.mtli_get_option('leftorright')));
	wp_enqueue_style('mtli_css_head');
}



if(mtli_get_option('enable_hidden_class')){
	add_filter('get_footer', 'mtli_add_hidden_class');
	add_filter('get_header', 'mtli_add_jquery');
	add_filter('get_footer', 'mtli_add_async_replace');
	
} else {
	add_filter('get_footer', 'mtli_no_hidden_class');
}
if(mtli_get_option('enable_async')){
	add_filter('get_header', 'mtli_add_jquery');
	add_filter('get_footer', 'mtli_add_async_replace');
} else {
	add_filter('the_content', 'mimetype_to_icon');
}
add_filter('get_header','mtli_add_css_to_head');
//add_filter('the_content', 'mtli_display_css');

if(mtli_get_option('show_file_size')){
	add_filter('get_header', 'mtli_add_jquery');
	add_filter('the_content','mtli_show_file_size_css');
}

// Adding Admin CSS
function mtli_admin_css() {
	echo "
	<style type='text/css'>
	.form-table				{ margin-bottom: 0 !important; }
	.form-table th			{ font-size: 11px; min-width: 200px; }
	.form-table .largetext	{ font-size: 12px; }
	.form-table td			{ max-width: 500px; }
	.form-table tr:last-child	{ border-bottom: 1px solid #DEDEDE; }
	.form-table tr:last-child td { padding-bottom: 20px; }
	.form-table select		{ width: 275px; }
	</style>
	";
}

//add_filter('admin_head', 'mtli_admin_css');






// **************
// initialization




// assume both header and footer are not hooked
global $mtli_header_hooked;
global $mtli_footer_hooked;
$mtli_header_hooked=false;
$mtli_footer_hooked=false;

// add UGA Options page to the Option menu
add_filter('admin_menu', 'mtli_admin');


add_filter('init', 'mtli_init');



?>
