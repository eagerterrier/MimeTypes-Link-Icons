<?php
/**
 * @package MimeTypeLinkImages
 * @version 3.2.19
 */
/*
Plugin Name: MimeTypes Link Icons
Plugin URI: http://blog.eagerterrier.co.uk/2010/10/holy-cow-ive-gone-and-made-a-mime-type-wordpress-plugin/
Description: This will add file type icons next to links automatically. Change options in the <a href="options-general.php?page=mimetypes-link-icons">settings page</a>
Version: 3.2.19
Author: Toby Cox, Juliette Reinders Folmer
Author URI: https://github.com/eagerterrier/MimeTypes-Link-Icons
Author: Toby Cox
Author URI: http://eagerterrier.co.uk/
Author: Juliette Reinders Folmer
Author URI: http://adviesenzo.nl/
Contributor: Keith Parker
Contributor URI: http://infas.net/
Contributor: Birgir Erlendsson
Contributor URI: http://wordpress.stackexchange.com/users/26350/birgire
Contributor: x06designs
Contributor URI: https://github.com/x06designs
Text Domain: mimetypes-link-icons
Domain Path: /languages
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


if ( ! class_exists( 'Mime_Types_Link_Icons' ) ) {
	/**
	 * @package WordPress\Plugins\MimeTypes Link Icons
	 * @version 3.2.19
	 * @link http://wordpress.org/plugins/mimetypes-link-icons/ MimeTypes Link Icons WordPress plugin
	 * @link https://github.com/eagerterrier/MimeTypes-Link-Icons GitHub development of MimeTypes Link Icons WordPress plugin
	 *
	 * @copyright 2010 - 2013 Toby Cox, Juliette Reinders Folmer
	 * @license http://creativecommons.org/licenses/GPL/2.0/ GNU General Public License, version 2
	 */
	class Mime_Types_Link_Icons {


		/* *** DEFINE CLASS CONSTANTS *** */

		/**
		 * @const string	Plugin version number
		 * @usedby upgrade_options(), __construct()
		 */
		const VERSION = '3.2.19';

		/**
		 * @const string	Version in which the front-end styles where last changed
		 * @usedby	wp_enqueue_scripts()
		 */
		const STYLES_VERSION = '3.0';

		/**
		 * @const string	Version in which the front-end scripts where last changed
		 * @usedby	wp_enqueue_scripts()
		 */
		const SCRIPTS_VERSION = '3.1.0';

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
		const DB_LASTCHANGE = '3.2';


		/**
		 * @const	string	Minimum required capability to change the plugin options
		 */
		const REQUIRED_CAP = 'manage_options';

		/**
		 * @const	string	Page underneath which the settings page will be hooked
		 */
		const PARENT_PAGE = 'options-general.php';

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
		 * @staticvar	string	$basename	Plugin Basename = 'dir/file.php'
		 */
		public static $basename;

		/**
		 * @staticvar	string	$name		Plugin name	  = dirname of the plugin
		 *									Also used as text domain for translation
		 */
		public static $name;

		/**
		 * @staticvar	string	$path		Full server path to the plugin directory, has trailing slash
		 */
		public static $path;

		/**
		 * @staticvar	string	$suffix		Suffix to use if scripts/styles are in debug mode
		 */
		public static $suffix;



		/* *** DEFINE CLASS PROPERTIES *** */

		/* *** Semi Static Properties *** */

		/**
		 * @var	array	Available file sizes
		 * @todo		IMPORTANT: for now on each change, also copy this array to style.php
		 */
		public $sizes = array(
			16,
			24,
			48,
			64,
			128,
		);

		/**
		 * @var array	Available images types
		 * @todo		IMPORTANT: for now on each change, also copy this array to style.php
		 */
		public $image_types = array(
			'gif',
			'png',
		);

		/**
		 * @var array	Available image alignments: key = setting, value = field label
		 *				Will be set by set_properties() as the field labels need translating
		 * @todo		IMPORTANT: for now on each change, also copy this array to style.php
		 */
		public $alignments;

		/**
		 * @var array	array of mimetypes
		 * @todo		IMPORTANT: for now on each change, also copy this array to style.php
		 *				and of course to the readme ;-)
		 */
		public $mime_types = array(
			'3g2', '3gp',
			'ai', 'air', 'asf', 'avi',
			'bib',
			'capx', 'cls', 'csv',
			'deb', 'djvu', 'dmg', 'doc', 'docx', 'dwf', 'dwg',
			'eps', 'epub', 'exe',
			'f', 'f77', 'f90', 'flac', 'flv',
			'gif', 'gz',
			'ico', 'indd', 'iso',
			'jpg', 'jpeg',
			'key',
			'log',
			'm4a', 'm4v', 'midi', 'mkv', 'mov', 'mp3', 'mp4', 'mpeg', 'mpg', 'msi', 'msix',
			'odp', 'ods', 'odt', 'oga', 'ogg', 'ogv',
			'pages', 'pdf', 'png', 'pps', 'ppsx', 'ppt', 'pptm', 'pptx', 'psd', 'pub', 'py',
			'qt',
			'ra', 'ram', 'rar', 'rm', 'rpm', 'rtf', 'rv',
			'skp', 'spx', 'sql', 'sty',
			'tar', 'tex', 'tgz', 'tiff', 'ttf', 'txt',
			'vob',
			'wav', 'wmv',
			'xls', 'xlsx', 'xml', 'xpi',
			'zip',
		);

		/**
		 * @var array   array of mimetypes which default to true / 'on' status
		 */
		public $default_is_true = array(
			'pdf',
		);

		/**
		 * @var array	Default option values - this array will be enriched by the enrich_default_settings() method
		 * @todo		IMPORTANT: For now, on change in default size, type or alignment, also copy
		 *				the new defaults to style.php
		 */
		public $defaults = array(
			'internal_domains'		=> array(),
			'image_size'			=> 16,
			'image_type'			=> 'png',
			'leftorright'			=> 'left',
			'show_file_size'		=> false,
			'precision'				=> 2,
			'use_cache'				=> true,
			'cache_time'			=> 604800, // seconds: 1 hour = 3600, 1 day = 86400, 1 week = 604800
			'enable_async'			=> false,
			'enable_async_debug'	=> false,
			'enable_hidden_class'	=> true,
			'hidden_classname'		=> array( 'wp-caption', ),
			'version'				=> null,
			'show_file_size_over'	=> 0,
			//'upgrading'			=> false, // will never change, not saved to db, only used to distinguish a call from the upgrade method
		);

		/**
		 * @var array   array of option form sections: key = setting area, value = section label
		 *				Will be set by set_properties() as the section labels need translating
		 * @usedby display_options_page()
		 */
		public $form_sections = array();

		/**
		 * @var array	array of byte suffixes for creating a human readable file size
		 *				Will be set by set_properties() as the labels need translating
		 * @usedby human_readable_filesize()
		 */
		public $byte_suffixes = array();



		/* *** Properties Holding Various Parts of the Class' State *** */

		/**
		 * @var string settings page registration hook suffix
		 */
		public $hook;

		/**
		 * @var array Variable holding current settings for this plugin
		 */
		public $settings = array();

		/**
		 * @var array Efficiency property - array of the mimetype for which the plugin should be active
		 */
		public $active_mimetypes = array();

		/**
		 * @var array	Array holding cached filesize values
		 *				key = sanitized file path
		 *				values = array( 'size' => file size, 'time' => time of last filesize retrieval in seconds )
		 */
		public $cache = array();

		/**
		 * @var array	Array holding the rel / filesize CSS styles to be added to the page
		 */
		public $filesize_styles = array();

		/**
		 * @var	resource 	Holds the curl resource if one exists
		 */
		public $curl;

		/**
		 * @var	bool	Debug setting to enable extra debugging for the plugin
		 */
		public $debug = false;



		/* *** PLUGIN INITIALIZATION METHODS *** */

		/**
		 * Object constructor for plugin
		 */
		public function __construct() {

			/* Initialize our settings option */
			$this->options_init();


			/* Check if we have any activation or upgrade actions to do */
			if ( ! isset( $this->settings['version'] ) || version_compare( self::DB_LASTCHANGE, $this->settings['version'], '>' ) ) {
				add_action( 'init', array( $this, 'upgrade_options' ), 8 );
			}
			// Make sure that the upgrade actions are run on (re-)activation as well.
			add_action( 'mimetype_link_icons_plugin_activate', array( $this, 'upgrade_options' ) );


			// Register the plugin initialization actions
			add_action( 'init', array( $this, 'pre_init' ), 5 );
			add_action( 'init', array( $this, 'init' ) );
			add_action( 'admin_menu', array( $this, 'add_options_page' ) );
			add_action( 'admin_init', array( $this, 'admin_init' ) );
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
			self::$name     = dirname( self::$basename );
			self::$path     = plugin_dir_path( __FILE__ );
			self::$suffix   = ( ( defined( 'SCRIPT_DEBUG' ) && true === SCRIPT_DEBUG ) ? '' : '.min' );
		}




		/** ******************* OPTION MANAGEMENT ******************* **/

		/**
		 * Initialize our settings option and add all relevant actions and filters
		 *
		 * @since 3.2
		 */
		public function options_init() {

			/* Enrich the defaults */
			$this->enrich_default_settings();


			/* Add filter which get applied to get_options() results */
			$this->add_default_filter();
			add_filter( 'option_' . self::SETTINGS_OPTION, array( $this, 'filter_option' ) );

			/* The option validation routines remove the default filters to prevent failing to insert
			   an option if it's new. Let's add them back afterwards */
			add_action( 'add_option', array( $this, 'add_default_filter' ) );

			if ( version_compare( $GLOBALS['wp_version'], '3.7', '!=' ) ) {
				add_action( 'update_option', array( $this, 'add_default_filter' ) );
			}
			else {
				// Abuse a filter for WP 3.7 where the update_option filter is placed in the wrong location
				add_filter( 'pre_update_option_' . self::SETTINGS_OPTION, array( $this, 'wp37_add_default_filters' ) );
			}


			/* Make sure the option will always get validated, independently of register_setting()
			   (which is only available on back-end) */
			add_filter( 'sanitize_option_' . self::SETTINGS_OPTION, array( $this, 'validate_options' ) );

			/* Register our option for the admin pages */
			add_action( 'admin_init', array( $this, 'register_setting' ) );


			/* Refresh the $settings property on option update */
			add_action( 'add_option_' . self::SETTINGS_OPTION, array( $this, 'on_add_option' ), 10, 2 );
			add_action( 'update_option_' . self::SETTINGS_OPTION, array( $this, 'on_update_option' ), 10, 2 );

			/* Initialize the $settings property */
			$this->refresh_current();


			/* Refresh the $cache property on cache option update */
			add_action( 'add_option_' . self::CACHE_OPTION, array( $this, 'on_add_cache_option' ), 10, 2 );
			add_action( 'update_option_' . self::CACHE_OPTION, array( $this, 'on_update_cache_option' ), 10, 2 );
		}


		/**
		 * Enrich the default settings array
		 */
		private function enrich_default_settings() {
			foreach ( $this->mime_types as $type ) {
				$this->defaults[ 'enable_' . $type ]	= ( false === in_array( $type, $this->default_is_true ) ? false : true );
			}
		}


		/**
		 * Register our option
		 *
		 * @since 3.2 (moved from admin_init to separate method)
		 */
		public function register_setting() {
			register_setting( self::SETTINGS_OPTION . '-group', self::SETTINGS_OPTION );
		}


		/**
		 * Add filtering of the option default values
		 *
		 * @since 3.2
		 */
		public function add_default_filter() {
			if ( has_filter( 'default_option_' . self::SETTINGS_OPTION, array( $this, 'filter_option_defaults' ) ) === false ) {
				add_filter( 'default_option_' . self::SETTINGS_OPTION, array( $this, 'filter_option_defaults' ) );
			};
		}

		/**
		 * Abusing a filter to re-add our default filters
		 * WP 3.7 specific as update_option action hook was in the wrong place temporarily
		 * @see http://core.trac.wordpress.org/ticket/25705
		 *
		 * @param   mixed $new_value
		 *
		 * @return  mixed   unchanged value
		 */
		public function wp37_add_default_filters( $new_value ) {
			$this->add_default_filter();
			return $new_value;
		}


		/**
		 * Remove filtering of the option default values
		 * Called from the validate_options() method to prevent failure to add new options
		 *
		 * @since 3.2
		 */
		public function remove_default_filter() {
			remove_filter( 'default_option_' . self::SETTINGS_OPTION, array( $this, 'filter_option_defaults' ) );
		}


		/**
		 * Filter option defaults
		 *
		 * This in effect means that get_option() will not return false if the option is not found,
		 * but will instead return our defaults. This way we always have all of our option values available.
		 *
		 * @since 3.2
		 */
		public function filter_option_defaults() {
			$this->refresh_current( $this->defaults );
			return $this->defaults;
		}


		/**
		 * Filter option
		 *
		 * This in effect means that get_option() will not just return our option from the database,
		 * but will instead return that option merged with our defaults.
		 * This way we always have all of our option values available. Even when we add new option
		 * values (to the defaults array) when the plugin is upgraded.
		 *
		 * @since 3.2
		 */
		public function filter_option( $options ) {
			$options = $this->array_filter_merge( $this->defaults, $options );
			$this->refresh_current( $options );
			return $options;
		}


		/**
		 * Set the $settings property to the value of our option
		 * @since 3.2
		 */
		private function refresh_current( $value = null ) {
			if ( ! isset( $value ) ) {
				$value = get_option( self::SETTINGS_OPTION );
			}
			$this->settings = $value;

			/* Update the active_mimetypes array */
			$this->active_mimetypes = array();
			foreach ( $this->mime_types as $mime_type ) {
				if ( true === $this->settings[ 'enable_' . $mime_type ] ) {
					$this->active_mimetypes[] = $mime_type;
				}
			}
			unset( $mime_type );
		}


		/**
		 * Refresh the $settings property when our property is added to wp
		 * @since 3.2
		 */
		public function on_add_option( $option_name, $value ) {
			$this->refresh_current( $value );
		}


		/**
		 * Refresh the $settings property when our property is updated
		 * @since 3.2
		 */
		public function on_update_option( $old_value, $value ) {
			$this->refresh_current( $value );
		}


		/**
		 * Set the $cache property to the value of our option
		 *
		 * @since 3.2
		 */
		private function refresh_cache( $value = null ) {
			if ( ! isset( $value ) ) {
				$value = get_option( self::CACHE_OPTION );
			}
			if ( $value === false ) {
				/* Set the default
				 - don't hook into WP as no validation is used and it would break when adding the option as new */
				$value = array();
			}
			$this->cache = $value;
		}


		/**
		 * Refresh the $cache property when our property is added to wp
		 *
		 * @since 3.2
		 *
		 * @param   $option_name    Name of the option added
		 * @param   $value          Option value
		 * @return  void
		 */
		public function on_add_cache_option( $option_name, $value ) {
			$this->refresh_cache( $value );
		}


		/**
		 * Refresh the $cache property when our property is updated
		 *
		 * @since 3.2
		 *
		 * @param   $old_value  Original option value
		 * @param   $value      New option value
		 * @return  void
		 */
		public function on_update_cache_option( $old_value, $value ) {
			$this->refresh_cache( $value );
		}


		/**
		 * Update cached filesizes
		 *
		 * @since 3.2 - replaces get_set_...() method
		 *
		 * @param	array|null		$update				New cache to save to db - make sure the new array
		 *												is validated first!
		 * @param	string|null		$key				file key to update the cache for
		 * @return	bool|void		if an update took place: whether it worked
		 */
		private function update_cache( $update, $key = null ) {
			$updated = null;

			// Is this a complete or a one field update ?
			if ( ! is_null( $key ) ) {
				$new_cache = $this->cache;
				$new_cache[ $key ] = array(
					'size'	=>	$update, // file size or false if size could not be determined
					'time'	=>	time(),
				);
				$update = $new_cache;
				unset( $new_cache );
			}

			if ( $update !== $this->cache ) {
				$updated = update_option( self::CACHE_OPTION, $update );
			}
			else {
				$updated = true; // no update necessary
			}
			return $updated;
		}





		/** ******************* ADMINISTRATIVE METHODS ******************* **/

		/**
		 * Make sure our text strings and properties are available
		 * @since 3.2
		 */
		public function pre_init() {
			/* Allow filtering of our plugin name */
			self::filter_statics();

			/* Load plugin text strings */
			load_plugin_textdomain( 'mimetypes-link-icons', false, self::$name . '/languages' );

			/* Translate a number of strings */
			$this->set_properties();
		}


		/**
		 * Allow filtering of the plugin name
		 * Mainly useful for non-standard directory setups
		 *
		 * @since 3.2
		 *
		 * @return void
		 */
		public static function filter_statics() {
			/* Allow filtering of the plugin name, Mainly useful for non-standard directory setups
			   @api	string	$plugin_name	plugin name */
			self::$name = apply_filters( 'mimetype_link_icons_plugin_name', self::$name );
		}


		/**
		 * Fill some property arrays with translated strings
		 * @since 3.0
		 */
		private function set_properties() {

			$this->alignments = array(
				'left'	   => __( 'Left', 'mimetypes-link-icons' ),
				'right'    => __( 'Right', 'mimetypes-link-icons' ),
			);

			$this->form_sections = array(
				'general'	=> __( 'General Settings', 'mimetypes-link-icons' ),
				'images'	=> __( 'Image Settings', 'mimetypes-link-icons' ),
				'advanced'	=> __( 'Advanced Settings', 'mimetypes-link-icons' ),
			);

			$this->byte_suffixes = array(
				_x( 'b', 'Abbreviation of "byte"', 'mimetypes-link-icons' ),
				__( 'kB', 'mimetypes-link-icons' ),
				__( 'MB', 'mimetypes-link-icons' ),
				__( 'GB', 'mimetypes-link-icons' ),
				__( 'TB', 'mimetypes-link-icons' ),
				__( 'PB', 'mimetypes-link-icons' ),
				__( 'EB', 'mimetypes-link-icons' ),
				__( 'ZB', 'mimetypes-link-icons' ),
				__( 'YB', 'mimetypes-link-icons' ),
			);
		}


		/**
		 * Add the actions for the front end functionality
		 */
		public function init() {
			/**
			 * Filter hook for active mime types list
			 * @api array	Allows a developer to filter (add/remove) mimetypes from the array of mimetypes
			 *				for which the plugin should be active as selected by the admin on the settings
			 *				page
			 */
			$this->active_mimetypes = apply_filters( 'mtli_active_mimetypes', $this->active_mimetypes );

			/* Validate/sanitize the active mime types array */
			$this->active_mimetypes = array_filter( $this->active_mimetypes, 'is_string' );
			$this->active_mimetypes = array_map( 'strtolower', $this->active_mimetypes );
			$this->active_mimetypes = preg_grep( '`^[a-z0-9]{2,8}$`', $this->active_mimetypes );

			// Don't do anything if no active_mimetypes or if we're not on the frontend
			if ( false === is_admin() && !wp_is_json_request() && array() !== $this->active_mimetypes ) {
				/* Register the_content filter */
				if ( false === $this->settings['enable_async'] || true === $this->settings['show_file_size'] ) {
					add_filter( 'the_content', array( $this, 'mimetype_to_icon' ), 15 );
					add_filter( 'acf_the_content', array( $this, 'mimetype_to_icon' ), 15 );
				}
				/* Add js and css files */
				add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
			}
		}


		/**
		 * Add the actions for the back-end functionality
		 */
		public function admin_init() {
			/* Don't do anything if user does not have the required capability */
			if ( false === is_admin() || false === current_user_can( self::REQUIRED_CAP ) ) {
				return;
			}

			/* Register the settings sections and their callbacks */
			foreach ( $this->form_sections as $section => $title ) {
				add_settings_section(
					'mtli-' . $section . '-settings', // id
					$title, // title
					array( $this, 'do_settings_section_' . $section ), // callback for this section
					self::$name // page menu_slug
				);
			}

			/* Add settings link on plugin page */
			add_filter( 'plugin_action_links_' . self::$basename , array( $this, 'add_settings_link' ), 10, 2 );


			/* Add js and css files */
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );


			/* Add help tabs for our settings page */
			add_action( 'load-' . $this->hook, array( $this, 'add_help_tab' ) );
		}


		/**
		 * Register the options page for all users that have the required capability
		 */
		public function add_options_page() {

			$this->hook = add_options_page(
				__( 'MimeType Link Icons', 'mimetypes-link-icons' ), /* page title */
				__( 'MimeType Icons', 'mimetypes-link-icons' ), /* menu title */
				self::REQUIRED_CAP, /* capability */
				self::$name, /* menu slug */
				array( $this, 'display_options_page' ) /* function for subpanel */
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
		public function add_settings_link( $links, $file ) {
			if ( self::$basename === $file && current_user_can( self::REQUIRED_CAP ) ) {
				$links[] = '<a href="' . esc_url( $this->plugin_options_url() ) . '" alt="' . esc_attr__( 'MimeType Link Icons Settings', 'mimetypes-link-icons' ) . '">' . esc_html__( 'Settings', 'mimetypes-link-icons' ) . '</a>';
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
		private function plugin_options_url() {
			return add_query_arg( 'page', self::$name, admin_url( self::PARENT_PAGE ) );
		}


		/**
		 * Conditionally enqueue scripts and styles for front-end pages
		 * @todo:	Probably quite difficult in this case: see if we can load our scripts and styles conditionally, i.e. only on the pages where used
		 * @todo:	For now: may be add the active mimetypes as an encoded setting to the url so as only to generate the css rules for the active mimetypes
		 * @todo:	May be generate a .css file on a settings save to avoid having to generate the .css file on each page load
		 * @todo:	Also generate a .min.css file
		 */
		public function wp_enqueue_scripts() {

			wp_register_style(
				self::$name, // id
				add_query_arg(
					'cssvars',
					base64_encode( 'mtli_height=' . $this->settings['image_size'] . '&mtli_image_type=' . $this->settings['image_type'] . '&mtli_leftorright=' . $this->settings['leftorright'] . '&active_types=' . implode('|', $this->active_mimetypes) ),
					plugins_url( 'css/style.php', __FILE__ )
				), // url
				array(), // not used
				self::STYLES_VERSION, // version
				'all'
			);
			wp_enqueue_style( self::$name );


			if ( ( true === $this->settings['enable_hidden_class'] && ( is_array( $this->settings['hidden_classname'] ) && array() !== $this->settings['hidden_classname'] ) ) || ( true === $this->settings['enable_async'] && ( is_array( $this->active_mimetypes ) && array() !== $this->active_mimetypes ) ) ) {
				wp_enqueue_script(
					self::$name, // id
					plugins_url( 'js/mtli-str-replace' . self::$suffix . '.js', __FILE__ ), // url
					array( 'jquery' ), // dependants
					self::SCRIPTS_VERSION, // version
					true // load in footer
				);

				wp_localize_script( self::$name, 'i18n_mtli', $this->get_javascript_i18n() );
			}
		}


		/**
		 * Retrieve the strings for use in the javascript file
		 *
		 * @since 3.0
		 * @usedby	wp_enqueue_scripts()
		 *
		 * @return	array
		 */
		private function get_javascript_i18n() {
			$strings = array(
				'hidethings'			=> ( ( true === $this->settings['enable_hidden_class'] && ( is_array( $this->settings['hidden_classname'] ) && array() !== $this->settings['hidden_classname'] ) ) ? true : false ),
				'enable_async'			=> ( ( true === $this->settings['enable_async'] && ( is_array( $this->active_mimetypes ) && array() !== $this->active_mimetypes ) ) ? true : false ),
				'enable_async_debug'	=> ( ( true === $this->settings['enable_async_debug'] && ( is_array( $this->active_mimetypes ) && array() !== $this->active_mimetypes ) ) ? true : false ),
			);

			/* Add jQuery class selector string if hidden classes are used */
			if ( true === $this->settings['enable_hidden_class'] && ( is_array( $this->settings['hidden_classname'] ) && array() !== $this->settings['hidden_classname'] ) ) {
				$strings['avoid_selector'] = '';
				foreach ( $this->settings['hidden_classname'] as $classname ) {
					$strings['avoid_selector'] .= '.' . $classname . ',';
				}
				$strings['avoid_selector'] = substr( $strings['avoid_selector'], 0, -1 );
			}

			/* Add array of active mimetypes if in async mode*/
			if ( true === $this->settings['enable_async'] && ( is_array( $this->active_mimetypes ) && array() !== $this->active_mimetypes ) ) {
				$strings['mime_array'] = $this->active_mimetypes;
			}

			return $strings;
		}


		/**
		 * Adds necessary javascript and css files for the back-end on the appropriate screen
		 */
		public function admin_enqueue_scripts() {

			$screen = get_current_screen();

			if ( property_exists( $screen, 'base' ) && $screen->base === $this->hook ) {
				wp_enqueue_script(
					self::$name, // id
					plugins_url( 'js/mtli-admin' . self::$suffix . '.js', __FILE__ ), // url
					array( 'jquery' ), // dependants
					self::ADMIN_SCRIPTS_VERSION, // version
					true // load in footer
				);
				wp_enqueue_style(
					self::$name, // id
					plugins_url( 'css/admin-style' . self::$suffix . '.css', __FILE__ ), // url
					array(), // not used
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
		private function get_admin_javascript_i18n() {
			$strings = array(
				'togglebox'     => '<div class="check-images"><span class="check-all">' . esc_html__( 'Check All', 'mimetypes-link-icons' ) . '</span>|<span class="uncheck-all">' . esc_html__( 'Uncheck All', 'mimetypes-link-icons' ) . '</span></div>',
			);
			return $strings;
		}



		/**
		 * Adds contextual help tab to the plugin page
		 *
		 * @since 3.0
		 */
		public function add_help_tab() {

			$screen = get_current_screen();

			if ( property_exists( $screen, 'base' ) && $screen->base === $this->hook ) {
				$screen->add_help_tab(
					array(
						'id'	  => self::$name . '-main', // This should be unique for the screen.
						'title'   => __( 'MimeType Link Icons', 'mimetypes-link-icons' ),
						'callback' => array( $this, 'get_helptext' ),
					)
				);
				$screen->add_help_tab(
					array(
						'id'	  => self::$name . '-advanced', // This should be unique for the screen.
						'title'   => __( 'Advanced Settings', 'mimetypes-link-icons' ),
						'callback' => array( $this, 'get_helptext' ),
					)
				);
				$screen->add_help_tab(
					array(
						'id'	  => self::$name . '-extras', // This should be unique for the screen.
						'title'   => __( 'Extras', 'mimetypes-link-icons' ),
						'callback' => array( $this, 'get_helptext' ),
					)
				);

				$screen->set_help_sidebar( $this->get_help_sidebar() );
			}
		}


		/**
		 * Function containing the helptext string
		 *
		 * @since 3.0
		 *
		 * @param 	object	$screen
		 * @param 			$tab
		 * @return  string  help text
		 */
		public function get_helptext( $screen, $tab ) {

			$helptext[ self::$name . '-main' ] = '
								<p>' .
								/* Translators: %s = link target. */
								sprintf( __( 'The <em><a href="%s">MimeTypes Link Icons</a></em> plugin will automatically add an icon next to links of the activated file types. If you like, you can also let the plugin add the file size of the linked file to the page.', 'mimetypes-link-icons' ), 'http://wordpress.org/plugins/mimetypes-link-icons/" target="_blank" class="ext-link' ) . '</p>
								<p>' . esc_html__( 'On this settings page you can specify the icon size, icon type (white matte gif or transparent png), icon alignment. You can also select the file types for which this plugin will be enabled.', 'mimetypes-link-icons' ) . '</p>';

			$helptext[ self::$name . '-advanced' ] = '
								<p>' . __( 'In the advanced settings, you can enable <em>"exclusion classnames"</em>, enable the display of the <em>file size</em> of a linked file and/or choose to use <em>asynchronous replacement</em>.', 'mimetypes-link-icons' ) . '</p>
								<p>' . __( '<strong>"Exclusion classnames"</strong> works as follows:', 'mimetypes-link-icons' ) . '<br />
								' . esc_html__( 'The plugin will look for the classname in your document and will remove the Mimetypes link icons (and file sizes) from all links wrapped within that class. You can add several classnames, just separate them with a comma.', 'mimetypes-link-icons' ) . '</p>';

			$helptext[ self::$name . '-extras' ] = '
								<p>' . __( 'There is even some more advanced functionality available: for instance an <em>output filter</em> for the file size output and a way to add the plugin\'s functionality to widgets or other areas of your blog outside of the main content area.', 'mimetypes-link-icons' ) . '</p>

								<p>' .
								/* Translators: %1$s = <a> tag, %2$s is closing</a> tag. */
								sprintf( esc_html__( 'For more information on these tasty extras, have a look at the %1$sFAQ%2$s', 'mimetypes-link-icons' ), '<a href="http://wordpress.org/plugins/mimetypes-link-icons/faq/" target="_blank" class="ext-link">', '</a>' ) . '</p>';


			echo $helptext[ $tab['id'] ];
		}

		/**
		 * Generate the links for the help sidebar
		 *
		 * @return string
		 */
		private function get_help_sidebar() {
			return '
				   <p><strong>' . esc_html__( 'For more information:', 'mimetypes-link-icons' ) . '</strong></p>
				   <p>
						<a href="http://wordpress.org/plugins/mimetypes-link-icons/" target="_blank">' . esc_html__( 'Official plugin page', 'mimetypes-link-icons' ) . '</a> |
						<a href="http://wordpress.org/plugins/mimetypes-link-icons/faq/" target="_blank">' . esc_html__( 'FAQ', 'mimetypes-link-icons' ) . '</a> |
						<a href="http://wordpress.org/plugins/mimetypes-link-icons/changelog/" target="_blank">' . esc_html__( 'Changelog', 'mimetypes-link-icons' ) . '</a> |
						<a href="http://wordpress.org/support/plugin/mimetypes-link-icons" target="_blank">' . esc_html__( 'Support&nbsp;Forum', 'mimetypes-link-icons' ) . '</a>
					</p>
				   <p><a href="https://github.com/eagerterrier/MimeTypes-Link-Icons" target="_blank">' . esc_html__( 'Github repository', 'mimetypes-link-icons' ) . '</a></p>
				   <p><a href="http://blog.eagerterrier.co.uk/2010/10/holy-cow-ive-gone-and-made-a-mime-type-wordpress-plugin/" target="_blank">' . esc_html__( 'Blog post about this plugin', 'mimetypes-link-icons' ) . '</a></p>
			';
		}


		/* *** PLUGIN ACTIVATION AND UPGRADING *** */


		/**
		 * Activate our plugin
		 *
		 * @since 3.2
		 * @static
		 * @return void
		 */
		public static function activate() {
			/* Security check */
			if ( ! current_user_can( 'activate_plugins' ) ) {
				return;
			}
			$plugin = ( isset( $_REQUEST['plugin'] ) ? sanitize_text_field( $_REQUEST['plugin'] ) : '' );
			check_admin_referer( 'activate-plugin_' . $plugin );

			/* Execute any actions registered */
			/* @api	Execute registered actions on activation of the plugin */
			do_action( 'mimetype_link_icons_plugin_activate' );
		}


		/**
		 * Function used when activating and/or upgrading the plugin
		 * - Initial activate: Save version number to option
		 * - v 3.0: change hidden_classname from string to array
		 *
		 * @since 3.0
		 */
		public function upgrade_options() {
			global $wp_version;

			$options = $this->settings;

			/**
			 * Upgrades for any version of this plugin lower than x.x
			 * N.B.: Version nr has to be hard coded to be future-proof, i.e. facilitate
			 * upgrade routines for various versions
			 */

			/* Settings upgrade for version 3.0 */
			if ( ! isset( $options['version'] ) || version_compare( $options['version'], '3.0', '<' ) ) {
				/* Change 'hidden_classname' from string to array to allow for more classnames
				   and validate the value */
				if ( isset( $options['hidden_classname'] ) && is_string( $options['hidden_classname'] ) ) {
					$classnames = $this->validate_classnames( $options['hidden_classname'] );
					if ( false !== $classnames ) {
						$options['hidden_classname'] = $classnames;
					}
					else {
						unset( $options['hidden_classname'] );
					}
					unset( $classnames );
				}

				/* Change 'internal_domains' from string to array */
				if ( isset( $options['internal_domains'] ) && ( is_string( $options['internal_domains'] ) && $options['internal_domains'] !== '' ) ) {
					$options['internal_domains'] = explode( ',', $options['internal_domains'] );
				}
			}
			/* Settings upgrade for version 3.1.4
			   Reset internal domains variable for changed determination */
			if ( ! isset( $options['version'] ) || version_compare( $options['version'], '3.1.4', '<' ) ) {
				unset( $options['internal_domains'] );
			}

			/**
			 * (Re-)Determine the site's domain on activation and on each upgrade
			 */
			$home_url = home_url();
			if ( $this->debug === true ) {
				trigger_error( 'MTLI DEBUG INFO - ' . __METHOD__ . '::internal_domains: home_url = ' . $home_url );
			}
			$start = ( ( strpos( $home_url, '://' ) !== false ) ? ( strpos( $home_url, '://' ) + 3 ) : 0 );
			if ( $this->debug === true ) {
				trigger_error( 'MTLI DEBUG INFO - ' . __METHOD__ . '::internal_domains: start = ' . $start );
			}
			$options['internal_domains'][] = $domain = substr( $home_url, $start, ( strpos( $home_url, '/', $start ) - $start ) );
			if ( $this->debug === true ) {
				trigger_error( 'MTLI DEBUG INFO - ' . __METHOD__ . '::internal_domains: domain = ' . $domain );
			}
			if ( stripos( $domain, 'www.' ) === 0 ) {
				$options['internal_domains'][] = str_ireplace( 'www.', '', $domain );
				if ( $this->debug === true ) {
					trigger_error( 'MTLI DEBUG INFO - ' . __METHOD__ . '::internal_domains: domain2 = ' . str_ireplace( 'www.', '', $domain ) );
				}
			}
			$options['internal_domains'] = array_unique( $options['internal_domains'] );
			unset( $home_url, $domain, $start );


			/* Always update the version number */
			$options['version']   = self::VERSION;
			$options['upgrading'] = true; // indicator to save internal domains and not to multiply cache time

			/* Validate and update the settings and refresh our $settings property */
			update_option( self::SETTINGS_OPTION, $options );

			return;
		}



		/* *** HELPER METHODS *** */

		/**
		 * Helper method - Combines a fixed array of default values with an options array
		 * while filtering out any keys which are not in the defaults array.
		 *
		 * @param	array	$defaults	Entire list of supported defaults.
		 * @param	array	$options	Current options.
		 * @return	array	Combined and filtered options array.
		 */
		private function array_filter_merge( $defaults, $options ) {
			$options = (array) $options;
			$return  = array();

			foreach ( $defaults as $name => $default ) {
				if ( array_key_exists( $name, $options ) ) {
					$return[ $name ] = $options[ $name ];
				}
				else {
					$return[ $name ] = $default;
				}
			}
			return $return;
		}


		/**
		 * Test a boolean PHP ini value
		 *
		 * @since 3.0
		 * @param string	$a	key of the value you want to get
		 * @return bool
		 */
		private function ini_get_bool( $a ) {
			$b = ini_get( $a );

			switch ( strtolower( $b ) ) {
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
		private function resolve_relative_url( $url ) {
			return preg_replace( '`\w+/\.\./`', '', $url );
		}


		/**
		 * Make sure all the directory separators are the same
		 *
		 * @since 3.0
		 * @var		string 	$url
		 * @return	string
		 */
		private function sync_dir_sep( $url ) {
			return str_replace( array( '/', '\\' ), DIRECTORY_SEPARATOR, $url );
		}




		/* *** FRONT-END: DISPLAY METHODS *** */

		/**
		 * Add mimetype icon classes and relevant style rules to content
		 *
		 * @param $content
		 * @return string
		 */
		public function mimetype_to_icon( $content ) {

			// Clear the styles array at the start to prevent styles being added erronously if the method
			// is called several times
			$this->filesize_styles = array();

			if ( array() !== $this->active_mimetypes ) {
				$mimetypes = array_map( 'preg_quote' , $this->active_mimetypes, array_fill( 0 , count( $this->active_mimetypes ) , '`' ) );
				$mimetypes = implode( '|', $mimetypes );

				if ( 0 < preg_match_all( '`<a [^>]*?(class=["\']([^"\']*)["\'])?[^>]*?(href=["\']([^"\'#]+\.(' . $mimetypes . '))(?:#[^\'" ]+["\']|["\']))[^>]*?(class=["\']([^"\']*)["\'])?[^>]*>`i', $content, $matches, PREG_SET_ORDER ) ) {
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

					foreach ( $matches as $match ) {
						$class_string = null;
						$classnames   = null;

						/* Find the class string & names if they exist */
						if ( '' !== $match[1] ) {
							$class_string = $match[1];
							$classnames   = $match[2];
						}
						else if ( isset( $match[6] ) && '' !== $match[6] ) {
							$class_string = $match[6];
							$classnames   = ( isset( $match[7] ) ? $match[7] : '' );
						}

						/* Test for 'hidden classes' */
						if ( ( true === $this->settings['enable_hidden_class'] && ( is_array( $this->settings['hidden_classname'] ) && array() !== $this->settings['hidden_classname'] ) ) && ( ! is_null( $classnames ) && '' !== $classnames ) ) {
							// We have existing classnames on the anchor
							$classes = explode( ' ', $classnames );
							foreach ( $classes as $class ) {
								if ( true === in_array( $class, $this->settings['hidden_classname'] ) ) {
									// Ok, we have a classname we should skip: skip out of the current match-item onto the next
									continue 2;
								}
							}
							unset( $classes, $class );
						}

						/* Still here, so we should do some work on this link ;-) */
						$replace = $match[0];

						/* Add the filesize info and styles */
						if ( true === $this->settings['show_file_size'] ) {
							$filesize = $this->get_filesize( $match[4] );

							if ( false !== $filesize ) {
								/* Add the rel attribute to the replacement anchor string */
								$replace = str_replace( $match[3], $match[3] . ' data-mtli="mtli_filesize' . str_replace( array( '.', ' ' ), '', $filesize ) . '"', $replace );

								// @api	string	$filesize	Allows filtering of the file size string
								$css_filesize_string = apply_filters( 'mtli_filesize', '(' . $filesize . ')' );
								// Make sure anything evil is stripped out of the filtered string
								$css_filesize_string = sanitize_text_field( $css_filesize_string );

								/* Add the css rule */
								$this->filesize_styles[] = 'a[data-mtli~="mtli_filesize' . str_replace( array( '.', ' ' ), '', $filesize ) . '"]:after {content:" ' . $css_filesize_string . '"}';
							}
							unset( $filesize, $css_filesize_string );
						}


						/* Add the attachment classes and avoid adding a second class attribute */
						if ( false === $this->settings['enable_async'] ) {
							$mtli_classes = 'mtli_attachment mtli_' . strtolower( $match[5] );
							if ( is_null( $classnames ) || '' === $classnames ) {
								$new_classnames = $mtli_classes;
							}
							else {
								$new_classnames = $classnames . ' ' . $mtli_classes;
							}

							/* Filter hook for classnames
							   @api string	$new_classnames Allows a developer to filter the class names string
							   before it is returned to the class attribute of the link tag */
							$new_classnames = apply_filters( 'mtli_classnames', $new_classnames );

							/* Validate/sanitize filtered classes */
							$new_classnames = explode( ' ', $new_classnames );
							$new_classnames = array_map( 'sanitize_html_class', $new_classnames );
							$new_classnames = implode( ' ', $new_classnames );


							if ( is_null( $class_string ) ) {
								// no previous class string found
								$replace = str_replace( $match[3], $match[3] . ' class="' . $new_classnames . '"', $replace );
							}
							else if ( is_null( $classnames ) || '' === $classnames ) {
								// empty previous class string
								$replace = str_replace( $class_string, substr( $class_string, 0, -1 ) . $new_classnames . substr( $class_string, -1 ), $replace );
							}
							else {
								// add to existing classes
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
			if ( true === $this->settings['show_file_size'] && ( is_array( $this->filesize_styles ) && array() !== $this->filesize_styles ) ) {
				$styles  = array_unique( $this->filesize_styles );
				$styles  = implode( '', $styles );
				echo '<style type="text/css">' . $styles . '</style>';
				unset( $styles );
			}


			/* Close curl resource if one has been opened */
			if ( is_resource( $this->curl ) ) {
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
		private function get_filesize( $url ) {
			static $has_cache = false;

			// Efficiency - only retrieve the cache once
			if ( true === $this->settings['use_cache'] && false === $has_cache ) {
				$this->refresh_cache();
				$has_cache = true;
			}

			if ( ! is_string( $url ) || $url === '' ) {
				return false;
			}


			/* Maybe get the cached value if still within the cache time interval */
			$cache_key = str_replace( '/', '__', $url );
			$cache_key = sanitize_key( $cache_key );
			if ( true === $this->settings['use_cache'] && ( isset( $this->cache[ $cache_key ] ) && $this->cache[ $cache_key ]['time'] > ( time() - $this->settings['cache_time'] ) ) ) {
				$filesize = $this->cache[ $cache_key ]['size'];
			}

			/* Otherwise retrieve the filesize from the actual file */
			else {
				$filesize = $this->retrieve_filesize( $url );

				/* Maybe cache the retrieved value */
				if ( true === $this->settings['use_cache'] ) {
					$this->update_cache( $filesize, $cache_key );
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
		private function retrieve_filesize( $url ) {
			static $home_path      = null; // has trailing slash
			static $site_path      = null; // has trailing slash
			static $wp_upload      = null;
			static $path_to_home   = null;
			static $site_root      = null;
			static $path_to_upload = '';


			/* Fill the statics - only run first time this method is called */
			if ( ( is_null( $home_path ) && is_null( $site_path ) ) && is_null( $wp_upload ) ) {
				$home_url = home_url();
				if ( $this->debug === true ) {
					trigger_error( 'MTLI DEBUG INFO - ' . __METHOD__ . '::set statics: home_url = ' . $home_url );
				}

				$site_url = site_url();
				if ( $this->debug === true ) {
					trigger_error( 'MTLI DEBUG INFO - ' . __METHOD__ . '::set statics: site_url = ' . $site_url );
				}

				$wp_upload = wp_upload_dir();
				$home_path = $site_path = $this->sync_dir_sep( ABSPATH );
				if ( $this->debug === true ) {
					trigger_error( 'MTLI DEBUG INFO - ' . __METHOD__ . '::set statics: home_path = site_path = ' . $site_path );
				}

				if ( $home_url !== $site_url ) {
					$diff = str_replace( $home_url, '', $site_url );
					$home_path = str_replace( $this->sync_dir_sep( $diff ), '', $site_path );
					if ( $this->debug === true ) {
						trigger_error( 'MTLI DEBUG INFO - ' . __METHOD__ . '::set statics: home_path = ' . $home_path );
					}
					unset( $diff );
				}
				$parsed_url = parse_url( $home_url );
				if ( $parsed_url !== false && ( isset( $parsed_url['path'] ) && $parsed_url['path'] !== '' ) ) {
					$path_to_home = $parsed_url['path'];
					if ( $this->debug === true ) {
						trigger_error( 'MTLI DEBUG INFO - ' . __METHOD__ . '::set statics: path_to_home = ' . $path_to_home );
					}

					$site_root = str_replace( $this->sync_dir_sep( $path_to_home ), '', $site_path );
					if ( $this->debug === true ) {
						trigger_error( 'MTLI DEBUG INFO - ' . __METHOD__ . '::set statics: site_root = ' . $site_root );
					}
				}
				$parsed_url = parse_url( $wp_upload['baseurl'] );
				if ( $parsed_url !== false && ( isset( $parsed_url['path'] ) && $parsed_url['path'] !== '' ) ) {
					$path_to_upload = str_replace( $path_to_home, '', $parsed_url['path'] );
					if ( $this->debug === true ) {
						trigger_error( 'MTLI DEBUG INFO - ' . __METHOD__ . '::set statics: path_to_upload = ' . $path_to_upload );
					}
				}
				unset( $home_url, $site_url, $parsed_url );
			}


			/* Negotiate local versus remote file */
			$local  = false;
			$remote = false;

			/* Is this a relative url starting with / \ or . ? */
			if ( true === in_array( substr( $url, 0, 1 ), array( '/', '\\', '.' ) ) ) {
				$rel_url = $this->resolve_relative_url( $url );
				if ( $this->debug === true ) {
					trigger_error( 'MTLI DEBUG INFO - ' . __METHOD__ . '::local vs remote: ==Branch 1== rel_url = ' . $rel_url );
				}
				$local = true;
			}
			else if ( false !== $this->is_own_domain( $url ) ) {
				$rel_url = $this->is_own_domain( $url );
				if ( $this->debug === true ) {
					trigger_error( 'MTLI DEBUG INFO - ' . __METHOD__ . '::local vs remote: ==Branch 2== rel_url = ' . $rel_url );
				}

				if ( ! is_null( $path_to_home ) ) {
					$pos     = stripos( $rel_url, $path_to_home );
					$rel_url = substr( $rel_url, ( $pos + strlen( $path_to_home ) ) );
					if ( $this->debug === true ) {
						trigger_error( 'MTLI DEBUG INFO - ' . __METHOD__ . '::local vs remote: ==Branch 2a== rel_url = ' . $rel_url );
					}
				}
				$local = true;
			}
			else {
				if ( 0 === strpos( $url, 'http://' ) ) {
					$remote = true;
				}
				/* Most likely external url, but could in rare situations be local: think 'favicon.ico' */
				else {
					$rel_url = $this->resolve_relative_url( $url );
					if ( $this->debug === true ) {
						trigger_error( 'MTLI DEBUG INFO - ' . __METHOD__ . '::local vs remote: ==Branch 3b== rel_url = ' . $rel_url );
					}
					$local = true;

					$url    = 'http://' . $url;
					$remote = true;
				}
			}


			/* Try and get the filesize for a local file */
			if ( true === $local && isset( $rel_url ) ) {
				$rel_url = explode( '#', $rel_url );
				$rel_url = explode( '?', $rel_url[0] );
				$rel_url = $rel_url[0];
				$rel_url =	$this->sync_dir_sep( $rel_url );
				$rel_url = ( 0 === strpos( $rel_url, DIRECTORY_SEPARATOR ) ? substr( $rel_url, 1 ) : $rel_url ); // remove potential slash from the start

				switch ( true ) {
					case file_exists( $home_path . $rel_url ):
						return filesize( $home_path . $rel_url );

					case file_exists( $site_path . $rel_url ):
						return filesize( $site_path . $rel_url );

					case ( ! is_null( $path_to_upload ) && file_exists( $this->sync_dir_sep( $wp_upload['basedir'] ) . substr( $rel_url, ( stripos( $rel_url, $path_to_upload ) + strlen( $path_to_upload ) ) ) ) ):
						return filesize( $this->sync_dir_sep( $wp_upload['basedir'] ) . substr( $rel_url, ( stripos( $rel_url, $path_to_upload ) + strlen( $path_to_upload ) ) ) );

					case ( ! is_null( $site_root ) && file_exists( $site_root . $rel_url ) ):
						return filesize( $site_root . $rel_url );

					case file_exists( $rel_url ):
						return filesize( $rel_url );

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
			if ( true === $remote ) {
				$filesize = $this->get_remote_filesize_via_curl( $url );
				if ( false === $filesize ) {
					// redundancy in case curl fails or gets blocked
					$filesize = $this->get_remote_filesize_via_headers( $url );
					if ( false === $filesize && true === $local ) {
						// Can't seem to resolve this url
						// -> show/log an error message for the web-savvy, silently fail for everyone else
						// @todo Should we only log an error message for local files or for all files where we couldn't get the filesize ? Let's start with local and see the response
						/* Translators: %s is the url to the file which could not be found. */
						trigger_error( sprintf( __( 'MimeTypes Link Icons can\'t resolve the following url, please make sure the file referred to exists. URL: %s', 'mimetypes-link-icons' ), esc_attr( $url ) ), E_USER_NOTICE );
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
		private function is_own_domain( $url ) {
			static $results; // remember results for re-use

			if ( isset( $results[ $url ] ) ) {
				return $results[ $url ];
			}

			$results[ $url ] = false;

			if ( is_array( $this->settings['internal_domains'] ) && array() !== $this->settings['internal_domains'] ) {
				foreach ( $this->settings['internal_domains'] as $domain ) {
					$pos = stripos( $url, $domain );
					if ( false !== $pos ) {
						$results[ $url ] = substr( $url, ( $pos + strlen( $domain ) ) );
						return $results[ $url ];
					}
					unset( $pos );
				}
				unset( $domain );
			}

			// Still here, redundancy test
			$domain = str_replace( 'www.', '', sanitize_text_field( $_SERVER['SERVER_NAME'] ) );
			$pos    = stripos( $url, $domain );
			if ( false !== $pos ) {
				$results[ $url ] = substr( $url, ( $pos + strlen( $domain ) ) );
			}
			unset( $domain, $pos );

			return $results[ $url ];
		}


		/**
		 * Get filesize of a remote file via a curl connection
		 *
		 * @param $url
		 * @return bool|int|mixed
		 */
		private function get_remote_filesize_via_curl( $url ) {

			/* Efficiency - only initialize once and keep the resource for re-use */
			if ( false === is_resource( $this->curl ) ) {
				$this->curl = curl_init();

				// Issue a HEAD request
				curl_setopt( $this->curl, CURLOPT_RETURNTRANSFER, true );
				curl_setopt( $this->curl, CURLOPT_HEADER, true );
				curl_setopt( $this->curl, CURLOPT_NOBODY, true );
				// Follow any redirects
				$open_basedir = ini_get( 'open_basedir' );
				if ( false === $this->ini_get_bool( 'safe_mode' ) && ( ( is_null( $open_basedir ) || empty( $open_basedir ) ) || $open_basedir == 'none' ) ) {
					curl_setopt( $this->curl, CURLOPT_FOLLOWLOCATION, true );
					curl_setopt( $this->curl, CURLOPT_MAXREDIRS, 5 );
				}
				unset( $open_basedir );
				// Bypass servers which refuse curl
				curl_setopt( $this->curl, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)' );
				// Set a time-out
				curl_setopt( $this->curl, CURLOPT_CONNECTTIMEOUT, 15 );
				curl_setopt( $this->curl, CURLOPT_TIMEOUT, 30 );
				// Stop as soon as an error occurs
				//curl_setopt( $this->curl, CURLOPT_FAILONERROR, true );
			}

			$filesize = false;

			/* Get the http headers for the given url */
			curl_setopt( $this->curl, CURLOPT_URL, $url );
			$header = curl_exec( $this->curl );

			/* If we didn't get an error, interpret the headers */
			if ( ( false !== $header && ! empty( $header ) ) && ( 0 === curl_errno( $this->curl ) ) ) {
				/* Get the http status */
				$statuscode = curl_getinfo( $this->curl, CURLINFO_HTTP_CODE );
				if ( false === $statuscode && preg_match( '/^HTTP\/1\.[01] (\d\d\d)/', $header, $matches ) ) {
					$statuscode = (int) $matches[1];
				}

				/* Only get the filesize if we didn't get an http error response */
				if ( 400 > $statuscode ) {
					$filesize = (int) curl_getinfo( $this->curl, CURLINFO_CONTENT_LENGTH_DOWNLOAD );
					/* Redundancy if curl_getinfo() fails */
					if ( ( false === $filesize || -1 === $filesize ) && preg_match( '/Content-Length: (\d+)/i', $header, $matches ) ) {
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
		the $default from the function and reverse at the end of $this->mimetype_to_icon(), just like curl resource closing

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
		private function get_remote_filesize_via_headers( $url ) {

			$filesize = false;
			$head     = @get_headers( $url, true );

			if ( false !== $head && is_array( $head ) ) {
				$head = array_change_key_case( $head );

				// Disregard files which return an error status
				if ( 400 > intval( substr( $head[0], 9, 3 ) ) ) {
					// Deal with redirected urls (where get_headers() will return an array for redundant headers)
					if ( isset( $head['content-length'] ) && is_string( $head['content-length'] ) ) {
						$filesize = (int) $head['content-length'];
					}
					else if ( isset( $head['content-length'] ) && is_array( $head['content-length'] ) ) {
						$filesize = (int) $head['content-length'][ ( count( $head['content-length'] ) - 1 ) ];
					}
				}
			}
			unset( $head );
			return $filesize;
		}



		/**
		 * Creates a human readable file size string
		 * - Returns <i>false</i> if the passed parameter is not an integer or a numeric string
		 *
		 * @uses 	$this->byte_suffixes		for the byte suffixes
		 * @param	int				$filesize	filesize in bytes
		 * @return	string|bool 	human readable filesize string
		 * 							or false if the passed variable was not an integer
		 **/
		public function human_readable_filesize( $filesize ) {
			static $count;

			// Will only run once per execution
			if ( ! isset( $count ) ) {
				$count = count( $this->byte_suffixes );
			}

			if ( is_int( $filesize ) && ( $this->settings['show_file_size_over'] * 1024 ) < $filesize ) {
				// Get the figure to use in the string
				for ( $i = 0; ( $i < $count && 1024 <= $filesize ); $i++ ) {
					$filesize = $filesize / 1024;
				}

				// Return the formatted number with the appropriate suffix and required precision
				if ( $i === 0 ) {
					return number_format_i18n( $filesize, 0 ) . ' ' . $this->byte_suffixes[ $i ];
				}
				else {
					return number_format_i18n( $filesize, $this->settings['precision'] ) . ' ' . $this->byte_suffixes[ $i ];
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
		public function validate_options( $received ) {

			$this->remove_default_filter();

			/* Don't change anything if user does not have the required capability */
			if ( false === is_admin() || false === current_user_can( self::REQUIRED_CAP ) ) {
				return $this->settings;
			}


			/* Start off with the current settings and where applicable, replace values with valid received values */
			$clean     = $this->settings;
			$upgrading = isset( $received['upgrading'] ) ? $received['upgrading'] : false;


			foreach ( $clean as $key => $value ) {
				$switch_key = $key;
				if ( strpos( $key, 'enable_' ) === 0 ) {
					$switch_key = 'enable_';
				}

				switch ( $switch_key ) {
					/* Always set the version */
					case 'version':
						$clean[ $key ] = self::VERSION;
						break;


					case 'internal_domains':
						// Only updated in upgrade/activation, otherwise leave as is
						if ( isset( $received[ $key ] ) && true === $upgrading ) {
							$clean[ $key ] = $received[ $key ];
						}
						break;


					case 'image_size':
						if ( isset( $received[ $key ] ) && in_array( $received[ $key ], $this->sizes ) ) {
							$clean[ $key ] = $received[ $key ];
						}
						else if ( function_exists( 'add_settings_error' ) ) {
							// Edge case: should never happen
							add_settings_error( self::SETTINGS_OPTION, $key, esc_html__( 'Invalid image size received', 'mimetypes-link-icons' ) . ', ' . esc_html__( 'the setting has not been changed.', 'mimetypes-link-icons' ), 'error' );
						}
						break;


					case 'image_type':
						if ( isset( $received[ $key ] ) && in_array( $received[ $key ], $this->image_types ) ) {
							$clean[ $key ] = $received[ $key ];
						}
						else if ( function_exists( 'add_settings_error' ) ) {
							// Edge case: should never happen
							add_settings_error( self::SETTINGS_OPTION, $key, esc_html__( 'Invalid image type received', 'mimetypes-link-icons' ) . ', ' . esc_html__( 'the setting has not been changed.', 'mimetypes-link-icons' ), 'error' );
						}
						break;


					case 'leftorright':
						if ( isset( $received[ $key ] ) && isset( $this->alignments[ $received[ $key ] ] ) ) {
							$clean[ $key ] = $received[ $key ];
						}
						else if ( function_exists( 'add_settings_error' ) ) {
							// Edge case: should never happen
							add_settings_error( self::SETTINGS_OPTION, $key, esc_html__( 'Invalid image placement received', 'mimetypes-link-icons' ) . ', ' . esc_html__( 'the setting has not been changed.', 'mimetypes-link-icons' ), 'error' );
						}
						break;


					case 'show_file_size_over':
						if ( isset( $received[ $key ] ) && '' !== trim( $received[ $key ] ) ) {
							$int = $this->validate_int( $received[ $key ] );
							if ( false !== $int ) {
								$clean[ $key ] = $int;
							}
							else if ( function_exists( 'add_settings_error' ) ) {
								add_settings_error( self::SETTINGS_OPTION, $key, esc_html__( 'Invalid show file size over received', 'mimetypes-link-icons' ) . ', ' . esc_html__( 'the setting has not been changed.', 'mimetypes-link-icons' ), 'error' );
							}
							unset( $int );
						}
						else {
							// Empty field, let's assume the user meant no decimals
							$clean[ $key ] = 0;
						}
						break;


					case 'precision':
						if ( isset( $received[ $key ] ) && '' !== trim( $received[ $key ] ) ) {
							$int = $this->validate_int( $received[ $key ] );
							if ( false !== $int ) {
								$clean[ $key ] = $int;
							}
							else if ( function_exists( 'add_settings_error' ) ) {
								add_settings_error( self::SETTINGS_OPTION, $key, esc_html__( 'Invalid rounding precision received', 'mimetypes-link-icons' ) . ', ' . esc_html__( 'the setting has not been changed.', 'mimetypes-link-icons' ), 'error' );
							}
							unset( $int );
						}
						else {
							// Empty field, let's assume the user meant no decimals
							$clean[ $key ] = 0;
						}
						break;


					case 'cache_time':
						// Value received is hours, needs to be converted to seconds before save
						if ( isset ( $received[ $key ] ) && ( is_string( $received[ $key ] ) && '' !== trim( $received[ $key ] ) ) ) {
							$int = $this->validate_int( $received[ $key ] );
							if ( $int !== false ) {
								$clean[ $key ] = ( (int) $int * 60 * 60 );
							}
							else if ( function_exists( 'add_settings_error' ) ) {
								add_settings_error( self::SETTINGS_OPTION, $key, esc_html__( 'Invalid cache time received', 'mimetypes-link-icons' ) . ', ' . esc_html__( 'the setting has not been changed.', 'mimetypes-link-icons' ), 'error' );
							}
							unset( $int );
						}
						else if ( ( isset( $received[ $key ] ) && is_int( $received[ $key ] ) ) && $upgrading === true ) {
							// Received an already validated & multiplied value from the upgrade routine
							$clean[ $key ] = $received[ $key ];
						}
						else if ( function_exists( 'add_settings_error' ) ) {
							add_settings_error( self::SETTINGS_OPTION, $key, esc_html__( 'Invalid cache time received', 'mimetypes-link-icons' ) . ', ' . esc_html__( 'the setting has not been changed.', 'mimetypes-link-icons' ), 'error' );
						}

						break;


					case 'hidden_classname':
						if ( isset( $received[ $key ] ) ) {
							if ( ( is_array( $received[ $key ] ) && $received[ $key ] !== array() ) || ( is_string( $received[ $key ] ) && '' !== trim( $received[ $key ] ) ) ) {
								$classnames = $this->validate_classnames( $received[ $key ] );

								if ( false !== $classnames ) {
									$clean[ $key ] = $classnames;

									if ( ( ! is_array( $received[ $key ] ) && ( $received[ $key ] !== implode( ',', $clean[ $key ] ) && $received[ $key ] !== implode( ', ', $clean[ $key ] ) ) ) && function_exists( 'add_settings_error' ) ) {
										add_settings_error( self::SETTINGS_OPTION, $key, esc_html__( 'One or more invalid classname(s) received, the values have been cleaned - this may just be the removal of spaces -, please check.', 'mimetypes-link-icons' ), 'updated' );
									}
								}
								else if ( function_exists( 'add_settings_error' ) ) {
									// Edge case: should never happen
									add_settings_error( self::SETTINGS_OPTION, $key, esc_html__( 'No valid classname(s) received', 'mimetypes-link-icons' ) . ', ' . esc_html__( 'the setting has not been changed.', 'mimetypes-link-icons' ), 'error' );
								}
							}
							else {
								// Empty field received, so clear out the setting
								$clean[ $key ] = array();
							}
						}
						break;

					/* Covers:
					   'enable_async',
					   'enable_async_debug',
					   'enable_hidden_class',
					   'enable_' . $mimetype */
					case 'enable_':
					case 'show_file_size':
					case 'use_cache':
					default:
						$clean[ $key ] = ( isset( $received[ $key ] ) ? filter_var( $received[ $key ], FILTER_VALIDATE_BOOLEAN ) : false );
						break;
				}
			}

			/* Delete the filesize cache if the cache option was unchecked to make sure a fresh cache
			   will be build if and when the cache option would be checked again */
			if ( false === $clean['use_cache'] && $clean['use_cache'] !== $this->settings['use_cache'] ) {
				delete_option( self::CACHE_OPTION );
			}

			return $clean;
		}



		/**
		 * Validate a value as integer
		 *
		 * @since 3.2
		 *
		 * @param	mixed	$value
		 * @return	mixed	int or false in case or failure to convert to int
		 */
		private function validate_int( $value ) {
			return filter_var( $value, FILTER_VALIDATE_INT );
		}



		/**
		 * Validate received classnames and parse them from a string to an array
		 * Returns false if received value is not a string or empty
		 *
		 * @usedby validate_options() and upgrade_options()
		 * @param string $classnames
		 * @return array|bool
		 */
		public function validate_classnames( $classnames = '' ) {
			$return = false;

			if ( is_array( $classnames ) && $classnames !== array() ) {
				return $this->validate_classnames( implode( ',', $classnames ) );
			}

			if ( is_string( $classnames ) && '' !== trim( $classnames ) ) {
				$classnames = sanitize_text_field( $classnames );
				$classnames = explode( ',', $classnames );
				$classnames = array_map( 'trim', $classnames );
				$classnames = array_map( 'sanitize_html_class', $classnames );
				$classnames = array_filter( $classnames ); // removes empty strings
				if ( is_array( $classnames ) && array() !== $classnames ) {
					$return = $classnames;
				}
			}
			return $return;
		}



		/**
		 * Display our options page using the Settings API
		 * @todo Decide which icon next to the title is most appropriate - options, links or media icon ?
		 * options: id="icon-options-general"
		 * media: id="icon-upload"
		 * links: id="icon-link-manager"
		 */
		public function display_options_page() {

			if ( ! current_user_can( self::REQUIRED_CAP ) ) {
				wp_die( __( 'You do not have sufficient permissions to access this page.', 'mimetypes-link-icons' ) );
			}

			echo '
		<div class="wrap">
		' . screen_icon() . '
		<h2>' . get_admin_page_title() . '</h2>
		<form action="' . esc_url( admin_url( 'options.php' ) ) . '" method="post" accept-charset="' . esc_attr( get_bloginfo( 'charset' ) ) . '">';

			settings_fields( self::SETTINGS_OPTION . '-group' );
			do_settings_sections( self::$name );
			submit_button();

			echo '
		</form>';

			if ( WP_DEBUG || $this->debug === true ) {
				if ( ! extension_loaded( 'xdebug' ) ) {
					echo '<pre>';
					var_dump( $this->settings );
					echo '</pre>';
				}
				else {
					var_dump( $this->settings );
				}
			}
		}


		/**
		 * Display the General Settings section of our options page
		 */
		public function do_settings_section_general() {

			echo '
			<fieldset class="options" name="general">
				<table cellspacing="2" cellpadding="5" class="editform form-table">';

			add_filter( 'mtli_setting_select_box_option_label_image_size', array( $this, 'image_size_option_label' ) );

			$this->do_select_box_row( __( 'Image Size', 'mimetypes-link-icons' ), 'image_size', $this->sizes );

			/* @todo maybe change this to radio buttons ? */
			$this->do_select_box_row( __( 'Image Type', 'mimetypes-link-icons' ), 'image_type', $this->image_types );

			/* @todo maybe change this to radio buttons ? */
			$this->do_select_box_row( __( 'Display images on left or right', 'mimetypes-link-icons' ), 'leftorright', $this->alignments );

			echo '
				</table>
			</fieldset>';
		}


		/**
		 * Display the Image Settings section of our options page
		 */
		public function do_settings_section_images() {

			echo '
			<fieldset class="options" name="images" id="images">
				<table cellspacing="2" cellpadding="5" class="editform form-table image-table">';

			$count = count( $this->mime_types );
			$rows  = ceil( $count / self::NR_OF_COLUMNS );

			// Make sure mimetypes are always sorted alphabetically
			uksort( $this->mime_types, 'strnatcasecmp' );

			for ( $i = 0; $i < $rows; $i++ ) {
				echo '
					<tr>';

				for ( $j = 0; $j < self::NR_OF_COLUMNS; $j++ ) {
					$index = ( ( $j * $rows ) + $i );

					// Normal cell
					if ( isset( $this->mime_types[ $index ] ) ) {
						$mime_type = $this->mime_types[ $index ];
						echo '
						<th nowrap valign="top" width="33%">
							<label for="' . esc_attr( 'enable_' . $mime_type ) . '">' .
							/* Translators: %s = file mime type. */
							sprintf( __( 'Add images to <strong>%s</strong> uploads/files', 'mimetypes-link-icons' ), $mime_type ) . '</label>
						</th>
						<td style="width:24px;"><img src="' . esc_url( plugins_url( '/images/' . $mime_type . '-icon-24x24.png', __FILE__ ) ) . '" alt="' .
						/* Translators: %s = file type. */
						esc_attr( sprintf( __( '%s icon', 'mimetypes-link-icons' ), $mime_type ) ) . '" /></td>
						<td>
							<input type="checkbox" name="' . esc_attr( self::SETTINGS_OPTION . '[enable_' . $mime_type . ']' ) . '" id="' . esc_attr( 'enable_' . $mime_type ) . '" value="on" ' . checked( $this->settings[ 'enable_' . $mime_type ], true, false ) . ' />
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
		public function do_settings_section_advanced() {

			echo '
			<fieldset class="options advanced-1" name="advanced-1">
				<legend>' . esc_html__( 'Enable/Disable classnames?', 'mimetypes-link-icons' ) . '</legend>
				<table width="100%" cellspacing="2" cellpadding="5" class="editform form-table">
					<tr>
						<td><label for="enable_hidden_class"><input type="checkbox" name="' . esc_attr( self::SETTINGS_OPTION . '[enable_hidden_class]' ) . '" id="enable_hidden_class" value="on" ' . checked( $this->settings['enable_hidden_class'], true, false ) . ' /> ' . __( 'Tick this box to have one or more <em>classname(s)</em> that will disable the mime type links (ie: around an image or caption).', 'mimetypes-link-icons' ) . '</label></td>
					</tr>
					<tr>
						<td><label for="hidden_classname">' . esc_html__( 'You can change the classname(s) by editing the field below. If you want to exclude several classnames, separate them with a comma (,).', 'mimetypes-link-icons' ) . '</label></td>
					</tr>
					<tr>
						<td><input type="text" name="' . esc_attr( self::SETTINGS_OPTION . '[hidden_classname]' ) . '" id="hidden_classname" value="' . esc_attr( implode( ', ', $this->settings['hidden_classname'] ) ) . '" /></td>
					</tr>
				</table>
			</fieldset>

			<fieldset class="options advanced-2" name="advanced-2">
				<legend>' . esc_html__( 'Show File Size?', 'mimetypes-link-icons' ) . '</legend>
				<table width="100%" cellspacing="2" cellpadding="5" class="editform form-table">
					<tr>
						<td><label for="show_file_size"><input type="checkbox" name="' . esc_attr( self::SETTINGS_OPTION . '[show_file_size]' ) . '" id="show_file_size" value="on" ' . checked( $this->settings['show_file_size'], true, false ) . ' /> ' . __( 'Display the <em>file size</em> of the attachment/linked file.', 'mimetypes-link-icons' ) . '</label></td>
						<td>
							<label for="precision">' . esc_html__( 'File size rounding precision:', 'mimetypes-link-icons' ) . '
							<input type="text" name="' . esc_attr( self::SETTINGS_OPTION . '[precision]' ) . '" id="precision" value="' . esc_attr( $this->settings['precision'] ) . '" /> ' . esc_html__( 'decimals', 'mimetypes-link-icons' ) . '</label><br />
							<small><em>' . esc_html__( 'sizes less than 1kB will always have 0 decimals', 'mimetypes-link-icons' ) . '</em></small>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<label for="show_file_size_over">' . esc_html__( 'Only show file sizes for files over :', 'mimetypes-link-icons' ) . '
							<input type="text" name="' . esc_attr( self::SETTINGS_OPTION . '[show_file_size_over]' ) . '" id="show_file_size_over" value="' . esc_attr( $this->settings['show_file_size_over'] ) . '" /> ' . esc_html__( 'Kb', 'mimetypes-link-icons' ) . '</label><br />
						</td>
					</tr>
					<tr>
						<td colspan="2">' . esc_html__( 'Retrieving the file sizes of (external) files can be slow. If the file sizes of the files you link to do not change very often, you may want to cache the results. This will result in faster page loading for most end-users of your website.', 'mimetypes-link-icons' ) . '</td>
					</tr>
					<tr>
						<td><label for="use_cache"><input type="checkbox" name="' . esc_attr( self::SETTINGS_OPTION . '[use_cache]' ) . '" id="use_cache" value="on" ' . checked( $this->settings['use_cache'], true, false ) . ' /> ' . esc_html__( 'Cache retrieved file sizes.', 'mimetypes-link-icons' ) . '</label></td>
						<td>
							<label for="cache_time">' . esc_html__( 'Amount of time to cache retrieved file sizes:', 'mimetypes-link-icons' ) . '
							<input type="text" name="' . esc_attr( self::SETTINGS_OPTION . '[cache_time]' ) . '" id="cache_time" value="' . esc_attr( round( $this->settings['cache_time'] / ( 60 * 60 ), 0 ) ) . '" /> ' . esc_html__( 'hours', 'mimetypes-link-icons' ) . '</label>
						</td>
					</tr>
				</table>
			</fieldset>

			<fieldset class="options advanced-3" name="advanced-3">
				<legend>' . esc_html__( 'Enable Asynchronous Replacement?', 'mimetypes-link-icons' ) . '</legend>
				<table width="100%" cellspacing="2" cellpadding="5" class="editform form-table">
					<tr>
						<td colspan="2">' . esc_html__( 'Some themes or plugins may conflict with this plugin. If you find you are having trouble you can switch on asynchronous replacement which (instead of PHP) uses JavaScript to find your links.', 'mimetypes-link-icons' ) . '</td>
					</tr>
					<tr>
						<td><label for="enable_async"><input type="checkbox" name="' . esc_attr( self::SETTINGS_OPTION . '[enable_async]' ) . '" id="enable_async" value="on" ' . checked( $this->settings['enable_async'], true, false ) . ' /> ' . __( 'Tick box to enable <em>asynchronous replacement</em>.', 'mimetypes-link-icons' ) . '</label></td>
						<td><label for="enable_async_debug"><input type="checkbox" name="' . esc_attr( self::SETTINGS_OPTION . '[enable_async_debug]' ) . '" id="enable_async_debug" value="on" ' . checked( $this->settings['enable_async_debug'], true, false ) . ' /> ' . __( 'Tick box to enable <em>asynchronous debug mode</em>.', 'mimetypes-link-icons' ) . '</label></td>
					</tr>
				</table>
			</fieldset>';
		}


		/**
		 * Create table row for a select box
		 *
		 * @since 3.2
		 *
		 * @param	string	$label
		 * @param	string	$field_id
		 * @param	array	$options_array
		 * @return	void
		 */
		private function do_select_box_row( $label, $field_id, $options_array ) {

			echo '
					<tr>
						<th nowrap valign="top" width="33%">
							<label for="' . esc_attr( $field_id ) . '">' . esc_html( $label ) . '</label>
						</th>
						<td>
							<select name="' . esc_attr( self::SETTINGS_OPTION . '[' . esc_attr( $field_id ) . ']' ) . '" id="' . esc_attr( $field_id ) . '">';

			foreach ( $options_array as $k => $v ) {
				$option_value = ( ( is_string( $k ) && $k !== '' ) ? $k : $v );
				/* @api	mixed	$v	Allows for filtering of the option label of a select box on our settings page */
				$option_label = apply_filters( 'mtli_setting_select_box_option_label_' . $field_id, $v );

				echo '
								<option value="' . esc_attr( $option_value ) . '" ' . selected( $this->settings[ $field_id ], $option_value, false ) . '>' . esc_html( $option_label ) . '</option>';
			}
			unset( $v );

			echo '
							</select>
						</td>
					</tr>';
		}


		/**
		 * Turns an image size into a usable label
		 * @param	int		$size
		 * @param	string
		 */
		public function image_size_option_label( $size ) {
			return ( $size . 'x' . $size );
		}


	} /* End of class */


	/* Instantiate our class */
	if ( ( function_exists( 'wp_installing' ) && wp_installing() === false ) || ( ! function_exists( 'wp_installing' ) && ( ! defined( 'WP_INSTALLING' ) || WP_INSTALLING === false ) ) ) {
		add_action( 'plugins_loaded', 'mimetypes_link_icons_init' );
	}

	if ( ! function_exists( 'mimetypes_link__icons_init' ) ) {
		/**
		 * Initialize the class
		 *
		 * @return void
		 */
		function mimetypes_link_icons_init() {
			/* Initialize the static variables */
			Mime_Types_Link_Icons::init_statics();

			$GLOBALS['mimetypes_link_icons'] = new Mime_Types_Link_Icons();
		}
	}


	if ( ! function_exists( 'mimetypes_to_icons' ) ) {
		/**
		 * Function to invoke the mimetypes_to_icons functionality for content
		 * outside of the loop
		 *
		 * @since 3.0
		 * @param	string	$content
		 * @return	string
		 */
		function mimetypes_to_icons( $content ) {
			if ( isset( $GLOBALS['mimetypes_link_icons'] ) ) {
				$async = $GLOBALS['mimetypes_link_icons']->settings['enable_async'];
				$GLOBALS['mimetypes_link_icons']->settings['enable_async'] = false;

				$content = $GLOBALS['mimetypes_link_icons']->mimetype_to_icon( $content );
				$GLOBALS['mimetypes_link_icons']->settings['enable_async'] = $async;
				unset( $async );
			}

			return $content;
		}
	}


	if ( ! function_exists( 'pause_mtli' ) ) {
		/**
		 * Function to temporarily pause the mimetypes link icons plugin
		 *
		 * This function is meant to be used by other plugins/themes as an easy way to temporarily suspend
		 * the adding of the mimetypes link icons.
		 *
		 * @since 3.0
		 * @return void
		 */
		function pause_mtli() {
			if ( isset( $GLOBALS['mimetypes_link_icons'] ) && has_filter( 'the_content', array( $GLOBALS['mimetypes_link_icons'], 'mimetype_to_icon' ) ) ) {
				remove_filter( 'the_content', array( $GLOBALS['mimetypes_link_icons'], 'mimetype_to_icon' ), 15 );
			}
		}
	}


	if ( ! function_exists( 'unpause_mtli' ) ) {
		/**
		 * Function to unpause the mimetypes link icons plugin
		 *
		 * This function is meant to be used by other plugins/themes as an easy way to un-suspend
		 * the adding of the mimetypes link icons.
		 *
		 * @since 3.0
		 * @return void
		 */
		function unpause_mtli() {
			if ( isset( $GLOBALS['mimetypes_link_icons'] ) && ( ( false === $GLOBALS['mimetypes_link_icons']->settings['enable_async'] || true === $GLOBALS['mimetypes_link_icons']->settings['show_file_size'] ) && false === has_filter( 'the_content', array( $GLOBALS['mimetypes_link_icons'], 'mimetype_to_icon' ) ) ) ) {
				add_filter( 'the_content', array( $GLOBALS['mimetypes_link_icons'], 'mimetype_to_icon' ), 15 );
			}
		}
	}

	/* Set up the (de-)activation actions */
	register_activation_hook( __FILE__, array( 'Mime_Types_Link_Icons', 'activate' ) );

} /* End of class-exists wrapper */