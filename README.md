# SEO Debloater

###### Original Author: [Pieter Bos](https://github.com/senlin)

Free addon for the Yoast SEO plugin to hide the bloat it adds to your WordPress backend.

## Description

> **Note:** This plugin is a fork of the original [Hide SEO Bloat](https://github.com/senlin/so-clean-up-wp-seo) plugin (also known as "SO Clean Up Yoast SEO") created by Pieter Bos.

Hides (sidebar) ads and premium version buttons of Yoast SEO from their settings pages and your website's dashboard (and frontend).

<hr>

SEO Debloater provides a single settings page where you can control what gets hidden from Yoast SEO's cluttered admin interface.

Since version 20.0 of Yoast SEO however, the Settings page has received a complete overhaul, which made hiding things much more difficult.

Things have become much, much more trickier to remove/hide now and some things simply can no longer be hidden (believe me, I have tried).

Why are there still people using Yoast SEO one might ask? There are so many great alternatives that come without screaming ads and hiding features behind a paywall!

For everyone to become much more productive and happier, my proposal therefore is to switch to any of the other SEO plugins, such as SEOPress, The SEO Framework, Rankmath, or any other one out there! Did you know that most SEO plugins come with easy one-click migration tools?

<hr>

If you like the SEO Debloater plugin, please consider leaving a review. You can also help a great deal by translating the plugin into your own language.
Alternatively you are welcome to make a [donation](https://so-wp.com/donations). Thanks!

## Frequently Asked Questions

### Where is the settings page?

The link to the page has been added to the Yoast SEO menu and of course there is also a link to it from the Plugins page.

### Can I use SEO Debloater on Multisite?

Yes, you can.

### The name of the plugin is confusing, it hides bloat of which SEO plugin?

The name refers to removing the bloat added by Yoast. There is only one SEO plugin that adds a lot of bloat to the WordPress Dashboard and that is the Yoast SEO plugin.

### The plugin doesn't do anything!

Do you have the Yoast SEO plugin installed? It hides the bloat from that plugin only.
If you have and the plugin still doesn't do anything, then please open a [support ticket](https://github.com/butialabs/seo-debloater/issues).

### With a settings page comes additional entries in the database; what happens on uninstall?

Great question!
Indeed the SEO Debloater plugin writes its settings to the database. The included `uninstall.php` file removes all the plugin-related entries from the database once you remove the plugin via the WordPress Plugins page (not on deactivation).

### I have an issue with this plugin, where can I get support?

Please open an issue here on [Github](https://github.com/butialabs/seo-debloater/issues)

## Contributions

I welcome your contributions very much! PR's will be considered and of course bug reports and feature requests can also be seen as contributions!

## License

* License: GNU Version 3 or Any Later Version
* License URI: http://www.gnu.org/licenses/gpl-3.0.html

## Changelog

### 0.0.1

* release date January 28, 2025
* added option to remove AI bloat
* added option to remove support screen

### 0.0.1

* release date January 27, 2025
* rename to SEO Debloater
