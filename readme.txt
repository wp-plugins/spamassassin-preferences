=== SpamAssassin Preferences ===
Contributors: GregRoss
Plugin URI: http://toolstack.com/sa-prefs
Author URI: http://toolstack.com
Tags: spamassassin
Requires at least: 3.5.0
Tested up to: 3.9.1
Stable tag: 0.1

Set your SpamAssassin preferences from your WordPress user profile.

== Description ==

This code is released under the GPL v2, see license.txt for details.

== Installation ==

1. Configure SpamAssassin to use SQL (see https://wiki.apache.org/spamassassin/UsingSQL)
2. Extract the archive file into your plugins directory in the sa-prefs folder.
3. Activate the plugin in the Plugin options.
4. Configure the plugin options under settings.
5. Login to WordPress and go to your profile page, update the options at the bottom.

== Frequently Asked Questions ==

= The user preferences are not show up =

Make sure you have configured your sa_prefs table correctly and set your user field preference as well.

= Can I select a different database then the WordPress database? = 

Yes, use the database.table notation in the settings page.

= Can I select a different SQL server then the one WordPress uses? =

No.

== Screenshots ==

1. Admin seetings screen.
2. User preferences screen.

== Changelog ==

= 0.1 =
* Initial release.

== Upgrade Notice ==

None.

