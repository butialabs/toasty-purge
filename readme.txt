=== SEO Debloater ===
Contributors: butialabs
Tags: seo, cleanup, bloat, hide, dashboard
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 8.2
Stable tag: 0.0.1
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Hide most of the bloat that the Yoast SEO plugin adds to your WordPress Dashboard.

== Description ==

Free addon for the Yoast SEO plugin to hide the bloat it adds to your WordPress backend.

> **Note:** This plugin is a fork of the original [Hide SEO Bloat](https://github.com/senlin/so-clean-up-wp-seo) plugin (also known as "SO Clean Up Yoast SEO") created by Pieter Bos.

SEO Debloater hides (sidebar) ads and premium version buttons of Yoast SEO from their settings pages and your website's dashboard (and frontend).

= Features =

* Hide Problems/Notifications boxes from Yoast SEO Dashboard
* Hide ads and premium upsells across Yoast SEO Settings pages
* Hide Premium, Workouts, and Redirects submenus
* Remove SEO columns from Posts/Pages admin screens
* Remove SEO/Readability score dropdown filters
* Hide featured image warning nag
* Hide content/keyword score from Publish metabox
* Hide premium features in Yoast metabox
* Hide advertisement after trashing content
* Remove Yoast SEO from admin bar
* Remove Yoast dashboard widget
* Remove permalinks warning notice
* Hide SEO settings on profile page
* Remove HTML comments from frontend
* Hide Support submenu
* Hide AI Brand Insights submenu
* Disable AI & LLMs.txt features

= Multisite Compatible =

Yes, SEO Debloater works on WordPress Multisite installations.

= Why use this plugin? =

Since version 20.0 of Yoast SEO, the Settings page has received a complete overhaul, but still contains many elements that clutter your WordPress admin. SEO Debloater provides a single settings page where you can control what gets hidden.

= Credits =

This plugin is maintained by [Butiá Labs](https://butialabs.com) and is a fork of the original work by [Pieter Bos](https://github.com/senlin).

== Installation ==

= Automatic Installation =

1. Go to Plugins > Add New in your WordPress admin
2. Search for "SEO Debloater"
3. Click "Install Now" and then "Activate"
4. Navigate to SEO > SEO Debloater to configure settings

= Manual Installation =

1. Download the plugin zip file
2. Upload the plugin files to the `/wp-content/plugins/seo-debloater` directory, or install the plugin through the WordPress plugins screen directly
3. Activate the plugin through the 'Plugins' screen in WordPress
4. Navigate to SEO > SEO Debloater to configure your preferred settings

= Requirements =

* Yoast SEO plugin must be installed and activated
* WordPress 4.9 or higher
* PHP 7.0 or higher

== Frequently Asked Questions ==

= Where is the settings page? =

The link to the page has been added to the Yoast SEO menu and of course there is also a link to it from the Plugins page.

= Can I use SEO Debloater on Multisite? =

Yes, you can. The plugin is fully compatible with WordPress Multisite installations.

= The name of the plugin is confusing, it hides bloat of which SEO plugin? =

The name refers to removing the bloat added by Yoast. There is only one SEO plugin that adds a lot of bloat to the WordPress Dashboard and that is the Yoast SEO plugin.

= The plugin doesn't do anything! =

Do you have the Yoast SEO plugin installed? SEO Debloater hides the bloat from that plugin only. If you have Yoast SEO installed and the plugin still doesn't do anything, please open a [support ticket](https://github.com/butialabs/seo-debloater/issues).

= What happens to database entries on uninstall? =

The plugin writes its settings to the database. The included `uninstall.php` file removes all the plugin-related entries from the database once you remove the plugin via the WordPress Plugins page (not on deactivation).

= I have an issue with this plugin, where can I get support? =

Please open an issue on [Github](https://github.com/butialabs/seo-debloater/issues)

== Screenshots ==

1. SEO Debloater settings page - Yoast SEO Settings section
2. SEO Debloater settings page - Posts, Pages, Custom post types section
3. SEO Debloater settings page - Miscellaneous section
4. SEO Debloater settings page - AI section

== Changelog ==

= 0.0.1 =
* Release date: January 28, 2025
* Added option to remove AI bloat
* Added option to remove support screen
* Added option to disable AI & LLMs.txt features

= 0.0.1 =
* Release date: January 27, 2025
* Renamed to SEO Debloater
* Forked from SO Clean Up Yoast SEO

== Upgrade Notice ==

= 0.0.1 =
New options to hide AI and LLMs.txt features added by Yoast SEO. Recommended update for all users.

= 0.0.1 =
Initial release as SEO Debloater. If you were using SO Clean Up Yoast SEO, this is the successor plugin.
