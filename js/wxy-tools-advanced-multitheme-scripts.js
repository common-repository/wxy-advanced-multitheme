// ALL CODE ©2015-Present Clarence "exoboy" Bowman and Bowman Design Works, http://www.bowmandesignworks.com
// this code made not be distributed or copied without prior written consent of the author.

// create a global flag...
var wxy_tools_advanced_multitheme_inited = false;

// begin encapsulation
(function($){

	// ********************************************************************
	// because we are altering the DOM, it will fire multiple times!
	// be sure to refer to jQuery as jQuery instead of $ ?
	// ********************************************************************
	jQuery( document ).on("DOMContentLoaded", function(){
		
		if( wxy_tools_advanced_multitheme_inited )
		{
			return;
		} else {
			wxy_tools_advanced_multitheme_inited = true;
			wxy_tools_advanced_multitheme_startup();
		}
	});
	
	
	// ********************************************************************
	// FUNCTION GLOBALS
	// ********************************************************************
	 
	// ********************************************************************
	// PAGE ONREADY (STARTUP)
	// ********************************************************************
	function wxy_tools_advanced_multitheme_startup()
	{
		// ------------------------------------------------------------------------
		// SELECT ALTERNATIVE THEME: user wants to use a different theme for this page
		// ------------------------------------------------------------------------
		$( "#wxy-advanced-multitheme-themes" ).on( "change", function()
		{
			// collect all our input field values to send to WP for saving
			var inputs = $( this ).parent().find( ".wxy-advanced-multitheme-form-input" );
					
			// get our current select value, this will be the base of our class of options to show/hide
			var theme = $( this ).val();
			
			// convert to lower case and remove any non alpha-numerics
			theme = String( theme ).toLowerCase();
			theme = String( theme ).replace( /[^a-z0-9]/g, "_" );
			
			// CLEAR our select menu that holds our options (except for the firsts child, our default)
			var children = $( ".wxy-advanced-multitheme-pages" ).find( ".wxy-adv-multi-page-option" );
			$( children ).remove();
			
			// now get the options we need from the holder and add them to the select menu
			var option = $( "#wxy-advanced-multitheme-options-holder" ).find( ".wxy-adv-multi-page-" + theme );
			var clone = $( option ).clone( true, true );
			
			// now add them to our menu
			$( clone ).appendTo( $( ".wxy-advanced-multitheme-pages" ) );
			
			// now show the parent select menu
			if( !$( ".wxy-advanced-multitheme-pages" ).is(":visible" ) )
			{
				$( ".wxy-advanced-multitheme-pages" ).show();
			}
			
			// now SAVE our change to make sure we do not lose it if they save or leave the page
			// submit the form via ajax, them let wordpress save the meta data update...
			// ALSO: do not save if it has the auto save class off
			if( !startup && !$( this ).hasClass( "wxy-advanced-multitheme-auto-save-off" ) )
			{
				submit_data( inputs );
			} else {
				// trigger a refresh of our secondary menu...
				$( "#wxy-advanced-multitheme-pages" ).trigger( "change" );
			}
			startup = false;

		});
		
		// trigger a refesh after the initial page load to update which (if any) template are selected in the above menu
		var startup = true;
		$( "#wxy-advanced-multitheme-themes" ).trigger( "change" );
		
		
		// ------------------------------------------------------------------------
		// SELECT ALTERNATIVE TEMPLATE: user wants to use a different template for this page
		// ------------------------------------------------------------------------
		$( "#wxy-advanced-multitheme-pages" ).on( "change", function()
		{	
			// collect all our input field values to send to WP for saving
			var inputs = $( this ).parent().find( ".wxy-advanced-multitheme-form-input" );
			
			// now SAVE our change to make sure we do not lose it if they save or leave the page
			// submit the form via ajax, them let wordpress save the meta data update...
			if( !startup && !$( this ).hasClass( "wxy-advanced-multitheme-auto-save-off" ) )
			{
				submit_data( inputs );
			}
			
		});
		
		// ------------------------------------------------------------------------
		// HELP BTN: user clicked the help button in the post/page editor
		// ------------------------------------------------------------------------
		$( ".wxy-advanced-multitheme-help-btn" ).on( "click", function()
		{
			alert( "INSTRUCTIONS: Select a theme, then choose a template to use for this post/page. You can set a different theme-template combination for every page/post in your site.\r\n\r\nPLEASE NOTE: Every effort is made to ensure compatibility with third party themes and theme building plugins. However, if this multitheme plugin does not function properly, there may be a conflict with your default theme/theme builder. Please go to Settings > WXY Advanced Multitheme > Plugins Blacklist and check the boxes next to your default theme or theme building plugins. This will allow WXY Multitheme the ability to avoid loading those plugins when it builds a page using an alternate theme-template." );
		});

	};
	
 	// ****************************************************************************************************************************************
	// ****************************************************************************************************************************************
	// ****************************************************************************************************************************************
	// FUNCTION DEFS BELOW
	// ****************************************************************************************************************************************
	// ****************************************************************************************************************************************
	// ****************************************************************************************************************************************

	// *********************************************************************
	// FORM SUBMISSION: send changes to options for base64 to WP
	// *********************************************************************
	function submit_data( inputs )
	{
		var inputs = inputs || null;
		var vars = {};
		var submit_method;
		var submission_path = $( inputs ).filter( "#wxy-advanced-multitheme-submission-path" ).val() || null;
		var wp_form_action = $( inputs ).filter( "#action" ).val() || null;
		var new_inputs = $( inputs ).clone(true,true);
		var new_form, ie_form;

		if( inputs )
		{
			// look for the input with this value and get its input and assign our new value to it
			var metaboxes = $( "#postcustomstuff" );
			var meta_key = $( "input[value|='wxy-advanced-multitheme-exempt-conversion']" );
			var meta_parent = $( meta_key ).closest( "tr" );
			var meta_value_id = "#" + $( meta_parent ).attr( "id" ) + "-value";
			var meta_value = $( meta_value_id );

			// ---------------------------------------------------
			// formData: pass form variables in a json object
			// ---------------------------------------------------
			
			// Create a new FormData object.
			if( window.FormData === undefined )
			{
				// this must be an OLDER browser
				submit_method = "legacy_submit";
				
				// ---------------------------------------------------
				// collect all values in case there is no formData object
				// ---------------------------------------------------
				new_form = '<form name="ie_form" id="ie_form" method="post" enctype="application/x-www-form-urlencoded" action="' + submission_path + '"></form>';
				$( new_form ).appendTo( $( "body" ) );
			
				ie_form = $( "#ie_form" );
				$( new_inputs ).appendTo( $( ie_form ) );	
				
			} else {
				
				// okay, this browser supports the formData object
				submit_method = "formData"
			
				form_data = new FormData();
			
				// collect our key-value pairs to json encode
				$( inputs ).each( function()
				{
					var self = this;
					var id = $( self ).attr( "id" ) || false;
					var val = $( self ).val();
				
					// see if it is a radio button or checkbox and make sure one is selected, otherwise, pass an empty value!
					var type = self.type || self.tagName.toLowerCase();
	    			
					// see if we need to verify if the type of input is checked
					switch (true)
					{
						case type == "radio":
							if( !$( self ).is( ":checked" ) )
							{
								val = "";
							}
							break;
							
						case type == "checkbox":
							if( !$( self ).is( ":checked" ) )
							{
								val = "";
							}
							break;	
					}
				
					if( id )
					{
						form_data.append( id, val );
	
						// EXOBOY
						console.log( id + " = " + val );
					}
				});
	
			}

			// ---------------------------------------------------
			// DECIDE WHICH SUBMIT METHOD TO USE
			// ---------------------------------------------------
			switch (true)
			{
				
				case submit_method == "legacy_submit":

					// ---------------------------------------------------
					// BROWSERS THAT DO NOT SUPPORT formData! - quietly send our request in the background
					// ---------------------------------------------------
					jQuery.ajax({
	 					"url": submission_path,
						"type":"POST",	
						"method":"POST",
						"action":wp_form_action,
						"dataType": 'text',
	        		    "data": $( ie_form ).serialize(),
				
						"success": function( responseText, statusText, jqXHR, form )
							{
								//alert("success");
						
								// show our result
								//console.log( "SUCCESS responseText: ");
								//console.log( responseText  );
						
							//	responseText = JSON.stringify( responseText);
								// process the result from the server
								process_response_text( responseText );
							},
					
						"fail": function(xhr, textStatus, errorThrown) {
	        					console.log(xhr, textStatus, errorThrown);
						},
				
						"error": function(xhr, textStatus, errorThrown) {
	        					console.log(xhr, textStatus, errorThrown);
	    					}
					});
					
					break;
					
					
				default:
					
					// ---------------------------------------------------
					// formData method: quietly send our request in the background
					// ---------------------------------------------------
					jQuery.ajax({
	 					"url": submission_path,
						"type":"POST",	
						"method":"POST",
						"action":wp_form_action,
						"data": form_data,
						"processData": false,
						"contentType": false,
			
						"success": function( responseText, statusText, jqXHR, form )
							{
								// show our result
								
								// EXOBOY
								//console.log( "responseText: " + responseText );
								
								// process the result from the server
								process_response_text( responseText );
							},
					
						"fail": function(xhr, textStatus, errorThrown) {
	        					console.log(xhr, textStatus, errorThrown);
						},
				
						"error": function(xhr, textStatus, errorThrown) {
	        					console.log(xhr, textStatus, errorThrown);
	    					}
					});
					
			}

		}// close if form
		
	};
	
	// ************************************************************************
	// AJAX: process our server response here...
	// ************************************************************************
	function process_response_text( raw_response )
	{
	//	console.log( "]" + raw_response + "[" );
		var parse_error = false, error_count = 0;

		// -------------------------------------------
		// parse our response object here and extract any other data sent from the server that is not part of our json result object
		// -------------------------------------------
		var response;
		
		var matches = String( raw_response ).match( /(\^\^\^\^JSON-START\^\^\^\^)(.*)*(\^\^\^\^JSON-END\^\^\^\^)/ );
		var console_msg = String( raw_response ).replace( /\^\^\^\^JSON-START\^\^\^\^(.*)*\^\^\^\^JSON-END\^\^\^\^/, "" );

		// DEBUGGING: if there is an error from WP, show it in the developer console
		if( String( console_msg ).length > 0 )
		{
			console.log( "CONSOLE MESSAGE: " );
			console.log( console_msg );
		}

		try {

			// try to parse the json result object
			response = JSON.parse( matches[2] );
			
			parse_error = false;

		} catch (e) {
			response = {};
			parse_error = true;
		}
		
		// -------------------------------------------
		// see if the action was a success and read the server message
		// -------------------------------------------
		if( response[ "message" ] == "Settings saved." || response[ "message" ] == "Settings cleared." )
		{
			// reload our current page to update the admin panels
			var href = window.location;
			
			window.location = href;	
		}
	};

// end encapsulation
})(jQuery);