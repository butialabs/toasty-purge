<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TOASTYPRG
 */
class TOASTYPRG {

	/**
	 * The single instance of TOASTYPRG.
	 *
	 * @var    object
	 * @access   private
	 * @since    v0.0.1
	 */
	private static $_instance = null;

	/**
	 * Settings class object
	 *
	 * @var     object
	 * @access  public
	 * @since   v0.0.1
	 */
	public $settings = null;

	/**
	 * The version number.
	 *
	 * @var     string
	 * @access  public
	 * @since   v0.0.1
	 */
	public $_version;

	/**
	 * The token.
	 *
	 * @var     string
	 * @access  public
	 * @since   v0.0.1
	 */
	public $_token;

	/**
	 * The option name.
	 *
	 * @var     string
	 * @access  public
	 * @since   v0.0.1
	 */
	public $_option_name;

	/**
	 * The main plugin file.
	 *
	 * @var     string
	 * @access  public
	 * @since   v0.0.1
	 */
	public $file;

	/**
	 * The main plugin directory.
	 *
	 * @var     string
	 * @access  public
	 * @since   v0.0.1
	 */
	public $dir;

	/**
	 * The plugin styles directory.
	 *
	 * @var     string
	 * @access  public
	 * @since   v0.0.1
	 */
	public $assets_dir;

	/**
	 * The plugin assets URL.
	 *
	 * @var     string
	 * @access  public
	 * @since   v0.0.1
	 */
	public $assets_url;

	/**
	 * The admin API instance.
	 *
	 * @var     object
	 * @access  public
	 * @since   v0.0.1
	 */
	public $admin = null;

	/**
	 * Holds an array of plugin options.
	 *
	 * @var array
	 * @access public
	 * @since  2.x
	 */
	public $options = array();

	/**
	 * Constructor function.
	 *
	 * @access  public
	 * @since   v0.0.1
	 *
	 * @param string $file
	 * @param string $version Version number.
	 */
	public function __construct( $file = '', $version = '0.0.1' ) {
		$this->_version     = $version;
		$this->_token       = 'toastyprg';
		$this->_option_name = $this->_token . '_options';

		// Load plugin environment variables
		$this->file       = $file;
		$this->dir        = dirname( $this->file );
		$this->assets_dir = trailingslashit( $this->dir ) . 'css';
		$this->assets_url = esc_url( trailingslashit( plugins_url( '/css/', $this->file ) ) );

		register_activation_hook( $this->file, array( $this, 'install' ) );

		/*** PLUGIN FUNCTIONS ***/

		add_action( 'admin_bar_menu', array( $this, 'remove_adminbar_settings' ), 999 );
		add_action( 'wp_dashboard_setup', array( $this, 'remove_dashboard_widget' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'admin_menu', array( $this, 'remove_menu_item'), 999 );
		add_action( 'plugins_loaded', array( $this, 'remove_frontend_html_comments' ), 999 );
		add_action( 'admin_init', array( $this, 'apply_remove_class_hook' ) );
		add_action( 'admin_menu', array( $this, 'remove_admin_columns_init' ), 11 );
		add_action( 'admin_init', array( $this, 'remove_seo_scores_dropdown_filters' ), 20 );
		add_action( 'admin_init', array( $this, 'disable_ai_llms_features' ), 5 );


		// Load API for generic admin functions
		if ( is_admin() ) {
			$this->admin = new TOASTYPRG_Admin_API();
		}

		$this->options = $this->_get_options();
	} // End __construct ()

	/**
	 * Remove Settings submenu in admin bar
	 * Since Yoast SEO 3.6 it is possible to disable the adminbar menu within
	 * Dashboard > Features but only in individual sites, not network admin
	 *
	 * credits [Lee Rickler](https://profiles.wordpress.org/lee-rickler/)
	 */
	public function remove_adminbar_settings() {
		if ( empty( $this->options['remove_adminbar'] ) ) {
			return;
		}
		global $wp_admin_bar;
		$nodes = array_keys( $wp_admin_bar->get_nodes() );
		foreach ( $nodes as $node ) {
			if ( false !== strpos( $node, 'wpseo' ) ) {
				$wp_admin_bar->remove_node( $node );
			}
		}
	}

	/**
	 * Version 2.3 of Yoast SEO introduced a dashboard widget
	 * This function removes this widget
	 *
	 * @since v1.5.0
	 */
	public function remove_dashboard_widget() {

		if ( ! empty( $this->options['remove_dbwidget'] ) ) {

			remove_meta_box( 'wpseo-dashboard-overview', 'dashboard', 'side' );

		}
	}

	/**
	 * at some point Yoast SEO has introduced "Primary Category Feature"
	 * This function removes this "feature"
	 *
	 * @since v3.6.0
	 */
	public function remove_primary_category_feature() {

		if ( ! empty( $this->options['remove_primarycatfeat'] ) ) {

			add_filter( 'wpseo_primary_term_taxonomies', '__return_empty_array' );

		}
	}

	/**
	 * Remove Search Console and other menu items
	 *
	 * @since v3.10.0
	 */
	public function remove_menu_item() {

		// Google has discontinued its Crawl Errors API so the Search Console page in Yoast is useless now; @since v3.12.0
		remove_submenu_page( 'wpseo_dashboard', 'wpseo_search_console' );

		// Remove Support submenu; @since v0.0.1
		if ( ! empty( $this->options['hide_support_submenu'] ) ) {
			remove_submenu_page( 'wpseo_dashboard', 'wpseo_page_support' );
		}

		// Remove AI Brand Insights submenu (free & premium versions); @since v0.0.1
		if ( ! empty( $this->options['hide_ai_brand_insights'] ) ) {
			remove_submenu_page( 'wpseo_dashboard', 'wpseo_brand_insights' );
			remove_submenu_page( 'wpseo_dashboard', 'wpseo_brand_insights_premium' );
		}

	}

	/**
	 * Upon request by many the plugin now also removes the frontend HTML comments left by Yoast
	 * credits [Robert Went](https://gist.github.com/robwent/f36e97fdd648a40775379a86bd97b332)
	 * credits [Emanuel-23](https://github.com/senlin/so-clean-up-wp-seo/issues/95)
	 *
	 * @since v3.11.0
	 * @modified v3.11.1
	 * @modified v3.14.6
	 */
	public function remove_frontend_html_comments() {

		if ( ! empty( $this->options['remove_html_comments'] ) ) {

			if ( defined( 'WPSEO_VERSION' ) ) {

				$wpseo_version = constant( 'WPSEO_VERSION' );

				// the wpseo_debug_markers() filter was added in WP SEO version 14.1
				if ( version_compare ( $wpseo_version , '14.1', '<' ) ) {

					add_action( 'get_header', function () { ob_start( function ( $o ) {
						return preg_replace( '/\n?<.*?Yoast SEO plugin.*?>/mi', '', $o ); } ); } );
					add_action( 'wp_head',function (){ ob_end_flush(); }, 999 );

				} else {

					add_filter( 'wpseo_debug_markers', '__return_false' );

				}

			}

		}
	}

    /**
	 * Remove warning notice when changing permalinks
	 *
	 * Removes the permalink notice action (see includes/remove-class.php)
	 * Uses @remove_class_hook.
	 *
	 * @since	v3.13.0
	 */
	public function apply_remove_class_hook() {

		if ( ! empty( $this->options['remove_permalinks_warning'] ) ) {

			toastyprg_remove_class_hook( 'admin_notices', 'WPSEO_Admin_Init', 'permalink_settings_notice' );

		}
	}

	/*
	 * Remove admin columns
	 * @since v0.0.1 remove seo columns one by one
	 * credits [Ronny Myhre Njaastad](https://github.com/ronnymn)
	 * credits [Dibbyo456](https://github.com/Dibbyo456)
	 */
	public function remove_admin_columns_init() {

		// post, page and custom post types
		$all_post_types = array_merge( array( 'post', 'page' ), get_post_types( array( '_builtin' => false ) ) );

		foreach( $all_post_types as $post_type ) {
			add_filter( 'manage_edit-'. $post_type .'_columns', array( $this, 'remove_admin_columns' ), 10, 1  );
		}

	}

	public function remove_admin_columns( $columns ) {

		// if empty return columns right away.
		if ( empty( $this->options['hide_admincolumns'] ) ) {
			return $columns;
		}

		// seo score column
		if ( in_array( 'seoscore', $this->options['hide_admincolumns'] ) ) {
			unset( $columns['wpseo-score'] );
		}

		// readability column
		if ( in_array( 'readability', $this->options['hide_admincolumns'] ) ) {
			unset( $columns['wpseo-score-readability'] );
		}

		// title column
		if ( in_array( 'title', $this->options['hide_admincolumns'] ) ) {
			unset( $columns['wpseo-title'] );
		}

		// meta description column
		if ( in_array( 'metadescr', $this->options['hide_admincolumns'] ) ) {
			unset( $columns['wpseo-metadesc'] );
		}

		// focus keyword column
		if ( in_array( 'focuskw', $this->options['hide_admincolumns'] ) ) {
			unset( $columns['wpseo-focuskw'] );
		}

		// outgoing internal links column
		if ( in_array( 'outgoing_internal_links', $this->options['hide_admincolumns'] ) ) {
			unset( $columns['wpseo-links'] );
			unset( $columns['wpseo-linked'] );
		}

		return $columns;

	}


	/**
	 * Remove (as opposed to hide) SEO/readability Scores dropdown filters on edit posts screens
	 *
	 * credits [Dibbyo456](https://github.com/Dibbyo456)
	 */
	public function remove_seo_scores_dropdown_filters() {
		if ( ! empty( $this->options['remove_seo_scores_dropdown_filters'] ) ) {
			global $wpseo_meta_columns ;
			if ( $wpseo_meta_columns  ) {
				remove_action( 'restrict_manage_posts', array( $wpseo_meta_columns , 'posts_filter_dropdown' ) );
				remove_action( 'restrict_manage_posts', array( $wpseo_meta_columns , 'posts_filter_dropdown_readability' ) );
			}
		}
	}

	/**
	 * Disable AI & LLMs.txt features
	 * Disables the enable_llms_txt option in Yoast SEO
	 *
	 * @since v0.0.1
	 */
	public function disable_ai_llms_features() {
		if ( ! empty( $this->options['disable_ai_llms_features'] ) ) {
			// Filter to force disable the enable_llms_txt option
			add_filter( 'wpseo_option_wpseo_defaults', array( $this, 'filter_llms_defaults' ), 999 );
			add_filter( 'option_wpseo', array( $this, 'filter_llms_option' ), 999 );
		}
	}

	/**
	 * Filter LLMs.txt defaults to disable it
	 *
	 * @param array $defaults The default options.
	 * @return array Modified defaults.
	 * @since v0.0.1
	 */
	public function filter_llms_defaults( $defaults ) {
		if ( isset( $defaults['enable_llms_txt'] ) ) {
			$defaults['enable_llms_txt'] = false;
		}
		return $defaults;
	}

	/**
	 * Filter wpseo option to disable LLMs.txt
	 *
	 * @param array $options The options.
	 * @return array Modified options.
	 * @since v0.0.1
	 */
	public function filter_llms_option( $options ) {
		if ( is_array( $options ) && isset( $options['enable_llms_txt'] ) ) {
			$options['enable_llms_txt'] = false;
		}
		return $options;
	}


	/**
	 * CSS needed to hide the various options ticked with checkboxes
	 *
	 * @since    v0.0.1
	 */
	public function enqueue_admin_assets() {

		$handle = 'toasty-purge-admin';
		wp_register_style( $handle, false, array(), $this->_version );
		wp_enqueue_style( $handle );

		ob_start();

		// Problems/Notification boxes
		if ( ! empty( $this->options['hide_dashboard_problems_notifications'] ) ) {
			if ( in_array( 'problems', $this->options['hide_dashboard_problems_notifications'] ) ) {
				echo '.yoast-container.yoast-container__error{display:none;}';
			}
			if ( in_array( 'notifications', $this->options['hide_dashboard_problems_notifications'] ) ) {
				echo '.yoast-container.yoast-container__warning{display:none;}';
			}
		}

		// Hide ads for premium version across Yoast SEO Settings pages
		if ( ! empty( $this->options['hide_ads'] ) ) {
			echo '
			/* hide sidebar ad */
			@media (min-width: 1280px){
				.seo_page_wpseo_page_settings .xl\:yst-right-8{
					display:none!important;
				}
			}
			/* hide sidebar ad General page */
			.toplevel_page_wpseo_dashboard #sidebar-container {
				display:none;
			}
			/* hide ad for premium at bottom of Settings screen */
			.seo_page_wpseo_page_settings .yst-grow.yst-space-y-6.yst-mb-8.xl\:yst-mb-0 .yst-p-6.xl\:yst-max-w-3xl.yst-rounded-lg.yst-bg-white.yst-shadow {
				display:none;
			}
			/* hide premium features from settings page */
			.seo_page_wpseo_page_settings div#card-wpseo-enable_link_suggestions,
			.seo_page_wpseo_page_settings div#card-wpseo-enable_index_now,
			.seo_page_wpseo_page_settings div#card-wpseo-enable_metabox_insights {
				display:none !important;
			}

			/* hide upsells */
			.seo_page_wpseo_page_settings .yst-feature-upsell.yst-feature-upsell--card {
				display:none;
			}

			/* hide premium upsell admin block that shows throughout Yoast backend */
			.yoast_premium_upsell,
			.yoast_premium_upsell_admin_block,
			#wpseo-local-seo-upsell,
			div[class^="SocialUpsell__PremiumInfoText"] {
				display:none;
			}

			/* hide upsell notice in Yoast SEO Dashboard */
			#yoast-warnings #wpseo-upsell-notice,
			#yoast-additional-keyphrase-collapsible-metabox,
			.wpseo-keyword-synonyms,.wpseo-multiple-keywords,
			.switch-container.premium-upsell,
			.yoast-settings-section-upsell,
			.yoast-settings-section-disabled{
				display:none !important;
			}

			/* hide help center */
			div#yoast-helpscout-beacon,
			.yoast-help-center__button {
				display:none !important;
			}
			';
		}

		// Yoast sidebar menu
		if ( ! empty( $this->options['hide_premium_submenu'] ) ) {
			echo '
				/* hide "Academy", "Premium", "Workouts" and "Redirects" submenus */
				li#toplevel_page_wpseo_dashboard>ul>li:nth-child(6),
				li#toplevel_page_wpseo_dashboard>ul>li:nth-child(7),
				li#toplevel_page_wpseo_dashboard>ul>li:nth-child(8),
				li#toplevel_page_wpseo_dashboard>ul>li:nth-child(9) {
					display:none;
				}

				/* hide issue counter */
				#wpadminbar .yoast-issue-counter,#toplevel_page_wpseo_dashboard .wp-menu-name .update-plugins{
					display:none;
				}
			';
		}

		// Hide Support submenu
		if ( ! empty( $this->options['hide_support_submenu'] ) ) {
			echo '
				/* hide Support submenu */
				li#toplevel_page_wpseo_dashboard ul li a[href*="wpseo_page_support"] {
					display:none !important;
				}
			';
		}

		// Hide AI Brand Insights submenu
		if ( ! empty( $this->options['hide_ai_brand_insights'] ) ) {
			echo '
				/* hide AI Brand Insights submenu (free & premium) */
				li#toplevel_page_wpseo_dashboard ul li a[href*="wpseo_brand_insights"],
				li#toplevel_page_wpseo_dashboard ul li a[href*="wpseo_brand_insights_premium"] {
					display:none !important;
				}
			';
		}

		// Hide AI & LLMs.txt features from settings page and disable llms-txt tab
		if ( ! empty( $this->options['disable_ai_llms_features'] ) ) {
			echo '
				/* hide AI options from site-features in Settings */
				.seo_page_wpseo_page_settings div#card-wpseo-ai_generate_titles_and_descriptions,
				.seo_page_wpseo_page_settings div#card-wpseo-enable_ai_generator,
				.seo_page_wpseo_page_settings div#card-wpseo-enable_llms_txt,
				.seo_page_wpseo_page_settings div[id*="ai_"],
				.seo_page_wpseo_page_settings div[id*="llms"] {
					display:none !important;
				}

				/* hide llms-txt tab from Settings navigation */
				.seo_page_wpseo_page_settings a[href*="llms-txt"],
				.seo_page_wpseo_page_settings button[data-id="llms-txt"],
				.seo_page_wpseo_page_settings [data-route*="llms-txt"],
				.seo_page_wpseo_page_settings nav a[href*="llms-txt"] {
					display:none !important;
				}

				/* hide AI feature cards and sections */
				.seo_page_wpseo_page_settings [class*="ai-generator"],
				.seo_page_wpseo_page_settings [class*="llms-txt"],
				.seo_page_wpseo_page_settings [id*="llms-txt"],
				.seo_page_wpseo_page_settings [data-testid*="llms"] {
					display:none !important;
				}

				/* hide entire AI Tools section block (Ferramentas de IA) */
				.seo_page_wpseo_page_settings fieldset:has(div[id*="ai_"]),
				.seo_page_wpseo_page_settings fieldset:has(div[id*="llms"]),
				.seo_page_wpseo_page_settings section:has(div[id*="ai_"]),
				.seo_page_wpseo_page_settings section:has(div[id*="llms"]),
				.seo_page_wpseo_page_settings .yst-card:has([id*="ai_"]),
				.seo_page_wpseo_page_settings .yst-card:has([id*="llms"]) {
					display:none !important;
				}

				/* hide AI Tools section by header text matching */
				.seo_page_wpseo_page_settings [id*="ai-tools"],
				.seo_page_wpseo_page_settings [class*="ai-tools"],
				.seo_page_wpseo_page_settings [data-id*="ai-tools"],
				.seo_page_wpseo_page_settings #section-ai-tools,
				.seo_page_wpseo_page_settings .yst-section-site-features-ai-tools,
				.seo_page_wpseo_page_settings [aria-labelledby*="ai"],
				.seo_page_wpseo_page_settings [id="ai-tools-section"] {
					display:none !important;
				}
			';
		}

		// all columns
		if ( ! empty( $this->options['hide_admincolumns'] ) ) {
			// seo score column
			if ( in_array( 'seoscore', $this->options['hide_admincolumns'] ) ) {
				echo '.column-wpseo-score,.column-wpseo_score{display:none;}'; // @since v0.0.1 remove seo columns one by one
			}
			// readability column
			if ( in_array( 'readability', $this->options['hide_admincolumns'] ) ) {
				echo '.column-wpseo-score-readability,.column-wpseo_score_readability{display:none;}'; // @since v2.6.0 remove added readibility column
			}
			// title column
			if ( in_array( 'title', $this->options['hide_admincolumns'] ) ) {
				echo '.column-wpseo-title{display:none;}'; // @since v0.0.1 remove seo columns one by one
			}
			// meta description column
			if ( in_array( 'metadescr', $this->options['hide_admincolumns'] ) ) {
				echo '.column-wpseo-metadesc{display:none;}'; // @since v0.0.1 remove seo columns one by one
			}
			// focus keyword column
			if ( in_array( 'focuskw', $this->options['hide_admincolumns'] ) ) {
				echo '.column-wpseo-focuskw{display:none;}'; // @since v0.0.1 remove seo columns one by one
			}
			// outgoing internal links column
			if ( in_array( 'outgoing_internal_links', $this->options['hide_admincolumns'] ) ) {
				echo '.column-wpseo-links{display:none;}'; // @since v3.10.1 add checkbox to hide outgoing internal links column
			}
		}

		// image warning nag
		if ( ! empty( $this->options['hide_imgwarning_nag'] ) ) {
			echo '#yst_opengraph_image_warning{display:none;}#postimagediv.postbox{border:1px solid #e5e5e5!important;}'; // @since v1.7.0 hide yst opengraph image warning nag
		}

		// hide content/keyword score on Publish/Update Post metabox
		// hide Premium SEO Analysis button on Publish/Update Post metabox
		if ( ! empty( $this->options['hide_content_keyword_score'] ) ) {
			echo '
				#misc-publishing-actions #content-score,
				#misc-publishing-actions #keyword-score,
				#misc-publishing-actions #inclusive-language-score,
				#misc-publishing-actions .yoast-zapier-text
				{display:none;}
			';
		}

		// hide Premium features on new/edit post-type screens
		if ( ! empty( $this->options['hide_premium_features_yoast_metabox'] ) ) {
			echo '
				button#yoast-premium-seo-analysis-metabox-modal-open-button,
				#wpseo-metabox-root button.wpseo-keyword-synonyms,
				#wpseo-metabox-root button.wpseo-multiple-keywords,
				button#yoast-additional-keyphrase-collapsible-metabox {
					display: none;
				}
			';
		}

		// hide Premium ad after deleting content (post, page, wc product, cpt)
		if ( ! empty( $this->options['hide_ad_after_trashing_content'] ) ) {
			echo '
				body.edit-php .yoast-notification.notice.notice-warning.is-dismissible,
				body[class*="taxonomy-"] .yoast-notification.notice.notice-warning.is-dismissible
				{display:none;}
			'; // @since v3.14.0; @modified v3.14.2
		}


		// seo settings profile page
		if ( ! empty( $this->options['hide_seo_settings_profile_page'] ) ) {
			echo '.profile-php .yoast.yoast-settings{display:none;}'; // @since v3.6.0
		}

		echo '</style>';

		$css = ob_get_clean();

		$css = preg_replace( '/^\s*<style[^>]*>\s*/i', '', $css );
		$css = preg_replace( '/\s*<\/style>\s*$/i', '', $css );

		wp_add_inline_style( $handle, $css );
	}

	/**
	 * Main TOASTYPRG Instance
	 *
	 * Ensures only one instance of TOASTYPRG is loaded or can be loaded.
	 *
	 * @since v0.0.1
	 * @static
	 * @see   toastyprg()
	 *
	 * @param string $file
	 * @param string $version Version number.
	 *
	 * @return TOASTYPRG $_instance
	 */
	public static function instance( $file = '', $version = '0.0.1' ) {
		if ( null === self::$_instance ) {
			self::$_instance = new self( $file, $version );
		}

		return self::$_instance;
	} // End instance ()

	/**
	 * Cloning is forbidden.
	 *
	 * @since v0.0.1
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'No Access', 'toasty-purge' ), esc_html( $this->_version ) );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since v0.0.1
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'No Access', 'toasty-purge' ), esc_html( $this->_version ) );
	} // End __wakeup ()

	/**
	 * Installation. Runs on activation.
	 *
	 * @access  public
	 * @since   v0.0.1
	 * @return  void
	 */
	public function install() {
		$this->_log_version_number();
		$this->_set_defaults();
	} // End install ()

	/**
	 * Log the plugin version number.
	 *
	 * @access  private
	 * @since   v0.0.1
	 * @return  void
	 */
	private function _log_version_number() {
		update_site_option( $this->_token . '_version', $this->_version );
	} // End _log_version_number ()

	/**
	 * Array containing the default values.
	 * Use `array_keys()` to return the key names.
	 *
	 * @return array
	 */
	public function get_defaults() {
		$defaults = array(
			'hide_dashboard_problems_notifications' => array(
				'problems',
				'notifications'
			),
			'hide_ads'                             => 'on',
			'hide_premium_submenu'				   => 'on',
			'hide_admincolumns'                    => array(
				'seoscore',
				'readability',
				'title',
				'metadescr',
				'outgoing_internal_links'
			),
			'remove_seo_scores_dropdown_filters'	=> 'on',
			'hide_imgwarning_nag'					=> 'on',
			'hide_content_keyword_score'			=> 'on',
			'hide_premium_features_yoast_metabox'	=> 'on',
			'hide_ad_after_trashing_content'		=> 'on',
			'remove_adminbar'                       => 'on',
			'remove_dbwidget'                       => 'on',
			'remove_permalinks_warning'				=> 'on',
			'hide_seo_settings_profile_page'		=> 'on',
			'remove_html_comments'					=> 'on',
			'hide_support_submenu'					=> 'on',
			'hide_ai_brand_insights'				=> 'on',
			'disable_ai_llms_features'				=> 'on'
		);

		return $defaults;
	}

	/**
	 * Set default values on activation.
	 *
	 * @access private
	 * @return void
	 */
	private function _set_defaults() {
		$defaults = $this->get_defaults();
		add_site_option( $this->_option_name, $defaults );
	} // End _set_defaults ()

	/**
	 * Get plugin options.
	 * Add new default options if missing from saved options.
	 *
	 * @return array $options Plugin options.
	 * @since 3.8.1
	 */
	private function _get_options() {
		$options = get_site_option( $this->_option_name );

		if ( false === $options ) {
			$options = array();
		}

		$defaults = $this->get_defaults();
		$diff     = array_diff_key( $defaults, $options );

		if ( ! empty( $diff ) ) {
			$options = array_merge( $options, $diff );
			update_site_option( $this->_option_name, $options );
		}

		return $options;
	}

}
