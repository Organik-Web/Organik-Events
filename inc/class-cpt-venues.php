<?php
/**
 * Define constant variables
 */
define( 'ORGNK_VENUES_CPT_NAME', 'venue' );
define( 'ORGNK_VENUES_SINGLE_NAME', 'Venue' );
define( 'ORGNK_VENUES_PLURAL_NAME', 'Venues' );

/**
 * Main Organik_Events_Venues class
 */
class Organik_Events_Venues {

	/**
     * Constructor function
     */
	public function __construct() {

		// Define the CPT rewrite variable on init - required here because we need to use get_permalink() which isn't available when plugins are initialised
		add_action( 'init', array( $this, 'orgnk_venues_cpt_archive_rewrite_slug' ) );

		// Hook into the 'init' action to add the Custom Post Type
		add_action( 'init', array( $this, 'orgnk_venues_cpt_register' ) );

		// Change the title placeholder
		add_filter( 'enter_title_here', array( $this, 'orgnk_venues_cpt_title_placeholder' ) );
	}

	/**
	 * orgnk_venues_cpt_register()
	 * Register the Venues custom post type inside of Events
	 */
	public function orgnk_venues_cpt_register() {

		$labels = array(
			'name'                      	=> ORGNK_VENUES_PLURAL_NAME,
			'singular_name'             	=> ORGNK_VENUES_SINGLE_NAME,
			'menu_name'                 	=> ORGNK_VENUES_PLURAL_NAME,
			'name_admin_bar'            	=> ORGNK_VENUES_SINGLE_NAME,
			'archives'              		=> 'Venue archives',
			'attributes'            		=> 'Venue Attributes',
			'parent_item_colon'     		=> 'Parent venue:',
			'all_items'             		=> 'Venues',
			'add_new_item'          		=> 'Add new venue',
			'add_new'               		=> 'Add new venue',
			'new_item'              		=> 'New venue',
			'edit_item'             		=> 'Edit venue',
			'update_item'           		=> 'Update venue',
			'view_item'             		=> 'View venue',
			'view_items'            		=> 'View venues',
			'search_items'          		=> 'Search venue',
			'not_found'             		=> 'Not found',
			'not_found_in_trash'    		=> 'Not found in Trash',
			'featured_image'        		=> 'Venue Image',
			'set_featured_image'    		=> 'Set venue image',
			'remove_featured_image' 		=> 'Remove venue image',
			'use_featured_image'    		=> 'Use as venue image',
			'insert_into_item'      		=> 'Insert into venue',
			'uploaded_to_this_item' 		=> 'Uploaded to this venue',
			'items_list'            		=> 'Venues list',
			'items_list_navigation' 		=> 'Venues list navigation',
			'filter_items_list'     		=> 'Filter venues list'
		);

		$rewrite = array(
			'slug'                  		=> ORGNK_VENUES_REWRITE_SLUG, // The slug for single posts
			'with_front'            		=> false,
			'pages'                 		=> true,
			'feeds'                 		=> false
		);

		$args = array(
			'label'                 		=> ORGNK_VENUES_SINGLE_NAME,
			'description'           		=> 'Manage and display venues',
			'labels'                		=> $labels,
			'supports'              		=> array( 'title' ),
			'taxonomies'            		=> array(),
			'hierarchical'          		=> false,
			'public'                		=> true,
			'show_ui'               		=> true,
			'show_in_menu'          		=> 'edit.php?post_type=' . ORGNK_EVENTS_CPT_NAME, // Move this CPT inside the Events CPT
			'menu_position'         		=> 25,
			'menu_icon'             		=> 'dashicons-bank',
			'show_in_admin_bar'     		=> true,
			'show_in_nav_menus'     		=> true,
			'can_export'            		=> true,
			'has_archive'           		=> false, // The slug for archive, bool toggle archive on/off
			'publicly_queryable'    		=> false, // Bool toggle single on/off
			'exclude_from_search'   		=> true,
			'capability_type'       		=> 'page',
			'rewrite'						=> $rewrite
		);
		register_post_type( ORGNK_VENUES_CPT_NAME, $args );
	}

	/**
	 * orgnk_venues_cpt_archive_rewrite_slug()
	 * Conditionally define the CPT archive permalink based on the pages for CPT functionality in Organik themes
	 * Includes a fallback string to use as the slug if the option isn't set
	 */
	public function orgnk_venues_cpt_archive_rewrite_slug() {
		$default_slug = 'venues';
		$archive_page_id = get_option( 'page_for_' . ORGNK_VENUES_CPT_NAME );
		$archive_page_slug = str_replace( home_url(), '', get_permalink( $archive_page_id ) );
		$archive_permalink = ( $archive_page_id ? $archive_page_slug : $default_slug );
		$archive_permalink = ltrim( $archive_permalink, '/' );
		$archive_permalink = rtrim( $archive_permalink, '/' );

		define( 'ORGNK_VENUES_REWRITE_SLUG', $archive_permalink );
	}

	/** 
	 * orgnk_venues_cpt_title_placeholder()
	 * Change CPT title placeholder on edit screen
	 */
	function orgnk_venues_cpt_title_placeholder( $title ) {

		$screen = get_current_screen();

		if ( $screen->post_type == ORGNK_VENUES_CPT_NAME ) {
			return 'Add venue name';
		}
		return $title;
	}
}
