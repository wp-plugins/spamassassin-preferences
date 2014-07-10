<?php
/*
Copyright (c) 2014 by Greg Ross

This software is released under the GPL v2.0, see license.txt for details
*/

if( !function_exists( 'SAPrefs_User_Setup' ) )
	{
	/*
	 	This function is called to setup the user preferences for the first time.
	*/
	Function SAPrefs_User_Setup( $user_id )
		{
		GLOBAL $wpdb;
		
		if ( !current_user_can( 'edit_user', $user_id ) ) { return false; }

		$SAPrefsOptions = get_option('SAPrefsOptions');
		$nameoptions = SAPrefsUserFieldList();

		if( !array_key_exists( 'dbtable', $SAPrefsOptions ) ) { return false; }
		
		if( $SAPrefsOptions['dbtable'] == '' ) { return false; }
		if( !in_array( $SAPrefsOptions['userfield'], $nameoptions ) ) { return false; }

		$sa_prefs_username = SAPrefsGetUsername( $SAPrefsOptions['userfield'] );
		
		if( $sa_prefs_username == '' ) { return false; }

		$defaults = array(
			"required_hits"=> "10",
			"rewrite_header Subject"=> "[SPAM]",
			"report_safe"=> "1",
			"fold_headers"=> "0",
			"use_bayes"=> "1",
			"rewrite_header"=> "Subject ",
			"use_terse_report"=> "0",
			"always_add_headers"=> "1",
			"spam_level_stars"=> "1",
			"spam_level_char"=> "*",
			"use_razor1"=> "0",
			"use_razor2"=> "0",
			"use_pyzor"=> "0",
			"use_dcc"=> "0",
			"skip_rbl_checks"=> "0",
			"ok_languages"=> "",
			"ok_locales"=> ""
			);
		
		foreach( $defaults as $key => $value )
			{
			$wpdb->insert( $SAPrefsOptions['dbtable'], array( 'username' => $sa_prefs_username, 'preference' => $key, 'value' => $value ) );
			}
		}
	}
?>