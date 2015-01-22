<?php
/**
 * Code used when the plugin is removed (not just deactivated but actively deleted by the WordPress Admin).
 *
 * @package MimeTypes Link Icons
 * @subpackage Uninstall
 * @version 1.0
 * @since 3.0
 *
 * @author Juliette Reinders Folmer
 * @license http://creativecommons.org/licenses/GPL/2.0/ GNU General Public License, version 2
 */

if ( ! current_user_can( 'activate_plugins' ) || ( ! defined( 'ABSPATH' ) || ! defined( 'WP_UNINSTALL_PLUGIN' ) ) ) {
	exit();
}

delete_option( 'mimetype_link_icon_options' );
delete_option( 'mimetype_link_icons_filesize_cache' );