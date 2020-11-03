<?php
/**
 * Define constant variables
 */
define( 'ORGNK_EVENTS_CPT_NAME', 'event' );
define( 'ORGNK_EVENTS_SINGLE_NAME', 'Event' );
define( 'ORGNK_EVENTS_PLURAL_NAME', 'Events' );

/**
 * Define permalinks
 */
$archive_page_id = get_option( 'page_for_' . ORGNK_EVENTS_CPT_NAME );
$archive_page_slug = str_replace( home_url(), '', get_permalink( $archive_page_id ) );
$archive_permalink = ( $archive_page_id ? $archive_page_slug : 'events' );
$archive_permalink = ltrim( $archive_permalink, '/' );
$archive_permalink = rtrim( $archive_permalink, '/' );
define( 'ORGNK_EVENTS_REWRITE_SLUG', $archive_permalink );

/**
 * Main Organik_Events plugin class
 */
class Organik_Events {

	/**
     * The single instance of Organik_Events
     */
	private static $instance = null;

	/**
     * Main class instance
     * Ensures only one instance of this class is loaded or can be loaded
     */
    public static function instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self;
        }
        return self::$instance;
	}
	
	/**
     * Constructor function
     */
	public function __construct() {

        // Hook into the 'init' action to add the Custom Post Type
		add_action( 'init', array( $this, 'orgnk_events_register_cpt' ), 0 );

        // Change the title placeholder
		add_filter( 'enter_title_here', array( $this, 'orgnk_events_cpt_title_placeholder' ) );

		// Modify the post type archive query
		add_action( 'pre_get_posts', array( $this, 'orgnk_events_cpt_modify_archive_query' ) );

		// Add the first event date to the admin list view and make it sortable
		add_filter( 'manage_' . ORGNK_EVENTS_CPT_NAME . '_posts_columns', array( $this, 'orgnk_events_cpt_admin_table_column' ) );
		add_action( 'manage_' . ORGNK_EVENTS_CPT_NAME . '_posts_custom_column', array( $this, 'orgnk_events_cpt_admin_table_content' ), 10, 2 );
		add_filter( 'manage_edit-' . ORGNK_EVENTS_CPT_NAME . '_sortable_columns', array( $this, 'orgnk_events_cpt_admin_table_sortable' ) );

		// Add schema for this post type to the head
		add_action( 'wp_head', array( $this, 'orgnk_events_event_schema_head' ) );

		// Add a notice to the admin list view about how events are ordered in the front-end
		add_action( 'views_edit-' . ORGNK_EVENTS_CPT_NAME, array( $this, 'orgnk_events_cpt_admin_table_notice' ) );

		// Register Venus CPT after this one has been setup
		new Organik_Events_Venues();
	}
	
	/**
	 * orgnk_events_register_cpt()
	 * Register the Events custom post type
	 */
	public function orgnk_events_register_cpt() {

		$labels = array(
			'name'                      	=> ORGNK_EVENTS_PLURAL_NAME,
			'singular_name'             	=> ORGNK_EVENTS_SINGLE_NAME,
			'menu_name'                 	=> ORGNK_EVENTS_PLURAL_NAME,
			'name_admin_bar'            	=> ORGNK_EVENTS_SINGLE_NAME,
			'archives'              		=> 'Event archives',
			'attributes'            		=> 'Event Attributes',
			'parent_item_colon'     		=> 'Parent event:',
			'all_items'             		=> 'All events',
			'add_new_item'          		=> 'Add new event',
			'add_new'               		=> 'Add new event',
			'new_item'              		=> 'New event',
			'edit_item'             		=> 'Edit event',
			'update_item'           		=> 'Update event',
			'view_item'             		=> 'View event',
			'view_items'            		=> 'View events',
			'search_items'          		=> 'Search event',
			'not_found'             		=> 'Not found',
			'not_found_in_trash'    		=> 'Not found in Trash',
			'featured_image'        		=> 'Event Image',
			'set_featured_image'    		=> 'Set event image',
			'remove_featured_image' 		=> 'Remove event image',
			'use_featured_image'    		=> 'Use as event image',
			'insert_into_item'      		=> 'Insert into event',
			'uploaded_to_this_item' 		=> 'Uploaded to this event',
			'items_list'            		=> 'Events list',
			'items_list_navigation' 		=> 'Events list navigation',
			'filter_items_list'     		=> 'Filter events list'
		);

		$rewrite = array(
			'slug'                  		=> ORGNK_EVENTS_REWRITE_SLUG, // The slug for single posts
			'with_front'            		=> false,
			'pages'                 		=> true,
			'feeds'                 		=> false
		);

		$args = array(
			'label'                 		=> ORGNK_EVENTS_SINGLE_NAME,
			'description'           		=> 'Manage and display events',
			'labels'                		=> $labels,
			'supports'              		=> array( 'title', 'editor', 'thumbnail', 'revisions' ),
			'taxonomies'            		=> array(),
			'hierarchical'          		=> false,
			'public'                		=> true,
			'show_ui'               		=> true,
			'show_in_menu'          		=> true,
			'menu_position'         		=> 25,
			'menu_icon'             		=> 'dashicons-tickets-alt',
			'show_in_admin_bar'     		=> true,
			'show_in_nav_menus'     		=> true,
			'can_export'            		=> true,
			'has_archive'           		=> true, // The slug for archive, bool toggle archive on/off
			'publicly_queryable'    		=> true, // Bool toggle single on/off
			'exclude_from_search'   		=> false,
			'capability_type'       		=> 'page',
			'rewrite'						=> $rewrite
		);
		register_post_type( ORGNK_EVENTS_CPT_NAME, $args );
	}

	/** 
	 * orgnk_events_cpt_title_placeholder()
	 * Change CPT title placeholder on edit screen
	 */
	public function orgnk_events_cpt_title_placeholder( $title, $post ) {

		if ( $post->post_type == ORGNK_EVENTS_CPT_NAME ) {
			return 'Add event title';
		}
		return $title;
	}

	/**
	 * orgnk_events_cpt_modify_archive_query()
	 * Change the events archive order to order by the event start date meta
	 */
	public function orgnk_events_cpt_modify_archive_query( $query ) {

		if ( $query->is_post_type_archive( ORGNK_EVENTS_CPT_NAME ) && !is_admin() && $query->is_main_query() ) {

			$query->set( 'meta_key', 'event_dates_0_start' );
			$query->set( 'orderby', 'meta_value' );
			$query->set( 'order', 'ASC' );
			$query->set( 'meta_query', array(
				'relation'			=> 'OR',
				array( // If start date or end date is greater than or equal to today
					'relation'      	=> 'OR',
					array(
						'key'       	=> 'event_dates_0_start',
						'value'     	=> time(),
						'compare'   	=> '>='
					),
					array(
						'key'       	=> 'event_dates_0_end',
						'value'     	=> time(),
						'compare'   	=> '>='
					),
				),
				array( // If start date is greater than or equal to today, and end date is not set
					array(
						'key'       	=> 'event_dates_0_start',
						'value'     	=> time(),
						'compare'   	=> '>='
					),
					array(
						'key'       	=> 'event_dates_0_end',
						'compare'   	=> 'NOT EXISTS'
					),
				),
			) );
		}

		return $query;
	}

	/**
	 * orgnk_events_cpt_admin_table_column()
	 * Register new column(s) in admin list view
	 */
	public function orgnk_events_cpt_admin_table_column( $defaults ) {
		
		$new_order = array();

		foreach ( $defaults as $key => $value ) {
			// When we find the date column, slip in the new column before it
			if ( $key == 'date' ) {
				$new_order['event_first_date'] = 'First Date';
				$new_order['event_status'] = 'Status';
			}
			$new_order[$key] = $value;
		}

		return $new_order;
	}

	/**
	 * orgnk_events_cpt_admin_table_content()
	 * Return the content for the new admin list view columns for each post
	 */
	public function orgnk_events_cpt_admin_table_content( $column_name, $post_id ) {
			
		if ( $column_name == 'event_first_date' ) {
			$first_date = strtotime( esc_html( get_post_meta( $post_id, 'event_dates_0_start', true ) ) );
			$first_date = date( 'g:i a, d F Y', $first_date );
			echo $first_date;
		}

		if ( $column_name == 'event_status' ) {
			$status = ucfirst( esc_html( get_post_meta( $post_id, 'event_status', true ) ) );
			echo $status;
		}
	}

	/**
	 * orgnk_events_cpt_admin_table_sortable()
	 * Make the new admin list view columns sortable
	 */
	public function orgnk_events_cpt_admin_table_sortable( $columns ) {
		$columns['event_first_date'] = 'event_first_date';
		return $columns;
	}

	/**
	 * orgnk_events_event_schema_head()
	 * Add Event schema to the document <head> if viewing a single event post
	 */
	public function orgnk_events_event_schema_head() {

		$schema_script = NULL;

		// Prevent the schema function from running on every page
		if ( is_singular( ORGNK_EVENTS_CPT_NAME ) ) {
			$schema_script = orgnk_single_event_schema();
		}

		echo $schema_script;
	}

	/**
	 * orgnk_events_cpt_admin_table_notice()
	 * Add a notice to the admin list view about how events are ordered in the front-end
	 */
	public function orgnk_events_cpt_admin_table_notice( $post ) {

		$output = '';
		$current_screen = get_current_screen();

		if ( is_admin() && $current_screen->post_type === ORGNK_EVENTS_CPT_NAME ) {
			$output .= '<div class="notice notice-info inline" style="margin: 15px 0;">';
			$output .= '<p><strong style="display: block; font-size: 16px; margin: 0 0 5px 0;">Automatic ordering of events</strong>Events are automatically ordered on the front-end by the \'Event First Date\' shown in the \'First Date\' column below. Additionally, events will only display on the front-end if the \'Event First Date\' is in the future. If an event is not displaying as expected, check you have set and ordered each event\'s dates correctly, with the soonest date first.</p>';
			$output .= '</div>';
		}

		echo $output;
	}
}
