<?php
header( 'Content-type: text/css' );

$getvars = explode( '&', base64_decode( $_GET['cssvars'] ) );
foreach ( $getvars as $v ) {
	$data     = explode( '=', $v );
	${$data[0]} = $data[1];
}
unset( $getvars, $v );

/* Input validation of $_GET variables */
$mtli_height      = ( in_array( intval( $mtli_height ), array( 16, 24, 48, 64, 128 ) ) ? intval( $mtli_height ) : 16 );
$mtli_leftorright = ( in_array( $mtli_leftorright, array( 'left', 'right' ) ) ? $mtli_leftorright : 'left' );
$mtli_image_type  = ( in_array( $mtli_image_type, array( 'gif', 'png' ) ) ? $mtli_image_type : 'png' );
$mtli_padding     = ( extension_loaded( 'bcmath' ) ? bcmul( $mtli_height, 1.2, 0 ) : ( $mtli_height * 1.2 ) );

echo '
.mtli_attachment {
	display:inline-block;
	height: auto;
	min-height:' . $mtli_height . 'px;
	background-position: top ' . $mtli_leftorright . ';
	background-attachment: scroll;
	background-repeat: no-repeat;
	padding-' . $mtli_leftorright . ': ' . $mtli_padding . 'px !important;
}';

$mtli_available_mime_types = array(
			'3g2', '3gp',
			'ai', 'air', 'asf', 'avi',
			'bib',
			'cls', 'csv',
			'deb', 'djvu', 'dmg', 'doc', 'docx', 'dwf', 'dwg',
			'eps', 'epub', 'exe',
			'f', 'f77', 'f90', 'flac', 'flv',
			'gif', 'gz',
			'ico', 'indd', 'iso',
			'jpg', 'jpeg',
			'key',
			'log',
			'm4a', 'm4v', 'midi', 'mkv', 'mov', 'mp3', 'mp4', 'mpeg', 'mpg', 'msi',
			'odp', 'ods', 'odt', 'oga', 'ogg', 'ogv',
			'pdf', 'png', 'pps', 'ppsx', 'ppt', 'pptx', 'psd', 'pub', 'py',
			'qt',
			'ra', 'ram', 'rar', 'rm', 'rpm', 'rtf', 'rv',
			'skp', 'spx', 'sql', 'sty',
			'tar', 'tex', 'tgz', 'tiff', 'ttf', 'txt',
			'vob',
			'wav', 'wmv',
			'xls', 'xlsx', 'xml', 'xpi',
			'zip',
);

// @todo Only generate for the enabled mime-types ?
foreach ( $mtli_available_mime_types as $type ) {
	echo '
.mtli_' . $type . ' {
	background-image: url(../images/' . $type . '-icon-' . $mtli_height . 'x' . $mtli_height . '.' . $mtli_image_type . '); }';
}
unset( $type, $mtli_height, $mtli_image_type, $mtli_leftorright );