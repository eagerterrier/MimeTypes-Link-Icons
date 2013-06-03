<?php
/*
Plugin Name: MimeTypes Link Icons
Plugin URI: http://blog.eagerterrier.co.uk/2010/10/holy-cow-ive-gone-and-made-a-mime-type-wordpress-plugin/
Description: This will add file type icons next to links automatically. Change options in the <a href="options-general.php?page=mime_type_link_images.php">settings page</a>
Version: 3.0
Author: Toby Cox, Juliette Reinders Folmer
Author URI: https://github.com/eagerterrier/MimeTypes-Link-Icons
Author: Toby Cox
Author URI: http://eagerterrier.co.uk/
Author: Juliette Reinders Folmer
Author URI: http://adviesenzo.nl/
Contributor: Keith Parker
Contributor URI: http://infas.net/
Text Domain: mimetypes-link-icons
Domain Path: /languages/
*/

/*
GNU General Public License, Free Software Foundation <http://creativecommons.org/licenses/GPL/2.0/>

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/



/**
 * @todo: test with safe_mode on and allow_url_fopen off ?
 *
 * POTENTIAL ROAD MAP:
 * @todo look into issue: http://wordpress.org/support/topic/async-replacement-causing-jquery-problems
 * @todo look into issue: http://wordpress.org/support/topic/problem-with-images-13
 *
 * @todo may be implement Google Drive file types ? http://wordpress.org/support/topic/ms-publisher
 * @todo may be implement some way to allow more jquery selector types for hidden, i.e. #selector, TAG etc ? http://wordpress.org/support/topic/problem-with-images-13?replies=5
 * @todo may be add a 'reset all settings' button ? this should delete the plugin option so all settings will revert back to default
 * @todo may be incorporate a directory crawler in the upgrade routine to inventarise the available file extensions and save the found extensions to a WP option. Only needs to be done on upgrade as that is the only time new extensions will be added. Problem with this is that the style.php file also uses the list and does not have access to the WP functions to retrieve the option.
 * @todo try and figure something out to only load the front-end stylesheet and js file when needed
 * @todo figure out a way how to rename this file to mimetypes-link-icons to be in line with the rest of the plugin. Problem: plugin will deactivate when people upgrade if the name of the main file has changed.... Has to do with option holding file names of activated plugins
 */


if ( !class_exists( 'mimetypes_link_icons' ) ) {

	/**
	 * @package WordPress\Plugins\MimeTypes Link Icons
	 * @version 3.0
	 * @link http://wordpress.org/extend/plugins/mimetypes-link-icons/ MimeTypes Link Icons WordPress plugin
	 *
	 * @copyright 2010 - 2013 Toby Cox, Juliette Reinders Folmer
	 * @license http://creativecommons.org/licenses/GPL/2.0/ GNU General Public License, version 2
	 */
	class mimetypes_link_icons {


		/* *** DEFINE CLASS CONSTANTS *** */

		/**
		 * @const string	Plugin version number
		 * @usedby upgrade_options(), __construct()
		 */
		const VERSION = '3.0.1';

		/**
		 * @const string	Version in which the front-end styles where last changed
		 * @usedby	wp_enqueue_scripts()
		 */
		const STYLES_VERSION = '3.0';

		/**
		 * @const string	Version in which the front-end scripts where last changed
		 * @usedby	wp_enqueue_scripts()
		 */
		const SCRIPTS_VERSION = '3.0';

		/**
		 * @const string	Version in which the admin styles where last changed
		 * @usedby	admin_enqueue_scripts()
		 */
		const ADMIN_STYLES_VERSION = '3.0';

		/**
		 * @const string	Version in which the admin scripts where last changed
		 * @usedby	admin_enqueue_scripts()
		 */
		const ADMIN_SCRIPTS_VERSION = '3.0';

		/**
		 * @const string	Plugin version in which the DB options structure was last changed
		 * @usedby upgrade_options()
		 */
		const DB_LASTCHANGE = '3.0';


		/**
		 * @const string    Minimum WP version needed for this plugin to work
		 * @usedby upgrade_options() to auto-deactivate if plugin can't work
		 */
		const MIN_WP_VERSION = '3.1.4';

		/**
		 * @const string    Minimum PHP version needed for this plugin to work
		 * @usedby upgrade_options() to auto-deactivate if plugin can't work
		 */
		const MIN_PHP_VERSION = '5.1';


		/**
		 * @const	string	Minimum required capability to change the plugin options
		 */
		const REQUIRED_CAP = 'manage_options';

		/**
		 * @const	string	Page underneath which the settings page will be hooked
		 */
		const PARENT_PAGE = 'options.php';

		/**
		 * @const	string	Name of options variable containing the plugin proprietary settings
		 */
		const SETTINGS_OPTION = 'mimetype_link_icon_options';

		/**
		 * @const	string	Name of options variable containing the filesize cached values
		 */
		const CACHE_OPTION = 'mimetype_link_icons_filesize_cache';

		/**
		 * @const   int     Number of columns to put the image settings in on the options page
		 */
		const NR_OF_COLUMNS = 2;



		/* *** DEFINE STATIC CLASS PROPERTIES *** */

		/**
		 * These static properties will be initialized - *before* class instantiation -
		 * by the static init() function
		 */

		/**
		 * @const	string	Plugin Basename = 'dir/file.php'
		 */
		public static $basename;

		/**
		 * @const	string	Plugin name	  = dirname of the plugin
		 *					Also used as text domain for translation
		 */
		public static $name;

		/**
		 * @const	string	Full url to the plugin directory, has trailing slash
		 */
		public static $url;

		/**
		 * @const	string	Full server path to the plugin directory, has trailing slash
		 */
		public static $path;

		/**
		 * @const	string	Suffix to use if scripts/styles are in debug mode
		 */
		public static $suffix;



		/* *** DEFINE CLASS PROPERTIES *** */

		/* *** Semi Static Properties *** */

		/**
		 * @var	array	Available file sizes: key = setting, value = field label
		 * @todo		IMPORTANT: for now on each change, also copy this array to style.php
		 */
		var $sizes = array(
			16,
			24,
			48,
			64,
			128,
		);

		/**
		 * @var array	Available images types: key = setting, value = field label
		 * @todo		IMPORTANT: for now on each change, also copy this array to style.php
		 */
		var $image_types = array(
			'gif',
			'png',
		);

		/**
		 * @var array	Available image alignments: key = setting, value = field label
		 *				Will be set by set_properties() as the field labels need translating
		 * @todo		IMPORTANT: for now on each change, also copy this array to style.php
		 */
		var $alignments;

		/**
		 * @var array	array of mimetypes
		 * @todo		IMPORTANT: for now on each change, also copy this array to style.php
		 *				and of course to the readme ;-)
		 */
		var $mime_types = array(
			'3g2', '3gp',
			'ai', 'air', 'asf', 'avi',
			'bib',
			'csv',
			'deb', 'djvu', 'dmg', 'doc', 'docx', 'dwf', 'dwg',
			'eps', 'epub', 'exe',
			'flac', 'flv',
			'gif', 'gz',
			'ico', 'indd', 'iso',
			'jpg', 'jpeg',
			'log',
			'm4a', 'm4v', 'midi', 'mkv', 'mov', 'mp3', 'mp4', 'mpeg', 'mpg', 'msi',
			'odp', 'ods', 'odt', 'oga', 'ogg', 'ogv',
			'pdf', 'png', 'pps', 'ppsx', 'ppt', 'pptx', 'psd', 'pub',
			'qt',
			'ra', 'ram', 'rm', 'rpm', 'rtf', 'rv',
			'skp', 'spx', 'sql',
			'tar', 'tex', 'tgz', 'tiff', 'ttf', 'txt',
			'vob',
			'wav', 'wmv',
			'xls', 'xlsx', 'xml', 'xpi',
			'zip',
		);

		/**
		 * @var array   array of mimetypes which default to true / 'on' status
		 */
		var $default_is_true = array(
			'pdf',
		);

		/**
		 * @var array	Default option values - this array will be enriched by the enrich_default_settings() method
		 * @todo		IMPORTANT: For now, on change in default size, type or alignment, also copy
		 *				the new defaults to style.php
		 */
		var $defaults = array(
			'internal_domains'		=> array(),
			'image_size'			=> 16,
			'image_type'			=> 'png',
			'leftorright'			=> 'left',
			'show_file_size'		=> false,
			'precision'				=> 2,
			'use_cache'				=> true,
			'cache_time'			=> 604800, // seconds: 1 hour = 3600, 1 day = 86400, 1 week = 604800
			'enable_async'			=> false,
			'enable_hidden_class'	=> true,
			'hidden_classname'		=> array( 'wp-caption', ),
			'version'				=> null,
		);

		/**
		 * @var array   array of option form sections: key = setting area, value = section label
		 *				Will be set by set_properties() as the section labels need translating
		 * @usedby display_options_page()
		 */
		var $form_sections = array();

		/**
		 * @var array	array of byte suffixes for creating a human readable file size
		 *				Will be set by set_properties() as the labels need translating
		 * @usedby human_readable_filesize()
		 */
		var $byte_suffixes = array();



		/* *** Properties Holding Various Parts of the Class' State *** */

		/**
		 * @var string settings page registration hook suffix
		 */
		var $hook;

		/**
		 * @var array Variable holding current settings for this plugin
		 */
		var $settings = array();

		/**
		 * @var array Efficiency property - array of the mimetype for which the plugin should be active
		 */
		var $active_mimetypes = array();

		/**
		 * @var array	Array holding cached filesize values
		 *				key = sanitized file path
		 *				values = array( 'size' => file size, 'time' => time of last filesize retrieval in seconds )
		 */
		var $cache = array();

		/**
		 * @var array	Array holding the rel / filesize CSS styles to be added to the page
		 */
		var $filesize_styles = array();

		/**
		 * @var	resource 	Holds the curl resource if one exists
		 */
		var $curl;




		/* *** PLUGIN INITIALIZATION METHODS *** */

		/**
		 * Object constructor for plugin
		 */
		function __construct() {

			/* Load plugin text strings */
			load_plugin_textdomain( self::$name, false, self::$name . '/languages/' );

			/* Translate a number of strings */
			$this->set_properties();

			/* Initialize/enrich settings properties */
			$this->enrich_default_settings();
			$this->_get_set_settings();


			/* Check if we have any activation or upgrade actions to do */
			if( !isset( $this->settings['version'] ) || self::DB_LASTCHANGE > $this->settings['version'] ) {
				add_action( 'init', array( &$this, 'upgrade_options' ), 8 );
			}
			// Make sure that an upgrade check is done on (re-)activation as well.
			register_activation_hook( __FILE__, array( &$this, 'upgrade_options' ) );


			// Register the plugin initialization actions
			add_action( 'init', array( &$this, 'init' ) );
			add_action( 'admin_menu', array( &$this, 'add_options_page' ) );
			add_action( 'admin_init', array( &$this, 'admin_init' ) );
		}


		/**
		 * Set the static path and directory variables for this class
		 * Is called from the global space *before* instantiating the class to make
		 * sure the correct values are available to the object
		 *
		 * @return void
		 */
		public static function init_statics() {

			self::$basename = plugin_basename( __FILE__ );
			self::$name 	= dirname( self::$basename );
			self::$url		= plugin_dir_url( __FILE__ );
			self::$path 	= plugin_dir_path( __FILE__ );
			self::$suffix	= ( ( defined( 'SCRIPT_DEBUG' ) && true === SCRIPT_DEBUG ) ? '' : '.min' );
		}


		/**
		 * Fill some property arrays with translated strings
		 */
		function set_properties() {

			$this->alignments = array(
				'left'	   => __( 'Left', self::$name ),
				'right'    => __( 'Right', self::$name ),
			);

			$this->form_sections = array(
				'general'	=> __( 'General Settings', self::$name ),
				'images'	=> __( 'Image Settings', self::$name ),
				'advanced'	=> __( 'Advanced Settings', self::$name ),
			);

			$this->byte_suffixes = array(
				__( 'b', self::$name ),
				__( 'kB', self::$name ),
				__( 'MB', self::$name ),
				__( 'GB', self::$name ),
				__( 'TB', self::$name ),
				__( 'PB', self::$name ),
				__( 'EB', self::$name ),
				__( 'ZB', self::$name ),
				__( 'YB', self::$name ),
			);
		}


		/**
		 * Enrich the default settings array
		 */
		function enrich_default_settings() {
			foreach( $this->mime_types as $type ) {
				$this->defaults['enable_' . $type]	= ( false === in_array( $type, $this->default_is_true ) ? false : true );
			}
		}



		/** ******************* ADMINISTRATIVE METHODS ******************* **/


		/**
		 * Add the actions for the front end functionality
		 */
		public function init() {

			// Don't do anything if no active_mimetypes or if we're not on the frontend
			if( false === is_admin() && 0 < count( $this->active_mimetypes ) ) {

				/* Register the_content filter */
				if( false === $this->settings['enable_async'] || true === $this->settings['show_file_size'] ) {
					add_filter( 'the_content', array( &$this, 'mimetype_to_icon' ) );
				}
				/* Add js and css files */
				add_action( 'wp_enqueue_scripts', array( &$this, 'wp_enqueue_scripts' ) );
			}
		}


		/**
		 * Add the actions for the back-end functionality
		 */
		function admin_init() {
			/* Don't do anything if user does not have the required capability */
			if ( false === is_admin() || false === current_user_can( self::REQUIRED_CAP ) ) {
				return;
			}

			/* Register our options field */
			register_setting(
				self::SETTINGS_OPTION . '-group',
				self::SETTINGS_OPTION, // option name
				array( &$this, 'validate_options' ) // validation callback
			);

			/* Register the settings sections and their callbacks */
			foreach( $this->form_sections as $section => $title ) {

				add_settings_section(
					'mtli-' . $section . '-settings', // id
					$title, // title
					array( &$this, 'do_settings_section_' . $section ), // callback for this section
					self::$name // page menu_slug
				);
			}

			/* Add settings link on plugin page */
			add_filter( 'plugin_action_links_' . self::$basename , array( &$this, 'add_settings_link' ), 10, 2 );


			/* Add js and css files */
			add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue_scripts' ) );


			/* Add contextual help action/filters */
			if( true === version_compare( $GLOBALS['wp_version'], '3.3', '>=' ) && method_exists( 'WP_Screen', 'add_help_tab' ) ) {
				// Add help tab *behind* existing core page help tabs
				// (reason for using admin_head hook instead of load hook)
				add_action( 'admin_head', array( &$this, 'add_help_tab' ) );
			}
			else {
				add_filter( 'contextual_help', array( &$this, 'add_contextual_help' ), 10, 3 );
			}
		}


		/**
		 * Register the options page for all users that have the required capability
		 */
		function add_options_page() {

			$this->hook = add_options_page(
				__( 'MimeType Link Icons', self::$name ), /* page title */
				__( 'MimeType Icons', self::$name ), /* menu title */
				self::REQUIRED_CAP, /* capability */
				self::$name, /* menu slug */
				array( &$this, 'display_options_page' ) /* function for subpanel */
			);
		}


		/**
		 * Add settings link to plugin row
		 *
		 * @since 3.0
		 *
		 * @param	array	$links	Current links for the current plugin
		 * @param	string	$file	The file for the current plugin
		 * @return	array
		 */
		function add_settings_link( $links, $file ) {
			if( self::$basename === $file && current_user_can( self::REQUIRED_CAP ) ) {
				$links[] = '<a href="' . esc_url( $this->plugin_options_url() ) . '" alt="' . esc_attr__( 'MimeType Link Icons Settings', self::$name ) . '">' . esc_html__( 'Settings', self::$name ) . '</a>';
			}
			return $links;
		}

		/**
		 * Return absolute URL of options page
		 *
		 * @since 3.0
		 *
		 * @return string
		 */
		function plugin_options_url() {
			return add_query_arg( 'page', self::$name, admin_url( self::PARENT_PAGE ) );
		}


		/**
		 * Conditionally enqueue scripts and styles for front-end pages
		 * @todo:	Probably quite difficult in this case: see if we can load our scripts and styles conditionally, i.e. only on the pages where used
		 * @todo:	For now: may be add the active mimetypes as an encoded setting to the url so as only to generate the css rules for the active mimetypes
		 * @todo:	May be generate a .css file on a settings save to avoid having to generate the .css file on each page load
		 * @todo:	Also generate a .min.css file
		 */
		function wp_enqueue_scripts() {

			wp_register_style(
				self::$name, // id
				add_query_arg(
					'cssvars',
					base64_encode( 'mtli_height=' . $this->settings['image_size'] . '&mtli_image_type=' . $this->settings['image_type'] . '&mtli_leftorright=' . $this->settings['leftorright'] ),
					self::$url . '/css/style.php'
				), // url
				false, // not used
				self::STYLES_VERSION, // version
				'all'
			);
			wp_enqueue_style( self::$name );


			if( ( true === $this->settings['enable_hidden_class'] && ( is_array( $this->settings['hidden_classname'] ) && 0 < count( $this->settings['hidden_classname'] ) ) ) || ( true === $this->settings['enable_async'] && ( is_array( $this->active_mimetypes ) && 0 < count( $this->active_mimetypes ) ) ) ) {

				wp_enqueue_script(
					self::$name, // id
					self::$url . '/js/mtli-str-replace' . self::$suffix . '.js', // url
					array( 'jquery' ), // dependants
					self::SCRIPTS_VERSION, // version
					true // load in footer
				);
			}
			// is this really necessary ?
			/*			else if( $this->settings['show_file_size'] === true ) {
							wp_enqueue_script( 'jquery' );
						}*/

			wp_localize_script( self::$name, 'i18n_mtli', $this->get_javascript_i18n() );
		}


		/**
		 * Retrieve the strings for use in the javascript file
		 *
		 * @since 3.0
		 * @usedby	wp_enqueue_scripts()
		 *
		 * @return	array
		 */
		function get_javascript_i18n() {
			$strings = array(
				'hidethings'	=> ( ( true === $this->settings['enable_hidden_class'] && ( is_array( $this->settings['hidden_classname'] ) && 0 < count( $this->settings['hidden_classname'] ) ) ) ? true : false ),
				'enable_async'	=> ( ( true === $this->settings['enable_async'] && ( is_array( $this->active_mimetypes ) && 0 < count( $this->active_mimetypes ) ) ) ? true : false ),
			);

			/* Add jQuery class selector string if hidden classes are used */
			if( true === $this->settings['enable_hidden_class'] && ( is_array( $this->settings['hidden_classname'] ) && 0 < count( $this->settings['hidden_classname'] ) ) ) {
				$strings['avoid_selector'] = '';
				foreach( $this->settings['hidden_classname'] as $classname ) {
					$strings['avoid_selector'] .= '.' . $classname . ',';
				}
				$strings['avoid_selector'] = substr( $strings['avoid_selector'], 0, -1 );
			}

			/* Add array of active mimetypes if in async mode*/
			if( true === $this->settings['enable_async'] && ( is_array( $this->active_mimetypes ) && 0 < count( $this->active_mimetypes ) ) ) {
				if( true === version_compare( $GLOBALS['wp_version'], '3.3', '>=' ) ) {
					$strings['mime_array'] = $this->active_mimetypes;
				}
				else { // backwards compatibility
					$strings['oldwp'] = true;
					$strings['mime_array'] = implode( ',', $this->active_mimetypes );
				}
			}

			return $strings;
		}


		/**
		 * Adds necessary javascript and css files for the back-end on the appropriate screen
		 */
		function admin_enqueue_scripts() {

			$screen = get_current_screen();

			if( property_exists( $screen, 'base' ) && $screen->base === $this->hook ) {

				wp_enqueue_script(
					self::$name, // id
					self::$url . 'js/mtli-admin' . self::$suffix . '.js', // url
					array( 'jquery' ), // dependants
					self::ADMIN_SCRIPTS_VERSION, // version
					true // load in footer
				);
				wp_enqueue_style(
					self::$name, // id
					self::$url . 'css/admin-style' . self::$suffix . '.css', // url
					false, // not used
					self::ADMIN_STYLES_VERSION, // version
					'all'
				);
				wp_localize_script( self::$name, 'i18n_mtli', $this->get_admin_javascript_i18n() );
			}
		}


		/**
		 * Retrieve the strings for use in the javascript file
		 *
		 * @since 3.0
		 * @usedby	admin_enqueue_scripts()
		 *
		 * @return	array
		 */
		function get_admin_javascript_i18n() {
			$strings = array(
                'togglebox'     => '<div class="check-images"><span class="check-all">' . __( 'Check All', self::$name ) . '</span>|<span class="uncheck-all">' . __( 'Uncheck All', self::$name ) . '</span></div>',
			);
			return $strings;
		}



		/**
		 * Adds contextual help tab to the plugin page
		 *
		 * @since 3.0
		 */
		function add_help_tab() {

			$screen = get_current_screen();

			if( property_exists( $screen, 'base' ) && $screen->base === $this->hook ) {

				$screen->add_help_tab( array(
						'id'	  => self::$name . '-main', // This should be unique for the screen.
						'title'   => __( 'MimeType Link Icons', self::$name ),
						'callback' => array( &$this, 'get_helptext' ),
					)
				);
				$screen->add_help_tab( array(
						'id'	  => self::$name . '-advanced', // This should be unique for the screen.
						'title'   => __( 'Advanced Settings', self::$name ),
						'callback' => array( &$this, 'get_helptext' ),
					)
				);
				$screen->add_help_tab( array(
						'id'	  => self::$name . '-extras', // This should be unique for the screen.
						'title'   => __( 'Extras', self::$name ),
						'callback' => array( &$this, 'get_helptext' ),
					)
				);

				$screen->set_help_sidebar( $this->get_help_sidebar() );
			}
		}


		/**
		 * Adds contextual help text to the plugin page
		 * Backwards compatibility for WP < 3.3.
		 *
		 * @since 3.0
		 */
		function add_contextual_help( $contextual_help, $screen_id, $screen ) {
			if( $screen_id === $this->hook ) {
				return $this->get_helptext( $screen, null, false );
			}
			return false;
		}


		/**
		 * Function containing the helptext string
		 *
		 * @since 3.0
		 *
		 * @param 	object	$screen
		 * @param 			$tab
		 * @param   bool    $echo    whether to echo or return the string
		 * @return  string  help text
		 */
		function get_helptext( $screen, $tab, $echo = true ) {

			$helptext[self::$name . '-main'] = '
								<p>' . sprintf( __( 'The <em><a href="%s">MimeTypes Link Icons</a></em> plugin will automatically add an icon next to links of the activated file types. If you like, you can also let the plugin add the file size of the linked file to the page.', self::$name ), 'http://wordpress.org/extend/plugins/mimetypes-link-icons/" target="_blank" class="ext-link') . '</p>
								<p>' . __( 'On this settings page you can specify the icon size, icon type (white matte gif or transparent png), icon alignment. You can also select the file types for which this plugin will be enabled.', self::$name) . '</p>';

			$helptext[self::$name . '-advanced'] = '
								<p>' . __( 'In the advanced settings, you can enable <em>"exclusion classnames"</em>, enable the display of the <em>file size</em> of a linked file and/or choose to use <em>asynchronous replacement</em>.', self::$name) . '</p>
								<p>' . __( '<strong>"Exclusion classnames"</strong> works as follows:', self::$name) . '<br />
								' . __( 'The plugin will look for the classname in your document and will remove the Mimetypes link icons (and file sizes) from all links wrapped within that class. You can add several classnames, just separate them with a comma.', self::$name) . '</p>';

			$helptext[self::$name . '-extras'] = '
								<p>' . __( 'There is even some more advanced functionality available: for instance an <em>output filter</em> for the file size output and a way to add the plugin\'s functionality to widgets or other areas of your blog outside of the main content area.', self::$name) . '</p>

								<p>' . sprintf( __( 'For more information on these tasty extras, have a look at the <a href="%s">FAQ</a>', self::$name ), 'http://wordpress.org/extend/plugins/mimetypes-link-icons/faq/" target="_blank" class="ext-link' ) . '</p>';

			if( $echo === true ) {
				echo $helptext[$tab['id']];
				return false;
			}
			else {
				// WP < 3.3
				// Return all help texts at once and add sidebar links to help text
				return implode( '', $helptext ) . $this->get_help_sidebar();
			}
		}

		/**
		 * Generate the links for the help sidebar
		 *
		 * @return string
		 */
		function get_help_sidebar() {
			return '
				   <p><strong>' . /* TRANSLATORS: no need to translate - standard WP core translation will be used */ __( 'For more information:' ) . '</strong></p>
				   <p>
						<a href="http://wordpress.org/extend/plugins/mimetypes-link-icons/" target="_blank">' . __( 'Official plugin page', self::$name ) . '</a> |
						<a href="http://wordpress.org/extend/plugins/mimetypes-link-icons/faq/" target="_blank">' . __( 'FAQ', self::$name ) . '</a> |
						<a href="http://wordpress.org/extend/plugins/mimetypes-link-icons/changelog/" target="_blank">' . __( 'Changelog', self::$name ) . '</a> |
						<a href="http://wordpress.org/support/plugin/mimetypes-link-icons" target="_blank">' . __( 'Support&nbsp;Forum', self::$name ) . '</a>
					</p>
				   <p><a href="https://github.com/eagerterrier/MimeTypes-Link-Icons" target="_blank">' . __( 'Github repository', self::$name ) . '</a></p>
				   <p><a href="http://blog.eagerterrier.co.uk/2010/10/holy-cow-ive-gone-and-made-a-mime-type-wordpress-plugin/" target="_blank">' . __( 'Blog post about this plugin', self::$name ) . '</a></p>
			';
		}


		/* *** PLUGIN ACTIVATION AND UPGRADING *** */

		/**
		 * Function used when activating and/or upgrading the plugin
		 * - Initial activate: Save version number to option
		 * - v 3.0: change hidden_classname from string to array
		 *
		 * @since 3.0
		 */
		function upgrade_options() {
			global $wp_version;

			/**
			 * Bail out early if the plugin can't be used... auto-deactivates plugin if requirements aren't met
			 * This switch will normally only run on activation.
			 */
			if( is_admin() && current_user_can( 'activate_plugins' ) ) {
				$deactivate = false;

				/* Test if the minimum required WP version is being used */
				if( true !== version_compare( $wp_version, self::MIN_WP_VERSION, '>=' ) ) {
					add_action( 'admin_notices', array( &$this, 'show_upgrade_wp_notice' ) );
					$deactivate = true;
				}

				/* Test if the minimum required PHP version is being used */
				if( true !== version_compare( PHP_VERSION, self::MIN_PHP_VERSION, '>=' ) ) {
					add_action( 'admin_notices', array( &$this, 'show_upgrade_php_notice' ) );
					$deactivate = true;
				}

				/* De-activate if minimum requirements not met */
				if( true === $deactivate ) {
					add_action( 'admin_init', array( &$this, 'deactivate_me' ), 1 );
					return;
				}
			}


//			$upgraded_settings = false;

			/**
			 * Upgrades for any version of this plugin lower than x.x
			 * N.B.: Version nr has to be hard coded to be future-proof, i.e. facilitate
			 * upgrade routines for various versions
			 */
			/* Settings upgrade for version 3.0 */
			if( !isset( $this->settings['version'] ) || version_compare( $this->settings['version'], '3.0', '<' ) ) {

				/* Change 'hidden_classname' from string to array to allow for more classnames
				   and validate the value */
				if( isset( $this->settings['hidden_classname'] ) && is_string( $this->settings['hidden_classname'] ) ) {
					$classnames = $this->validate_classnames( $this->settings['hidden_classname'] );
					if( false !== $classnames ) {
						$this->settings['hidden_classname'] = $classnames;
					}
					else {
						unset( $this->settings['hidden_classname'] );
					}
					unset( $classnames );
//					$upgraded_settings = true;
				}

				/* Change 'internal_domains' from string to array */
				if( isset( $this->settings['internal_domains'] ) && ( is_string( $this->settings['internal_domains'] ) && $this->settings['internal_domains'] !== '' ) ) {
					$this->settings['internal_domains'] = explode( ',', $this->settings['internal_domains'] );
				}
			}

			/**
			 * (Re-)Determine the site's domain on activation and on each upgrade
			 */
			$home_url = home_url();
			$start = ( strpos( $home_url, '://' ) + 3 );
			$this->settings['internal_domains'][] = $domain = substr( $home_url, $start, ( strpos( $home_url, '/', $start ) - $start ) );
			if( stripos( $domain, 'www.' ) === 0 ) {
				$this->settings['internal_domains'][] = str_ireplace( 'www.', '', $domain );
			}
			$this->settings['internal_domains'] = array_unique( $this->settings['internal_domains'] );
			unset( $home_url, $domain, $start );


			/* Update the settings */
			$this->settings['version'] = self::VERSION;
			$this->_get_set_settings( $this->settings );
			return;
		}


		/**
		 * Deactivate this plugin - does not work in all WP versions, but it's a start
		 *
		 * @return void
		 */
		function deactivate_me() {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}
		}

		/**
		 * Show upgrade WP notice if WP version is too low for this plugin to run
		 *
		 * @since 3.0
		 */
		function show_upgrade_wp_notice() {
			global $wp_version;
			echo '<div class=\"error\"><p>' . sprintf( __( 'Version %s of the <em>MimeType Link Icons</em> plugin requires WordPress %s+. You have WordPress %s installed. The plugin has been de-activated.', self::$name ), self::VERSION, self::MIN_WP_VERSION, $wp_version ) . '</p><p>' . sprintf( __( 'Please upgrade your WordPress installation to %s+. Using the latest version is always advisable (and not just for security reasons!).', self::$name ), self::MIN_WP_VERSION ) . '</p></div>';
		}

		/**
		 * Show upgrade PHP notice if PHP version is too low for this plugin to run
		 *
		 * @since 3.0
		 */
		function show_upgrade_php_notice() {
			echo '<div class=\"error\"><p>' . sprintf( __( 'Version %s of the <em>MimeType Link Icons</em> plugin requires PHP %s+. Your WordPress installation is running on PHP %s. The plugin has been de-activated.', self::$name ), self::VERSION, self::MIN_PHP_VERSION, PHP_VERSION ) . '</p><p>' . sprintf( __( 'Either ask your web host to upgrade PHP or alternatively you could install an <a %s>older version of this plugin</a>.', self::$name ), 'href="http://wordpress.org/extend/plugins/' . self::$name . '/developers/" target="_blank"') . '</p></div>';
		}



		/* *** HELPER METHODS *** */


		/**
		 * Intelligently set/get the plugin settings
		 *
		 * @since 3.0
		 *
		 * @static	bool|array	$original_settings	remember originally retrieved settings array for reference
		 * @param	array|null	$update				New settings to save to db - make sure the
		 *											new array is validated first!
		 * @return	void|bool	if an update took place: whether it worked
		 */
		function _get_set_settings( $update = null ) {
			static $original_settings = false;
			$updated = null;

			/* Do we have something to update ? */
			if( !is_null( $update ) ) {
				if( $update !== $original_settings ) {
					$updated = update_option( self::SETTINGS_OPTION, $update );
					$this->settings = $original_settings = $update;
				}
				else {
					$updated = true; // no update necessary
				}
				return $updated;
			}

			/* No update received or update failed -> get the option from db */
			if( ( is_null( $this->settings ) || false === $this->settings ) || ( false === is_array( $this->settings ) || 0 === count( $this->settings ) ) ) {
				// returns either the option array or false if option not found
				$option = get_option( self::SETTINGS_OPTION );

				if( $option === false ) {
					// Option was not found, set settings to the defaults
					$option = $this->defaults;
				}
				else {
					// Otherwise merge with the defaults array to ensure all options are always set
					$option = wp_parse_args( $option, $this->defaults );
				}
				$this->settings = $original_settings = $option;
				unset( $option );
			}

			/* Update the active_mimetypes array */
			$this->active_mimetypes = array();
			foreach( $this->mime_types as $mime_type ) {
				if( true === $this->settings['enable_' . $mime_type] ) {
					$this->active_mimetypes[] = $mime_type;
				}
			}
			unset( $mime_type );

            return;
		}


		/**
		 * Intelligently set/get the cached filesizes
		 *
		 * @since 3.0
		 *
		 * @static	bool|array		$original_settings	remember originally retrieved filesizes array
		 *												for reference
		 * @param	array|null		$update				New cache to save to db - make sure the new array
		 *												is validated first!
		 * @param	string|null		$key				file key to update the cache for
		 * @return	bool|void		if an update took place: whether it worked
		 */
		function _get_set_filesize_cache( $update = null, $key = null ) {
			static $original_cache = false;
			$updated = null;

			/* Do we have something to update ? */
			if( !is_null( $update ) ) {
				// Is this a complete or a one field update ?
				if( !is_null( $key ) ) {
					$new_cache = $this->cache;
					$new_cache[$key] = array(
						'size'	=>	$update, // file size or false if size could not be determined
						'time'	=>	time(),
					);
					$update = $new_cache;
					unset( $new_cache );
				}
				if( $update !== $original_cache ) {
					$updated = update_option( self::CACHE_OPTION, $update );
					$this->cache = $original_cache = $update;
				}
				else {
					$updated = true; // no update necessary
				}
				return $updated;
			}

			/* No update received or update failed -> get the option from db */
			if( ( is_null( $this->cache ) || false === $this->cache ) || ( false === is_array( $this->cache ) || 0 === count( $this->cache ) ) ) {
				// returns either the option array or false if option not found
				$cache = get_option( self::CACHE_OPTION );
				// Default to an empty array rather than to false
				if( $cache === false ) {
					$cache = array();
				}
				$this->cache = $original_cache = $cache;
				unset( $cache );
			}

            return;
		}


		/**
		 * Test a boolean PHP ini value
		 *
		 * @since 3.0
		 * @param string	$a	key of the value you want to get
		 * @return bool
		 */
		function ini_get_bool( $a ) {
			$b = ini_get( $a );

			switch( strtolower( $b ) ) {
				case 'on':
				case 'yes':
				case 'true':
					return 'assert.active' !== $a;

				default:
					return (bool) (int) $b;
			}
		}


		/**
		 * Resolve relative urls
		 * Example: '/cool/yeah/../zzz' would become '/cool/zzz'
		 *
		 * @since 3.0
		 * @var		string 	$url	relative url
		 * @return	string
		 */
		function resolve_relative_url( $url ) {
			return preg_replace( '`\w+/\.\./`', '', $url );
		}


		/**
		 * Make sure all the directory separators are the same
		 *
		 * @since 3.0
		 * @var		string 	$url
		 * @return	string
		 */
		function sync_dir_sep( $url ) {
			return str_replace( array( '/', '\\' ), DIRECTORY_SEPARATOR, $url );
		}




		/* *** FRONT-END: DISPLAY METHODS *** */

		/**
		 * Add mimetype icon classes and relevant style rules to content
		 *
		 * @param $content
		 * @return string
		 */
		function mimetype_to_icon( $content ) {

			if( 0 < count( $this->active_mimetypes ) ) {

				$mimetypes = array_map( 'preg_quote' , $this->active_mimetypes, array_fill( 0 , count( $this->active_mimetypes ) , '`' ) );
				$mimetypes = implode( '|', $mimetypes );

				if( 0 < preg_match_all( '`<a .*?(class=["\']([^"\']*)["\'])?.*?(href=["\']([^"\'#]+\.(' . $mimetypes . '))(?:#[^\'" ]+["\']|["\'])).*?(class=["\']([^"\']*)["\'])?[^>]*>`i', $content, $matches, PREG_SET_ORDER ) ) {
					/* Returns:
						[0] full <a ... > tag
						[1] empty string or class="classnames"
						[2] empty string or classnames
						[3] href="url"
						[4] url
						[5] file extension / mimetype
						[6] not always set : class="classname" if set after href
						[7] not always set : empty string or classnames
					*/

					foreach( $matches as $match ) {

						$class_string = null;
						$classnames = null;

						/* Find the class string & names if they exist */
						if( '' !== $match[1] ) {
							$class_string = $match[1];
							$classnames = $match[2];
						}
						else if( isset( $match[6] ) && '' !== $match[6] ) {
							$class_string = $match[6];
							$classnames = ( isset( $match[7] ) ? $match[7] : '' );
						}

						/* Test for 'hidden classes' */
						if( ( true === $this->settings['enable_hidden_class'] && ( is_array( $this->settings['hidden_classname'] ) && 0 < count( $this->settings['hidden_classname'] ) ) ) && ( !is_null( $classnames ) && '' !== $classnames ) ) {
							// We have existing classnames on the anchor
							$classes = explode( ' ', $classnames );
							foreach( $classes as $class ) {
								if( true === in_array( $class, $this->settings['hidden_classname'] ) ) {
									// Ok, we have a classname we should skip: skip out of the current match-item onto the next
									continue 2;
								}
							}
							unset( $classes, $class );
						}

						/* Still here, so we should do some work on this link ;-) */
						$replace = $match[0];

						/* Add the filesize info and styles */
						if( true === $this->settings['show_file_size'] ) {

							$filesize = $this->get_filesize( $match[4] );

							if( false !== $filesize ) {

								/* Add the rel attribute to the replacement anchor string */
								$replace = str_replace( $match[3], $match[3] . ' rel="mtli_filesize' . str_replace( array( '.', ' ' ), '', $filesize ) . '"', $replace );

								/* Add the css rule */
								$css_filesize_string = apply_filters( 'mtli_filesize', '(' . $filesize . ')' );
								$this->filesize_styles[] = 'a[rel~="mtli_filesize' . str_replace( array( '.', ' ' ), '', $filesize ) . '"]:after {content:" ' . $css_filesize_string . '"}';
							}
							unset( $filesize, $css_filesize_string );
						}


						/* Add the attachment classes and avoid adding a second class attribute */
						if( false === $this->settings['enable_async'] ) {

							$mtli_classes = 'mtli_attachment mtli_' . strtolower( $match[5] );
							if( is_null( $classnames ) || '' === $classnames ) {
								$new_classnames = $mtli_classes;
							}
							else {
								$new_classnames = $classnames . ' ' . $mtli_classes;
							}

							if( is_null( $class_string ) ) { // no previous class string found
								$replace = str_replace( $match[3], $match[3] . ' class="' . $new_classnames . '"', $replace );
							}
							else if( is_null( $classnames ) || '' === $classnames ) { // empty previous class string
								$replace = str_replace( $class_string, substr( $class_string, 0, -1 ) . $new_classnames . substr( $class_string, -1 ), $replace );
							}
							else { // add to existing classes
								$replace = str_replace( $class_string, str_replace( $classnames, $new_classnames, $class_string ), $replace );
							}
							unset( $mtli_classes, $new_classnames );
						}

						/* Replace the actual anchor with the changed version */
						$content = str_replace( $match[0], $replace, $content );

						unset( $class_string, $classnames, $replace );
					}
					unset( $match );
				}
				unset( $mimetypes, $matches );
			}

			/* Add filesize CSS rules to the content if we have any */
			if( true === $this->settings['show_file_size'] && ( is_array( $this->filesize_styles ) && 0 < count( $this->filesize_styles ) ) ) {

				$styles = array_unique( $this->filesize_styles );
				$styles = implode( '', $styles );
				$content = $content . '<style type="text/css">' . $styles . '</style>';
				unset( $styles );
			}


			/* Close curl resource if one has been opened */
			if( is_resource( $this->curl ) ) {
				curl_close( $this->curl );
			}


			return $content;
		}


		/**
		 * Get filesize from cache if applicable otherwise request filesize from file
		 *
		 * @param   string  $url
		 * @return  bool|string filesize string or false if no filesize could be determined
		 */
		function get_filesize( $url ) {
			static $has_cache = false;

			// Efficiency - only retrieve the cache once
			if( true === $this->settings['use_cache'] && false === $has_cache ) {
				$this->_get_set_filesize_cache();
				$has_cache = true;
			}

			if( !is_string( $url ) || $url === '' ) {
				return false;
			}


			/* Maybe get the cached value if still within the cache time interval */
			$cache_key = str_replace( '/', '__', $url );
			$cache_key = sanitize_key( $cache_key );
			if( true === $this->settings['use_cache'] && ( isset( $this->cache[$cache_key] ) && $this->cache[$cache_key]['time'] > ( time() - $this->settings['cache_time'] ) ) ) {
				$filesize = $this->cache[$cache_key]['size'];
			}

			/* Otherwise retrieve the filesize from the actual file */
			else {
				$filesize = $this->retrieve_filesize( $url );

				/* Maybe cache the retrieved value */
				if( true === $this->settings['use_cache'] ) {
					$this->_get_set_filesize_cache( $filesize, $cache_key );
				}
			}
			unset( $cache_key );

			return $this->human_readable_filesize( $filesize );
		}


		/**
		 * Negotiate whether a file is local or remote and retrieve the filesize in a situation appropriate manner
		 * @todo	Maybe use WP FileSystem API for filesize retrieval, though this is probably overkill
		 *
		 * @param   string  $url
		 * @return  bool|string
		 */
		function retrieve_filesize( $url ) {
			static $home_path = null; // has trailing slash
			static $site_path = null; // has trailing slash
			static $wp_upload = null;
			static $path_to_home = null;
			static $site_root = null;
			static $path_to_upload = '';


			/* Fill the statics - only run first time this method is called */
			if( ( is_null( $home_path ) && is_null( $site_path ) ) && is_null( $wp_upload ) ) {
				$home_url = home_url();
				$site_url = site_url();
				$wp_upload = wp_upload_dir();
				$home_path = $site_path = $this->sync_dir_sep( ABSPATH );
				if( $home_url !== $site_url ) {
					$diff = str_replace( $home_url, '', $site_url );
					$home_path = str_replace( $this->sync_dir_sep( $diff ), '', $site_path );
					unset( $diff );
				}
				$parsed_url = parse_url( $home_url );
				if( $parsed_url !== false && ( isset( $parsed_url['path'] ) && $parsed_url['path'] !== '' ) ) {
					$path_to_home = $parsed_url['path'];
					$site_root = str_replace( $this->sync_dir_sep( $path_to_home ), '', $site_path );
				}
				$parsed_url = parse_url( $wp_upload['baseurl'] );
				if( $parsed_url !== false && ( isset( $parsed_url['path'] ) && $parsed_url['path'] !== '' ) ) {
					$path_to_upload = str_replace( $path_to_home, '', $parsed_url['path'] );
				}
				unset( $home_url, $site_url, $parsed_url );
			}


			/* Negotiate local versus remote file */
			$local = false;
			$remote = false;

			/* Is this a relative url starting with / \ or . ? */
			if( true === in_array( substr( $url, 0, 1 ), array( '/', '\\', '.' ) ) ) {
				$rel_url = $this->resolve_relative_url( $url );
				$local = true;
			}
			else if( false !== $this->is_own_domain( $url ) ) {
				$rel_url = $this->is_own_domain( $url );
				if( !is_null( $path_to_home ) ) {
					$pos = stripos( $rel_url, $path_to_home );
					$rel_url = substr( $rel_url, ( $pos + strlen( $path_to_home ) ) );
				}
				$local = true;
			}
			else {
				if( 0 === strpos( $url, 'http://' ) ) {
					$remote = true;
				}
				/* Most likely external url, but could in rare situations be local: think 'favicon.ico' */
				else {
					$rel_url = $this->resolve_relative_url( $url );
					$local = true;

					$url = 'http://' . $url;
					$remote = true;
				}
			}


			/* Try and get the filesize for a local file */
			if( true === $local && isset( $rel_url ) ) {

				$rel_url = explode( '#', $rel_url );
				$rel_url = explode( '?', $rel_url[0] );
				$rel_url = $rel_url[0];
				$rel_url =	$this->sync_dir_sep( $rel_url );
				$rel_url = ( 0 === strpos( $rel_url, DIRECTORY_SEPARATOR ) ? substr( $rel_url, 1 ) : $rel_url ); // remove potential slash from the start

				switch( true ) {

					case file_exists( $home_path . $rel_url ):
						return filesize( $home_path . $rel_url );

					case file_exists( $site_path . $rel_url ):
						return filesize( $site_path . $rel_url );

					case ( !is_null( $path_to_upload ) && file_exists( $this->sync_dir_sep( $wp_upload['basedir'] ) . substr( $rel_url, ( stripos( $rel_url, $path_to_upload ) + strlen( $path_to_upload ) ) ) ) ):
						return filesize( $this->sync_dir_sep( $wp_upload['basedir'] ) . $rel_url );

					case ( !is_null( $site_root ) && file_exists( $site_root . $rel_url ) ):
						return filesize( $site_root . $rel_url );

					case file_exists( $rel_url ):
						return filesize( $rel_url );

					case file_exists( $url ):
						return filesize( $url );

					case file_exists( $url ):
						return filesize( $url );

					case file_exists( $url ):
						return filesize( $url );

					default:
						// Try getting the filesize using the remote file methods
						$remote = true;
						break;
				}
			}

			// Still here, so this is definitely not a local file
			/* Try and get the filesize for a remote file */
			if( true === $remote ) {

				$filesize = $this->get_remote_filesize_via_curl( $url );
				if( false === $filesize ) { // redundancy in case curl fails or gets blocked
					$filesize = $this->get_remote_filesize_via_headers( $url );
					if( false === $filesize && true === $local ) {
						// Can't seem to resolve this url
						// -> show/log an error message for the web-savvy, silently fail for everyone else
						// @todo Should we only log an error message for local files or for all files where we couldn't get the filesize ? Let's start with local and see the response
						trigger_error( sprintf( __( 'MimeTypes Link Icons can\'t resolve the following url, please make sure the file referred to exists. URL: <strong>%s</strong>', self::$name ), esc_attr( $url ) ), E_USER_NOTICE );
					}
				}
				return $filesize;
			}

			// Redundant
			return false;
		}


		/**
		 * Test whether a given url is local
		 *
		 * @param     string        $url
		 * @return    string|bool    relative local url or false if not local
		 */
		function is_own_domain( $url ) {
			static $results; // remember results for re-use

			if( isset( $results[$url] ) ) {
				return $results[$url];
			}

			$results[$url] = false;

			if( is_array( $this->settings['internal_domains'] ) && 0 < count( $this->settings['internal_domains'] ) ) {

				foreach( $this->settings['internal_domains'] as $domain ) {
					$pos = stripos( $url, $domain );
					if( false !== $pos ) {
						$results[$url] = substr( $url, ( $pos + strlen( $domain ) ) );
						return $results[$url];
					}
					unset( $pos );
				}
				unset( $domain );
			}

			// Still here, redundancy test
			$domain = str_replace( 'www.', '', $_SERVER['SERVER_NAME'] );
			$pos = stripos( $url, $domain );
			if( false !== $pos ) {
				$results[$url] = substr( $url, ( $pos + strlen( $domain ) ) );
			}
			unset( $domain, $pos );

			return $results[$url];
		}


		/**
		 * Get filesize of a remote file via a curl connection
		 *
		 * @param $url
		 * @return bool|int|mixed
		 */
		function get_remote_filesize_via_curl( $url ) {

			/* Efficiency - only initialize once and keep the resource for re-use */
			if( false === is_resource( $this->curl ) ) {

				$this->curl = curl_init();

				// Issue a HEAD request
				curl_setopt( $this->curl, CURLOPT_RETURNTRANSFER, true );
				curl_setopt( $this->curl, CURLOPT_HEADER, true );
				curl_setopt( $this->curl, CURLOPT_NOBODY, true );
				// Follow any redirects
				$open_basedir = ini_get( 'open_basedir' );
				if( false === $this->ini_get_bool( 'safe_mode' ) && ( ( is_null( $open_basedir ) || empty( $open_basedir ) ) || $open_basedir == 'none' ) ) {
					curl_setopt( $this->curl, CURLOPT_FOLLOWLOCATION, true );
					curl_setopt( $this->curl, CURLOPT_MAXREDIRS, 5 );
				}
				unset( $open_basedir );
				// Bypass servers which refuse curl
				curl_setopt( $this->curl, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)' );
				// Set a time-out
				curl_setopt( $this->curl, CURLOPT_CONNECTTIMEOUT, 30 );
				// Stop as soon as an error occurs
//				curl_setopt( $this->curl, CURLOPT_FAILONERROR, true );
			}

			$filesize = false;

			/* Get the http headers for the given url */
			curl_setopt( $this->curl, CURLOPT_URL, $url );
			$header = curl_exec( $this->curl );

			/* If we didn't get an error, interpret the headers */
			if( ( false !== $header && !empty( $header ) ) && ( 0 === curl_errno( $this->curl ) ) ) {

				/* Get the http status */
				$statuscode = curl_getinfo( $this->curl, CURLINFO_HTTP_CODE );
				if( false === $statuscode && preg_match( '/^HTTP\/1\.[01] (\d\d\d)/', $header, $matches ) ) {
					$statuscode = (int) $matches[1];
				}

				/* Only get the filesize if we didn't get an http error response */
				if( 400 > $statuscode ) {
					$filesize = (int) curl_getinfo( $this->curl, CURLINFO_CONTENT_LENGTH_DOWNLOAD );
					/* Redundancy if curl_getinfo() fails */
					if( ( false === $filesize || -1 === $filesize ) && preg_match( '/Content-Length: (\d+)/i', $header, $matches ) ) {
						$filesize = (int) $matches[1];
					}
				}
				unset( $statuscode );
			}
			unset( $header );

			//curl_close( $this->curl ); -> will be done from main function so we can re-use the connection
			return $filesize;
		}


		/*
		@todo maybe change the stream context ? if so, add new property $stream_default with null value, set
		the $default from the function and reverse at the end of $this->mimetype_to_icons(), just like curl resource closing

		// By default get_headers uses a GET request to fetch the headers. If you
		// want to send a HEAD request instead, you can do so using a stream context:
		$default = stream_context_get_default($default_opts);
		stream_context_get_default(
			array(
				'http' => array(
					'method' => 'HEAD'
				)
			)
		);
		$headers = get_headers('http://example.com');
		stream_context_get_default($default); // return to original

		//stream_context_set_default should be used, but is only PHP5.3+
		*/
		/**
		 * Get filesize of a remote file via a header request
		 *
		 * @param $url
		 * @return bool
		 */
		function get_remote_filesize_via_headers( $url ) {

			$filesize = false;

			$head = @get_headers( $url, true );

			if( false !== $head && is_array( $head ) ) {

				$head = array_change_key_case( $head );
				// Disregard files which return an error status
				if( 400 > intval( substr( $head[0], 9, 3 ) ) ) {
					// Deal with redirected urls (where get_headers() will return an array for redundant headers)
					if( isset( $head['content-length'] ) && is_string( $head['content-length'] ) ) {
						$filesize = (int) $head['content-length'];
					}
					else if( isset( $head['content-length'] ) && is_array( $head['content-length'] ) ) {
						$filesize = (int) $head['content-length'][(count($head['content-length'])-1)];
					}
				}
			}
			unset( $head );
			return $filesize;
		}



		/**
		 * Creates a human readable file size string
		 * - Returns <i>false</i> is the passed parameter is not an integer or a numeric string
		 *
		 * @uses 	$this->byte_suffixes		for the byte suffixes
		 * @param	int				$filesize	filesize in bytes
		 * @return	string|bool 	human readable filesize string
		 * 							or false if the passed variable was not an integer
		 **/
		function human_readable_filesize( $filesize ) {
			static $count;

			if( !isset( $count ) ) { // Will only run once per execution
				$count = count( $this->byte_suffixes );
			}

			if( is_int( $filesize ) && 0 < $filesize ) {

				// Get the figure to use in the string
				for( $i = 0; ( $i < $count && 1024 <= $filesize ); $i++ ) {
					$filesize = $filesize / 1024;
				}

				// Return the formatted number with the appropriate suffix and required precision
				if( $i === 0 ) {
					return number_format_i18n( $filesize, 0 ) . ' ' . $this->byte_suffixes[$i];
				}
				else {
					return number_format_i18n( $filesize, $this->settings['precision'] ) . ' ' . $this->byte_suffixes[$i];
				}
			}
			else {
				return false;
			}
		}





		/* *** BACK-END: OPTIONS PAGE METHODS *** */

		/**
		 * Validated the settings received from our options page
		 *
		 * @param  array    $received     Our $_POST variables
		 * @return array    Cleaned settings to be saved to the db
		 */
		function validate_options( $received ) {
			$clean = $this->settings;

			/* General settings */
			if( isset( $received['image_size'] ) && true === in_array( $received['image_size'], $this->sizes ) ) {
				$clean['image_size'] = $received['image_size'];
			}
			else { // Edge case: should never happen
				add_settings_error( self::SETTINGS_OPTION, 'image_size', __( 'Invalid image size received', self::$name ) . ', ' . __( 'the value has been reset to the default.', self::$name ), 'error' );
			}

			if( isset( $received['image_type'] ) && true === in_array( $received['image_type'], $this->image_types ) ) {
				$clean['image_type'] = $received['image_type'];
			}
			else {// Edge case: should never happen
				add_settings_error( self::SETTINGS_OPTION, 'image_size', __( 'Invalid image type received', self::$name ) . ', ' . __( 'the value has been reset to the default.', self::$name ), 'error' );
			}

			if( isset( $received['leftorright'] ) && true === array_key_exists( $received['leftorright'], $this->alignments ) ) {
				$clean['leftorright'] = $received['leftorright'];
			}
			else { // Edge case: should never happen
				add_settings_error( self::SETTINGS_OPTION, 'leftorright', __( 'Invalid image placement received', self::$name ) . ', ' . __( 'the value has been reset to the default.', self::$name ), 'error' );
			}


			/* Images settings */
			foreach( $this->mime_types as $mimetype ) {
				$clean['enable_' . $mimetype] = ( ( isset( $received['enable_' . $mimetype] ) && 'true' === $received['enable_' . $mimetype] ) ? true : false );
			}


			/* Advanced settings */
			$clean['enable_hidden_class'] = ( ( isset( $received['enable_hidden_class'] ) && 'true' === $received['enable_hidden_class'] ) ? true : false );

			if( isset( $received['hidden_classname'] ) && '' !== $received['hidden_classname'] ) {
				$classnames = $this->validate_classnames( $received['hidden_classname'] );
				if( false !== $classnames ) {
					$clean['hidden_classname'] = $classnames;
					if( $received['hidden_classname'] !== implode( ',', $clean['hidden_classname'] ) && $received['hidden_classname'] !== implode( ', ', $clean['hidden_classname'] ) ) {
						add_settings_error( self::SETTINGS_OPTION, 'hidden_classname', __( 'One or more invalid classname(s) received, the values have been cleaned - this may just be the removal of spaces -, please check.', self::$name ), 'updated' );
					}
				}
				else { // Edge case: should never happen
					add_settings_error( self::SETTINGS_OPTION, 'hidden_classname', __( 'No valid classname(s) received', self::$name ) . ', ' . __( 'the value has been reset to the default.', self::$name ), 'error' );
				}
			}


			$clean['show_file_size'] = ( ( isset( $received['show_file_size'] ) && 'true' === $received['show_file_size'] ) ? true : false );

			if( ( isset( $received['precision'] ) && '' !== $received['precision'] ) && ( true === ctype_digit( $received['precision'] ) && ( intval( $received['precision'] ) == $received['precision'] ) ) ) {
				$clean['precision'] = (int) $received['precision'];
			}
			else{
				add_settings_error( self::SETTINGS_OPTION, 'precision', __( 'Invalid rounding precision received', self::$name ) . ', ' . __( 'the value has been reset to the default.', self::$name ), 'error' );
			}

			$clean['use_cache'] = ( ( isset( $received['use_cache'] ) && 'true' === $received['use_cache'] ) ? true : false );
			// Delete the filesize cache if the cache option was unchecked to make sure a fresh cache will be build if and when the cache option would be checked again
			if( false === $clean['use_cache'] && $clean['use_cache'] !== $this->settings['use_cache'] ) {
				delete_option( self::CACHE_OPTION );
			}

			// Value received is hours, needs to be converted to seconds before save
			if( ( isset( $received['cache_time'] ) && '' !== $received['cache_time'] ) && ( true === ctype_digit( $received['cache_time'] ) && ( intval( $received['cache_time'] ) == $received['cache_time'] ) ) ) {
				$clean['cache_time'] = ( (int) $received['cache_time'] * 60 * 60 );
			}
			else{
				add_settings_error( self::SETTINGS_OPTION, 'cache_time', __( 'Invalid cache time received', self::$name ) . ', ' . __( 'the value has been reset to the default.', self::$name ), 'error' );
			}


			$clean['enable_async'] = ( ( isset( $received['enable_async'] ) && 'true' === $received['enable_async'] ) ? true : false );


			/* Always update the version to current ?*/
			$clean['version'] = self::VERSION;

			return $clean;
		}


		/**
		 * Validate received classnames and parse them from a string to an array
		 * Returns false if received value is not a string or empty
		 *
		 * @usedby validate_options() and upgrade_options()
		 * @param string $classnames
		 * @return array|bool
		 */
		function validate_classnames( $classnames = '' ) {
			$return = false;

			if( is_string( $classnames ) && '' !== $classnames ) {
				$classnames = sanitize_text_field( $classnames );
				$classnames = explode( ',', $classnames );
				$classnames = array_map( 'trim', $classnames );
				$classnames = array_map( 'sanitize_html_class', $classnames );
				$classnames = array_filter( $classnames ); // removes empty strings
				if( is_array( $classnames ) && 0 < count( $classnames ) ) {
					$return = $classnames;
				}
			}
			return $return;
		}




		/*
		$url = wp_nonce_url('themes.php?page=example','example-theme-options');
		if (false === ($creds = request_filesystem_credentials($url, '', false, false, null) ) ) {
			return; // stop processing here
		}

		The request_filesystem_credentials() call takes five arguments.

			The URL to which the form should be submitted (a nonced URL to a theme page was used in the example above)
			A method override (normally you should leave this as the empty string: "")
			An error flag (normally false unless an error is detected, see below)
			A context directory (false, or a specific directory path that you want to test for access)
			Form fields (an array of form field names from your previous form that you wish to "pass-through" the resulting credentials form, or null if there are none)

		The request_filesystem_credentials call will test to see if it is capable of writing to the local filesystem directly without credentials first. If this is the case, then it will return true and not do anything. Your code can then proceed to use the WP_Filesystem class.

		The request_filesystem_credentials call also takes into account hardcoded information, such as hostname or username or password, which has been inserted into the wp-config.php file using defines. If these are pre-defined in that file, then this call will return that information instead of displaying a form, bypassing the form for the user.

		If it does need credentials from the user, then it will output the FTP information form and return false. In this case, you should stop processing further, in order to allow the user to input credentials. Any form fields names you specified will be included in the resulting form as hidden inputs, and will be returned when the user resubmits the form, this time with FTP credentials.

		Note: Do not use the reserved names of hostname, username, password, public_key, or private_key for your own inputs. These are used by the credentials form itself. Alternatively, if you do use them, the request_filesystem_credentials function will assume that they are the incoming FTP credentials.

		When the credentials form is submitted, it will look in the incoming POST data for these fields, and if found, it will return them in an array suitable for passing to WP_Filesystem, which is the next step.
		Initializing WP_Filesystem_Base

		Before the WP_Filesystem can be used, it must be initialized with the proper credentials. This can be done like so:

		if ( ! WP_Filesystem($creds) ) {
			request_filesystem_credentials($url, '', true, false, null);
			return;
		}

		First you call the WP_Filesystem function, passing it the credentials from before. It will then attempt to verify the credentials. If they are good, then it will return true. If not, then it will return false.

		In the case of bad credentials, the above code then makes another call to request_filesystem_credentials(), but this time with the error flag set to true. This forces the function to display the form again, this time with an error message for the user saying that their information was incorrect. The user can then re-enter their information and try again.
		Using the WP_Filesystem_Base Class

		Once the class has been initialized, then the global $wp_filesystem variable becomes defined and available for you to use. The WP_Filesystem_Base class defines several methods you can use to read and write local files. For example, to write a file, you could do this:

		global $wp_filesystem;
		$wp_filesystem->put_contents(
		  '/tmp/example.txt',
		  'Example contents of a file',
		  FS_CHMOD_FILE // predefined mode settings for WP files
		);

		*/


		/**
		 * Display our options page using the Settings API
		 * @todo Decide which icon next to the title is most appropriate - options, links or media icon ?
		 * options: id="icon-options-general"
		 * media: id="icon-upload"
		 * links: id="icon-link-manager"
		 */
		function display_options_page() {

			if( !current_user_can( self::REQUIRED_CAP ) ) {
				/* TRANSLATORS: no need to translate - standard WP core translation will be used */
				wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
			}

			echo '
		<div class="wrap">
		<div class="icon32" id="icon-options-general"></div>
		<h2>' . __( 'MimeType Link Icons', self::$name ) . '</h2>
		<form action="options.php" method="post"' . ( ( defined( 'DB_CHARSET' ) && DB_CHARSET === 'utf8' ) ? ' accept-charset="utf-8"' : '' ) . '>';

			settings_fields( self::SETTINGS_OPTION . '-group' );
			do_settings_sections( self::$name );
			submit_button();

			echo '
		</form>';

			if( WP_DEBUG ) {
				print '<pre>';
				print_r( $this->settings );
				print '</pre>';
			}
		}


		/**
		 * Display the General Settings section of our options page
		 */
		function do_settings_section_general() {

			echo '
			<fieldset class="options" name="general">
				<table cellspacing="2" cellpadding="5" class="editform form-table">
					<tr>
						<th nowrap valign="top" width="33%">
							<label for="image_size">' . esc_html__( 'Image Size', self::$name ) . '</label>
						</th>
						<td>
							<select name="' . esc_attr( self::SETTINGS_OPTION . '[image_size]' ) . '" id="image_size">';

			foreach( $this->sizes as $v ) {
				echo '
								<option value="' . esc_attr( $v ) . '" ' . selected( $this->settings['image_size'], $v, false ) . '>' . esc_html( $v . 'x' . $v ) . '</option>';
			}
			unset( $v );

			echo '
							</select>
						</td>
					</tr>
					<tr>' /* @todo maybe change this to radio buttons ? if so, remove th label */ . '
						<th nowrap valign="top" width="33%">
							<label for="image_type">' . esc_html__( 'Image Type', self::$name ) . '</label>
						</th>
						<td>
							<select name="' . esc_attr( self::SETTINGS_OPTION . '[image_type]' ) . '" id="image_type">';

			foreach( $this->image_types as $v ) {
				echo '
									<option value="' . esc_attr( $v ) . '" ' . selected( $this->settings['image_type'], $v, false ) . '>' . esc_html( $v ) . '</option>';
			}
			unset( $v );

			echo '
							</select>
						</td>
					</tr>
					<tr>' /* @todo maybe change this to radio buttons ? if so, remove th label */ . '
						<th nowrap valign="top" width="33%">
							<label for="leftorright">' . esc_html__( 'Display images on left or right', self::$name ) . '</label>
						</th>
						<td>
							<select name="' . esc_attr( self::SETTINGS_OPTION . '[leftorright]' ) . '" id="leftorright">';

			foreach( $this->alignments as $k => $v ) {
				echo '
									<option value="' . esc_attr( $k ) . '" ' . selected( $this->settings['leftorright'], $k, false ) . '>' . esc_html( $v ) . '</option>';
			}
			unset( $k, $v );

			echo '
							</select>
						</td>
					</tr>
				</table>
			</fieldset>';
		}


		/**
		 * Display the Image Settings section of our options page
		 */
		function do_settings_section_images() {

			echo '
			<fieldset class="options" name="images" id="images">
				<table cellspacing="2" cellpadding="5" class="editform form-table image-table">';

			$count = count( $this->mime_types );
			$rows = ceil( $count / self::NR_OF_COLUMNS );
//			$last_row = $count % self::NR_OF_COLUMNS;

			// Make sure mimetypes are always sorted alphabetically
			uksort( $this->mime_types, 'strnatcasecmp' );

			for( $i = 0; $i < $rows; $i++ ) {

				echo '
					<tr>';

				for( $j = 0; $j < self::NR_OF_COLUMNS; $j++ ) {

					$index = ( ( $j * $rows )+ $i );

					// Normal cell
					if( isset( $this->mime_types[$index] ) ) {
						$mime_type = $this->mime_types[$index];
						echo '
						<th nowrap valign="top" width="33%">
							<label for="' . esc_attr( 'enable_' . $mime_type ) . '">' . sprintf( __( 'Add images to <strong>%s</strong> uploads/files', self::$name ), $mime_type ) . '</label>
						</th>
						<td style="width:24px;"><img src="' . esc_url( self::$url . '/images/' . $mime_type . '-icon-24x24.png' ) . '" alt="' . esc_attr( sprintf( __( '%s icon', self::$name ), $mime_type ) ) . '" /></td>
						<td>
							<input type="checkbox" name="' . esc_attr( self::SETTINGS_OPTION . '[enable_' . $mime_type . ']' ) . '" id="' . esc_attr( 'enable_' . $mime_type ) . '" value="true" ' . checked( $this->settings['enable_' . $mime_type], true, false ) . ' />
						</td>';
						unset( $mime_type );
					}
					// Empty cell in the last row
					else {
						echo '
						<th>&nbsp;</th>
						<td>&nbsp;</td>
						<td>&nbsp;</td>';
					}
					unset( $index );
				}
				unset( $j );

				echo '
					</tr>';
			}
			unset( $i, $count, $rows );

			echo '

				</table>
			</fieldset>';
		}


		/**
		 * Display the Advanced Settings section of our options page
		 */
		function do_settings_section_advanced() {

			echo '
			<fieldset class="options advanced-1" name="advanced-1">
				<legend>' . __( 'Enable/Disable classnames?', self::$name ) . '</legend>
				<table width="100%" cellspacing="2" cellpadding="5" class="editform form-table">
					<tr>
						<td><label for="enable_hidden_class"><input type="checkbox" name="' . esc_attr( self::SETTINGS_OPTION . '[enable_hidden_class]' ) . '" id="enable_hidden_class" value="true" ' . checked( $this->settings['enable_hidden_class'], true, false ) . ' /> ' . __( 'Tick this box to have one or more <i>classname(s)</i> that will disable the mime type links (ie: around an image or caption).', self::$name ) . '</label></td>
					</tr>
					<tr>
						<td><label for="hidden_classname">' . esc_html__( 'You can change the classname(s) by editing the field below. If you want to exclude several classnames, separate them with a comma (,).', self::$name ) . '</label></td>
					</tr>
					<tr>
						<td><input type="text" name="' . esc_attr( self::SETTINGS_OPTION . '[hidden_classname]' ) . '" id="hidden_classname" value="' . esc_attr( implode( ', ', $this->settings['hidden_classname'] ) ) . '" /></td>
					</tr>
				</table>
			</fieldset>

			<fieldset class="options advanced-2" name="advanced-2">
				<legend>' . esc_html__( 'Show File Size?', self::$name ) . '</legend>
				<table width="100%" cellspacing="2" cellpadding="5" class="editform form-table">
					<tr>
						<td><label for="show_file_size"><input type="checkbox" name="' . esc_attr( self::SETTINGS_OPTION . '[show_file_size]' ) . '" id="show_file_size" value="true" ' . checked( $this->settings['show_file_size'], true, false ) . ' /> ' . __( 'Display the <i>file size</i> of the attachment/linked file.', self::$name ) . '</label></td>
						<td>
							<label for="precision">' . esc_html__( 'File size rounding precision:', self::$name ) . '
							<input type="text" name="' . esc_attr( self::SETTINGS_OPTION . '[precision]' ) . '" id="precision" value="' . esc_attr( $this->settings['precision'] ) . '" /> ' . esc_html__( 'decimals', self::$name ) . '</label><br />
							<small><em>sizes less than 1kB will always have 0 decimals</em></small>
						</td>
					</tr>
					<tr>
						<td colspan="2">' . __( 'Retrieving the file sizes of (external) files can be slow. If the file sizes of the files you link to do not change very often, you may want to cache the results. This will result in faster page loading for most end-users of your website.', self::$name ) . '</td>
					</tr>
					<tr>
						<td><label for="use_cache"><input type="checkbox" name="' . esc_attr( self::SETTINGS_OPTION . '[use_cache]' ) . '" id="use_cache" value="true" ' . checked( $this->settings['use_cache'], true, false ) . ' /> ' . __( 'Cache retrieved file sizes.', self::$name ) . '</label></td>
						<td>
							<label for="cache_time">' . esc_html__( 'Amount of time to cache retrieved file sizes:', self::$name ) . '
							<input type="text" name="' . esc_attr( self::SETTINGS_OPTION . '[cache_time]' ) . '" id="cache_time" value="' . esc_attr( round( $this->settings['cache_time'] / ( 60 * 60 ), 0 ) ) . '" /> ' . esc_html__( 'hours', self::$name ) . '</label>
						</td>
					</tr>
				</table>
			</fieldset>

			<fieldset class="options advanced-3" name="advanced-3">
				<legend>' . esc_html__( 'Enable Asynchronous Replacement?', self::$name ) . '</legend>
				<table width="100%" cellspacing="2" cellpadding="5" class="editform form-table">
					<tr>
						<td>' . esc_html__( 'Some themes or plugins may conflict with this plugin. If you find you are having trouble you can switch on asynchronous replacement which (instead of PHP) uses JavaScript to find your links.', self::$name ) . '</td>
					</tr>
					<tr>
						<td><label for="enable_async"><input type="checkbox" name="' . esc_attr( self::SETTINGS_OPTION . '[enable_async]' ) . '" id="enable_async" value="true" ' . checked( $this->settings['enable_async'], true, false ) . ' /> ' . __( 'Tick box to enable <i>asynchronous replacement</i>.', self::$name ) . '</label></td>
					</tr>
				</table>
			</fieldset>';
		}


	} /* End of class */


	/* Initialize the static variables */
	mimetypes_link_icons::init_statics();

	/* Instantiate our class */
	add_action( 'plugins_loaded', 'mimetypes_link_icons_init' );

	if( !function_exists( 'mimetypes_link__icons_init' ) ) {
		function mimetypes_link_icons_init() {
			$GLOBALS['mimetypes_link_icons'] = new mimetypes_link_icons();
		}
	}



	if( !function_exists( 'mimetypes_to_icons' ) ) {
		/**
		 * Function to invoke the mimetypes_to_icons functionality for content
		 * outside of the loop
		 *
		 * @since 3.0
		 * @param	string	$content
		 * @return	string
		 */
		function mimetypes_to_icons( $content ) {
			global $mimetypes_link_icons;
			$async = $mimetypes_link_icons->settings['enable_async'];
			$mimetypes_link_icons->settings['enable_async'] = false;
			$content = $mimetypes_link_icons->mimetype_to_icon( $content );
			$mimetypes_link_icons->settings['enable_async'] = $async;
			unset( $async );
			return $content;
		}
	}


	/**
	 * Function to temporarily pause the mimetypes link icons plugin
	 *
	 * This function is meant to be used by other plugins/themes as an easy way to temporarily suspend
	 * the adding of the mimetypes link icons.
	 *
	 * @since 3.0
	 * @return void
	 */
	if( !function_exists( 'pause_mtli' ) ) {
		function pause_mtli() {
			global $mimetypes_link_icons;

			if( has_filter( 'the_content', array( &$mimetypes_link_icons, 'mimetype_to_icon' ) ) ) {
				remove_filter( 'the_content', array( &$mimetypes_link_icons, 'mimetype_to_icon' ) );
			}
		}
	}


	/**
	 * Function to unpause the mimetypes link icons plugin
	 *
	 * This function is meant to be used by other plugins/themes as an easy way to un-suspend
	 * the adding of the mimetypes link icons.
	 *
	 * @since 3.0
	 * @return void
	 */
	if( !function_exists( 'unpause_mtli' ) ) {
		function unpause_mtli() {
			global $mimetypes_link_icons;

			if( ( false === $mimetypes_link_icons->settings['enable_async'] || true === $mimetypes_link_icons->settings['show_file_size'] ) && false === has_filter( 'the_content', array( &$mimetypes_link_icons, 'mimetype_to_icon' ) ) ) {
				add_filter( 'the_content', array( &$mimetypes_link_icons, 'mimetype_to_icon' ) );
			}
		}
	}


} /* End of class-exists wrapper */