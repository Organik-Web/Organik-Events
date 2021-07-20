<?php
/**
 * Define constant variables
 */
define( 'ORGNK_EVENTS_CPT_NAME', 'event' );
define( 'ORGNK_EVENTS_SINGLE_NAME', 'Event' );
define( 'ORGNK_EVENTS_PLURAL_NAME', 'Events' );

/**
 * Main Organik_Events class
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

		// Define the CPT rewrite variable on init - required here because we need to use get_permalink() which isn't available when plugins are initialised
		add_action( 'init', array( $this, 'orgnk_events_cpt_rewrite_slug' ) );

        // Hook into the 'init' action to add the Custom Post Type
		add_action( 'init', array( $this, 'orgnk_events_cpt_register' ) );

        // Change the title placeholder
		add_filter( 'enter_title_here', array( $this, 'orgnk_events_cpt_title_placeholder' ) );

		// Add post meta to the admin list view for this CPT
		add_filter( 'manage_' . ORGNK_EVENTS_CPT_NAME . '_posts_columns', array( $this, 'orgnk_events_cpt_admin_table_column' ) );
		add_action( 'manage_' . ORGNK_EVENTS_CPT_NAME . '_posts_custom_column', array( $this, 'orgnk_events_cpt_admin_table_content' ), 10, 2 );
		add_filter( 'manage_edit-' . ORGNK_EVENTS_CPT_NAME . '_sortable_columns', array( $this, 'orgnk_events_cpt_admin_table_sortable' ) );
		add_action( 'pre_get_posts', array( $this, 'orgnk_events_cpt_admin_table_sortby' ) );

		// Add a notice to the admin list view for this CPT
		add_action( 'admin_notices', array( $this, 'orgnk_events_cpt_admin_table_notice' ) );

		// Register a custom CRON event to call functions that updates recurring events dates & automatically set expired events to draft
		add_action( 'init', array( $this, 'orgnk_events_register_cron' ) );
		add_action( 'orgnk_events_cron_schedule', array( $this, 'orgnk_events_cron_tasks' ) );

		// Modify the post type archive query
		add_action( 'pre_get_posts', array( $this, 'orgnk_events_cpt_archive_query' ) );

		// Modify the Organik theme sitemap get posts arguments
		add_action( 'orgnk_sitemap_get_posts_arguments', array( $this, 'orgnk_events_sitemap_get_posts_arguments' ) );

		// Add schema for this post type to the document head
		add_action( 'wp_head', array( $this, 'orgnk_events_cpt_schema_head' ) );

		add_action( 'save_post', array($this, 'orgnk_events_set_next_occurrence' ), 10, 3);

		// Register Venues CPT after this one has been setup
		new Organik_Events_Venues();

		// Register ACF Fields
		new Organik_Events_ACF_Fields();
	}

	/**
	 * orgnk_events_cpt_register()
	 * Register the custom post type
	 */
	public function orgnk_events_cpt_register() {

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
	 * orgnk_events_cpt_rewrite_slug()
	 * Conditionally define the CPT archive permalink based on the pages for CPT functionality in Organik themes
	 * Includes a fallback string to use as the slug if the option isn't set
	 */
	public function orgnk_events_cpt_rewrite_slug() {
		$default_slug = 'events';
		$archive_page_id = get_option( 'page_for_' . ORGNK_EVENTS_CPT_NAME );
		$archive_page_slug = str_replace( home_url(), '', get_permalink( $archive_page_id ) );
		$archive_permalink = ( $archive_page_id ? $archive_page_slug : $default_slug );
		$archive_permalink = ltrim( $archive_permalink, '/' );
		$archive_permalink = rtrim( $archive_permalink, '/' );

		define( 'ORGNK_EVENTS_REWRITE_SLUG', $archive_permalink );
	}

	/**
	 * orgnk_events_cpt_title_placeholder()
	 * Change CPT title placeholder on edit screen
	 */
	public function orgnk_events_cpt_title_placeholder( $title ) {

		$screen = get_current_screen();

		if ( $screen && $screen->post_type == ORGNK_EVENTS_CPT_NAME ) {
			return 'Add event title';
		}
		return $title;
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
				$new_order['event_first_date'] = 'Next Date';
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
			$first_date = esc_html( get_post_meta( $post_id, 'next_event_start_date', true ) ) ;
			$first_date = date("F j, Y, g:i a", $first_date );
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
	 * orgnk_events_cpt_admin_table_sortby()
	 * Modify the query arguments when sorting the event first date column to order by the event first date meta value
	 */
	public function orgnk_events_cpt_admin_table_sortby( $query ) {

		$orderby = $query->get( 'orderby' );

		if ( isset( $orderby ) && 'event_first_date' == $orderby ) {
			$query->set( 'meta_key', 'next_event_start_date' );
			$query->set( 'orderby', 'meta_value' );
		}
	}

	/**
	 * orgnk_events_cpt_admin_table_notice()
	 * Add a notice to the admin list view about how events are ordered in the front-end
	 */
	public function orgnk_events_cpt_admin_table_notice() {

		$output = '';
		$current_screen = get_current_screen();

		if ( is_admin() && $current_screen && $current_screen->post_type === ORGNK_EVENTS_CPT_NAME && $current_screen->base === 'edit' ) {
			$output .= '<div class="notice notice-info" style="margin: 15px 0;">';
			$output .= '<p><strong style="display: block; font-size: 16px; margin: 0 0 10px 0;">Event ordering</strong>Events are automatically ordered on the front-end by the \'Event First Date\' shown in the \'First Date\' column below. If an event is not displaying as expected, check you have set and ordered each event\'s dates correctly, with the soonest date first.</p>';
			$output .= '<hr style="margin: 10px 0;">';
			$output .= '<p><strong style="display: block; font-size: 16px; margin: 0 0 10px 0;">Event expiry</strong>When an event\'s last date is in the past, the event\'s status will automatically be set to \'draft\' to hide it on the front-end.</p>';
			$output .= '</div>';
		}

		echo $output;
	}

	/**
	 * orgnk_events_remove_expired_task()
	 * Handle drafting of events periodically
	 */
	public function orgnk_events_remove_expired_task() {

		$events = get_posts( array(
			'post_type'      	=> ORGNK_EVENTS_CPT_NAME,
			'post_status'    	=> 'publish',
			'fields' 			=> 'ids',
			'posts_per_page' 	=> -1
		) );

		foreach ( $events as $event ) {
			$date_type		= esc_html( get_post_meta( $event, 'date_type', true ) );
			if( $date_type === 'scheduled' ) {

				$expiration_date = NULL;
				$date_count = esc_html( get_post_meta( $event, 'event_dates', true ) );
				$i = $date_count -1; // Subtract one from count to get correct interger
				$last_date_start = esc_html( get_post_meta( $event, 'event_dates_' . $i . '_start', true ) );
				$last_date_end = esc_html( get_post_meta( $event, 'event_dates_' . $i . '_end', true ) );

				if ( $last_date_end ) {
					$expiration_date = $last_date_end;
				} else {
					$expiration_date = $last_date_start;
				}

				// Bail if no expire date set
				if ( ! $expiration_date ) {
					return;
				}

				$expiration_date = strtotime( $expiration_date );
				$now = time();

				if ( $expiration_date <= $now ) {
					wp_update_post( array(
						'ID'          	=> $event,
						'post_status' 	=> 'draft'
					) );
				}
			}
			if ( $date_type == 'recurring' ) {
				$this->orgnk_events_set_next_occurrence($event);
			}
		}
	}

	/**
	 * orgnk_events_cpt_archive_query()
	 * Change the events archive order to order by the event start date meta
	 */
	public function orgnk_events_cpt_archive_query( $query ) {

		if ( $query->is_post_type_archive( ORGNK_EVENTS_CPT_NAME ) && ! is_admin() && $query->is_main_query() ) {

			$query->set( 'orderby', 'meta_value' );
			$query->set( 'order', 'ASC' );
			$meta_query = array([
						'relation'    => 'OR',
						'next_event_start_date'    	=> array(
						'key'     					=> 'next_event_start_date',
						),
						'event_featured' 			=> array(
						'key'       				=> 'event_featured',
						),
					],
					'orderby' => [
						'event_featured' => 'DESC',
						'next_event_start_date' => 'ASC',
					]);
					$query->set( 'meta_query', $meta_query );

				return $query;
			}
		}

	/**
	 * orgnk_events_sitemap_get_posts_arguments()
	 * Change the events archive order to order by the event start date meta
	 */
	public function orgnk_events_sitemap_get_posts_arguments( $args ) {

		if ( $args['post_type'] === 'event' ) {
			$args['meta_key'] = 'event_featured';
			$args['orderby'] = 'meta_value';
			$args['order'] = 'DESC';
		}

		return $args;
	}

	/**
	 * orgnk_events_cpt_schema_head()
	 * Add event schema to the document head if viewing a single event post
	 */
	public function orgnk_events_cpt_schema_head() {

		$schema_script = NULL;

		// Prevent the schema function from running on every page
		if ( is_singular( ORGNK_EVENTS_CPT_NAME ) ) {
			$schema_script = orgnk_single_event_schema();
		}

		echo $schema_script;
	}

	/**
	 * orgnk_events_register_cron()
	 * Register a custom CRON event on init to call cron tasks hourly
	 */
	public function orgnk_events_register_cron() {

		$timestamp = wp_next_scheduled( 'orgnk_events_cron_schedule' );

		if ( $timestamp == false ) {
			wp_schedule_event( time(), 'hourly', 'orgnk_events_cron_schedule' );
		}
	}

	/**
	 * orgnk_events_set_next_occurrence()
	 * Updates the date/time of an events post meta
	 * Used to change recurring events dates/times based on whether they have occured yet
	 */
	public function orgnk_events_set_next_occurrence($event) {
		$event_times = orgnk_events_get_next_unix_date( $event );
		if ( $event_times ) {
			update_post_meta( $event, 'next_event_start_date', $event_times['start_time'] );
			update_post_meta( $event, 'next_event_end_date', $event_times['end_time'] );
		}
	}

	/**
	 * orgnk_events_cron_tasks()
	 * Register a custom CRON event on init to check for events to draft hourly & updated next occurence of event
	 */
	public function orgnk_events_cron_tasks() {
		$this->orgnk_events_remove_expired_task();
	}
}
