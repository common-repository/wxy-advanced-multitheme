<?PHP
/*
	Plugin Name: WXY Advanced Multitheme Plugin Filter
	Plugin URI: http://www.wxytools.com
	Description: A mu-plugin that works with the WXY Tools Advanced Multitheme plugin to temporarily filter out possibly conflicting third party plugins on a page-by-page (or post-by-post) basis.
	Version: 0.1.2
	Author: Clarence "exoboy" Bowman
	Author URI: http://www.bowmandesignworks.com
	License: GPL2
*/


// **********************************************************************************
// BEFORE ANY PLUGINS GET LOADED!
// We need to filter the option that is a list of the currently active plugins, remove the ones we do not want to run then return the edited list.
// **********************************************************************************	
function wxy_advanced_multitheme_mu()
{
	$request_uri = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );

	$is_admin = strpos( $request_uri, '/wp-admin/' );

	$url = $_SERVER[ "REQUEST_URI" ];
	$parsed = parse_url( $url );
		
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

	// now get the ID of the current post/page, so we can load its meta data
	switch (true)
	{
		case isset( $post ) && !empty( $path ) && $path != "/":
			
			// we have a post object, and the path is not empty and is not the root "/"
			$id = $post->ID;

			break;
			
		case empty( $path ) || $path == "/":

			// no path... must be the front page
			$id = get_option('page_on_front');

			break;

		default:
			// do nothing
	}

	// make sure we have an ID to work with
	if( isset( $id ) && !empty( $id ) )
	{
		// now retrieve the metadata
		$meta = get_post_meta( $id );
	
		// see if we should remove certain plugins or not
		if( isset( $meta[ "wxy_tools_advanced_multitheme_stylesheet" ][0] ) && $meta[ "wxy_tools_advanced_multitheme_stylesheet" ][0] != "default" && !empty( $meta[ "wxy_tools_advanced_multitheme_stylesheet" ][0] ) )	
		{	
			// go ahead and filter out certain plugins
			add_filter( 'option_active_plugins', function( $plugins )
			{

				// an array of plugin names/files that should NOT be loaded on this page!
				$blacklist = get_option( 'wxy_advanced_multitheme_blacklist' );

				if( !is_null( $blacklist ) )
				{
					foreach( $blacklist as $key => $val )
					{
						foreach( $plugins as $plugin_key => $plugin_val )
						{						
							// if we have a match, then REMOVE this plugin from the list
							if( strpos( $plugin_val, $val ) === 0)
							{	
								// be sure to clear it out of the POST var space			
								unset( $plugins[ $plugin_key ] );
							}
						}
					}
				}
				// get an array that contains all other plugins NOT in our switch_off array
				//$plugins = array_diff( $plugins, $blacklist );
		
				// return a list of plugins that should be active
				return $plugins;

			} );
		
		} else {
			// there is no alternative template to use, so ignore this part!
		}
	} else {
		// no ID to work with, so ignore this page!
	}

}

wxy_advanced_multitheme_mu();

?>