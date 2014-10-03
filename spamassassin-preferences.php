<?php
/*
Plugin Name: SpamAssassin Preferences
Version: 1.0
Plugin URI: http://toolstack.com/sa-prefs
Author: Greg Ross
Author URI: http://toolstack.com
Description: Set your SpamAssassin preferences from your WordPress user profile.

Compatible with WordPress 3.5+.

Read the accompanying readme.txt file for instructions and documentation.

Copyright (c) 2014 by Greg Ross

This software is released under the GPL v2.0, see license.txt for details
*/

if( !function_exists( 'SAPrefsLoad' ) )
	{
	define( 'SAPrefsVersion', '1.0' );

	/*
	 	This function is called when a user edits their profile and creates the Just Writing section.
		
		$user = the user who's profile we're viewing
	*/
	
	Function SAPrefsUserFieldList()
		{
		return array( 'Username', 'First Name', 'Last Name', 'Nickname', 'Full E-mail', 'Truncated E-mail' );
		}

	Function SAPrefsGetUsername( $fieldname )
		{
		if( !in_array( $fieldname, SAPrefsUserFieldList() ) ) { return; }

		$user_info = get_userdata( get_current_user_id() );
		
		switch( $fieldname ) 
			{
			case 'Username':
				return $user_info->user_login;
				
				break;
			case 'First Name':
				return $user_info->first_name;

				break;
			case 'Last Name':
				return $user_info->last_name;
				
				break;
			case 'Nickname':
				return $user_info->nickname;
				
				break;
			case 'Full E-mail':
				return $user_info->email;
				
				break;
			case 'Truncated E-mail':
				$temp = explode( '@', $user_info->email );
				return $temp[0];
				
				break;
			}
		}
		
	Function SAPrefsLoadProfile( $user )
		{
		include_once( "spamassassin-preferences-options.php" );
		sa_prefs_user_profile_fields( $user );
		}
		
	/*
	 	This function is called when a user edits their profile and saves the Just Writing preferences.
		
		$user = the user who's settings we're saving
	*/
	Function SAPrefsSaveProfile( $user )
		{
		include_once( "spamassassin-preferences-options.php" );
		sa_prefs_save_user_profile_fields( $user );
		}

	/*
	 	This function is called to add the new buttons to the distraction free writing mode.
		
	 	It's registered at the end of the file with an add_action() call.
	 */
	Function SAPrefsLoad( $source )
		{
		// Get the user option to see if we're enabled.
		$cuid = get_current_user_id();
		$SAPrefsEnabled = get_the_author_meta( 'sa_prefs_enabled', $cuid );
		
		// If the enabled check returned a blank string it's because this is the first run and no config
		// has been written yet, so let's do that now.
		if( $SAPrefsEnabled == "" )
			{
			include_once( "just-writing-user-setup.php" );
			sa_prefs_User_Setup( $cuid );
			$SAPrefsEnabled = "on";
			}
		
		// If we're enabled, setup as required.
		if( $SAPrefsEnabled == "on" )
			{
//			wp_register_style( 'SAPrefs_style', plugins_url( '', __FILE__ ) . '/just-writing.' . SAPrefsFileVersion() . '.css' );
//			wp_enqueue_style( 'SAPrefs_style' ); 

			// Get the options to pass to the javascript code
			$DisableFade = 0;
			if( get_the_author_meta( 'sa_prefs_d_fade', $cuid ) == 'on' ) { $DisableFade = 1; } 
			$HideWordCount = 0;
			if( get_the_author_meta( 'sa_prefs_h_wc', $cuid ) == 'on' ) { $HideWordCount = 1; } 
			$HidePreview = 0;
			if( get_the_author_meta( 'sa_prefs_h_p', $cuid ) == 'on' ) { $HidePreview = 1; } 
			$HideBorder = 0;
			if( get_the_author_meta( 'sa_prefs_h_b', $cuid ) == 'on' ) { $HideBorder = 2; } 
			if( get_the_author_meta( 'sa_prefs_l_b', $cuid ) == 'on' ) { $HideBorder = 1; } 
			$HideModeBar = 0;
			if( get_the_author_meta( 'sa_prefs_h_mb', $cuid ) == 'on' ) { $HideModeBar = 1; } 
			$FormatLB = 0;
			if( get_the_author_meta( 'sa_prefs_f_lb', $cuid ) == 'on' ) { $FormatLB = 1; } 
			$CenterTB = 0;
			if( get_the_author_meta( 'sa_prefs_c_tb', $cuid ) == 'on' ) { $CenterTB = 1; } 
			$DisableJSCP = 0;
			if( get_the_author_meta( 'sa_prefs_d_jscp', $cuid ) == 'on' ) { $DisableJSCP = 1; } 
			
			// By default, assume we're not autoloading DFWM.
			$AutoLoad = 0;
			
			if( $source == "new" )
				{
				// Check to see if we're supposed to autoload DFWM if we're creating a new post.
				if( get_the_author_meta( 'sa_prefs_al_new', $cuid ) == 'on' ) { $AutoLoad = 1; } 
				}

			if( $source == "edit" )
				{
				// Check to see if we're supposed to autoload DFWM if we're editing a post.
				if( get_the_author_meta( 'sa_prefs_al_edit', $cuid ) == 'on' ) { $AutoLoad = 1; } 
				}
				
			// Finally, check to see if we were passed an autoload variable on the URL, which happens if the user has
			// clicked DFWM in the post/pages list.
			if( array_key_exists( 'SAPrefsAutoLoad', $_GET ) )
				{
				if( $_GET['SAPrefsAutoLoad'] == 1 )
					{
					$AutoLoad = 1;
					}
				}
	
			}
		}

	/*
	 	This function generates the Just Writing settings page and handles the actions assocaited with it.
	 */
	function SAPrefsAdminPage()
		{
		include_once( "spamassassin-preferences-admin-options.php" );
		}
		
	/*
	 	This function adds the admin page to the settings menu.
	 */
	function SAPrefsAddSettingsMenu()
		{
		add_options_page( 'SpamAssassin Preferences', 'SpamAssassin Preferences', 'manage_options', basename( __FILE__ ), 'SAPrefsAdminPage');
		}
	}

// Add the admin page to the settings menu.
add_action( 'admin_menu', 'SAPrefsAddSettingsMenu', 1 );

// Handle the user profile items
add_action( 'show_user_profile', 'SAPrefsLoadProfile' );
add_action( 'edit_user_profile', 'SAPrefsLoadProfile' );
add_action( 'personal_options_update', 'SAPrefsSaveProfile' );
add_action( 'edit_user_profile_update', 'SAPrefsSaveProfile' );
	
?>