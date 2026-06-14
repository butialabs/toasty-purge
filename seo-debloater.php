<?php
/**
 * Plugin Name: 		SEO Debloater
 * Plugin URI:  		https://butialabs.com
 * Description:			Hide most of the bloat that the Yoast SEO plugin adds to your WordPress Dashboard
 * Version:     		1.0.1
 * Author:				Butiá Labs
 * Author URI:  		https://butialabs.com
 * License:    			GPL-3.0+
 * License URI:			http://www.gnu.org/licenses/gpl-3.0.txt
 * Domain Path: 		/languages
 * Text Domain: 		seo-debloater
 * Network:     		true
 * GitHub Plugin URI:	https://github.com/butialabs/seo-debloater
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
	$instance = SEO_Debloater::instance( __FILE__, '1.0.1' );

	if ( null === $instance->settings ) {
		$instance->settings = SEO_Debloater_Settings::instance( $instance );
	}

	return $instance;
}

SEO_Debloater();
