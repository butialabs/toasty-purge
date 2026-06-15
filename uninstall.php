<?php

/**
 *
 * This file runs when the plugin in uninstalled (deleted).
 * This will not run when the plugin is deactivated.
 * Ideally you will add all your clean-up scripts here
 * that will clean-up unused meta, options, etc. in the database.
 *
 */

// If uninstall not called from WordPress, then exit (do nothing)
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Remove all Toasty_Purge_ options from the database
 *
 * @since v2.0.0
 */
global $wpdb;

if ( is_multisite() ) {
	$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->prepare(
			"DELETE FROM `{$wpdb->base_prefix}sitemeta` WHERE `meta_key` LIKE %s LIMIT 1000",
			'%Toasty_Purge_%'
		)
	);
} else {
	$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->prepare(
			"DELETE FROM `{$wpdb->base_prefix}options` WHERE `option_name` LIKE %s LIMIT 1000",
			'%Toasty_Purge_%'
		)
	);
}
