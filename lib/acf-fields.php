<?php

/**
 * Main Organik_Events_ACF_Fields class
 */
class Organik_Events_ACF_Fields {

	/**
     * Constructor function
     */
	public function __construct() {

		// Hook into the 'init' action to add the ACF Fields on to CPT
		add_filter( 'init', array( $this, 'orgnk_events_cpt_acf_fields' ) );
	}

	/**
	 * orgnk_events_cpt_acf_fields()
	 * Manually insert ACF fields for this CPT
	 */
	public function orgnk_events_cpt_acf_fields() {

		// Return early if ACF isn't active
		if ( ! class_exists( 'ACF' ) || ! function_exists( 'acf_add_local_field_group' ) || ! is_admin() || ! defined( 'ORGNK_EVENTS_CPT_NAME' ) ) return;

		// ACF Field Group for Single Event Post Type
		acf_add_local_field_group(array(

			'key' => 'group_5f83bfe49d082',
			'title' => 'Single Event Settings',
			// Fields
			'fields' => array(

				// Field - Event Dates - Tab
				array(
					'key' 				=> 'field_5f8535323a6a7',
					'label'				=> 'Dates',
					'name' 				=> '',
					'type' 				=> 'tab',
					'instructions'		=> '',
					'required'			=> 0,
					'conditional_logic'	=> 0,
					'wrapper'			=> array(
						'width' 	=> '',
						'class' 	=> '',
						'id' 		=> '',
					),
					'placement'			=> 'top',
					'endpoint' 			=> 0,
				),

				// Field - Event Dates - Button group
				// This allows a user to select between  scheduled and recurring event types & show the appropiate fields based on what date type is selected
				array(
					'key' 				=> 'field_60d0069a49d4a',
					'label'				=> 'Dates Type',
					'name' 				=> 'date_type',
					'type' 				=> 'button_group',
					'instructions' 		=> '',
					'required' 			=> 0,
					'conditional_logic' => 0,
					'wrapper' 			=> array(
						'width' 	=> '',
						'class' 	=> '',
						'id' 		=> '',
					),
					// Choices Selected or Recurring
					'choices' 			=> array(
						'scheduled' => 'Scheduled',
						'recurring' => 'Recurring',
					),
					'allow_null' 		=> 0,
					'default_value'		=> '',
					'layout' 			=> 'horizontal',
					'return_format' 	=> 'value',
				),

				// Field - Event Scheduled Dates - Repeater
				// This field only appears if the date type scheduled is selected
				array(
					'key' 				=> 'field_5f83c001c0453',
					'label'				=> 'Scheduled Dates',
					'name' 				=> 'event_dates',
					'type' 				=> 'repeater',
					'instructions' 		=> '<strong>Important:</strong> ensure that dates are arranged in the correct chronological order, with the soonest date first. The first date in this field is used to order, and determine which events are show on, the events archive.',
					'required' 			=> 0,
					'conditional_logic' => array(
						array(
							array(
								'field' 	=> 'field_60d0069a49d4a',
								'operator' 	=> '==',
								'value' 	=> 'scheduled',
							),
						),
					),
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' 	=> '',
					),
					'collapsed' 	=> '',
					'min' 			=> 1,
					'max' 			=> 0,
					'layout' 		=> 'table',
					'button_label' 	=> 'Add date',
					'sub_fields' 	=> array(

						// Field - Scheduled Event Start Time - Date/time picker
						array(
							'key' 				=> 'field_5f8536073a6a8',
							'label' 			=> 'Start',
							'name' 				=> 'start',
							'type' 				=> 'date_time_picker',
							'instructions' 		=> '',
							'required' 			=> 1,
							'conditional_logic' => 0,
							'wrapper' 			=> array(
								'width' 	=> '',
								'class' 	=> '',
								'id' 		=> '',
							),
							'display_format'	=> 'j F Y, g:i a',
							'return_format' 	=> 'g:i a, j F Y',
							'first_day' 		=> 1,
						),

						// Field - Scheduled Event End Time - Date/time picker
						array(
							'key' 				=> 'field_5f8536233a6a9',
							'label'				=> 'End',
							'name' 				=> 'end',
							'type' 				=> 'date_time_picker',
							'instructions' 		=> '',
							'required' 			=> 0,
							'conditional_logic' => 0,
							'wrapper' 			=> array(
								'width' 	=> '',
								'class' 	=> '',
								'id' 		=> '',
							),
							'display_format' 	=> 'j F Y, g:i a',
							'return_format' 	=> 'g:i a, j F Y',
							'first_day' 		=> 1,
						),
					),
				),

				// Field - Event Frequency - Select option
				// This field only appears if the date type recurring is selected
				array(
					'key' 						=> 'field_60d0076d49d4e',
					'label'						=> 'Frequency',
					'name' 						=> 'event_frequency',
					'type' 						=> 'select',
					'instructions' 				=> '',
					'required' 					=> 0,
					'conditional_logic' 		=> array(
						array(
							array(
								'field' 	=> 'field_60d0069a49d4a',
								'operator' 	=> '==',
								'value' 	=> 'recurring',
							),
						),
					),
					'wrapper' 			=> array(
						'width' 	=> '',
						'class' 	=> '',
						'id' 		=> '',
					),

					// Choices are every day, every week, every month
					'choices' 			=> array(
						'daily' 	=> 'Every day',
						'weekly' 	=> 'Every Week',
						'fortnightly'	=> 'Fortnightly',
						'monthly' 	=> 'Every Month',
					),
					'default_value' 			=> false,
					'allow_null' 				=> 0,
					'multiple' 					=> 0,
					'ui' 						=> 0,
					'return_format' 			=> 'value',
					'ajax' 						=> 0,
					'placeholder' 				=> '',
				),

				// Field - Event Day - Select
				// Field only appears on recurring weekly events
				array(
					'key' 						=> 'field_60d02d302433c',
					'label' 					=> 'Day',
					'name' 						=> 'event_day',
					'type' 						=> 'select',
					'instructions' 				=> '',
					'required' 					=> 0,
					'conditional_logic'			=> array(
						array(
							array(
								'field'		=> 'field_60d0076d49d4e',
								'operator' 	=> '==',
								'value' 	=> 'weekly',
							),
						),
					),
					'wrapper' 			=> array(
						'width' 	=> '',
						'class' 	=> '',
						'id'	 	=> '',
					),

					// Choices are each day of the week
					'choices' 			=> array(
						'monday' 		=> 'Monday',
						'tuesday' 		=> 'Tuesday',
						'wednesday'		=> 'Wednesday',
						'thursday' 		=> 'Thursday',
						'friday' 		=> 'Friday',
						'saturday' 		=> 'Saturday',
						'sunday' 		=> 'Sunday',
					),
					'default_value' 	=> false,
					'allow_null' 		=> 0,
					'multiple' 			=> 0,
					'ui' 				=> 0,
					'return_format' 	=> 'value',
					'ajax' 				=> 0,
					'placeholder' 		=> '',
				),
				array(
					'key' => 'field_640954bd34d4f',
					'label' => 'Starting From',
					'name' => 'event_fortnight_day',
					'type' => 'date_picker',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' 	=> array(
						array(
							array(
								'field' 	=> 'field_60d0076d49d4e',
								'operator' 	=> '==',
								'value' 	=> 'fortnightly',
							),
						),
					),
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'display_format' => 'd/m/Y',
					'return_format' => 'd/m/Y',
					'first_day' => 1,

				),
				// Field - Event Monthly occurence - Select
				// Field only appears on monthly recurring
				// Allows a user to select an event that starts on the nth occurence of each month
				array(
					'key' 				=> 'field_60e43984d6cdc',
					'label'				=> 'Occurrence',
					'name' 				=> 'event_month_occurrence',
					'type' 				=> 'select',
					'instructions'		=> '',
					'required' 				=> 0,
					'conditional_logic' 	=> array(
						array(
							array(
								'field' 	=> 'field_60d0076d49d4e',
								'operator' 	=> '==',
								'value' 	=> 'monthly',
							),
						),
					),
					'wrapper' 			=> array(
						'width' 	=> '',
						'class' 	=> '',
						'id' 		=> '',
					),
					'choices' 			=> array(
						'first' 	=> 'First',
						'second' 	=> 'Second',
						'third' 	=> 'Third',
						'fourth' 	=> 'Fourth',
					),
					'default_value' => false,
					'allow_null' 	=> 0,
					'multiple' 		=> 0,
					'ui' 			=> 0,
					'return_format' => 'value',
					'ajax' 			=> 0,
					'placeholder' 	=> '',
				),

				// Field - Day of month - Select
				// Allows a user to select day that an event occurs each month
				array(
					'key' 				=> 'field_60e4394ad6cdb',
					'label' 			=> 'Day of month',
					'name' 				=> 'event_month_day',
					'type' 				=> 'select',
					'instructions' 		=> '',
					'required' 			=> 0,
					'conditional_logic'	=> array(
						array(
							array(
								'field' 	=> 'field_60d0076d49d4e',
								'operator' 	=> '==',
								'value' 	=> 'monthly',
							),
						),
					),
					'wrapper' 			=> array(
						'width'		=> '',
						'class' 	=> '',
						'id' 		=> '',
					),
					'choices' 			=> array(
						'monday' 	=> 'Monday',
						'tuesday' 	=> 'Tuesday',
						'wednesday' => 'Wednesday',
						'thursday'	=> 'Thursday',
						'friday' 	=> 'Friday',
						'saturday' 	=> 'Saturday',
						'sunday' 	=> 'Sunday',
					),
					'default_value' => false,
					'allow_null' 	=> 0,
					'multiple' 		=> 0,
					'ui' 			=> 0,
					'return_format' => 'value',
					'ajax' 			=> 0,
					'placeholder' 	=> '',
				),

				// Field - Event Recurring Start Time - Time picker
				// Only shows on recurring events
				array(
					'key' 				=> 'field_60e43a781819d',
					'label'				=> 'Start',
					'name' 				=> 'event_start_time',
					'type' 				=> 'time_picker',
					'instructions'		=> '',
					'required'			=> 0,
					'conditional_logic' => array(
						array(
							array(
								'field' 	=> 'field_60d0069a49d4a',
								'operator' 	=> '==',
								'value' 	=> 'recurring',
							),
						),
					),
					'wrapper' 			=> array(
						'width' 	=> '',
						'class' 	=> '',
						'id' 		=> '',
					),
					'display_format'	=> 'g:i a',
					'return_format' 	=> 'g:i a',
				),

				// Field - Event Recurring End Time - Time picker
				// Only shows on recurring events
				array(
					'key' 					=> 'field_60e43a771819c',
					'label'					=> 'End',
					'name' 					=> 'event_end_time',
					'type' 					=> 'time_picker',
					'instructions'			=> '',
					'required'				=> 0,
					'conditional_logic'		=> array(
						array(
							array(
								'field' 	=> 'field_60d0069a49d4a',
								'operator' 	=> '==',
								'value' 	=> 'recurring',
							),
						),
					),
					'wrapper' 			=> array(
						'width' 	=> '',
						'class' 	=> '',
						'id' 		=> '',
					),
					'display_format' => 'g:i a',
					'return_format' => 'g:i a',
				),
				// Field - Event Tagline - Text
				array(
					'key' 					=> 'field_5f83fcddb5a98a',
					'label' 				=> 'Event Date Byline',
					'name' 					=> 'event_date_byline',
					'type' 					=> 'text',
					'instructions' 			=> 'Setting this will display a custom text field instead of the date',
					'required' 				=> 0,
					'conditional_logic' 	=> 0,
					'wrapper' 				=> array(
						'width' 		=> '',
						'class' 		=> '',
						'id' 			=> '',
					),
					'default_value' 		=> '',
					'placeholder' 			=> '',
					'prepend' 				=> '',
					'append' 				=> '',
					'maxlength' 			=> '',
				),

				// Field - Event Details - Tab
				array(
					'key' 					=> 'field_5f83bfebc0452',
					'label'					=> 'Details',
					'name' 					=> '',
					'type' 					=> 'tab',
					'instructions'			=> '',
					'required'				=> 0,
					'conditional_logic' 	=> 0,
					'wrapper' 				=> array(
						'width' 	=> '',
						'class' 	=> '',
						'id' 		=> '',
					),
					'placement' 		=> 'top',
					'endpoint' 			=> 0,
				),

				// Field - Event Type - Button group
				// Allows a user to select whether an online event is held at a venue, online or both
				array(
					'key' 					=> 'field_5f83f5f2750a6',
					'label'					=> 'Event Type',
					'name' 					=> 'event_type',
					'type' 					=> 'button_group',
					'instructions'			=> '',
					'required'				=> 0,
					'conditional_logic'		=> 0,
					'wrapper' 				=> array(
						'width' 		=> '',
						'class' 		=> '',
						'id' 			=> '',
					),
					'choices' 				=> array(
						'offline' 		=> 'Venue Event',
						'online' 		=> 'Online Event',
						'mixed' 		=> 'Both',
					),
					'allow_null' 			=> 0,
					'default_value'			=> 'offline',
					'layout' 				=> 'horizontal',
					'return_format' 		=> 'value',
				),

				// Field - Event Venue - Post object
				// Retrieves list of venues from VENUES_CPT
				// Allows a user to select a venue for their event
				// Only appears for events that are offline or mixed
				array(
					'key' 					=> 'field_5f83c08c0d0f8',
					'label'					=> 'Venue',
					'name' 					=> 'event_venue',
					'type' 					=> 'post_object',
					'instructions'			=> '',
					'required'				=> 1,
					'conditional_logic' 	=> array(
						array(
							array(
								'field' 	=> 'field_5f83f5f2750a6',
								'operator' 	=> '==',
								'value' 	=> 'offline',
							),
						),
						array(
							array(
								'field' 	=> 'field_5f83f5f2750a6',
								'operator' 	=> '==',
								'value' 	=> 'mixed',
							),
						),
					),
					'wrapper' 				=> array(
						'width'			=> '',
						'class'			=> '',
						'id' 			=> '',
					),
					'post_type' 			=> array(
						0 				=> 'venue',
					),
					'taxonomy' 				=> '',
					'allow_null' 			=> 1,
					'multiple' 				=> 0,
					'return_format' 		=> 'id',
					'ui' 					=> 1,
				),

				// Field - Virtual Loaction - Url
				array(
					'key' 					=> 'field_5f83f9fc40b4e',
					'label' 				=> 'Virtual Location',
					'name' 					=> 'event_virtual_location',
					'type' 					=> 'url',
					'instructions' 			=> 'Enter a URL where the virtual event will be held. If this is not available yet, leave this blank.',
					'required' 				=> 0,
					'conditional_logic' 	=> array(
						array(
							array(
								'field' 	=> 'field_5f83f5f2750a6',
								'operator' 	=> '==',
								'value' 	=> 'online',
							),
						),
						array(
							array(
								'field' 	=> 'field_5f83f5f2750a6',
								'operator' 	=> '==',
								'value' 	=> 'mixed',
							),
						),
					),
					'wrapper' 				=> array(
						'width' 		=> '',
						'class'			=> '',
						'id' 			=> '',
					),
					'default_value' 		=> '',
					'placeholder' 			=> '',
				),

				// Field - Event Organiser - Text
				array(
					'key' 					=> 'field_5f83fcddb5a98',
					'label' 				=> 'Organiser',
					'name' 					=> 'event_organiser',
					'type' 					=> 'text',
					'instructions' 			=> 'Provide an organiser for this event if your business is not the organiser.',
					'required' 				=> 0,
					'conditional_logic' 	=> 0,
					'wrapper' 				=> array(
						'width' 		=> '',
						'class' 		=> '',
						'id' 			=> '',
					),
					'default_value' 		=> '',
					'placeholder' 			=> '',
					'prepend' 				=> '',
					'append' 				=> '',
					'maxlength' 			=> '',
				),

				// Field - Event Organiser Link - url
				// Allows user to provide a url to an organisers website
				array(
					'key' 					=> 'field_5f842126f2195',
					'label' 				=> 'Organiser Link',
					'name' 					=> 'event_organiser_link',
					'type' 					=> 'url',
					'instructions' 			=> 'Provide a URL for organiser\'s website if your business is not the organiser.',
					'required' 				=> 0,
					'conditional_logic'		=> array(
						array(
							array(
								'field' 	=> 'field_5f83fcddb5a98',
								'operator' 	=> '!=empty',
							),
						),
					),
					'wrapper' 				=> array(
						'width' 	=> '',
						'class' 	=> '',
						'id' 		=> '',
					),
					'default_value' => '',
					'placeholder' 	=> '',
				),

				// Field - Event Notes - Text area
				array(
					'key' 				=> 'field_5f87f7aea68dd',
					'label' 			=> 'Notes',
					'name' 				=> 'event_notes',
					'type' 				=> 'textarea',
					'instructions' 		=> 'If you need to add any general notes to this event, include them here.',
					'required' 			=> 0,
					'conditional_logic' => 0,
					'wrapper' 			=> array(
						'width' 	=> '',
						'class' 	=> '',
						'id' 		=> '',
					),
					'default_value'		=> '',
					'placeholder'		=> '',
					'maxlength' 		=> '',
					'rows' 				=> 4,
					'new_lines' 		=> '',
				),

				// Field - Featured Event - True/False
				// Allows user to mark an event as featured which will push it to the top of the archive list
				array(
					'key' 				=> 'field_60e463b22252d',
					'label' 			=> 'Featured Event',
					'name' 				=> 'event_featured',
					'type' 				=> 'true_false',
					'instructions' 		=> 'Marking an event as featured will bring it to the top the posts',
					'required' 			=> 0,
					'conditional_logic' => 0,
					'wrapper' 			=> array(
						'width' 		=> '',
						'class' 		=> '',
						'id' 		 	=> '',
					),
					'message' 			=> '',
					'default_value' 	=> 0,
					'ui' 				=> 1,
					'ui_on_text' 		=> '',
					'ui_off_text' 		=> '',
				),

				// Field - Event Tickets Tab
				array(
					'key' 					=> 'field_5f83fa4b40b4f',
					'label' 				=> 'Tickets',
					'name' 					=> '',
					'type' 					=> 'tab',
					'instructions' 			=> '',
					'required' 				=> 0,
					'conditional_logic' 	=> 0,
					'wrapper' 				=> array(
						'width' 		=> '',
						'class' 		=> '',
						'id' 			=> '',
					),
					'placement' 			=> 'top',
					'endpoint' 				=> 0,
				),

				// Field - Event Toggle Ticketing - true/false
				// Enables ticketing
				array(
					'key' 					=> 'field_5f83fa6240b50',
					'label' 				=> 'Toggle Ticketing',
					'name' 					=> 'event_ticket_toggle',
					'type' 					=> 'true_false',
					'instructions' 			=> '',
					'required' 				=> 0,
					'conditional_logic' 	=> 0,
					'wrapper' 				=> array(
						'width' 		=> '',
						'class' 		=> '',
						'id' 			=> '',
					),
					'message' 				=> '',
					'default_value'			=> 0,
					'ui' 					=> 1,
					'ui_on_text'			=> 'On',
					'ui_off_text'			=> 'Off',
				),

				// Field - Event Ticket Status - Button group
				// Allows user to select status of tickets
				// Fields only show if event ticking is set to true
				array(
					'key' 					=> 'field_5f83fbf940b53',
					'label' 				=> 'Status',
					'name' 					=> 'event_ticket_status',
					'type' 					=> 'button_group',
					'instructions' 			=> '',
					'required' 				=> 1,
					'conditional_logic' 	=> array(
						array(
							array(
								'field' 	=> 'field_5f83fa6240b50',
								'operator' 	=> '==',
								'value' 	=> '1',
							),
						),
					),
					'wrapper' 				=> array(
						'width' 	=> '',
						'class' 	=> '',
						'id' 		=> '',
					),
					// Ticketing choices - Available/Pre-order/Sold out
					'choices' 				=> array(
						'in-stock' 		=> 'Available now',
						'pre-order' 	=> 'Pre-order',
						'sold-out' 		=> 'Sold out',
					),
					'allow_null' 			=> 0,
					'default_value' 		=> 'in-stock',
					'layout' 				=> 'horizontal',
					'return_format' 		=> 'value',
				),

				// Field - Event Ticket Link - Url
				array(
					'key' 					=> 'field_5f83c06d0d0f7',
					'label' 				=> 'Ticket Link',
					'name' 					=> 'event_ticket_link',
					'type' 					=> 'url',
					'instructions'			=> '',
					'required'				=> 1,
					'conditional_logic' 	=> array(
						array(
							array(
								'field' 	=> 'field_5f83fa6240b50',
								'operator' 	=> '==',
								'value' 	=> '1',
							),
						),
					),
					'wrapper' 			=> array(
						'width' 	=> '',
						'class' 	=> '',
						'id' 		=> '',
					),
					'default_value' => '',
					'placeholder' 	=> '',
				),

				// Field - Event Ticket On-Sale Date - Date/time picker
				// Allows a user to select when tickets are available to buy
				array(
					'key' 					=> 'field_5f867d6da5349',
					'label' 				=> 'On-Sale Date',
					'name' 					=> 'event_ticket_onsale_date',
					'type' 					=> 'date_time_picker',
					'instructions' 			=> 'If you do not set an on-sale date, it will be assumed that tickets for this event are available immediately.',
					'required' 				=> 0,
					'conditional_logic' 	=> array(
						array(
							array(
								'field' 	=> 'field_5f83fa6240b50',
								'operator' 	=> '==',
								'value' 	=> '1',
							),
						),
					),
					'wrapper' 		=> array(
						'width' 	=> '',
						'class' 	=> '',
						'id' 		=> '',
					),
					'display_format' 	=> 'j F Y, g:i a',
					'return_format' 	=> 'g:i a, j F Y',
					'first_day'			 => 1,
				),

				// Field - Event Currency - Text
				// Required for events schema, user inouts currency code for the country the tickets are sold in
				array(
					'key' 					=> 'field_5f87f798a68dc',
					'label' 				=> 'Currency',
					'name' 					=> 'event_ticket_currency',
					'type' 					=> 'text',
					'instructions'		 	=> 'If you are enabling ticketing, you must include a currency code (for example \'AUD\'). Currency is only used for generating schema.',
					'required' 				=> 1,
					'conditional_logic' 	=> array(
						array(
							array(
								'field' 	=> 'field_5f83fa6240b50',
								'operator' 	=> '==',
								'value' 	=> '1',
							),
							array(
								'field' 	=> 'field_5f83c001c0453',
								'operator' 	=> '!=empty',
							),
						),
					),
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' 		=> '',
					'placeholder' 			=> '',
					'prepend' 				=> '',
					'append'				=> '',
					'maxlength' 			=> '',
				),

				// Field - Ticket types - Repeater
				// Allows user to specify what kind of tickets are available and any relevant details
				array(
					'key' 					=> 'field_5f8672688c4ac',
					'label'					=> 'Ticket Types',
					'name' 					=> 'event_ticket_types',
					'type' 					=> 'repeater',
					'instructions' 			=> 'Note: ticket prices will output exactly as written, so include currency symbols etc. Only input a single price for each ticket type, <strong>do not include a price range</strong>.',
					'required'				=> 1,
					'conditional_logic' 	=> array(
						array(
							array(
								'field' 	=> 'field_5f83fa6240b50',
								'operator' 	=> '==',
								'value' 	=> '1',
							),
						),
					),
					'wrapper' 				=> array(
						'width' 	=> '',
						'class' 	=> '',
						'id' 		=> '',
					),
					'collapsed' 			=> '',
					'min' 					=> 1,
					'max' 					=> 0,
					'layout' 				=> 'table',
					'button_label' 			=> 'Add ticket type',
					'sub_fields' 			=> array(
						// Field - Ticket Type - Text
						array(
							'key' 					=> 'field_5f86727e8c4ad',
							'label'					=> 'Type',
							'name' 					=> 'type',
							'type' 					=> 'text',
							'instructions' 			=> '',
							'required' 				=> 1,
							'conditional_logic' 	=> 0,
							'wrapper' 				=> array(
								'width' 		=> '',
								'class' 		=> '',
								'id' 			=> '',
							),
							'default_value' 		=> '',
							'placeholder' 			=> '',
							'prepend' 				=> '',
							'append' 				=> '',
							'maxlength' 			=> '',
						),
						// Field - Ticket Price - Text
						array(
							'key' 					=> 'field_5f8672958c4ae',
							'label'					=> 'Price',
							'name' 					=> 'price',
							'type' 					=> 'text',
							'instructions' 			=> '',
							'required' 				=> 1,
							'conditional_logic' 	=> 0,
							'wrapper' 				=> array(
								'width' 		=> '',
								'class' 		=> '',
								'id' 			=> '',
							),
							'default_value' 		=> '',
							'placeholder' 			=> '',
							'prepend' 				=> '',
							'append' 				=> '',
							'maxlength' 			=> '',
						),
					),
				),

				// Field - Event Status - Tab
				array(
					'key' 					=> 'field_5f83f5cb750a5',
					'label'				 	=> 'Status',
					'name' 					=> '',
					'type' 					=> 'tab',
					'instructions' 			=> '',
					'required' 				=> 0,
					'conditional_logic' 	=> 0,
					'wrapper' 				=> array(
						'width' 		=> '',
						'class' 		=> '',
						'id' 			=> '',
					),
					'placement' 			=> 'top',
					'endpoint'		 		=> 0,
				),

				// Field - Event Status - Button group
				// Allows user to select the status of an evetn from: Sceduled/Postponed/Cancelled
				array(
					'key' 					=> 'field_5f83fd49b5a99',
					'label'					=> 'Event Status',
					'name' 					=> 'event_status',
					'type' 					=> 'button_group',
					'instructions' 			=> '',
					'required' 				=> 0,
					'conditional_logic' 	=> 0,
					'wrapper' 				=> array(
						'width' 		=> '',
						'class' 		=> '',
						'id' 			=> '',
					),
					'choices' 				=> array(
						'scheduled' 	=> 'Scheduled',
						'postponed' 	=> 'Postponed',
						'cancelled' 	=> 'Cancelled',
					),
					'allow_null' 		=> 0,
					'default_value' 	=> 'scheduled',
					'layout' 			=> 'horizontal',
					'return_format' 	=> 'value',
				),
			),
			// Location Rules - Single Events CPT
			'location' => array(
				array(
					array(
						'param'			=> 'post_type',
						'operator' 		=> '==',
						'value' 		=> ORGNK_EVENTS_CPT_NAME,
					),
				),
			),
			'menu_order' 				=> 0,
			'position' 					=> 'acf_after_title',
			'style' 					=> 'default',
			'label_placement' 			=> 'left',
			'instruction_placement' 	=> 'label',
			'hide_on_screen' 			=> '',
			'active' 					=> true,
			'description' 				=> '',
		));

		// Single Venue Settings
		acf_add_local_field_group(array(
			'key' 						=> 'group_5f83e6cecf9f6',
			'title' 					=> 'Single Venue Settings',
			'fields' 					=> array(
				// Field - Venue address - Text
				array(
					'key' 					=> 'field_5f83e6d7becde',
					'label'					=> 'Address',
					'name' 					=> 'venue_address',
					'type' 					=> 'text',
					'instructions' 			=> '',
					'required' 				=> 1,
					'conditional_logic'		=> 0,
					'wrapper'				=> array(
						'width' 		=> '',
						'class' 		=> '',
						'id' 			=> '',
					),
					'default_value' 		=> '',
					'placeholder' 			=> '',
					'prepend' 				=> '',
					'append' 				=> '',
					'maxlength' 			=> '',
				),

				// Field - Venue Suburb - Text
				array(
					'key'					=> 'field_5f83ffd49acaf',
					'label'					=> 'Suburb',
					'name' 					=> 'venue_suburb',
					'type' 					=> 'text',
					'instructions' 			=> '',
					'required' 				=> 1,
					'conditional_logic' 	=> 0,
					'wrapper' 				=> array(
						'width' 		=> '',
						'class' 		=> '',
						'id' 			=> '',
					),
					'default_value' 		=> '',
					'placeholder' 			=> '',
					'prepend' 				=> '',
					'append' 				=> '',
					'maxlength' 			=> '',
				),

				// Field - Venue City - Text
				array(
					'key' 					=> 'field_5f8400319acb3',
					'label' 				=> 'City',
					'name' 					=> 'venue_city',
					'type' 					=> 'text',
					'instructions' 			=> '',
					'required' 				=> 1,
					'conditional_logic' 	=> 0,
					'wrapper' 				=> array(
						'width' 		=> '',
						'class' 		=> '',
						'id' 			=> '',
					),
					'default_value' 		=> '',
					'placeholder' 			=> '',
					'prepend' 				=> '',
					'append' 				=> '',
					'maxlength' 			=> '',
				),

				// Field - Venue State - text
				array(
					'key' 					=> 'field_5f83fff39acb1',
					'label' 				=> 'State/Region',
					'name' 					=> 'venue_region',
					'type' 					=> 'text',
					'instructions' 			=> 'Enter state/region abbreviation, for example \'WA\' or \'VIC\'.',
					'required' 				=> 1,
					'conditional_logic' 	=> 0,
					'wrapper' 				=> array(
						'width' 		=> '',
						'class' 		=> '',
						'id' 			=> '',
					),
					'default_value' 		=> '',
					'placeholder' 			=> '',
					'prepend' 				=> '',
					'append' 				=> '',
					'maxlength' 			=> '',
				),

				// Field - Venue Postcode - text
				array(
					'key' 					=> 'field_5f83ffe19acb0',
					'label' 				=> 'Post Code',
					'name' 					=> 'venue_post_code',
					'type' 					=> 'text',
					'instructions' 			=> '',
					'required' 				=> 1,
					'conditional_logic' 	=> 0,
					'wrapper' 			=> array(
						'width' 	=> '',
						'class' 	=> '',
						'id' 		=> '',
					),
					'default_value' 		=> '',
					'placeholder'			=> '',
					'prepend' 				=> '',
					'append' 				=> '',
					'maxlength' 			=> '',
				),

				// Field - Venue Country - text
				array(
					'key' 					=> 'field_5f8400079acb2',
					'label' 				=> 'Country',
					'name' 					=> 'venue_country',
					'type' 					=> 'text',
					'instructions' 			=> '',
					'required' 				=> 1,
					'conditional_logic' 	=> 0,
					'wrapper' 				=> array(
						'width' 		=> '',
						'class' 		=> '',
						'id' 			=> '',
					),
					'default_value' 		=> '',
					'placeholder' 			=> '',
					'prepend' 				=> '',
					'append' 				=> '',
					'maxlength' 			=> '',
				),
			),
			// Location Rules - Single Location CPT
			'location' => array(
				array(
					array(
						'param' 			=> 'post_type',
						'operator' 			=> '==',
						'value' 			=> 'venue',
					),
				),
			),
			'menu_order' 				=> 0,
			'position' 					=> 'normal',
			'style' 					=> 'default',
			'label_placement' 			=> 'left',
			'instruction_placement' 	=> 'label',
			'hide_on_screen' 			=> '',
			'active' 					=> true,
			'description' 				=> '',
		));

	}
}