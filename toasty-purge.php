<?php
/**
 * Plugin Name:       Toasty Purge
 * Plugin URI:        https://github.com/butialabs/toasty-purge
 * Description:       Hide most of the bloat that the Yoast SEO plugin adds to your WordPress Dashboard
 * Version:           0.0.2
 * Requires at least: 6.5
 * Tested up to:      7.0
 * Requires PHP:      8.2
 * Author:            Butiá Labs
 * Author URI:        https://butialabs.com
 * License:           GPL-2.0-or-later
 * Text Domain:       toasty-purge
 * Domain Path:       /languages
 */

// don't load the plugin file directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Load plugin class files
require_once( 'includes/class-toasty-purge.php' );
require_once( 'includes/class-toasty-purge-settings.php' );

// Load separate remove class function
require_once( 'includes/remove-class.php' );

// Load plugin libraries
require_once( 'admin/class-toasty-purge-admin-api.php' );

/**
 * Returns the main instance of Toasty_Purge to prevent the need to use globals.
 *
 * @since  v0.0.1
 * @return object Toasty_Purge
 */
function Toasty_Purge () {
	$instance = Toasty_Purge::instance( __FILE__, '0.0.1' );

	if ( null === $instance->settings ) {
		$instance->settings = Toasty_Purge_Settings::instance( $instance );
	}

	return $instance;
}

Toasty_Purge();
