<?php
/*
Copyright (c) 2014 by Greg Ross

This software is released under the GPL v2.0, see license.txt for details
*/

/*
 	This function returns either on or off depending on the state of an HTML checkbox 
    input field returned from a post command.
*/
function sa_prefs_get_checked_state( $value )
	{
	if( $value == 'on' ) 
		{
		return '1';
		}
	else
		{
		return '0';
		}
	}

/*
 	This function is called to save the user profile settings for Just Writing.
*/
function sa_prefs_save_user_profile_fields( $user_id )
	{
	GLOBAL $wpdb;
	
	// If we can't edit the current user, don't save the changes.
	if ( !current_user_can( 'edit_user', $user_id ) ) { return; }

	// Make sure we have some values to save.
	if( array_key_exists( 'sa_prefs_user_options', $_POST ) )
		{
	// Get the plugin options and user field options list.
		$SAPrefsOptions = get_option('SAPrefsOptions');
		$nameoptions = SAPrefsUserFieldList();

		if( !array_key_exists( 'dbtable', $SAPrefsOptions ) ) { return; }
		
		// If the table is blank or the userfield doesn't match one of the available options, don't display the options.
		if( $SAPrefsOptions['dbtable'] == '' ) { return; }
		if( !in_array( $SAPrefsOptions['userfield'], $nameoptions ) ) { return; }

		// Get the username to use for the preferences. 
		$sa_prefs_username = SAPrefsGetUsername( $SAPrefsOptions['userfield'] );
		
		// If the username is blank, don't display the options.
		if( $sa_prefs_username == '' ) { return; }

		// Get the stored user options, or if they haven't been set yet, the defaults.
		$SAPrefsUserOptions = sa_prefs_get_user_options( $SAPrefsOptions['dbtable'], $sa_prefs_username );
		
		// Merge the stored user prefs (default settings if this is the first run) and the submitted data (this makes sure checkboxes are handled correctly).
		$Merge = array_merge( $SAPrefsUserOptions, $_POST['sa_prefs_user_options'] );

		// Get rid of the white/black list data from the merged array.
		unset( $Merge["whitelist_from"] );
		unset( $Merge["whitelist_to"] );
		unset( $Merge["blacklist_from"] );
		
		// Loop through the merged array and handle each case.
		foreach( $Merge as $key => $value )
			{
			switch( $key )
				{
				case 'add_whitelist_from':
				case 'add_whitelist_to':
				case 'add_blacklist_from':
					// Adding a new white/black list entry.
					$new_key = str_replace( "add_", "", $key );
					
					if( $value != "" ) 
						{
						$wpdb->insert( $SAPrefsOptions['dbtable'], array( 'username' => $sa_prefs_username, 'preference' => $new_key, 'value' => $value ) );
						}
					
					break;
				case 'delete_whitelist_from':
				case 'delete_whitelist_to':
				case 'delete_blacklist_from':
					// Delete one or more white/black list entries.
					$new_key = str_replace( "delete_", "", $key );
					
					foreach( $value as $addr )
						{
						$wpdb->delete( $SAPrefsOptions['dbtable'], array( 'username' => $sa_prefs_username, 'preference' => $new_key, 'value' => $addr ) );
						}
					
					break;
				case 'fold_headers':
				case 'use_bayes':
					// Handle the checkbox entries.  Fall through to the default handler after setting the value.
					$value = sa_prefs_get_checked_state( $value );
					
				default:
					// Store a value if it's different from what's in the database already.
					if( $value != $SAPrefsUserOptions[$key] )
						{
						$result = $wpdb->update( $SAPrefsOptions['dbtable'], array( 'value' => $value ), array( 'username' => $sa_prefs_username, 'preference' => $key ) );

						if( $result == false ) 
							{
							$wpdb->insert( $SAPrefsOptions['dbtable'], array( 'username' => $sa_prefs_username, 'preference' => $key, 'value' => $value ) );
							}
						}
						
					break;
				}
			}
		}
		
//		exit();
	}

/*
 	This function returns the user preferences, if no preferences are found for the user the defaults are setup.
*/
function sa_prefs_get_user_options( $table, $username )
	{
	GLOBAL $wpdb;

	// Get all the results from the database.
	$SAPrefRows = $wpdb->get_results( 'SELECT * FROM ' . $table . ' WHERE username = \'' . $username . '\' ORDER BY value', ARRAY_A );
	
	// Check to see if this is the first time we've run for this user and no config
	// has been written yet, so let's do that now.
	if( $wpdb->num_rows == 0 )
		{
		include_once( "spamassassin-preferences-user-setup.php" );
		SAPrefs_User_Setup( $user->ID );
		
		$SAPrefRows = $wpdb->get_results( 'SELECT * FROM ' . $table . ' WHERE username = \'' . $username . '\' ORDER BY value', ARRAY_A );
		}

	// Setup the user options for white/black lists as arrays.
	$SAPrefsUserOptions = array( 'whitelist_from' => array(), 'whitelist_to' => array(), 'blacklist_from' => array(), );
		
	// Loop through all the returned preferences returned from the database and structure them so that the white/black
	// list entries are stored in their own arrays.
	foreach( $SAPrefRows as $row )
		{
		switch( $row['preference'] )
			{
			case 'whitelist_from':
				$SAPrefsUserOptions['whitelist_from'][] .= $row['value'];
				
				break;
			case 'blacklist_from':
				$SAPrefsUserOptions['blacklist_from'][] .= $row['value'];
				
				break;
			case 'whitelist_to':
				$SAPrefsUserOptions['whitelist_to'][] .= $row['value'];
				
				break;
			default:
				$SAPrefsUserOptions[$row['preference']] = $row['value'];
			
				break;
			}
		}
	
	return $SAPrefsUserOptions;
	}
	
/*
 	This function is called to draw the user settings page for SpamAssassin Preferences.
*/
function sa_prefs_user_profile_fields( $user ) 
	{ 
	GLOBAL $wpdb;
	
	// Get the plugin options and user field options list.
	$SAPrefsOptions = get_option('SAPrefsOptions');
	$nameoptions = SAPrefsUserFieldList();

	// If the options haven't been saved yet, don't display the options.
	if( !is_array( $SAPrefsOptions ) ) { return; }

	// If the table hasn't been defined yet, don't display the options.
	if( !array_key_exists( 'dbtable', $SAPrefsOptions ) ) { return; }
	
	// If the table is blank or the userfield doesn't match one of the available options, don't display the options.
	if( $SAPrefsOptions['dbtable'] == '' ) { return; }
	if( !in_array( $SAPrefsOptions['userfield'], $nameoptions ) ) { return; }
	
	// Get the username to use for the preferences. 
	$sa_prefs_username = SAPrefsGetUsername( $SAPrefsOptions['userfield'] );
	
	// If the username is blank, don't display the options.
	if( $sa_prefs_username == '' ) { return; }
	
	// Get the stored user options, or if they haven't been set yet, the defaults.
	$SAPrefsUserOptions = sa_prefs_get_user_options( $SAPrefsOptions['dbtable'], $sa_prefs_username );
?>
	<h3 id=SAPrefs>SpamAssassin Preferences</h3>
	 
	<table class="form-table">
		<tr>
			<th></th>
			<td>
				<span class="description"><?php echo __("SpamAssassin Preferences lets you control your spam filter settings from within WordPress.  To find out more, please visit the ") . "<a href='http://wordpress.org/plugins/spamassassin-preferences/' target=_blank>WordPress Plugin Directory page</a> " . __("or plugin home page on") . " <a href='http://toolstack.com/sa-prefs' target=_blank>ToolStack.com</a>.<br><br>" . __("And don't forget to ") . "<a href='http://wordpress.org/support/view/plugin-reviews/spamassassin-preferences' target=_blank>" . __("rate and review") . "</a>" . __(" it too!");?></span>
			</td>
		</tr>

		<tr>
			<th><label for="sa_prefs_user_options[required_hits]"><?php echo __("Spam score");?></label></th>
			<td>
				<select name="sa_prefs_user_options[required_hits]">
<?php
				for( $i = -10; $i < 11; $i++ )
					{
					if( $SAPrefsUserOptions['required_hits'] == $i ) { $selected = " SELECTED"; } else { $selected = ""; }	
					echo "\t\t\t\t\t<option value=\"" . $i . "\"" . $selected . ">" . $i . "</option>";
					}
?>
				</select>
			</td>
		</tr>

		<tr>
			<th><label for="sa_prefs_user_options[rewrite_header Subject]"><?php echo __("Subject tag");?></label></th>
			<td>
				<input type=text name="sa_prefs_user_options[rewrite_header Subject]" value="<?php echo $SAPrefsUserOptions['rewrite_header Subject'];?>">
			</td>
		</tr>

		<tr>
			<th><label for="sa_prefs_user_options[report_safe]"><?php echo __("Spam reporting");?></label></th>
			<td>
				<select name="sa_prefs_user_options[report_safe]">
					<option value="0"<?php if( $SAPrefsUserOptions['report_safe'] == 0 ) { echo " SELECTED"; }?>>Only add X-Spam- headers</option>
					<option value="1"<?php if( $SAPrefsUserOptions['report_safe'] == 1 ) { echo " SELECTED"; }?>>Attach the original e-mail as a rfc822 attachment</option>
					<option value="2"<?php if( $SAPrefsUserOptions['report_safe'] == 2 ) { echo " SELECTED"; }?>>Attach the original e-mail as a plain text attachment</option>
				</select>
			</td>
		</tr>

		<tr>
			<th><label for="sa_prefs_user_options[fold_headers]"><?php echo __("Fold headers");?></label></th>
			<td>
				<input type="checkbox" name="sa_prefs_user_options[fold_headers]"<?php if( $SAPrefsUserOptions['fold_headers'] == 1 ) { echo " CHECKED"; }?>>
			</td>
		</tr>

		<tr>
			<th><label for="sa_prefs_user_options[use_bayes]"><?php echo __("Use Bayesian classifier");?></label></th>
			<td>
				<input type="checkbox" name="sa_prefs_user_options[use_bayes]"<?php if( $SAPrefsUserOptions['use_bayes'] == 1 ) { echo " CHECKED"; }?>>
			</td>
		</tr>
		
		<tr>
			<th><label for="sa_prefs_user_options[add_whitelist_from]"><?php echo __("Add whitelist 'From' entry");?></label></th>
			<td>
				<input type=text name="sa_prefs_user_options[add_whitelist_from]" size="30"> <?php submit_button( __('Add'), null, null, false ); ?>
			</td>
		</tr>

		<tr>
			<th><label for="sa_prefs_user_options[delete_whitelist_from][]"><?php echo __("Delete whitelist 'From' entry");?></label></th>
			<td>
				<select size="5" multiple="yes" name="sa_prefs_user_options[delete_whitelist_from][]"
<?php
					foreach( $SAPrefsUserOptions['whitelist_from'] as $addr )
						{
						echo '<option value="' . $addr . '">' . $addr . '</option>';
						}
?>
				</select>
				<?php submit_button( __('Delete'), null, null, false ); ?>
			</td>
		</tr>

		<tr>
			<th><label for="sa_prefs_user_options[add_whitelist_to]"><?php echo __("Add whitelist 'To' entry");?></label></th>
			<td>
				<input type=text name="sa_prefs_user_options[add_whitelist_to]" size="30"> <?php submit_button( __('Add'), null, null, false ); ?>
			</td>
		</tr>

		<tr>
			<th><label for="sa_prefs_user_options[delete_whitelist_to][]"><?php echo __("Delete whitelist 'To' entry");?></label></th>
			<td>
				<select size="5" multiple="yes" name="sa_prefs_user_options[delete_whitelist_to][]"
<?php
					foreach( $SAPrefsUserOptions['whitelist_to'] as $addr )
						{
						echo '<option value="' . $addr . '">' . $addr . '</option>';
						}
?>
				</select>
				<?php submit_button( __('Delete'), null, null, false ); ?>
			</td>
		</tr>

		<tr>
			<th><label for="sa_prefs_user_options[add_blacklist_from]"><?php echo __("Add blacklist 'From' entry");?></label></th>
			<td>
				<input type=text name="sa_prefs_user_options[add_blacklist_from]" size="30"> <?php submit_button( __('Add'), null, null, false ); ?>
			</td>
		</tr>

		<tr>
			<th><label for="sa_prefs_user_options[delete_blacklist_from][]"><?php echo __("Delete blacklist 'From' entry");?></label></th>
			<td>
				<select size="5" multiple="yes" name="sa_prefs_user_options[delete_blacklist_from][]"
<?php
					foreach( $SAPrefsUserOptions['blacklist_from'] as $addr )
						{
						echo '<option value="' . $addr . '">' . $addr . '</option>';
						}
?>
				</select>
				<?php submit_button( __('Delete'), null, null, false ); ?>
			</td>
		</tr>

	</table>
<?php 
	}
?>