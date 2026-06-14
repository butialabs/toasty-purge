<?php
/**
 * Plugin Name:       SEO Debloater
 * Plugin URI:        https://github.com/butialabs/seo-debloater
 * Description:       Hide most of the bloat that the Yoast SEO plugin adds to your WordPress Dashboard
 * Version:           0.0.1
 * Requires at least: 6.5
 * Tested up to:      7.0
 * Requires PHP:      8.2
 * Author:            Butiá Labs
 * Author URI:        https://butialabs.com
 * License:           GPL-2.0-or-later
 * Text Domain:       seo-debloater
 * Domain Path:       /languages
 */

// don't load the plugin file directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Load plugin class files
require_once( 'includes/class-seo-debloater.php' );
require_once( 'includes/class-seo-debloater-settings.php' );

// Load separate remove class function
require_once( 'includes/remove-class.php' );

// Load plugin libraries
require_once( 'admin/class-seo-debloater-admin-api.php' );

/**
 * Returns the main instance of SEO_Debloater to prevent the need to use globals.
 *
 * @since  v2.0.0
 * @return object SEO_Debloater
 */
function SEO_Debloater () {
	$instance = SEO_Debloater::instance( __FILE__, '0.0.1' );

	if ( null === $instance->settings ) {
		$instance->settings = SEO_Debloater_Settings::instance( $instance );
	}

	return $instance;
}

SEO_Debloater();
