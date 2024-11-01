<?php 
/*
	Plugin Name: WXY Advanced Multitheme
	Plugin URI: http://www.wxytools.com
	Description: Assign a different theme to every page in your site, and use a different layout for each page of that theme!
	Version: 0.1.2
	Author: Clarence "exoboy" Bowman
	Author URI: http://www.bowmandesignworks.com
	License: GPL2
*/


// ***********************************************************************
// plugin version
// ***********************************************************************
//$wxy_tools_advanced_multitheme_jal_db_version = '0.1.2';

// ***********************************************************************
// block access of this code to outsiders
// ***********************************************************************
if( !defined( 'ABSPATH' ) )
{
	exit;
};

// these are our JSON headers used to talk to the client side JS
define( 'WXY_ADVANCED_MULTITHEME_JSON_HEADER', "^^^^JSON-START^^^^" );
define( 'WXY_ADVANCED_MULTITHEME_JSON_FOOTER', "^^^^JSON-END^^^^" );

// ***********************************************************************
// SETTINGS: handle our settings and help page...
// ***********************************************************************
if( is_admin() )
{
	add_action('admin_menu', 'wxy_advanced_multitheme_plugin_create_menu');

	function wxy_advanced_multitheme_plugin_create_menu()
	{
		//create new top-level menu
		add_options_page('Advanced Multitheme > Settings', 'WXY Advanced Multitheme', 'administrator', 'wxy_advanced_multitheme_options_page' , 'wxy_advanced_multitheme_options_page' );
	}

	// OPTIONS-CONTROL PANE: this is where all the user-facing controls are...
	function wxy_advanced_multitheme_options_page()
	{	
		if( !current_user_can( 'edit_pages' ) )
		{
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		} else {
			
			// include our external file of HTML
			include( "extras/wxy-tools-advanced-multitheme-options.php" );
		}
	}
};

// ***********************************************************************
// ERROR NOTICES: see if there are any error notices to show the user
// ***********************************************************************

// we have a count of the converted images in our page, so let the user know what that count is
if( is_admin() )
{
	add_action( 'admin_notices', 'wxy_tools_advanced_multitheme_my_error_notice' );
};

function wxy_tools_advanced_multitheme_my_error_notice()
{
	// see if we have any messages to display
	$msg = get_transient( "wxy_advanced_multitheme_alert" );

	// see if we are exempted
	if( strlen( $msg ) > 0 )
	{
		echo '<div class="error notice is-dismissible"><p>' . $msg . "</p></div>";
	}
	
	// for non-error messaging
	//	echo '<div class="notice notice-success is-dismissible"><p>WXY: message goes here.</p></div>';	

	// be sure to remove the transient, so things do not display tewice!
	if( $msg )
	{
		delete_transient( "wxy_advanced_multitheme_alert" );
	}
};

// ***********************************************************************
// PLUGIN ACTIVATED: do these items when this plugin is ACTIVATED
// ***********************************************************************
register_activation_hook( __FILE__, 'wxy_tools_advanced_multitheme_activation');

function wxy_tools_advanced_multitheme_activation()
{
	// ------------------------------------------------------
	// move a copy of our plugin filter mu-plugin to the mu-plugin folder...
	// ------------------------------------------------------
	
	// our source file to move
	$source = plugin_dir_path(__FILE__) . '/mu-plugins/wxy_advanced_multitheme_filter_plugins.php';
	
	// see if our constants exist
	$mu_plugins_dir = ( defined( 'WPMU_PLUGIN_DIR' ) && defined( 'WPMU_PLUGIN_URL' ) ) ? WPMU_PLUGIN_DIR : trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins';
	$mu_plugins_dir = untrailingslashit( $mu_plugins_dir );
	$dest = $mu_plugins_dir . '/wxy_advanced_multitheme_filter_plugins.php';

	// create our directory
	$result = wp_mkdir_p( $mu_plugins_dir );

	// create an error object
	$error = "";

	if( $result )
	{
		// we have or created the mu-plugin directory, copy our file from the plugin to the mu-plugin directory
		$result = copy( $source, $dest );
		
		if( $result )
		{
			// file moved, now change its permissions
			$result = chmod( $dest, 0755);// 0777 read-write everyone
			
		} else {
			// file could not be copied, throw an error
			$error = '<span style="color:red;font-size:18px;">WXY Advanced Multitheme was unable to install a required mu-plugin!</span><br /><br /><span style="font-style:italic;">This plugin will not function properly without it. Please use ftp software to sign in to your hosting server and install the file directly from:<br /><span style="color:red;">wp-content > plugins > wxy-tools-advanced-multitheme > mu-plugins > wxy_advanced_multitheme_filter_plugins.php</span><br />into: <span style="color:red;">wp-content > mu-plugins > wxy_advanced_multitheme_filter_plugins.php</span>';		
		}
	} else {
		// directory could not be created, show error
		$error = '<span style="color:red;font-size:18px;">WXY Advanced Multitheme was unable to create a mu-directory!</span><br /><br /><span style="font-style:italic;">This plugin will not function properly without its included mu-plugin file. Please use ftp software to sign in to your hosting server, create a directory called "mu-plugins" in the wp-content directory and install the file directly from:<br /><span style="color:red;">wp-content > plugins > wxy-tools-advanced-multitheme > mu-plugins > wxy_advanced_multitheme_filter_plugins.php</span><br />into: <span style="color:red;">wp-content > mu-plugins > wxy_advanced_multitheme_filter_plugins.php</span>';
	}

	// installation was successful! Show alert about turning off plugins in the settings panel
	if( strlen( $error ) <= 0 )
	{
		$error = '<span style="color:red;font-size:18px;">WXY Advanced Multitheme IMPORTANT NOTE:</span><br />This plugin is designed to work with any theme or theme builder, however there are times when a conflict may occur and your pages/posts will not load the alternate theme or may behave erratically. If that happens, then go to <a href="'. site_url() . '/wp-admin/options-general.php?page=wxy_advanced_multitheme_options_page">Settings > WXY Advanced Multitheme > Plugins Blacklist</a> and check the boxes next to your default theme or theme building plugins. This will temporarily turn these off to allow WXY to deliver page/post content using the correct alternate theme, instead of your default theme.';
	}
	
	// we will always pass a message for success or failure
	set_transient( "wxy_advanced_multitheme_alert", $error, 60 * 60 *.25 );// expire in 15 minutes

};


// ***********************************************************************
// PLUGIN DE-ACTIVATED: do these items when this plugin is DE-ACTIVATED
// ***********************************************************************
register_deactivation_hook( __FILE__, 'wxy_tools_advanced_multitheme_deactivation');

function wxy_tools_advanced_multitheme_deactivation()
{
	// ------------------------------------------------------
	// REMOVE our mu-plugin
	// ------------------------------------------------------
	$mu_plugins_dir = ( defined( 'WPMU_PLUGIN_DIR' ) && defined( 'WPMU_PLUGIN_URL' ) ) ? WPMU_PLUGIN_DIR : trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins';
	$mu_plugins_dir = untrailingslashit( $mu_plugins_dir );
	$source = $mu_plugins_dir . '/wxy_advanced_multitheme_filter_plugins.php';
	
	// change persmission to make sure it is removable
	chmod( $source, 0777);
	
	// now remove the file!
	unlink( $source );
};

// ***********************************************************************
// PLUGIN UPGRADED: make sure to move mu-plugin to its mu-plugins folder
// ***********************************************************************
add_action( 'upgrader_process_complete', 'wxy_advanced_multitheme_upgraded',10, 2);

function wxy_advanced_multitheme_upgraded( $upgrader_object, $options )
{
    $current_plugin_path_name = plugin_basename( __FILE__ );

	if( $options['action'] == 'update' && $options['type'] == 'plugin' )
	{
		foreach( $options['plugins'] as $each_plugin)
		{
			if ($each_plugin == $current_plugin_path_name )
			{
				// ------------------------------------------------------
				// move a copy of our plugin filter mu-plugin to the mu-plugin folder...
				// ------------------------------------------------------
	
				// our source file to move
				$source = plugin_dir_path(__FILE__) . '/mu-plugins/wxy_advanced_multitheme_filter_plugins.php';
	
				// see if our constants exist
				$mu_plugins_dir = ( defined( 'WPMU_PLUGIN_DIR' ) && defined( 'WPMU_PLUGIN_URL' ) ) ? WPMU_PLUGIN_DIR : trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins';
				$mu_plugins_dir = untrailingslashit( $mu_plugins_dir );
				$dest = $mu_plugins_dir . '/wxy_advanced_multitheme_filter_plugins.php';

				// create our directory
				$result = wp_mkdir_p( $mu_plugins_dir );

				// we have or created the mu-plugin directory, copy our file from the plugin to the mu-plugin directory
				$result = copy( $source, $dest );		
			}
		}
	}
}


// ***********************************************************************
// PLUGIN UN-INSTALLED: do these items when this plugin is UN-INSTALLED
// ***********************************************************************
register_uninstall_hook ( __FILE__, 'wxy_tools_advanced_multitheme_uninstall' );

function wxy_tools_advanced_multitheme_uninstall()
{
	// ------------------------------------------------------
	// REMOVE our mu-plugin
	// ------------------------------------------------------
	$mu_plugins_dir = ( defined( 'WPMU_PLUGIN_DIR' ) && defined( 'WPMU_PLUGIN_URL' ) ) ? WPMU_PLUGIN_DIR : trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins';
	$mu_plugins_dir = untrailingslashit( $mu_plugins_dir );
	$source = $mu_plugins_dir . '/wxy_advanced_multitheme_filter_plugins.php';
	
	// change persmission to make sure it is removable
	chmod( $source, 0777);
	
	// now remove the file!
	unlink( $source );
};



// *************************************************************************************************
// GET META DATA: we need this in a global for later, since we cannot get the ID in all places we need it
// *************************************************************************************************

// create a global to use for temporary values
$wxy_tools_adv_multi_meta = array();

// default to no alternate page layout
$wxy_tools_adv_multi_meta[ "switch_theme" ] = false;
$wxy_tools_adv_multi_meta[ "page_type" ] = "";

// get meta object for page/post, as long as we are NOT on an admin page
if( $GLOBALS[ 'pagenow' ] != 'wp-login.php' )//!is_admin() && 
{
	function wxy_tools_advanced_multitheme_get_meta()
	{
		global $wxy_tools_adv_multi_meta;
	
		// see if an old meta global is set, if so, wipe it
		if( isset( $_GLOBALS[ "wxy_tools_adv_multi_meta" ] ) )
		{
			unset( $_GLOBALS[ "wxy_tools_adv_multi_meta" ] );
		}
		
		$_GLOBALS[ "wxy_tools_adv_multi_meta" ] = array();
		$wxy_tools_adv_multi_meta[ "switch_theme" ] = false;
	
		$url = $_SERVER[ "REQUEST_URI" ];
		$parsed = parse_url( $url );
		
		if( is_admin() && ( isset( $_GET ) || isset( $_POST ) ) )
		{
			switch (true)
			{
				case isset( $_GET[ "post" ] ):
					$id = $_GET[ "post" ];
					break;
					
				case isset( $_POST[ "post" ] ):
					$id = $_POST[ "post" ];
					break;
			}

		} else {
		
			$path = $parsed[ "path" ];
		
			// get the full path for path 1
			$path1 = $parsed[ "path" ];

			// now get both post data sets by path, in case this page is just a single post
			$post1 = get_page_by_path( $path1, OBJECT, array( 'page', 'post' ));
		
			// this one is only the trailing portion of the url
			$path2 = basename( untrailingslashit( $path ) );
			$post2 = get_page_by_path( $path2, OBJECT, array( 'page', 'post' ));

			if( !isset( $post1 ) || empty( $post1 ) )
			{
				$post = $post2;
				$path = $path2;
			
				// assign whether this is a post or page
				$wxy_tools_adv_multi_meta[ "page_type" ] = "post";
			} else {
				$post = $post1;
				$path = $path1;
			
				// assign whether this is a post or page
				$wxy_tools_adv_multi_meta[ "page_type" ] = "page";
			}

			// this is for comparing our server url to our home url, in case this site is inside a folder...
			$server_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
			$server_url = rtrim( $server_url, "/" );
			$home_url = home_url();

			// now get the ID of the current post/page, so we can load its meta data
			switch (true)
			{
				case isset( $post ) && !empty( $path ) && $path != "/":
			
					// we have a post object, and the path is not empty and is not the root "/"
					$id = $post->ID;

					break;
				
				case empty( $path ) || $path == "/" || $home_url == $server_url:

					// no path... must be the front page
					$id = get_option('page_on_front');
					break;

				default:
					// do nothing
			}
		}
		
		// now retrieve the metadata (if any)
		if( isset( $id ) )
		{
			$meta = get_post_meta( $id );
		} else {
			$meta = NULL;
		}
		
		// -------------------------------------------------------------------------
		// get our post/pages saved meta data and transfer our meta values to our global meta object for use later
		// -------------------------------------------------------------------------
		$wxy_tools_adv_multi_meta[ "wxy_tools_advanced_multitheme_stylesheet" ] = ( isset( $meta[ "wxy_tools_advanced_multitheme_stylesheet" ][0] ) ? $meta[ "wxy_tools_advanced_multitheme_stylesheet" ][0] : NULL );
		$wxy_tools_adv_multi_meta[ "wxy_tools_advanced_multitheme_template" ] = ( isset( $meta[ "wxy_tools_advanced_multitheme_template" ][0] ) ? $meta[ "wxy_tools_advanced_multitheme_template" ][0] : NULL );
		
		// -------------------------------------------------------------------------
		// see if this is a search query
		// -------------------------------------------------------------------------
		if( isset( $parsed[ "query" ] ) && ( strpos( $parsed[ "query" ], "s=" ) !== false || strpos( $parsed[ "query" ], "search=" ) !== false ) )
		{
			// now get our options to see what to replace the search theme with....
			$options = get_option( 'wxy_advanced_multitheme_options' );
			
			// see if there is a setting for an override, otherwise, ignore this
			if( isset( $options["search_stylesheet"] ) && !empty( $options["search_stylesheet"] ) )
			{
				// set our data in the global object, this will override any saved (or not saved) settings
				$wxy_tools_adv_multi_meta[ "wxy_tools_advanced_multitheme_stylesheet" ] = ( isset( $options["search_stylesheet"] ) ? $options["search_stylesheet"] : NULL );
				$wxy_tools_adv_multi_meta[ "wxy_tools_advanced_multitheme_template" ] = ( isset( $options["search_template"] ) ? $options["search_template"] : "search.php" );
				
				// now make sure that even if it is empty, we replace it with the default
				if( strlen( $wxy_tools_adv_multi_meta[ "wxy_tools_advanced_multitheme_template" ] ) <= 0 )
				{
					$wxy_tools_adv_multi_meta[ "wxy_tools_advanced_multitheme_template" ] = "search.php";
				}
			}
		}
		
		// now see if this page does NOT have an alternate theme, so we can ignore it
		$stylesheet = $wxy_tools_adv_multi_meta[ "wxy_tools_advanced_multitheme_stylesheet" ];
		$template = $wxy_tools_adv_multi_meta[ "wxy_tools_advanced_multitheme_template" ];
		
		// see if we should ignore trying to switch themes....	
		if( !isset( $stylesheet ) || $stylesheet == "default" || empty( $stylesheet ) === true )
		{	
			$wxy_tools_adv_multi_meta[ "switch_theme" ] = false;
		} else {
			$wxy_tools_adv_multi_meta[ "switch_theme" ] = true;
		}

	}// end ƒ

	// -------------------------------------------------------------------------
	// make sure we only switch themes where appropriate in the site
	// -------------------------------------------------------------------------
	switch (true)
	{
		case is_admin() && $GLOBALS[ 'pagenow' ] == "post.php":
		
			wxy_tools_advanced_multitheme_get_meta();
			break;
		
		case !is_admin() && $GLOBALS[ 'pagenow' ] != 'wp-login.php':
		
			wxy_tools_advanced_multitheme_get_meta();
			break;
	}
};


// ***********************************************************************
// ADMIN: load our external JS
// ***********************************************************************
/**
 * Enqueue a script in the WordPress admin, excluding edit.php.
 *
 * @param int $hook Hook suffix for the current admin page.
 */
if( is_admin() )
{
	add_action( 'admin_enqueue_scripts', 'wxy_advanced_multitheme_scripts' );
	//add_action( 'admin_print_scripts-wxy_advanced_multitheme_options_page', 'wxy_advanced_multitheme_scripts');
};

function wxy_advanced_multitheme_scripts()
{
	//wp_enqueue_script( string $handle, string $src = '', array $deps = array(), string|bool|null $ver = false, bool $in_footer = false )
	wp_register_script('wxy_tools_advanced_multitheme_scripts', plugins_url('js/wxy-tools-advanced-multitheme-scripts.js', __FILE__), array("jquery"), '', true );
 	wp_enqueue_script('wxy_tools_advanced_multitheme_scripts');
};


// ***********************************************************************
// ADMIN META BOX: add a select menu with alternate themes and their pages to the page editing panel
// ***********************************************************************
add_action("add_meta_boxes", "wxy_advanced_multitheme_custom_meta_box");

function wxy_advanced_multitheme_custom_meta_box()
{
    add_meta_box( "wxy-advanced-multitheme-meta-box", "WXY Advanced Multitheme", "wxy_advanced_multitheme_custom_meta_box_markup", array( "page","post" ), "side", "high", null);
};

function wxy_advanced_multitheme_custom_meta_box_markup()
{
	global $post, $wxy_tools_adv_multi_meta;
	$ID = $post->ID;

	$wxy_tools_adv_multi_meta = get_post_meta( $ID );

	// ------------------------------------------------------------------------------------
	// METADATA: get the current setting for this page's theme/template from our page's meta data
	// ------------------------------------------------------------------------------------
	$stylesheet = ( isset( $wxy_tools_adv_multi_meta[ 'wxy_tools_advanced_multitheme_stylesheet' ][0] ) ? $wxy_tools_adv_multi_meta[ 'wxy_tools_advanced_multitheme_stylesheet' ][0] : NULL );
	$template = ( isset( $wxy_tools_adv_multi_meta[ 'wxy_tools_advanced_multitheme_template' ][0] ) ? $wxy_tools_adv_multi_meta[ 'wxy_tools_advanced_multitheme_template' ][0] : NULL );
	
	// ------------------------------------------------------------------------------
	// this is the action thaty client-side wants to perform
	// ------------------------------------------------------------------------------
	$action = esc_url( admin_url('admin-ajax.php') );

	// ------------------------------------------------------------------------------
	// get a list of ALL themes...
	// ------------------------------------------------------------------------------
	$args = array();
	$themes = wp_get_themes( $args );

	// ------------------------------------------------------------------------------
	// create a form to show themes/pages and allow for changes....
	// ------------------------------------------------------------------------------
	$select_menu = '<input type="hidden" id="wxy-advanced-multitheme-submission-path" value="' . $action . '" class="wxy-advanced-multitheme-form-input" /><select id="wxy-advanced-multitheme-themes" class="wxy-advanced-multitheme-form-input"><option value="default">Select theme for this page</option>';

	// get the titles of for each theme and any value we need for the select menus
	foreach( $themes as $key => $value )
	{
		$selected = "";

		if( isset( $stylesheet ) && $stylesheet != "default" && $stylesheet == $key )
		{
			// we have a match, so select this option
			$selected = "selected";
		}
		
		// get the names of each theme
		$select_menu .= '<option value="' . $key . '" '.$selected.'>' . $value . '</option>';	
	}

	//  close our select menu and populate our hidden form fields
	$select_menu .= '</select>	
		<input type="hidden" id="action" value="wxy_advanced_multitheme_request" class="wxy-advanced-multitheme-form-input" />
		<input type="hidden" id="wxy_advanced_multitheme_action" name="wxy_advanced_multitheme_action" value="wxy_advanced_multitheme_settings_save" class="wxy-advanced-multitheme-form-input" />
		<input type="hidden" id="wxy_advanced_multitheme_post_id" name="wxy_advanced_multitheme_post_id" value="' . $ID . '" class="wxy-advanced-multitheme-form-input" />';
	
	// now get all page layouts for each of the above themes
	$select_menu .= '<select id="wxy-advanced-multitheme-pages" class="wxy-advanced-multitheme-pages wxy-advanced-multitheme-form-input" ><option value="default">Default Template</option></select>';

	// place our options into a hidden div, so we can turn them on and off in the select menu
	$select_menu .= '<div id="wxy-advanced-multitheme-options-holder" class="wxy-advanced-multitheme-options-holder" style="display:none;">';
	
	// get all the page layouts (templates) for each theme...
	foreach( $themes as $theme )
	{
		// get all the page layouts (templates) for each theme...
		$templates = $theme->get_page_templates();
		
		// use the template name for name so that it will match up with our select menu values...
		$name = $theme->template;
		
		// create a string to append to our class that is based on the template name, get rid of all any spces and convert to lower case
		$suffix = strtolower( $name );
		$suffix = preg_replace( '/[^a-z0-9]/', "_", $suffix );
				
		// only assign option entries if this theme has templates other than default!
		if( !empty( $templates ) )
		{
			foreach ( $templates as $template_name => $template_filename )
			{
				// look for a matching value, so we can flag it as selected
				$selected = "";

				if( isset( $template ) && $template != "default" && $template == $template_name )
				{
					// we have a match, so select this option
					$selected = "selected";
				}
				
				// assign a class based on the parent theme to allow us to show/hide templates for specific themes
				$select_menu .= '<option value="'.$template_name.'" class="wxy-adv-multi-page-option wxy-adv-multi-page-'.$suffix.'" '.$selected.'>'.$template_filename.'</option>';
    		}
		}
		
	}

	$select_menu .= '</div><a href="'. site_url() . '/wp-admin/options-general.php?page=wxy_advanced_multitheme_options_page"><div class="wxy-advanced-multitheme-donate-btn">$</div></a><div class="wxy-advanced-multitheme-help-btn">?</div><style type="text/css">
		.wxy-advanced-multitheme-help-btn { width:25px;height:25px;line-height:25px;background-color:#AAA;text-align:center;color:#FFF;font-size:17px;font-weight:bold;position:relative;top:13px;-moz-border-radius:50px;-webkit-border-radius:50px;-khtml-border-radius:50px; border-radius:50px;border-radius:50px;margin:0px 0px 8px 0px; }
		.wxy-advanced-multitheme-help-btn:hover { background-color:#000;cursor:pointer; }
		
		.wxy-advanced-multitheme-donate-btn { width:25px;height:25px;line-height:25px;background-color:#AAA;text-align:center;color:#FFF;font-size:17px;font-weight:bold;position:absolute;bottom:7px;left:45px;-moz-border-radius:50px;-webkit-border-radius:50px;-khtml-border-radius:50px; border-radius:50px;border-radius:50px; }
		.wxy-advanced-multitheme-donate-btn:hover { background-color:#000;cursor:pointer; }
		
		.wxy-advanced-multitheme-pages { margin-top:10px;display:none; }
		
		#wxy-advanced-multitheme-themes { margin-top:8px; }
		
	</style>';

echo $select_menu;

};

// ************************************************************************************
// ADMIN: FORM POST SUBMISSIONS!
// ************************************************************************************
if( is_admin() )
{
	add_action( 'admin_init', 'wxy_advanced_multitheme_add_ajax_actions' );
	add_action( 'wp_loaded', 'wxy_advanced_multitheme_add_ajax_actions' );
};

function wxy_advanced_multitheme_add_ajax_actions()
{
	add_action( 'wp_ajax_wxy_advanced_multitheme_request', 'wxy_advanced_multitheme_post_handler' );
	add_action( 'wp_ajax_nopriv_wxy_advanced_multitheme_request', 'wxy_advanced_multitheme_post_handler' );
};
	

function wxy_advanced_multitheme_post_handler()
{		
	// -------------------------------------------------------------------------------------------
	// when user changes settings, it should save to page's meta data
	$vars = isset( $_POST ) ? $_POST : NULL;
	$ID = isset( $_POST[ "wxy_advanced_multitheme_post_id" ] ) ? $_POST[ "wxy_advanced_multitheme_post_id" ] : NULL;
	
	$result = array();

	if( $vars )
	{	
		if( $vars[ "wxy_advanced_multitheme_action" ] == "wxy_advanced_multitheme_settings_save" )
		{
			$result[ "result" ] = "";
			$result[ "status" ] = "fail";
			$result[ "message" ] = "Settings not saved, try again.";
			
			$theme = $vars[ 'wxy-advanced-multitheme-themes' ];
			$page = $vars[ 'wxy-advanced-multitheme-pages' ];
			
			
			//delete_post_meta($post_id, $meta_key, $meta_value);
			delete_post_meta( $ID, 'wxy_tools_advanced_multitheme_stylesheet', NULL );
			delete_post_meta( $ID, 'wxy_tools_advanced_multitheme_template', NULL);
				
			if( isset( $theme ) && $theme == "default" )
			{
				// user is not using an alternate theme for this page!
			
				$result[ "status" ] = "success";
				$result[ "message" ] = "Settings cleared.";
				
			} else {
				
				// add our new option, since it does not already exist AND is not the same value already
				$updated_theme = add_post_meta( $ID, 'wxy_tools_advanced_multitheme_stylesheet', $theme, true);

				// add our new option, since it does not already exist AND is not the same value already
				$updated_page = add_post_meta( $ID, 'wxy_tools_advanced_multitheme_template', $page, true);
			
				if( $updated_page != false && $updated_theme != false )
				{
					$result[ "status" ] = "success";
					$result[ "message" ] = "Settings saved.";
				}
			}
		}
		
		
	} else {

		// no vars, so fail....
		$result[ "result" ] = NULL;
		$result[ "status" ] = "fail";
		$result[ "message" ] = "No form vars supplied";
		
	}
	
	// send back a result object
	echo WXY_ADVANCED_MULTITHEME_JSON_HEADER . json_encode( $result ) . WXY_ADVANCED_MULTITHEME_JSON_FOOTER;
	exit();
};


// ***********************************************************************
// BEGIN THEME SWITCH: switch from the default theme/page layout to our alternate theme/page layout
// ***********************************************************************
if( $wxy_tools_adv_multi_meta[ "switch_theme" ] === true )//&& !is_admin()
{
	add_filter( 'pre_option_stylesheet', 'wxy_get_stylesheet' );
	add_filter( 'pre_option_template', 'wxy_get_template' );
};

function wxy_get_stylesheet( $default )
{
	$result = wxy_tools_advanced_multitheme_get( 'stylesheet', $default );

	return $result;
};

function wxy_get_template( $default )
{
	global $wxy_tools_adv_multi_meta;
	
	$result = wxy_tools_advanced_multitheme_get( 'template', $default );

	return $result;
};


// *************************************************************************************************
// GET THEME OR STYLESHEET: see if we need to use an alternative theme instead of the default
// *************************************************************************************************
function wxy_tools_advanced_multitheme_get( $type, $default )
{	
	// get the ID by the slug, so we can find out if there is a theme/template to use instead of the default
	global $wxy_tools_adv_multi_meta;

	$stylesheet = ( isset( $wxy_tools_adv_multi_meta[ 'wxy_tools_advanced_multitheme_stylesheet' ] ) ? $wxy_tools_adv_multi_meta[ 'wxy_tools_advanced_multitheme_stylesheet' ] : NULL );
	$template = ( isset( $wxy_tools_adv_multi_meta[ 'wxy_tools_advanced_multitheme_template' ] ) ? $wxy_tools_adv_multi_meta[ 'wxy_tools_advanced_multitheme_template' ] : NULL );
						
	if( $default != false )
	{
		$result = $default;
	}

	switch (true)
	{
		case isset( $type ) && $type == "stylesheet":

			$result = $stylesheet;
	
			break;
			
		case isset( $type ) && $type == "template":

			switch (true)
			{
				case $wxy_tools_adv_multi_meta[ "page_type" ] == "post":
				
					$template = $stylesheet;//"single.php";
					break;
						
				case $wxy_tools_adv_multi_meta[ "page_type" ] == "page":
				
					$template = $stylesheet;
					break;
			}

			$result = $template;

			break;

		default:
			// just pass along our default value (assigned above)....
	}

	return $result;

};


// *************************************************************************************************
// SWITCH PAGE LAYOUT: make sure the site uses the correct assigned page file for this alternate theme
// *************************************************************************************************
if( $wxy_tools_adv_multi_meta[ "switch_theme" ] === true )//&& !is_admin() 
{
	add_filter( 'template_include', 'wxy_tools_advanced_multisite_switch_template' );
	add_filter( 'single_template', 'wxy_tools_advanced_multisite_switch_template' );
};

function wxy_tools_advanced_multisite_switch_template( $default )
{
	// get the ID by the slug, so we can find out if there is a theme/template to use instead of the default
	global $wxy_tools_adv_multi_meta;

	$stylesheet = ( isset( $wxy_tools_adv_multi_meta[ 'wxy_tools_advanced_multitheme_stylesheet' ] ) ? $wxy_tools_adv_multi_meta[ 'wxy_tools_advanced_multitheme_stylesheet' ] : NULL );
	$template = ( isset( $wxy_tools_adv_multi_meta[ 'wxy_tools_advanced_multitheme_template' ] ) ? $wxy_tools_adv_multi_meta[ 'wxy_tools_advanced_multitheme_template' ] : NULL );

	// make sure our stylesheet is NOT "default" and is not empty
	if( isset( $stylesheet ) && $stylesheet != "default" && !empty( $stylesheet ) )
	{
		// next, make sure the template is not empty or "default"
		if( !isset( $template ) || $template == "default" || empty( $template ) )
		{
			switch (true)
			{
				case $wxy_tools_adv_multi_meta[ "page_type" ] == "post":
				
					$template = "single.php";
					break;
						
				case $wxy_tools_adv_multi_meta[ "page_type" ] == "page":
				
					$template = "page.php";
					break;
			}
		}
		
		// now join the two into our new path
		$path = get_theme_root( $stylesheet ) . "/$stylesheet/$template";

		return $path;
	} else {

		return $default;
	}
};

?>