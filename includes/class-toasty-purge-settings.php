<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TOASTYPRG_Settings {

	/**
	 * The single instance of TOASTYPRG_Settings.
	 *
	 * @var    object
	 * @access   private
	 * @since    v1.0.0
	 */
	private static $_instance = null;

	/**
	 * The main plugin object.
	 *
	 * @var    object
	 * @access   public
	 * @since    v1.0.0
	 */
	public $parent = null;

	/**
	 * Prefix for plugin settings.
	 *
	 * @var     string
	 * @access  public
	 * @since   v1.0.0
	 */
	public $base = '';

	/**
	 * Available settings for plugin.
	 *
	 * @var     array
	 * @access  public
	 * @since   v1.0.0
	 */
	public $settings = array();

	/**
	 * TOASTYPRG_Settings constructor.
	 *
	 * @param $parent
	 */
	public function __construct( $parent ) {
		$this->parent = $parent;

		$this->base = $this->parent->_token . '_';

		$plugin_slug = plugin_basename( $this->parent->file );

		// Initialise settings
		add_action( 'init', array( $this, 'init_settings' ), 11 );

		// Register plugin settings
		add_action( 'admin_init', array( $this, 'register_settings' ) );

		// Add settings page to menu
		if ( is_multisite() ) {
			add_action( 'network_admin_menu', array( $this, 'add_menu_item' ), 15 );
		} else {
			add_action( 'admin_menu', array( $this, 'add_menu_item' ), 15 );
		}
		// Add settings link to plugins page
		if ( is_multisite() ) {
			add_filter( 'network_admin_plugin_action_links_' . $plugin_slug, array( $this, 'add_settings_link' ) );
		} else {
			add_filter( 'plugin_action_links_' . $plugin_slug, array( $this, 'add_settings_link' ) );
		}

		// Save setting in Multisite
		add_action( 'network_admin_edit_' . $this->parent->_token . '_options', array(
			$this,
			'update_settings',
		) );

	}

	/**
	 * Save settings when on single-site or multisite network admin.
	 *
	 * @access public
	 * @since  2.x
	 */
	public function update_settings() {

		if ( ! isset( $_POST['option_page'], $_POST['action'] ) ) {
			return;
		}

		$option_group = $this->parent->_token . '_options';
		$nonce_action = $option_group . '-options';

		// Verify nonce added by settings_fields().
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ), $nonce_action ) ) {
			return;
		}

		$capability = is_multisite() ? 'manage_network' : 'manage_options';
		if ( ! current_user_can( $capability ) ) {
			wp_die( esc_html__( 'You do not have permission to save these settings.', 'toasty-purge' ) );
		}

		$plugin        = TOASTYPRG::instance();
		$options_list  = array_keys( $plugin->get_defaults() );
		$multi_options = array(
			'hide_admincolumns',
			'hide_dashboard_problems_notifications',
			'hide_helpcenter',
		);

		if ( $option_group === sanitize_key( wp_unslash( $_POST['option_page'] ) ) &&
		     'update' === sanitize_key( wp_unslash( $_POST['action'] ) )
		) {
			$options = array();
			foreach ( $options_list as $option ) {
				$key = $this->parent->_token . '_' . $option;
				if ( ! isset( $_POST[ $key ] ) ) {
					$options[ $option ] = in_array( $option, $multi_options, true ) ? array() : null;
				} elseif ( is_array( $_POST[ $key ] ) ) {
					$options[ $option ] = array_map( 'sanitize_key', wp_unslash( $_POST[ $key ] ) );
				} else {
					$options[ $option ] = sanitize_text_field( wp_unslash( $_POST[ $key ] ) );
				}
			}
			update_site_option( $this->parent->_option_name, $options );

			$location = add_query_arg(
				array( 'page' => $option_group ),
				network_admin_url( 'admin.php' )
			);
			wp_safe_redirect( $location );
			exit;
		}
	}

	/**
	 * Initialise settings
	 *
	 * @return void
	 * @since   v1.0.0
	 */
	public function init_settings() {
		$this->settings = $this->settings_fields();
	}

	/**
	 * Add settings page to admin menu
	 *
	 * @return void
	 * @since   v1.0.0
	 */
	public function add_menu_item() {
		$capability = is_multisite() ? 'manage_network' : 'manage_options';
		add_submenu_page(
			'wpseo_dashboard',
			__( 'Toasty Purge Settings', 'toasty-purge' ),
			__( 'Toasty Purge', 'toasty-purge' ),
			$capability,
			$this->parent->_token . '_options',
			array( $this, 'settings_page' )
		);
	}

	/**
	 * Add settings link to plugin list table
	 *
	 * @param  array $links Existing links
	 *
	 * @return array        Modified links
	 * @since   v1.0.0
	 */
	public function add_settings_link( $links ) {
		$settings_link = '<a href="admin.php?page=' . esc_attr( $this->parent->_token . '_options' ) . '">' . esc_html__( 'Settings', 'toasty-purge' ) . '</a>';
		array_unshift( $links, $settings_link );

		return $links;
	}

	/**
	 * Build settings fields
	 *
	 * @return array Fields to be displayed on settings page
	 * @since    v1.0.0
	 */
	private function settings_fields() {
		$plugin  = TOASTYPRG::instance();
		$options = $plugin->get_defaults();

		$settings['section_1'] = array(
			'title'  => __( 'Yoast SEO Settings pages', 'toasty-purge' ),
			'fields' => array(
				array(
					'id'          => 'hide_dashboard_problems_notifications',
					'label'       => __( 'General > Dashboard tab > Problems/Notifications boxes', 'toasty-purge' ),
					'description' => '<br>' . __( 'Hide entire Problems/Notifications boxes from the Dashboard tab under General Settings.', 'toasty-purge' ),
					'type'        => 'checkbox_multi',
					'options'     => array(
						'problems'      => __( 'Hide entire Problems box', 'toasty-purge' ),
						'notifications' => __( 'Hide entire Notifications box', 'toasty-purge' ),
					),
					'default'     => $options['hide_dashboard_problems_notifications'],
				),
				array(
					'id'          => 'hide_ads',
					'label'       => __( 'Settings page > Yoast Premium', 'toasty-purge' ),
					'description' => __( 'Hide as many as possible ads, premium features or upsells from the Yoast Settings pages.', 'toasty-purge' ),
					'type'        => 'checkbox',
					'default'     => $options['hide_ads'],
				),
				array(
					'id'          => 'hide_premium_submenu',
					'label'       => __( 'Premium submenus and issue counter', 'toasty-purge' ),
					'description' => __( 'Hides the "Premium", "Workouts" and "Redirects" submenus as well as the issue counter from the admin sidebar.', 'toasty-purge' ),
					'type'        => 'checkbox',
					'default'     => $options['hide_premium_submenu'],
				),
			)
		);

		$settings['section_2'] = array(
			'title'	=> __( 'Posts, Pages, Custom post type, Taxonomy pages', 'toasty-purge' ),
			'fields' => array(
				array(
					'id'          => 'hide_admincolumns',
					'label'       => __( 'Admin columns', 'toasty-purge' ),
					'description' => '<br>' . __( 'There are so many admin columns added to Posts/Pages/taxonomies that it is impossible to see the things that matter, such as the Title. Multiple selections are allowed.', 'toasty-purge' ),
					'type'        => 'checkbox_multi',
					'options'     => array(
						'seoscore'    => __( 'Remove SEO score column', 'toasty-purge' ),
						'readability' => __( 'Remove Readability score column', 'toasty-purge' ),
						'title'       => __( 'Remove SEO title column', 'toasty-purge' ),
						'metadescr'   => __( 'Remove Meta Desc. column', 'toasty-purge' ),
						'focuskw'     => __( 'Remove keyphrase column', 'toasty-purge' ),
						'outgoing_internal_links' => __( 'Remove outgoing/received internal links column', 'toasty-purge' ),
					),
					'default'     => $options['hide_admincolumns'],
				),
				array(
					'id'          => 'remove_seo_scores_dropdown_filters',
					'label'       => __( 'SEO/Readability Scores Dropdown Filters', 'toasty-purge' ),
					'description' => __( 'Remove SEO Scores and Readability Scores Dropdown Filters on the Edit Posts/Pages screen', 'toasty-purge' ),
					'type'        => 'checkbox',
					'default'     => $options['remove_seo_scores_dropdown_filters'],
				),
				array(
					'id'          => 'hide_imgwarning_nag',
					'label'       => __( 'Featured image nag', 'toasty-purge' ),
					'description' => __( 'Hide image warning nag that shows in edit Post/Page screen when featured image is smaller than 200x200 pixels.', 'toasty-purge' ),
					'type'        => 'checkbox',
					'default'     => $options['hide_imgwarning_nag'],
				),
				array(
					'id'          => 'hide_content_keyword_score',
					'label'       => __( 'Keyword/Content Score', 'toasty-purge' ),
					'description' => __( 'Hide the Keyword/Content Score from the Publish/Update Metabox on the Edit Post/Page/CPT screen.', 'toasty-purge' ),
					'type'        => 'checkbox',
					'default'     => $options['hide_content_keyword_score'],
				),
				array(
					'id'          => 'hide_premium_features_yoast_metabox',
					'label'       => __( 'Hide Premium features on new/edit post-type screens', 'toasty-purge' ),
					'description' => __( 'Hide Premium features in the Yoast SEO metabox when publishing or editing content.', 'toasty-purge' ),
					'type'        => 'checkbox',
					'default'     => $options['hide_premium_features_yoast_metabox'],
				),
				array(
					'id'          => 'hide_ad_after_trashing_content',
					'label'       => __( 'Hide Ad after trashing content', 'toasty-purge' ),
					'description' => __( 'When deleting content (Post, Page, Product and other Custom Post Type) a new notice appears on the edit screen that is an upsell ad for the premium version of Yoast SEO. This setting hides that notice.', 'toasty-purge' ),
					'type'        => 'checkbox',
					'default'     => $options['hide_ad_after_trashing_content'],
				),
			)
		);

		$settings['section_3'] = array(
			'title'	=> __( 'Miscellaneous', 'toasty-purge' ),
			'fields' => array(
				array(
					'id'          => 'remove_adminbar',
					'label'       => __( 'SEO menu admin bar', 'toasty-purge' ),
					'description' => __( 'Remove Yoast SEO icon and drop-down menu with more premium buttons from the admin bar.', 'toasty-purge' ),
					'type'        => 'checkbox',
					'default'     => $options['remove_adminbar'],
				),
				array(
					'id'          => 'remove_dbwidget',
					'label'       => __( 'Dashboard widget', 'toasty-purge' ),
					'description' => __( 'Remove the Yoast SEO widget from the WordPress Dashboard.', 'toasty-purge' ),
					'type'        => 'checkbox',
					'default'     => $options['remove_dbwidget'],
				),
				array(
					'id'          => 'remove_permalinks_warning',
					'label'       => __( 'Remove Permalinks Warning Notice', 'toasty-purge' ),
					'description' => __( 'Remove the notice that shows when changing permalinks informing the user that it is not a good idea', 'toasty-purge' ),
					'type'        => 'checkbox',
					'default'     => $options['remove_permalinks_warning'],
				),
				array(
					'id'          => 'hide_seo_settings_profile_page',
					'label'       => __( 'Profile page', 'toasty-purge' ),
					'description' => __( 'Hide SEO Settings on individual profile page.', 'toasty-purge' ),
					'type'        => 'checkbox',
					'default'     => $options['hide_seo_settings_profile_page'],
				),
				array(
					'id'          => 'remove_html_comments',
					'label'       => __( 'Remove HTML Comments', 'toasty-purge' ),
					'description' => __( 'Remove the HTML Comments from the source code (frontend) of the site', 'toasty-purge' ),
					'type'        => 'checkbox',
					'default'     => $options['remove_html_comments'],
				),
				array(
					'id'          => 'hide_support_submenu',
					'label'       => __( 'Support submenu', 'toasty-purge' ),
					'description' => __( 'Hide the Support submenu from the Yoast SEO menu.', 'toasty-purge' ),
					'type'        => 'checkbox',
					'default'     => $options['hide_support_submenu'],
				),
			),
		);

		$settings['section_4'] = array(
			'title'	=> __( 'IA', 'toasty-purge' ),
			'fields' => array(
				array(
					'id'          => 'hide_ai_brand_insights',
					'label'       => __( 'AI Brand Insights', 'toasty-purge' ),
					'description' => __( 'Hide the AI Brand Insights submenu from the Yoast SEO menu.', 'toasty-purge' ),
					'type'        => 'checkbox',
					'default'     => $options['hide_ai_brand_insights'],
				),
				array(
					'id'          => 'disable_ai_llms_features',
					'label'       => __( 'Yoast AI & LLMs.txt', 'toasty-purge' ),
					'description' => __( 'Hide the AI and LLMs.txt options block from the Yoast Settings page (Site features) and disable the LLMs.txt tab. Also disables the enable_llms_txt option.', 'toasty-purge' ),
					'type'        => 'checkbox',
					'default'     => $options['disable_ai_llms_features'],
				),
			),
		);

		$settings = apply_filters( $this->parent->_token . '_options_fields', $settings ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound

		return $settings;
	}

	/**
	 * Sanitize settings values before saving.
	 *
	 * @param mixed $value Raw settings value.
	 * @return mixed Sanitized settings value.
	 */
	public function sanitize_settings( $value ) {
		return map_deep( $value, 'sanitize_text_field' );
	}

	/**
 	* Register plugin settings
 	*
 	* @return void
 	* @since   v1.0.0
 	*/
	public function register_settings() {
		$settings    = $this->settings_fields();
		$option_page = $this->parent->_token . '_options';

		if ( is_array( $settings ) ) {
			foreach ( $settings as $section => $data ) {
				register_setting(
					$option_page,
					$this->parent->_option_name,
					array( 'sanitize_callback' => array( $this, 'sanitize_settings' ) )
				);
				add_settings_section( $section, $data['title'], array( $this, 'settings_section' ), $option_page );

				foreach ( $data['fields'] as $field ) {
					add_settings_field( $field['id'], $field['label'], array( $this->parent->admin, 'display_field' ), $option_page, $section, array( 'field' => $field, 'prefix' => $this->base ) );
				}
			}
		}

		// Multisite saves via update_settings() via the network_admin_edit_ hook — not here.
		if ( ! is_multisite() && isset( $_POST['action'] ) && 'update' === $_POST['action'] ) {
			if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ), $option_page . '-options' ) ) {
				$this->update_settings();
			}
		}
	}


	/**
	 * @access public
	 *
	 * @param $section
	 */
	public function settings_section( $section ) {
		echo "\n";
	}

	/**
	 * Load settings page content
	 *
	 * @return void
	 * @since   v1.0.0
	 */
	public function settings_page() {

		echo '<div class="wrap" id="' . esc_attr( $this->parent->_token . '_options' ) . '">' . "\n";
		echo '<h2>' . esc_html__( 'Toasty Purge Settings', 'toasty-purge' ) . '</h2>' . "\n";

		echo '<p>';
		echo esc_html__( 'On this settings page you can adjust things here and there to your liking.', 'toasty-purge' );
		echo '<br>';
		echo esc_html__( 'Although some settings are for "features" that can easily be dismissed on a per user basis, hiding or removing them here, has two advantages:', 'toasty-purge' );
		echo '</p><ol><li>';
		echo esc_html__( 'the settings here are global, for all users', 'toasty-purge' );
		echo '</li><li>';
		echo esc_html__( "these settings are centralised on one page, no need to keep dismissing stuff all over the site's backend", 'toasty-purge' );
		echo '</li></ol>' . "\n";

		echo '<p>' . esc_html__( 'The default settings, when you activate the plugin, are that almost all boxes have been ticked; why else would you install this plugin?', 'toasty-purge' ) . '</p>' . "\n";

		echo '<p>';
		echo esc_html__( 'If you ever want to remove the Toasty Purge plugin, then you can rest assured that it cleans up after itself:', 'toasty-purge' );
		echo '<br />';
		echo esc_html__( 'upon deletion it removes all options automatically.', 'toasty-purge' );
		echo '</p>' . "\n";

		echo '<p><strong>' . esc_html__( 'Without further ado: Hide the bloat', 'toasty-purge' ) . '</strong></p><hr>' . "\n";

		$action = is_network_admin()
			? 'edit.php?action=' . esc_attr( $this->parent->_token . '_options' )
			: 'options.php';

		echo '<form method="post" action="' . esc_url( $action ) . '" enctype="multipart/form-data">' . "\n";

		settings_fields( $this->parent->_token . '_options' );
		do_settings_sections( $this->parent->_token . '_options' );

		echo '<p class="submit">' . "\n";
		submit_button( __( 'Save Settings', 'toasty-purge' ), 'primary', 'Submit', false );
		echo '</p>' . "\n";
		echo '</form>' . "\n";
		echo '</div>' . "\n";
	}

	/**
	 * Main TOASTYPRG_Settings Instance
	 *
	 * Ensures only one instance of TOASTYPRG_Settings is loaded or can be loaded.
	 *
	 * @since v1.0.0
	 * @static
	 * @see   toastyprg()
	 *
	 * @param TOASTYPRG $parent Instance of main class.
	 *
	 * @return TOASTYPRG_Settings $_instance
	 */
	public static function instance( $parent ) {
		if ( null === self::$_instance ) {
			self::$_instance = new self( $parent );
		}

		return self::$_instance;
	} // End instance()

	/**
	 * Cloning is forbidden.
	 *
	 * @since v1.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Access denied', 'toasty-purge' ), esc_html( $this->parent->_version ) );
	} // End __clone()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since v1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Access denied', 'toasty-purge' ), esc_html( $this->parent->_version ) );
	} // End __wakeup()

}
