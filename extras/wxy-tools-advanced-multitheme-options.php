<?php
	/*
		options and documentation for WXY Tools Advanced Multitheme
		(c)2016-Present Clarence "exoboy" Bowman and Bowman Design Works.
		WXY Tools™ at http://www.wxytools.com
		
	*/

	// ***********************************************************************
	// OPTIONS PAGE
	// ***********************************************************************
	wxy_advanced_multitheme_options();
	
	function wxy_advanced_multitheme_options()
	{
		// FORM SUBMITTED - BUILD NEW BLACKLIST From POST
		// see if we need to update our option
		$blacklist = array();
		
		// -------------------------------------------------------------
		// get our current blacklist entries
		// -------------------------------------------------------------
		$blacklist = get_option( 'wxy_advanced_multitheme_blacklist' );
		
		// -------------------------------------------------------------
		// get all other options
		// -------------------------------------------------------------
		$options = get_option( 'wxy_advanced_multitheme_options' );
		
		// -------------------------------------------------------------
		// create the options if we do not have one
		// -------------------------------------------------------------
		if( !isset( $blacklist ) || $blacklist === false || empty( $blacklist ) )
		{
			$blacklist = array();
			
			// create a new option
			add_option( 'wxy_advanced_multitheme_blacklist', $blacklist );
		}
		
		if( !isset( $options ) || $options === false || empty( $options ) )
		{
			$options = array();
			
			// create a new option
			add_option( 'wxy_advanced_multitheme_options', $options );
		}

		// -------------------------------------------------------------
		// see if we have a save in progress
		// -------------------------------------------------------------
		if( isset( $_POST[ "blacklist_checkbox_action" ] ) && $_POST[ "blacklist_checkbox_action" ] == "save" )
		{
			unset( $_POST[ "blacklist_checkbox_action" ] );
	
			// since we have a save command, reset our blacklist var and build the new one
			$blacklist = array();
			$reset_form = false;
			$index = 0;
				
			foreach( $_POST as $key => $val)
			{	
				if( strpos( $key, 'blacklist_checkbox_') === 0)
				{
					array_push( $blacklist, $val );
			
					// be sure to clear it out of the POST var space			
					unset( $_POST[ $key ] );
				}
			}
			
			// handle our options
			$options[ "search_stylesheet" ] = $_POST[ "wxy_advamced_multitheme_options_stylesheet" ];
			$options[ "search_template" ] = $_POST[ "wxy_advamced_multitheme_options_template" ];

			// we had a save or other event, so save the newly calculated blacklist!
			$result = update_option('wxy_advanced_multitheme_blacklist', $blacklist );
			$result = update_option('wxy_advanced_multitheme_options', $options );

			// now force a reload to discard the form
			echo '<script type="text/javascript">location =  location + "";</script>';
				
		}

		// -------------------------------------------------------------
		// always save our options back to Wordpress
		// -------------------------------------------------------------
		update_option('wxy_advanced_multitheme_blacklist', $blacklist );
		update_option('wxy_advanced_multitheme_options', $options );

?>

<!-- css -->
<style type="text/css">
	.wxy_advanced_multitheme_checkbox_holder { width:100%;height:auto;position:relative;display:block;font-size:18px;text-align:left;padding:0px 0px 25px 30px; }
	.wxy_advanced_multitheme_checkbox { display:block;position:absolute;left:0px;top:5px; }



</style>

<!-- HTML BEGINS HERE -->
<div class="wrap">
	<br /><h2 style="font-size:24px;">WXY Tools Advanced Multitheme </h2>version 0.1.2
	
	<!-- divider bar --><div style="width:99%;height:2px;background-color:#666;position:relative;"></div>
	
	<!-- content spacer --><div class="wxy-tools-stickyscroll-content-spacer"></div>
	
<!-- HIDES THIS FOR NOW -->
</div>

<!-- TAB BUTTONS -->
<h2 class="nav-tab-wrapper" style="margin:0px;display:none;">
    <div class="wxy-tools-stickyscroll-tab-btn nav-tab" data-content="wxy-tools-stickyscroll-docs-tab">About</div>
</h2>

<!-- ============================= -->
<!-- page content starts here OPEN --><div style="width:80%;max-width:700px;position:relative;height:auto;padding:20px;display:block;">


	<br /><h2>Plugins Blacklist</h2>
	
	<p>When a page is requested by a visitor, WordPress puts the page together on the server from multiple pieces and using many processes. After compiling the requested content into a single page, it sends it on its way to be displayed in your browser.</p>
	
	<p>WXY Tools Advanced Multitheme gives you the ability to assign not only a different theme for each page/post, but also a different layout (template). It does this by checking to see if an alternative theme/template has been assigned for each page/post each time WordPress recieves a requested for content.</p>
	
	<p>That is where this blacklist comes in — you see, there are a huge number of theme builders and other content creation tools that affect the flow of the page-assembly process. In order to work with all of these and not cause crashes or mangled content, WXY Tools Advanced Multitheme uses a blacklist to tell WordPress to temporarily avoid loading and running certain plugins that might cause a conflict.</p>
	
	<p>Don't worry, the change is completely temporary, and does not actually turn off the plugin, it merely ignores it for that particular page/post where you want to use an alternative theme/template.</p>
	
	<!-- content spacer --><div style="height:20px;"></div>
		
	<p style="color:#F00;font-style:italic;font-size:16px;">Select the plugins that you DO NOT want to load when a page/post that uses an alternative theme/template is requested by a visitor.</p>
	
	<!-- content spacer --><div style="height:10px;"></div>
	
	<form method="POST" action="<?php echo $_SERVER[ "REQUEST_URI" ] ?>">
	
<?php
	
	// get the inxes of the active plugins
	$plugins_active = get_option('active_plugins');
	
	// get all the plugins to serch through
	$plugins_all = get_plugins();
		
	$count = 0;
	
	foreach( $plugins_active as $index )
	{
		// see if this is already checked in our blacklist.....
		$checked = "";
				
		if( in_array( $index, $blacklist ) )
		{
			$checked = "checked";
		}

		// make sure we do not list our own plugin!
		if( strtolower( $plugins_all[ $index ]["Name"] ) != strtolower( "WXY Advanced Multitheme" ) )
		{
			echo '<div class="wxy_advanced_multitheme_checkbox_holder"><input name="blacklist_checkbox_'.$count.'" type="checkbox" class="wxy_advanced_multitheme_checkbox" value="'.$index.'" '.$checked.' />'.$plugins_all[ $index ]["Name"].'</div>';
		}
		
		$count += 1;
	}
	
	?>
	
	<!-- content spacer --><div style="height:20px;"></div>
	
	<?php
	echo submit_button( "Save Blacklist", 'primary', 'submit', false, array() );
	
	?>
	
	<!-- content spacer --><div style="height:65px;"></div>
	
	<!-- other options -->
	<p style="color:#F00;font-size:24px;font-weight:bold;margin:0px;">Special Settings</p>
	<p style="font-style:italic;font-size:14px;margin:0px 0px 20px 0px;">Below are some special settings to handle unique situations, such as which theme to use for WordPress's search functions and search pagination.</p>
	
	<?php
	
	// ------------------------------------------------------------------------------
	// get a list of ALL themes...
	// ------------------------------------------------------------------------------
	$args = array();
	$themes = wp_get_themes( $args );

	// ------------------------------------------------------------------------------
	// create a form to show themes/pages and allow for changes....
	// ------------------------------------------------------------------------------
	
	
	
	$select_menu = '<h2 style="margin-bottom:10px;">Theme/Template to Use for Site Searches</h2><p style="font-style:italic;font-size:14px;margin:0px 0px 10px 0px;">Select a different theme to use for WordPress site searches, then select the search results page layout you would like to use from that theme. If there are no additional layout options, then the theme defaults to the "search.php" template file.</p><select id="wxy-advanced-multitheme-themes" name="wxy_advamced_multitheme_options_stylesheet" class="wxy-advanced-multitheme-form-input wxy-advanced-multitheme-auto-save-off"><option value="">Select theme to use for site searches</option>';

	// get the titles of for each theme and any value we need for the select menus
	foreach( $themes as $key => $value )
	{
		$selected = "";

		if( isset( $options[ "search_stylesheet" ] ) && $options[ "search_stylesheet" ] != "default" && $options[ "search_stylesheet" ] == $key )
		{
			// we have a match, so select this option
			$selected = "selected";
		}
		
		// get the names of each theme
		$select_menu .= '<option value="' . $key . '" '.$selected.'>' . $value . '</option>';	
	}

	//  close our select menu and populate our hidden form fields
	$select_menu .= '</select>';
	
	// now get all page layouts for each of the above themes
	$select_menu .= '<select name="wxy_advamced_multitheme_options_template" id="wxy-advanced-multitheme-pages" class="wxy-advanced-multitheme-pages wxy-advanced-multitheme-form-input wxy-advanced-multitheme-auto-save-off" style="display:block;position:relative;margin:10px 0px 0px 0px;"><option value="">Use the theme\'s default search template, or select another&hellip;</option></select>';

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

				if( isset( $options[ "search_template" ] ) && $options[ "search_template" ] != "default" && $options[ "search_template" ] == $template_name )
				{
					// we have a match, so select this option
					$selected = "selected";
				}
				
				// assign a class based on the parent theme to allow us to show/hide templates for specific themes
				$select_menu .= '<option value="'.$template_name.'" class="wxy-adv-multi-page-option wxy-adv-multi-page-'.$suffix.'" '.$selected.'>'.$template_filename.'</option>';
    		}
		}
		
	}

	$select_menu .= '</div>';

echo $select_menu;

?>	

	<input type="hidden" name="blacklist_checkbox_action" value="save" />
	
	<!-- content spacer --><div style="height:50px;"></div>
	
	<?php
		echo submit_button( "Save All Settings", 'primary', 'submit', false, array() );
	?>

	
	</form>	

<br /><br /><br /><br /><br /><br />

<p style="color:#F00;font-size:24px;font-weight:bold;margin:0px;">Please Contribute!</p>
<p style="font-style:italic;font-size:14px;margin:0px 0px 20px 0px;">People like you make plugins like this possible! So, please contribute, $1, $3, $5, or any dollar amount to help us keep the lights on and be able to improve this plugin and develop other great new plugins!</p>
<p style="font-size:14px;margin:0px 0px 20px 0px;">When you donate to WXY Tools, you will be taken to Paypal, where our parent company name <a href="http://www.bowmandesignworks.com">(Bowman Design Works)</a> will be displayed. Then you will be returned here after completing your contribution.</p>

<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_s-xclick" />
<input type="hidden" name="hosted_button_id" value="PZP96N97ETCBU" />
<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" title="PayPal - The safer, easier way to pay online!" alt="Donate with PayPal button" />
<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" />
</form>


<!-- content spacer --><div class="wxy-tools-content-spacer" style="height:20px;float:none;clear:both;"></div>
<!-- divider bar --><div style="width:90%;height:2px;background-color:#666;position:relative;"></div>
<!-- content spacer --><div class="wxy-tools-stickyscroll-content-spacer" style="height:10px;float:none;clear:both;"></div>
	<span style="font-size:1em;font-style:italic;display:block;width:65%;height:auto;position:relative;text-align:left;"><a href="http://www.wxytools.com">"WXY Tools"</a> and all content in this plugin are &copy;2016-Present Clarence "exoboy" Bowman and <a href="http://www.bowmandesignworks.com">Bowman Design Works.com</a> and may not be altered or sold without prior written permission.</span>


<!-- page content starts here CLOSE --></div>
<!-- ============================== -->

<?php
	}
?>