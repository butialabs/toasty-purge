<?php
/**
 * Plugin Name:       Toasty Purge
 * Plugin URI:        https://github.com/butialabs/toasty-purge
 * Description:       Hide most of the bloat that the Yoast SEO plugin adds to your WordPress Dashboard
 * Version:           1.0.1
 * Requires at least: 6.5
 * Tested up to:      7.0
 * Requires PHP:      8.2
 * Author:            Butiá Labs
 * Author URI:        https://butialabs.com
 * License:           GPL-2.0-or-later
 * Text Domain:       toasty-purge
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) exit;

require_once( 'includes/class-toasty-purge.php' );
require_once( 'includes/class-toasty-purge-settings.php' );
require_once( 'includes/remove-class.php' );
require_once( 'admin/class-toasty-purge-admin-api.php' );

/**
 * Returns the main instance of TOASTYPRG to prevent the need to use globals.
 *
 * @since  v1.0.0
 * @return object TOASTYPRG
 */
function toastyprg () {
	$instance = TOASTYPRG::instance( __FILE__, '1.0.1' );

	if ( null === $instance->settings ) {
		$instance->settings = TOASTYPRG_Settings::instance( $instance );
	}

	return $instance;
}

toastyprg();
