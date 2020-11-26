<?php
//=======================================================================================================================================================
// Event helper & templating functions
//=======================================================================================================================================================

/**
 * orgnk_events_entry_schedule()
 * Lists the event times and dates in a neat format
 * Can be limited to only show the first date for an event by passing $first = true
 */
function orgnk_events_entry_schedule( $first = false, $date_size = false ) {

	$output = '';
	$date_class = ( $date_size ) ? ' ' . $date_size : '';
	$type_class = ( $first ) ? ' simple-schedule' : ' list-schedule';

	// Modify the HTML output if only one date is being printed
	$ul = ( $first === true ) ? 'div' : 'ul';
	$li = ( $first === true ) ? 'span' : 'li';

	// Get dates
	$dates             		= esc_html( get_post_meta( orgnk_get_the_ID(), 'event_dates', true ) );

	if ( $dates ) {

		$output .= '<' . $ul . ' class="event-schedule' . $type_class . '">';

		for ( $i = 0; $i < $dates; $i++ ) {

			// Variables
			$event_start 			= strtotime( esc_html( get_post_meta( orgnk_get_the_ID(), 'event_dates_' . $i . '_start', true ) ) );
			$start_time 			= ( $event_start ) ? date( 'g:i a', $event_start ) : '';
			$start_date 			= ( $event_start ) ? date( 'j F Y', $event_start ) : '';

			$event_end 				= strtotime( esc_html( get_post_meta( orgnk_get_the_ID(), 'event_dates_' . $i . '_end', true ) ) );
			$end_time 				= ( $event_end ) ? date( 'g:i a', $event_end ) : '';
			$end_date 				= ( $event_end ) ? date( 'j F Y', $event_end ) : '';

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
			}

			if ( $first === true ) {
				if ( $dates > 1 ) {
					$output .= '<span class="more-dates">+ more dates</span>';
				}

				// End the loop early to only return the first date
				if ( $i++ == 0 ) break;
			}
		}

		$output .= '</' . $ul . '>';
	}

    return $output;
}




/**
 * orgnk_events_entry_ticket_types()
 * Lists the event ticket types and price in a neat format
 */
function orgnk_events_entry_ticket_types() {

	$output 				= '';
	$tickets	            = esc_html( get_post_meta( orgnk_get_the_ID(), 'event_ticket_types', true ) );

	if ( $tickets ) {

		$output .= '<ul class="event-ticket-types">';

		for ( $i = 0; $i < $tickets; $i++ ) {

			// Variables
			$ticket_type		= esc_html( get_post_meta( orgnk_get_the_ID(), 'event_ticket_types_' . $i . '_type', true ) );
			$ticket_price      	= esc_html( get_post_meta( orgnk_get_the_ID(), 'event_ticket_types_' . $i . '_price', true ) );

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




/**
 * orgnk_events_entry_online_event_badge()
 * Returns a small badge indicating the current event's availability of tickets
 * Returns nothing if the current event's tickets are available, which would be assumed otherwise
 */
function orgnk_events_entry_online_event_badge() {

	$output 				= '';
	$type                   = esc_html( get_post_meta( orgnk_get_the_ID(), 'event_type', true ) );

	if ( $type === 'online' || $type === 'mixed' ) {
		$output .= '<div class="badge virtual-event"><i class="icon"></i>Online event</div>';
	}

	return $output;
}




/**
 * orgnk_events_entry_sale_status_badge()
 * Returns a small badge indicating the current event's status and ticket sale status
 * If the event status if is anything other than 'scheduled', then no ticket sale status badges will show
 * This function will only return nothing if the event status is 'scheduled', the current event's tickets are set as 'available', but the on-sale date is in the future
 */
function orgnk_events_entry_sale_status_badge() {

	$output 				= '';
	$event_status			= esc_html( get_post_meta( orgnk_get_the_ID(), 'event_status', true ) );
	$ticket_status			= esc_html( get_post_meta( orgnk_get_the_ID(), 'event_ticket_status', true ) );
	$onsale          		= strtotime( esc_html( get_post_meta( orgnk_get_the_ID(), 'event_ticket_onsale_date', true ) ) );

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



/**
 * orgnk_events_entry_badge_list()
 * Compiles a list of badges for an event
 */
function orgnk_events_entry_badge_list() {

	$badges = '';
	$output = '';

	if ( orgnk_events_entry_online_event_badge() ) {
		$badges .= orgnk_events_entry_online_event_badge();
	}

	if ( orgnk_events_entry_sale_status_badge() ) {
		$badges .= orgnk_events_entry_sale_status_badge();
	}

	if ( $badges ) {
		$output = '<div class="event-badge-list">' . $badges . '</div>';
	}

	return $output;
}



/**
 * orgnk_events_entry_venue()
 * Returns the events venue details, either in a short format (name, street address and suburb), or in full
 */
function orgnk_events_entry_venue( $short = false ) {

	$output 				= '';
	$type                   = esc_html( get_post_meta( orgnk_get_the_ID(), 'event_type', true ) );
	$venue_id               = esc_html( get_post_meta( orgnk_get_the_ID(), 'event_venue', true ) );

	if ( ( $type === 'offline' || $type === 'mixed' ) && $venue_id ) {

		// Get venue post variables
		$venue_name		        = esc_html( get_the_title( $venue_id ) );
		$venue_address          = esc_html( get_post_meta( $venue_id, 'venue_street_address', true ) );
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




/**
 * orgnk_events_entry_tickets_button()
 * Generates an offsite booking button for an event, if the event's on sale date isn't set or if it's set and in the past
 * Accepts a string for changing the button text
 */
function orgnk_events_entry_tickets_button( $button_text = 'Book now' ) {
	
	$output = '';

	$event_status			= esc_html( get_post_meta( orgnk_get_the_ID(), 'event_status', true ) );
	$ticket_status			= esc_html( get_post_meta( orgnk_get_the_ID(), 'event_ticket_status', true ) );
	$link            		= esc_url( get_post_meta( orgnk_get_the_ID(), 'event_ticket_link', true ) );
	$onsale          		= strtotime( esc_html( get_post_meta( orgnk_get_the_ID(), 'event_ticket_onsale_date', true ) ) );

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




/**
 * orgnk_events_entry_first_date_badge()
 * Creates a small badge with an abbreviation of the first date, for example: Dec 02
 */
function orgnk_events_entry_first_date_badge() {

	$output 				= '';
	$first_date        		= strtotime( esc_html( get_post_meta( orgnk_get_the_ID(), 'event_dates_0_start', true ) ) );

	if ( $first_date ) {
		
		$output .= '<div class="event-start-badge">';
		$output .= '<span class="month">' . date( 'M', $first_date ) . '</span>';
		$output .= '<span class="day">' . date( 'd', $first_date ) . '</span>';
		$output .= '</div>';
	}

	return $output;
}



/**
 * orgnk_events_entry_meta()
 * Generates a full table of the event's details
 */
function orgnk_events_entry_meta( $heading_size = 'h3' ) {

	$output = '';

	// Variables
	$dates             		= esc_html( get_post_meta( orgnk_get_the_ID(), 'event_dates', true ) );
	$type                   = esc_html( get_post_meta( orgnk_get_the_ID(), 'event_type', true ) );
	$venue_id               = esc_html( get_post_meta( orgnk_get_the_ID(), 'event_venue', true ) );
	$virtual_location       = esc_url( get_post_meta( orgnk_get_the_ID(), 'event_virtual_location', true ) );
	$organiser              = esc_html( get_post_meta( orgnk_get_the_ID(), 'event_organiser', true ) );
	$organiser_link         = esc_url( get_post_meta( orgnk_get_the_ID(), 'event_organiser_link', true ) );
	$notes			        = esc_html( get_post_meta( orgnk_get_the_ID(), 'event_notes', true ) );
	$toggle_ticketing       = esc_html( get_post_meta( orgnk_get_the_ID(), 'event_ticket_toggle', true ) );
	$tickets	            = esc_html( get_post_meta( orgnk_get_the_ID(), 'event_ticket_types', true ) );

	if ( $dates ) {

		$output .= '<div class="entry-meta entry-meta-table event-entry-meta">';

			$output .= '<div class="meta-table-header">';
			$output .= '<span class="title ' . $heading_size . '">Event details</span>';
			$output .= '</div>';

			$output .= '<div class="meta-group dates">';

				$output .= '<div class="group-label">';
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
						$output .= '<span class="label">Notes</span>';
					$output .= '</div>';

					$output .= '<div class="group-content">';
						$output .= $notes;
					$output .= '</div>';

				$output .= '</div>';
			}
		$output .= '</div>';
	}
	return $output;
}
