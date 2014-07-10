<?php
/*
Copyright (c) 2014 by Greg Ross

This software is released under the GPL v2.0, see license.txt for details
*/
$nameoptions = SAPrefsUserFieldList();

$SAPrefsOptions = get_option('SAPrefsOptions');

if( !is_array( $SAPrefsOptions ) ) { $SAPrefsOptions = array(); }

if( array_key_exists( 'sa_prefs_options', $_POST ) )
	{
	if( !empty($_POST['sa_prefs_options']['dbtable'] ) ) { $SAPrefsOptions['dbtable'] = $_POST['sa_prefs_options']['dbtable']; }
	if( !empty($_POST['sa_prefs_options']['userfield'] ) ) 
		{ 
		if( in_array( $_POST['sa_prefs_options']['userfield'], $nameoptions ) )
			{
			$SAPrefsOptions['userfield'] = $_POST['sa_prefs_options']['userfield']; 
			}
		}
		
	update_option('SAPrefsOptions', $SAPrefsOptions);
	}

if( $SAPrefsOptions['dbtable'] == '' ) { $SAPrefsOptions['dbtable'] = 'sa_userprefs'; }
if( $SAPrefsOptions['userfield'] == '' ) { $SAPrefsOptions['userfield'] = 'Username'; }

?>
<div class="wrap">
	
	<fieldset style="border:1px solid #cecece;padding:15px; margin-top:25px" >
		<legend><span style="font-size: 24px; font-weight: 700;">&nbsp;<?php _e('User Settings');?>&nbsp;</span></legend>
		<p><?php echo sprintf(__('User settings can be found in %syour profile page%s, under the SpamAssassin Preferences heading.'), '<a href="' . get_edit_profile_url(get_current_user_id()) . '">', '</a>' );?></p>
	</fieldset>
	
	<form method="post">

	<fieldset style="border:1px solid #cecece;padding:15px; margin-top:25px" >
		<legend><span style="font-size: 24px; font-weight: 700;">&nbsp;<?php _e('Options'); ?>&nbsp;</span></legend>
		<p>Database table to use: <input name="sa_prefs_options[dbtable]" type="text" value="<?php echo $SAPrefsOptions['dbtable'];?>" size=20></p>
		<p>User name field to use: <Select name="sa_prefs_options[userfield]">
<?php
		for( $i = 0; $i < sizeof( $nameoptions ); $i++ )
			{
			echo "			<option value='" . $nameoptions[$i] . "'";
			if( $SAPrefsOptions['userfield'] == $nameoptions[$i] ) { echo " SELECTED"; }
			echo ">" . $nameoptions[$i] . "</option>\r\n";
			}
?>
		</select></p>

		<div class="submit"><input type="submit" name="info_update" value="<?php _e('Update Options') ?>" /></div>

	</fieldset>

	</form>
	
	<fieldset style="border:1px solid #cecece;padding:15px; margin-top:25px" >
		<legend><span style="font-size: 24px; font-weight: 700;">&nbsp;<?php _e('About'); ?>&nbsp;</span></legend>
		<h2><?php echo sprintf( __('SpamAssassin Preferences Version %s'), SAPrefsVersion );?></h2>
		<p><?php _e('by');?> <a href="https://profiles.wordpress.org/gregross" target=_blank>Greg Ross</a></p>
		<p>&nbsp;</p>
		<p><?php printf(__('Licenced under the %sGPL Version 2%s'), '<a href="http://www.gnu.org/licenses/gpl-2.0.html" target=_blank>', '</a>');?></p>
		<p><?php printf(__('To find out more, please visit the %sWordPress Plugin Directory page%s or the plugin home page on %sToolStack.com%s'), '<a href="http://wordpress.org/plugins/spamassassin-preferences/" target=_blank>', '</a>', '<a href="http://toolstack.com/sa-prefs" target=_blank>', '</a>');?></p>
		<p>&nbsp;</p>
		<p><?php printf(__("Don't forget to %srate and review%s it too!"), '<a href="http://wordpress.org/support/view/plugin-reviews/spamassassin-preferences" target=_blank>', '</a>');?></p>
	</fieldset>
</div>