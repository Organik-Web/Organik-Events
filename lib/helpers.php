<?php
//=======================================================================================================================================================
// Events helper & templating functions
//=======================================================================================================================================================

/**
 * orgnk_events_entry_schedule()
 * Lists the event times and dates in a neat format
 * Can be limited to only show the first date for an event by passing $first = true
 * Can be limited to only display future events by passing $exclude_past = true
 */
function orgnk_events_entry_schedule( $first = false, $date_size = false, $exclude_past = true) {

	$output = NULL;
	$date_class = ( $date_size ) ? ' ' . $date_size : NULL;
	$type_class = ( $first ) ? ' simple-schedule' : ' list-schedule';

	// Modify the HTML output if only one date is being printed
	$ul = ( $first === true ) ? 'div' : 'ul';
	$li = ( $first === true ) ? 'span' : 'li';

	// Get dates
	$date_type   			= esc_html( get_post_meta( get_the_ID(), 'date_type', true ) );
	$dates             		= esc_html( get_post_meta( get_the_ID(), 'event_dates', true ) );

	// Check if dates & date type is not recurring
	if ( $dates && $date_type != 'recurring') {

		$output .= '<' . $ul . ' class="event-schedule' . $type_class . '">';

		for ( $i = 0; $i < $dates; $i++ ) {

			// Variables
			$event_start 			= strtotime( esc_html( get_post_meta( get_the_ID(), 'event_dates_' . $i . '_start', true ) ) );
			$event_end 				= strtotime( esc_html( get_post_meta( get_the_ID(), 'event_dates_' . $i . '_end', true ) ) );
			$start_time 			= ( $event_start ) ? date( 'g:i a', $event_start ) : NULL;
			$start_date 			= ( $event_start ) ? date( 'j F Y', $event_start ) : NULL;
			$end_time 				= ( $event_end ) ? date( 'g:i a', $event_end ) : NULL;
			$end_date 				= ( $event_end ) ? date( 'j F Y', $event_end ) : NULL;

			// Check if exclude_past events is set to true, if it is set to true set current event check to always be in the future
			// Else set the current event depending on the start/end date
			$now = time();

			if ( $exclude_past === false ) {
				$current_event = ( time() + 3600 );  // Add 1 hour
			} else {
				$current_event = $event_end;
			}

			// Check if current_event has occured
			if ( $current_event > $now ) {

				// Start date and time is the bare minimum needed to run this function
				if ( $start_time && $start_date ) {

					$output .= '<' . $li . ' class="event-date' . $date_class . '">';
					$output .= $start_time;

					if ( $end_time && $end_date === $start_date ) {
						$output .= ' - '; // Time seperator
						$output .= $end_time;
					}

					$output .= ', '; // Time/date seperator
					$output .= $start_date;

					if ( $end_date && $end_date !== $start_date ) {
						$output .= ' - '; // Time seperator

						if ( $end_time ) {
							$output .= $end_time;
							$output .= ', '; // Time/date seperator
						}

						$output .= $end_date;
					}

					$output .= '</' . $li . '>';

					if ( $first === true ) {
						if ( $dates > 1 ) {
							$output .= '<span class="more-dates">+ more dates</span>';
						}
						// End the loop early to only return the first date
						if ( $i++ == 0 ) break;
					}
				}
			}
		}
		$output .= '</' . $ul . '>';
	}
	elseif ( $date_type === 'recurring') {
		$output = orgnk_events_recurring_format_pretty();
	}

    return $output;
}

//=======================================================================================================================================================
/*
 * orgnk_events_recurring_format_pretty()
 * Outputs a recurring events start & finish time in a neat format
 */
function orgnk_events_recurring_format_pretty() {

	$date_type   			= esc_html( get_post_meta( get_the_ID(), 'date_type', true ) );
	$output					= null;

	if ( $date_type === 'recurring' ) {

		$output					.= '<span class="event-date">';
		$event_frequency		= esc_html( get_post_meta( orgnk_get_the_ID(), 'event_frequency', true ) );
		$event_start 			= strtotime( esc_html( get_post_meta( get_the_ID(), 'event_start_time', true ) ) );
		$event_end 				= strtotime( esc_html( get_post_meta( get_the_ID(), 'event_end_time', true ) ) );
		$start_time 			= ( $event_start ) ? date( 'g:i a', $event_start ) : NULL;
		$end_time 				= ( $event_end ) ? date( 'g:i a', $event_end ) : NULL;

		if ( $event_frequency === 'daily' ) {

			$output 				.= 'Every day between: ' . $start_time . ' and ' . $end_time;

		} elseif ( $event_frequency === 'weekly' ) {

			$event_day 				= date('l', strtotime( esc_html( get_post_meta( get_the_ID(), 'event_day', true ) ) ) );
			$output 				.=  $start_time . ', every ' . $event_day;

		} elseif ( $event_frequency === 'fortnightly' ) {
			$event_date_start 			= esc_html( get_post_meta( get_the_ID(), 'next_event_start_date', true ) );
			$event_start_time_unix 	  = date( 'g:i a, F j, Y', $event_date_start );
			$output 				.=  $event_start_time_unix;

		}  elseif ( $event_frequency === 'monthly' ) {

			$event_day 				= date( 'l', strtotime( esc_html( get_post_meta( get_the_ID(), 'event_month_day', true ) ) ) );
			$event_occurence		= esc_html( get_post_meta( get_the_ID(), 'event_month_occurrence', true ) );
			$output 				.=  $start_time . ', On the  ' . $event_occurence . ' ' . $event_day . ' of every month';

		}

		$output	.= '</span>';
		return $output;
	}
}

//=======================================================================================================================================================
/**
 * orgnk_events_get_next_unix_date()
 * Checks a recurring events start time and compares it to the current time
 * Depending on the time set it will add the next events start and end time to an array
 * For Scheduled events use orgnk_events_get_next_scheduled() to return next scheduled event
 * Stores dates in a unix timestamp format in an array
 */
function orgnk_events_get_next_unix_date( $id = null ) {
	// Return early if no id is provided
	if ( ! $id ) return;
	$output					= [];
	$date_type   			= esc_html( get_post_meta( $id, 'date_type', true ) );

	if ( $date_type === 'recurring' ) {

		// Set common recurring date variables
		$current_time				= strtotime( 'now' );
		$current_month				= date("F");
		$current_day				= date( 'l', $current_time );
		$event_frequency			= esc_html( get_post_meta( $id, 'event_frequency', true ) );
		$event_start_time_unix		= strtotime('today ' .  esc_html( get_post_meta( $id, 'event_start_time', true ) ) );
		$event_end_time_unix		= strtotime( 'today ' . esc_html( get_post_meta( $id, 'event_end_time', true ) ) );

		// Here we are checking the frequency of an event
		// There are 3 types of recurring events: daily, weekly and  monthly.
		// For each of these events we check whether the current time is greater than the end time, if it is we set the next event date as neeed
		if ( $event_frequency === 'daily' ) {

			if ($current_time >  $event_end_time_unix ) {
				$event_start_time_unix		= strtotime('tomorrow ' .  esc_html( get_post_meta( $id, 'event_start_time', true ) ) );
				$event_end_time_unix		= strtotime( 'tomorrow ' . esc_html( get_post_meta( $id, 'event_end_time', true ) ) );
			}

		} elseif ( $event_frequency === 'weekly' ) {

			// Event meta variables
			$event_day			= date( 'l', strtotime( esc_html( get_post_meta( $id, 'event_day', true ) ) ) );
			$event_start		= ( $event_start_time_unix ) ? date( 'g:i a', $event_start_time_unix ) : NULL;
			$event_end			= ( $event_end_time_unix ) ? date( 'g:i a', $event_end_time_unix ) : NULL;

			// If the day is equal to the current day and the event has not ended, the event start time is today else it is the next event day
			if ( ( $event_day === $current_day )  && ( $current_time < $event_end_time_unix ) ) {

				$event_start_time_unix		= strtotime( 'today'  . $event_start );
				$event_end_time_unix		= strtotime( 'today'  . $event_end );

			} else {

				$event_start_time_unix 		= strtotime( 'next ' . $event_day . $event_start );
				$event_end_time_unix 		= strtotime( 'next ' . $event_day . $event_end );

			}

		} elseif ( $event_frequency === 'fortnightly' ) {

			// Event meta variables
			// Get the start date of the fortnight from the user
			$start_date 	= date( 'Y-m-d', strtotime( esc_html( get_post_meta( $id, 'event_fortnight_day', true ) ) ) );
			$event_start	= ( $event_start_time_unix ) ? date( 'g:i a', $event_start_time_unix ) : NULL;
			$event_end		= ( $event_end_time_unix ) ? date( 'g:i a', $event_end_time_unix ) : NULL;
			// Get the current date and time
			$current_datetime = new DateTime();
			$event_end_time_unix = strtotime( $event_end . ' ' . $start_date );

			if ( $current_time < $event_end_time_unix ) {
				$event_start_time_unix = strtotime( $event_start . ' ' . $start_date );
				$event_end_time_unix = strtotime( $event_end . ' ' . $start_date );
			} else {
				// Calculate the number of days between the start date and the current date
				$days_since_start = $current_datetime->diff(new DateTime($start_date))->days;

				// Calculate the number of fortnights since the start date
				$fortnights_since_start = floor($days_since_start / 14);

				// Calculate the next occurrence of the event
				$next_occurrence = date('Y-m-d', strtotime('+' . (2 - ($fortnights_since_start % 2)) . ' week', strtotime($start_date)));

				// Check if the next occurrence has passed
				if ($next_occurrence <= $current_datetime->format('Y-m-d')) {
					// Calculate the next occurrence of the event
					$next_occurrence = date('Y-m-d', strtotime('+2 week', strtotime($next_occurrence)));
					// Calculate the start and end times for the next occurrence of the event
					$event_start_time_unix = strtotime( $event_start . ' ' . $next_occurrence );
					$event_end_time_unix = strtotime( $event_end . ' ' . $next_occurrence );
				}

				// Calculate the start and end times for the next occurrence of the event
				$event_start_time_unix = strtotime( $event_start . ' ' . $next_occurrence );
				$event_end_time_unix = strtotime( $event_end . ' ' . $next_occurrence );
			}
		} elseif ( $event_frequency === 'monthly' ) {

			// Event meta variables
			$event_day 					= date( 'l', strtotime( esc_html( get_post_meta( $id, 'event_month_day', true ) ) ) );
			$event_occurence			= esc_html( get_post_meta( $id, 'event_month_occurrence', true ) );
			$event_start 				= ( $event_start_time_unix ) ? date( 'g:i a', $event_start_time_unix ) : NULL;
			$event_end					= ( $event_end_time_unix ) ? date( 'g:i a', $event_end_time_unix ) : NULL;

			$event_start_time_unix		= strtotime($event_start , strtotime(' ' . $event_occurence . ' ' . $event_day . ' of ' . $current_month . '') ) ;
			$event_end_time_unix		= strtotime($event_end , strtotime(' ' . $event_occurence . ' ' . $event_day . ' of ' . $current_month . '') ) ;

			if ( $current_time > $event_end_time_unix ) {

				$event_start            	= ( $event_start_time_unix ) ? date( 'Y-m-d H:i:s', $event_start_time_unix ) : NULL;
				$event_end              	= ( $event_end_time_unix ) ? date( 'Y-m-d H:i:s', $event_end_time_unix ) : NULL;
				$event_start_time_unix		= strtotime($event_start . " +1 month");
				$event_end_time_unix		= strtotime($event_end . " +1 month");

			}
		}

		// Set output to be returned
		$output['start_time'] 	= $event_start_time_unix;
		$output['end_time']		= $event_end_time_unix;

	} else {
			// Call orgnk_events_get_next_scheduled to return the next scheduled event times
			$output = orgnk_events_get_next_scheduled($id);
		}

	return $output;
}


//=======================================================================================================================================================
/**
 * orgnk_events_get_next_scheduled()
 * Returns the next event date for a scheduled event
 */
function orgnk_events_get_next_scheduled( $id = null ) {

	$date_count = esc_html( get_post_meta( $id, 'event_dates', true ) );

	// Loop over dates to check whether the event has occurred or not
	for ( $i = 0; $i < $date_count; $i ++ ) {

		// Event meta variables
		$event_date_start = esc_html( get_post_meta( $id, 'event_dates_' . $i . '_start', true ) );
		$event_date_end = esc_html( get_post_meta( $id, 'event_dates_' . $i . '_end', true ) );
		$current_event = strtotime( $event_date_end );
		$now = time();

		// When a date that hasn't occurred is found store it in an array and break out of loop
		if ( $current_event > $now ) {

			$event_start_time_unix = strtotime($event_date_start );
			$event_end_time_unix = strtotime( $event_date_end );
			$output['start_time'] 	= $event_start_time_unix;
			$output['end_time']		= $event_end_time_unix;

			return $output;
		}
	}
}



//=======================================================================================================================================================
/**
 * orgnk_events_get_posts()
 * Returns a posts loop which contains event posts
 * Pass in a $posts_per_page value to change the number of events it returns, the default is 3
 */
function orgnk_events_get_posts($posts_per_page = 3) {

	$args = array(
		'post_type' 		=> 'event',
		'post_status' 		=> 'publish',
		'order' 			=> 'DESC',
		'meta_query'		=> array([
							'relation'    => 'AND',
							'event_featured'    	=> array(
							'key'       				=> 'event_featured',
							'type' 						=> 'numeric',
							'compare'   				=> 'EXISTS',
							),
							'next_event_start_date'    	=> array(
							'key'     					=> 'next_event_start_date',
							'type' 						=> 'numeric',
							'compare'   				=> 'EXISTS',
							)
						]),
			'orderby' 		=>  array(
							'event_featured' 			=> 'DESC',
							'next_event_start_date' 	=> 'ASC',
			),
		'posts_per_page' 	=> $posts_per_page,
	);

	$posts_loop = new WP_Query( $args );

	return $posts_loop;

}

//=======================================================================================================================================================

/**
 * orgnk_events_entry_ticket_types()
 * Lists the event ticket types and price in a neat format
 */
function orgnk_events_entry_ticket_types() {

	$output 				= NULL;
	$tickets	            = esc_html( get_post_meta( get_the_ID(), 'event_ticket_types', true ) );

	if ( $tickets ) {

		$output .= '<ul class="event-ticket-types">';

		for ( $i = 0; $i < $tickets; $i++ ) {

			// Variables
			$ticket_type		= esc_html( get_post_meta( get_the_ID(), 'event_ticket_types_' . $i . '_type', true ) );
			$ticket_price      	= esc_html( get_post_meta( get_the_ID(), 'event_ticket_types_' . $i . '_price', true ) );

			if ( $ticket_type && $ticket_price ) {

				$output .= '<li class="event-date">';
					$output .= $ticket_type . ' - ' . $ticket_price;
				$output .= '</li>';
			}
		}

		$output .= '</ul>';
	}

	return $output;
}

//=======================================================================================================================================================

/**
 * orgnk_events_entry_online_event_badge()
 * Returns a small badge indicating the current event's availability of tickets
 * Returns nothing if the current event's tickets are available, which would be assumed otherwise
 */
function orgnk_events_entry_online_event_badge() {

	$output 				= NULL;
	$type                   = esc_html( get_post_meta( get_the_ID(), 'event_type', true ) );

	if ( $type === 'online' || $type === 'mixed' ) {
		$output .= '<div class="badge virtual-event"><i class="icon"></i>Online event</div>';
	}

	return $output;
}

//=======================================================================================================================================================

/**
 * orgnk_events_entry_sale_status_badge()
 * Returns a small badge indicating the current event's status and ticket sale status
 * If the event status if is anything other than 'scheduled', then no ticket sale status badges will show
 * This function will only return nothing if the event status is 'scheduled', the current event's tickets are set as 'available', but the on-sale date is in the future
 */
function orgnk_events_entry_sale_status_badge() {

	$output 				= NULL;
	$event_status			= esc_html( get_post_meta( get_the_ID(), 'event_status', true ) );
	$ticket_status			= esc_html( get_post_meta( get_the_ID(), 'event_ticket_status', true ) );
	$onsale          		= strtotime( esc_html( get_post_meta( get_the_ID(), 'event_ticket_onsale_date', true ) ) );

	if ( $event_status && $ticket_status ) {

		if ( $event_status === 'scheduled' ) {

			if ( $ticket_status === 'sold-out' ) {
				$output .= '<div class="badge ticket-status status-sold-out"><i class="icon"></i>Sold out</div>';
			} elseif ( $ticket_status === 'pre-order' ) {
				$output .= '<div class="badge ticket-status status-pre-order"><i class="icon"></i>Pre-order</div>';
			} elseif ( $ticket_status === 'in-stock' ) {
				// If the on-sale date is not set, or it is set and the on-sale date is in the past
				if ( ! $onsale || ( $onsale && $onsale < time() ) ) {
					$output .= '<div class="badge ticket-status status-on-sale"><i class="icon"></i>On-sale now</div>';
				}
			}
		} elseif ( $event_status === 'postponed' ) {
			$output .= '<div class="badge event-status status-postponed"><i class="icon"></i>Postponed</div>';
		} elseif ( $event_status === 'cancelled' ) {
			$output .= '<div class="badge event-status status-cancelled"><i class="icon"></i>Cancelled</div>';
		}
	}

	return $output;
}

//=======================================================================================================================================================

/**
 * orgnk_events_entry_badge_list()
 * Compiles a list of badges for an event
 */
function orgnk_events_entry_badge_list() {

	$badges = NULL;
	$output = NULL;

	if ( orgnk_events_entry_online_event_badge() ) {
		$badges .= orgnk_events_entry_online_event_badge();
	}

	if ( orgnk_events_entry_sale_status_badge() ) {
		$badges .= orgnk_events_entry_sale_status_badge();
	}

	if ( $badges ) {
		$output = '<div class="badge-group event-badge-list">' . $badges . '</div>';
	}

	return $output;
}

//=======================================================================================================================================================

/**
 * orgnk_events_entry_venue()
 * Returns the events venue details, either in a short format or in full (name, street address and suburb)
 */
function orgnk_events_entry_venue( $short = false ) {

	$output 				= NULL;
	$type                   = esc_html( get_post_meta( get_the_ID(), 'event_type', true ) );
	$venue_id               = esc_html( get_post_meta( get_the_ID(), 'event_venue', true ) );

	if ( ( $type === 'offline' || $type === 'mixed' ) && $venue_id ) {

		// Get venue post variables
		$venue_name		        = esc_html( get_the_title( $venue_id ) );
		$venue_address          = esc_html( get_post_meta( $venue_id, 'venue_address', true ) );
		$venue_suburb           = esc_html( get_post_meta( $venue_id, 'venue_suburb', true ) );
		$venue_city	            = esc_html( get_post_meta( $venue_id, 'venue_city', true ) );
		$venue_region           = esc_html( get_post_meta( $venue_id, 'venue_region', true ) );
		$venue_post_code        = esc_html( get_post_meta( $venue_id, 'venue_post_code', true ) );

		$output .= '<div class="event-venue">';
		$output .= '<span class="venue-name">' . $venue_name . '</span>';

		if ( $short === true ) {

			if ( $venue_address && $venue_suburb ) {
				$output .= '<span class="venue-short-address">' . $venue_address . ', ' . $venue_suburb . '</span>';
			}

		} else {

			if ( $venue_address ) {
				$output .= '<span class="venue-address">' . $venue_address;

				if ( $venue_suburb ) {
					$output .= ', ' . $venue_suburb;
				}

				if ( $venue_city || $venue_region || $venue_post_code ) {
					$output .= '<br>';
					$output .= implode( ' ', array( $venue_city, $venue_region, $venue_post_code ) );
				}

				$output .= '<span>';
			}
		}

		$output .= '</div>';
	}

	return $output;
}

//=======================================================================================================================================================

/**
 * orgnk_events_entry_tickets_button()
 * Generates an offsite booking button for an event, if the event's on sale date isn't set or if it's set and in the past
 * Accepts a string for changing the button text
 */
function orgnk_events_entry_tickets_button( $button_text = 'Book now' ) {

	$output				= NULL;

	$event_status		= esc_html( get_post_meta( get_the_ID(), 'event_status', true ) );
	$ticket_status		= esc_html( get_post_meta( get_the_ID(), 'event_ticket_status', true ) );
	$link				= esc_url( get_post_meta( get_the_ID(), 'event_ticket_link', true ) );
	$onsale				= strtotime( esc_html( get_post_meta( get_the_ID(), 'event_ticket_onsale_date', true ) ) );

	if ( $link && $event_status === 'scheduled' ) {

		if ( $ticket_status === 'in-stock' || $ticket_status === 'pre-order' ) {

			// If the on-sale date is not set, or it is set and the on-sale date is in the past
			if ( ! $onsale || ( $onsale && $onsale < time() ) ) {
				$output .= '<a class="primary-button" href="' . $link . '" target="_blank" rel="noopener">' . $button_text . '</a>';
			}
		}
	}

	return $output;
}

//=======================================================================================================================================================

/**
 * orgnk_events_entry_first_date_badge()
 * Creates a small badge with an abbreviation of the first date, for example: Dec 02
 */
function orgnk_events_entry_first_date_badge() {

	$output 				= NULL;
	$first_date        		= strtotime( esc_html( get_post_meta( get_the_ID(), 'event_dates_0_start', true ) ) );

	if ( $first_date ) {

		$output .= '<div class="event-start-badge">';
		$output .= '<span class="month">' . date( 'M', $first_date ) . '</span>';
		$output .= '<span class="day">' . date( 'd', $first_date ) . '</span>';
		$output .= '</div>';
	}

	return $output;
}

//=======================================================================================================================================================

/**
 * orgnk_events_entry_meta_table()
 * Generates a table of the event's details
 */
function orgnk_events_entry_meta_table( $heading_text = 'Event details', $heading_size = 'h3' ) {

	$output = NULL;

	// Variables
	$dates             		= esc_html( get_post_meta( get_the_ID(), 'event_dates', true ) );
	$type                   = esc_html( get_post_meta( get_the_ID(), 'event_type', true ) );
	$venue_id               = esc_html( get_post_meta( get_the_ID(), 'event_venue', true ) );
	$virtual_location       = esc_url( get_post_meta( get_the_ID(), 'event_virtual_location', true ) );
	$organiser              = esc_html( get_post_meta( get_the_ID(), 'event_organiser', true ) );
	$organiser_link         = esc_url( get_post_meta( get_the_ID(), 'event_organiser_link', true ) );
	$notes			        = esc_html( get_post_meta( get_the_ID(), 'event_notes', true ) );
	$toggle_ticketing       = esc_html( get_post_meta( get_the_ID(), 'event_ticket_toggle', true ) );
	$tickets	            = esc_html( get_post_meta( get_the_ID(), 'event_ticket_types', true ) );

	if ( $dates ) {

		$output .= '<div class="entry-meta entry-meta-table event-entry-meta">';

			$output .= '<div class="meta-table-header">';
			$output .= '<span class="title ' . $heading_size . '">' . $heading_text . '</span>';
			$output .= '</div>';

			$output .= '<div class="meta-table-wrap">';

				$output .= '<div class="meta-group dates">';

					$output .= '<div class="group-label">';
						$output .= '<i class="icon dates"></i>';
						$output .= '<span class="label">';
						$output .= ( $dates > 1 ) ? 'Dates' : 'Date';
						$output .= '</span>';
					$output .= '</div>';

					$output .= '<div class="group-content">';
						$output .= orgnk_events_entry_schedule();
					$output .= '</div>';

				$output .= '</div>';

				if ( ( $type === 'offline' || $type === 'mixed' ) && $venue_id ) {

					$output .= '<div class="meta-group venue">';

						$output .= '<div class="group-label">';
							$output .= '<i class="icon venue"></i>';
							$output .= '<span class="label">Venue</span>';
						$output .= '</div>';

						$output .= '<div class="group-content">';
							$output .= orgnk_events_entry_venue();
						$output .= '</div>';

					$output .= '</div>';
				}

				if ( ( $type === 'online' || $type === 'mixed' ) && $virtual_location ) {
					$output .= '<div class="meta-group virtual-location">';

						$output .= '<div class="group-label">';
							$output .= '<i class="icon virtual-location"></i>';
							$output .= '<span class="label">Watch online</span>';
						$output .= '</div>';

						$output .= '<div class="group-content">';
							$output .= '<a class="event-url" href="' . $virtual_location . '" target="_blank" rel="noopener">' . $virtual_location . '</a>';
						$output .= '</div>';

					$output .= '</div>';
				}

				if ( $toggle_ticketing && $tickets ) {

					$output .= '<div class="meta-group tickets">';

						$output .= '<div class="group-label">';
							$output .= '<i class="icon tickets"></i>';
							$output .= '<span class="label">Tickets</span>';
						$output .= '</div>';

						$output .= '<div class="group-content">';
							$output .= orgnk_events_entry_ticket_types();
						$output .= '</div>';

					$output .= '</div>';
				}

				if ( $organiser ) {
					$output .= '<div class="meta-group organiser">';

						$output .= '<div class="group-label">';
							$output .= '<i class="icon organiser"></i>';
							$output .= '<span class="label">Organiser</span>';
						$output .= '</div>';

						$output .= '<div class="group-content">';
							if ( $organiser_link ) {
								$output .= '<a class="organiser-name organiser-link" href="' . $organiser_link . '" target="_blank" rel="noopener">' . $organiser . '</a>';
							} else {
								$output .= '<span class="organiser-name">' . $organiser . '</span>';
							}
						$output .= '</div>';

					$output .= '</div>';
				}

				if ( $notes ) {

					$output .= '<div class="meta-group notes">';

						$output .= '<div class="group-label">';
							$output .= '<i class="icon notes"></i>';
							$output .= '<span class="label">Notes</span>';
						$output .= '</div>';

						$output .= '<div class="group-content">';
							$output .= $notes;
						$output .= '</div>';

					$output .= '</div>';
				}

			$output .= '</div>';
		$output .= '</div>';
	}

	return $output;
}
