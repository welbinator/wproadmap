<?php

/**
 * Check for the presence of specific shortcodes on the page and set options for enqueuing CSS files.
 */

/**
 * Checks if the 'new_idea_form' shortcode is present on the current page.
 * Sets an option for enqueuing related CSS files if the shortcode is found.
 */
function wp_roadmap_check_for_new_idea_shortcode(): void {
	global $post;

	if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'new_idea_form' ) ) {
		update_option( 'wp_roadmap_new_idea_shortcode_loaded', true );
	}
}
add_action( 'wp', 'wp_roadmap_check_for_new_idea_shortcode' );

/**
 * Checks if the 'display_ideas' shortcode is present on the current page.
 * Sets an option for enqueuing related CSS files if the shortcode is found.
 */
function wp_roadmap_check_for_ideas_shortcode(): void {
	global $post;

	if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'display_ideas' ) ) {
		update_option( 'wp_roadmap_ideas_shortcode_loaded', true );
	}
}
add_action( 'wp', 'wp_roadmap_check_for_ideas_shortcode' );

/**
 * Checks if the 'roadmap' shortcode is present on the current page.
 * Sets an option for enqueuing related CSS files if the shortcode is found.
 */
function wp_roadmap_check_for_roadmap_shortcode(): void {
	global $post;

	if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'roadmap' ) ) {
		update_option( 'wp_roadmap_roadmap_shortcode_loaded', true );
	}
}
add_action( 'wp', 'wp_roadmap_check_for_roadmap_shortcode' );

/**
 * Checks if the 'roadmap' shortcode is present on the current page.
 * Sets an option for enqueuing related CSS files if the shortcode is found.
 */
function wp_roadmap_check_for_single_idea_shortcode(): void {
	global $post;

	if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'roadmap' ) ) {
		update_option( 'wp_roadmap_single_idea_shortcode_loaded', true );
	}
}
add_action( 'wp', 'wp_roadmap_check_for_single_idea_shortcode' );

/**
 * Enqueues admin styles for specific admin pages and post types.
 *
 * @param string $hook The current admin page hook.
 */
function wp_roadmap_enqueue_admin_styles( $hook ): void {
	global $post;

	// Enqueue CSS for 'idea' post type editor
	if ( 'post.php' == $hook && isset( $post ) && 'idea' == $post->post_type ) {
		$css_url = plugin_dir_url( __FILE__ ) . 'assets/css/idea-editor-styles.css';
		wp_enqueue_style( 'wp-roadmap-idea-admin-styles', $css_url );
	}

	// Enqueue CSS for taxonomies admin page
	if ( $hook === 'roadmap_page_wp-roadmap-taxonomies' ) {
		$css_url = plugin_dir_url( __FILE__ ) . 'assets/css/admin-styles.css';
		wp_enqueue_style( 'wp-roadmap-general-admin-styles', $css_url );
	}

	// Enqueue CSS for help page
	if ( $hook === 'roadmap_page_wp-roadmap-help' ) {
		$tailwind_css_url = plugin_dir_url( __FILE__ ) . '../dist/styles.css';
		wp_enqueue_style( 'wp-roadmap-tailwind-styles', $tailwind_css_url );
	}

	// Enqueue JS for the 'Taxonomies' admin page
	if ( 'roadmap_page_wp-roadmap-taxonomies' == $hook ) {
		wp_enqueue_script( 'wp-roadmap-taxonomies-js', plugin_dir_url( __FILE__ ) . 'assets/js/taxonomies.js', array( 'jquery' ), null, true );
		wp_localize_script(
			'wp-roadmap-taxonomies-js',
			'wpRoadmapAjax',
			array(
				'ajax_url'              => admin_url( 'admin-ajax.php' ),
				'delete_taxonomy_nonce' => wp_create_nonce( 'wp_roadmap_delete_taxonomy_nonce' ),
				'delete_terms_nonce'    => wp_create_nonce( 'wp_roadmap_delete_terms_nonce' ),
			)
		);
	}
}
add_action( 'admin_enqueue_scripts', 'wp_roadmap_enqueue_admin_styles' );

/**
 * Enqueues front end styles and scripts for the plugin.
 *
 * This function checks whether any of the plugin's shortcodes are loaded or if it's a singular 'idea' post,
 * and enqueues the necessary styles and scripts.
 */
function wp_roadmap_enqueue_frontend_styles(): void {
	global $post;

	// Initialize flags
	$has_new_idea_form_shortcode = false;
	$has_display_ideas_shortcode = false;
	$has_roadmap_shortcode       = false;
	$has_single_idea_shortcode   = false;
	$has_block                   = false;

	// Check for shortcode presence in the post content
	if ( is_a( $post, 'WP_Post' ) ) {
		$has_new_idea_form_shortcode = has_shortcode( $post->post_content, 'new_idea_form' );
		$has_display_ideas_shortcode = has_shortcode( $post->post_content, 'display_ideas' );
		$has_roadmap_shortcode       = has_shortcode( $post->post_content, 'roadmap' );
		$has_single_idea_shortcode   = has_shortcode( $post->post_content, 'single_idea' );

		// Check for block presence
		$has_block = has_block( 'wp-roadmap-pro/new-idea-form', $post ) ||
					has_block( 'wp-roadmap-pro/display-ideas', $post ) ||
					has_block( 'wp-roadmap-pro/roadmap', $post );
	}

	// Enqueue styles if a shortcode or block is loaded
	if ( $has_new_idea_form_shortcode || $has_display_ideas_shortcode || $has_roadmap_shortcode || $has_single_idea_shortcode || $has_block || is_singular( 'idea' ) ) {

		// Enqueue Tailwind CSS
		$tailwind_css_url = plugin_dir_url( __FILE__ ) . '../dist/styles.css';
		wp_enqueue_style( 'wp-roadmap-tailwind-styles', $tailwind_css_url );

		// Enqueue your custom frontend styles
		$custom_css_url = plugin_dir_url( __FILE__ ) . 'assets/css/wp-roadmap-frontend.css';
		wp_enqueue_style( 'wp-roadmap-frontend-styles', $custom_css_url );

		// Enqueue scripts and localize them as before
		wp_enqueue_script( 'wp-roadmap-voting', plugin_dir_url( __FILE__ ) . 'assets/js/voting.js', array( 'jquery' ), null, true );
		wp_localize_script(
			'wp-roadmap-voting',
			'wpRoadMapVoting',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'wp-roadmap-vote-nonce' ),
			)
		);

		wp_enqueue_script( 'wp-roadmap-idea-filter', plugin_dir_url( __FILE__ ) . 'assets/js/idea-filter.js', array( 'jquery' ), '', true );
		wp_localize_script(
			'wp-roadmap-idea-filter',
			'wpRoadMapAjax',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'wp-roadmap-vote-nonce' ),
			)
		);
	}
}

add_action( 'wp_enqueue_scripts', 'wp_roadmap_enqueue_frontend_styles' );

function wp_roadmap_redirect_to_post_type(): string {
	$post_type_url = admin_url( 'edit.php?post_type=idea' );
	wp_redirect( $post_type_url );
	exit;
}

/**
 * Adds admin menu pages for the plugin.
 *
 * This function creates a top-level menu item 'RoadMap' in the admin dashboard,
 * along with several submenu pages like Settings, Ideas, and Taxonomies.
 */
function wp_roadmap_add_admin_menu(): void {

	add_menu_page(
		__( 'RoadMap', 'roadmapwp-free' ),
		__( 'RoadMap', 'roadmapwp-free' ),
		'manage_options',
		'roadmapwp-free',
		'wp_roadmap_redirect_to_post_type',
		'dashicons-chart-line',
		6
	);

	add_submenu_page(
		'roadmapwp-free',
		__( 'Ideas', 'roadmapwp-free' ),
		__( 'Ideas', 'roadmapwp-free' ),
		'manage_options',
		'edit.php?post_type=idea'
	);

	add_submenu_page(
		'roadmapwp-free',
		__( 'Settings', 'roadmapwp-free' ),
		__( 'Settings', 'roadmapwp-free' ),
		'manage_options',
		'wp-roadmap-settings',
		// @phpstan-ignore-next-line
		'wp_roadmap_settings_page'
	);

	add_submenu_page(
		'roadmapwp-free',
		__( 'Taxonomies', 'roadmapwp-free' ),
		__( 'Taxonomies', 'roadmapwp-free' ),
		'manage_options',
		'wp-roadmap-taxonomies',
		// @phpstan-ignore-next-line
		'wp_roadmap_taxonomies_page'
	);

	add_submenu_page(
		'roadmapwp-free',
		__( 'Help', 'roadmapwp-free' ),
		__( 'Help', 'roadmapwp-free' ),
		'manage_options',
		'wp-roadmap-help',
		// @phpstan-ignore-next-line
		'wp_roadmap_free_help_page'
	);

	remove_submenu_page( 'roadmapwp-free', 'roadmapwp-free' );
}
add_action( 'admin_menu', 'wp_roadmap_add_admin_menu' );

/**
 * Adds the plugin license page to the admin menu.
 *
 * @return void
 */





/**
 * Registers settings for the RoadMap plugin.
 *
 * This function sets up a settings section for the plugin, allowing configuration of various features and functionalities.
 */
function wp_roadmap_register_settings(): void {
	register_setting( 'wp_roadmap_settings', 'wp_roadmap_settings' );
}
add_action( 'admin_init', 'wp_roadmap_register_settings' );

/**
 * Dynamically enables or disables comments on 'idea' post types.
 *
 * @param bool $open Whether the comments are open.
 * @param int  $post_id The post ID.
 * @return bool Modified status of comments open.
 */
function wp_roadmap_filter_comments_open( $open, $post_id ) {
	global $post;

	if ( $post instanceof WP_Post && $post->post_type === 'idea' ) {
		$options = get_option( 'wp_roadmap_settings' );
		if ( is_array( $options ) && isset( $options['allow_comments'] ) && $options['allow_comments'] == 1 ) {
			return true;
		}
	}

	return $open;
}
add_filter( 'comments_open', 'wp_roadmap_filter_comments_open', 10, 2 );

function wp_roadmap_redirect_single_idea( string $template ): string {
	global $post;

	if ( 'idea' === $post->post_type ) {
		$options = get_option( 'wp_roadmap_settings' );
		if ( is_array( $options ) ) {
			$single_idea_page_id = isset( $options['single_idea_page'] ) ? $options['single_idea_page'] : '';
			$chosen_template     = isset( $options['single_idea_template'] ) ? $options['single_idea_template'] : 'plugin';

			// If you have further logic that modifies the $template based on these options,
			// that logic would go here.
		}
	}

	return $template;
}


add_filter( 'single_template', 'wp_roadmap_redirect_single_idea' );
