<?php
/**
 * Define constant variables
 */
define( 'ORGNK_EVENTS_CATEGORIES_TAX_NAME', 'event-category' );

/**
 * Main Organik_Events_Categories class
 */
class Organik_Events_Categories {

	/**
     * Constructor function
     */
	public function __construct() {

		// Register taxonomies
		add_action( 'init', array( $this, 'orgnk_event_categories_register_taxonomy') );

	}

	/**
	 * Register taxonomy
	 */
	public function orgnk_event_categories_register_taxonomy() {

		$labels = array(
			'name'                       	=> 'Event Categories',
			'singular_name'              	=> 'Event Category',
			'menu_name'                  	=> 'Event categories',
			'all_items'                  	=> 'All Event categories',
			'parent_item'                	=> 'Parent Event category',
			'parent_item_colon'          	=> 'Parent Event category:',
			'new_item_name'              	=> 'New Event Category Name',
			'add_new_item'               	=> 'Add New Category',
			'edit_item'                  	=> 'Edit Category',
			'update_item'                	=> 'Update Category',
			'view_item'                  	=> 'View Category',
			'separate_items_with_commas' 	=> 'Separate categories with commas',
			'add_or_remove_items'        	=> 'Add or remove categories',
			'choose_from_most_used'      	=> 'Choose from the most used',
			'popular_items'              	=> 'Popular categories',
			'search_items'               	=> 'Search categories',
			'not_found'                  	=> 'Not Found',
			'no_terms'                   	=> 'No categories',
			'items_list'                 	=> 'Categories list',
			'items_list_navigation'      	=> 'Categories list navigation'
		);

		$rewrite = array(
			'slug'                  		=> ORGNK_EVENTS_REWRITE_SLUG . '/category',
			'with_front'            		=> false,
			'hierarchical'					=> true
		);

		$args = array(
			'labels'                     	=> $labels,
			'hierarchical'               	=> true,
			'public'                     	=> true,
			'show_ui'                    	=> true,
			'show_admin_column'          	=> true,
			'show_in_nav_menus'          	=> true,
			'show_tagcloud'              	=> true,
			'rewrite'						=> $rewrite
		);
		register_taxonomy( ORGNK_EVENTS_CATEGORIES_TAX_NAME, array( ORGNK_EVENTS_CPT_NAME ), $args );
	}
}
