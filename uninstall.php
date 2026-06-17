<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_site_option( 'toastyprg_options' );
delete_site_option( 'toastyprg_version' );
