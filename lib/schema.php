<?php
/**
 * orgnk_single_event_schema()
 * Generates the single event schema script for outputting in the document head
 */
function orgnk_single_event_schema() {

    $schema = NULL;
    $sub_schema = array();

    if ( is_singular( ORGNK_EVENTS_CPT_NAME ) ) {

        // Event variables
        $featured_image         = esc_url( get_the_post_thumbnail_url( get_the_ID(), 'full' ) );
        $description            = esc_html( get_post_meta( get_the_ID(), 'entry_subtitle', true ) );
        $date_count             = esc_html( get_post_meta( get_the_ID(), 'event_dates', true ) );
        $type                   = esc_html( get_post_meta( get_the_ID(), 'event_type', true ) );
        $venue_id               = esc_html( get_post_meta( get_the_ID(), 'event_venue', true ) );
        $virtual_location       = esc_url( get_post_meta( get_the_ID(), 'event_virtual_location', true ) );
        $organiser              = esc_html( get_post_meta( get_the_ID(), 'event_organiser', true ) );
        $organiser_link         = esc_url( get_post_meta( get_the_ID(), 'event_organiser_link', true ) );
        $status                 = esc_html( get_post_meta( get_the_ID(), 'event_status', true ) );
        $toggle_ticketing       = esc_html( get_post_meta( get_the_ID(), 'event_ticket_toggle', true ) );
        $ticket_status          = esc_html( get_post_meta( get_the_ID(), 'event_ticket_status', true ) );
        $ticket_link            = esc_url( get_post_meta( get_the_ID(), 'event_ticket_link', true ) );
        $ticket_onsale_date     = esc_html( get_post_meta( get_the_ID(), 'event_ticket_onsale_date', true ) );
        $ticket_currency        = esc_html( get_post_meta( get_the_ID(), 'event_ticket_currency', true ) );
        $ticket_count           = esc_html( get_post_meta( get_the_ID(), 'event_ticket_types', true ) );

        // Check there is at least 1 date
        if ( $date_count ) {

            // Loop through repeaters
            for ( $i = 0; $i < $date_count; $i++ ) {

                $event_start            = esc_html( get_post_meta( get_the_ID(), 'event_dates_' . $i . '_start', true ) );
                $event_end              = esc_html( get_post_meta( get_the_ID(), 'event_dates_' . $i . '_end', true ) );

                // First, check minimum required parameters to generate schema for each date
                if ( $event_start && ( $venue_id || $virtual_location ) ) {

                    // Setup the bare minimum sub schema details
                    $event_schema = array(
                        '@type'     		=> 'Event',
                        'name' 				=> esc_html( get_the_title() ),
                        'startDate'         => (new DateTime( $event_start ) )->format('c') // ISO-8601 date/time format
                    );

                    if ( $event_end ) {
                        $event_schema['endDate'] = (new DateTime( $event_end ) )->format('c'); // ISO-8601 date/time format
                    }

                    if ( $status ) {
                        if ( $status === 'scheduled' ) {
                            $event_schema['eventStatus'] = 'https://schema.org/EventScheduled';
                        } elseif ( $status === 'postponed' ) {
                            $event_schema['eventStatus'] = 'https://schema.org/EventPostponed';
                        } elseif ( $status === 'cancelled' ) {
                            $event_schema['eventStatus'] = 'https://schema.org/EventCancelled';
                        }
                    }

                    // Setup venue schema for use later
                    if ( $venue_id ) {

                        // Venue variables
                        $street_address         = esc_html( get_post_meta( $venue_id, 'venue_street_address', true ) );
                        $suburb                 = esc_html( get_post_meta( $venue_id, 'venue_suburb', true ) );
                        $post_code              = esc_html( get_post_meta( $venue_id, 'venue_post_code', true ) );
                        $region                 = esc_html( get_post_meta( $venue_id, 'venue_region', true ) );
                        $country                = esc_html( get_post_meta( $venue_id, 'venue_country', true ) );

                        $venue_schema = array(
                            '@type'				=> 'Place',
                            'name'              => esc_html( get_the_title( $venue_id ) ),
                            'address'           => array(
                                '@type'             => 'PostalAddress',
                                'streetAddress'     => $street_address,
                                'addressLocality'   => $suburb,
                                'postalCode'        => $post_code,
                                'addressRegion'     => $region,
                                'addressRCountry'   => $country
                            )
                        );
                    }

                    // Setup virtual location schema for use later
                    if ( $virtual_location ) {
                        $virtual_location_schema = array(
                            '@type'				=> 'VirtualLocation',
                            'url'              => $virtual_location,
                        );
                    }

                    if ( $type ) {
                        if ( $type === 'offline' ) {

                            $event_schema['eventAttendanceMode'] = 'https://schema.org/OfflineEventAttendanceMode';

                            if ( $venue_schema ) {
                                $event_schema ['location'] = $venue_schema;
                            }

                        } elseif ( $type === 'online' ) {

                            $event_schema['eventAttendanceMode'] = 'https://schema.org/OfflineEventAttendanceMode';

                            if ( $virtual_location_schema ) {
                                $event_schema ['location'] = $virtual_location_schema;
                            }
                            
                        }  elseif ( $type === 'mixed' ) {
                            $event_schema['eventAttendanceMode'] = 'https://schema.org/MixedEventAttendanceMode';

                            if ( $venue_schema && $virtual_location_schema ) {
                                $event_schema ['location'] = array( $venue_schema, $virtual_location_schema );
                            }
                        }
                    }

                    if ( $featured_image ) {
                        $event_schema['image'] = $featured_image;
                    }

                    if ( $description ) {
                        $event_schema['description'] = $description;
                    }

                    if ( $toggle_ticketing && $ticket_count ) {

                        // Setup an empty array to store multiple offers
                        $event_schema['offers'] = array();

                        for ( $a = 0; $a < $ticket_count; $a++ ) {

                            // Variables
                            $ticket_type		    = esc_html( get_post_meta( get_the_ID(), 'event_ticket_types_' . $a . '_type', true ) );
                            $ticket_price           = esc_html( get_post_meta( get_the_ID(), 'event_ticket_types_' . $a . '_price', true ) );
                            $ticket_price           = preg_replace( '/[^0-9.]/', '', $ticket_price ); // Strip non numerical characters

                            if ( $ticket_type && $ticket_price ) {

                                $offer_schema = array(
                                    '@type'				=> 'Offer'
                                );

                                if ( $ticket_type ) {
                                    $offer_schema['name'] = $ticket_type;
                                }

                                if ( $ticket_price ) {
                                    $offer_schema['price'] = $ticket_price;
                                }

                                if ( $ticket_currency ) {
                                    $offer_schema['priceCurrency'] = $ticket_currency;
                                }

                                if ( $ticket_onsale_date ) {
                                    $offer_schema['validFrom'] = (new DateTime( $ticket_onsale_date ) )->format('c'); // ISO-8601 date/time format
                                }

                                if ( $ticket_link ) {
                                    $offer_schema['url'] = $ticket_link;
                                }
        
                                if ( $ticket_status ) {
                                    if ( $ticket_status === 'in-stock' ) {
                                        $offer_schema['availability'] = 'https://schema.org/InStock';
                                    } elseif ( $ticket_status === 'pre-order' ) {
                                        $offer_schema['availability'] = 'https://schema.org/PreOrder';
                                    } elseif ( $ticket_status === 'sold-out' ) {
                                        $offer_schema['availability'] = 'https://schema.org/SoldOut';
                                    }
                                }

                                // If there's more than 1 ticket type, add this type's schema to a multidimensional array
                                // Otherwise, the ticket schema IS the offer schema so override it with the single ticket array
                                if ( $ticket_count > 1 ) {
                                    $event_schema['offers'][] = $offer_schema;
                                } else {
                                    $event_schema['offers'] = $offer_schema;
                                }
                            }
                        }
                    }

                    // If an organiser is provided, use their details
                    if ( $organiser ) {
                        $event_schema['organizer'] = array(
                            '@type'				=> 'Organization',
                            'name'              => $organiser
                        );

                        if ( $organiser_link ) {
                            $event_schema['organizer']['url'] = $organiser_link;
                        }
                    } 
                    
                    // Otherwise the organiser is this website/business so use their website name and home URL
                    else {
                        $event_schema['organizer'] = array(
                            '@type'				=> 'Organization',
                            'name'              => esc_html( get_bloginfo( 'name' ) ),
                            'url'               => esc_url( home_url( '/' ) )
                        );
                    }

                    // If there's more than 1 date, add this date's schema to a multidimensional array
                    // Otherwise, the event schema IS the sub schema so override it with the single date item
                    if ( $date_count > 1 ) {
                        $sub_schema[] = $event_schema;
                    } else {
                        $sub_schema = $event_schema;
                    }
                }
            }
        }

        // Check if anything has been stored for output
		if ( $sub_schema ) {

            $schema = array(
                '@context'  		=> 'http://schema.org'
            );

            if ( $date_count > 1 ) {
                $schema['graph'] = $sub_schema;
            } else {
                $schema = array_merge( $schema, $sub_schema[0] );
            }
        }

        // Finally, check if there is any compiled schema to return
        if ( $schema ) {
            // var_dump( json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) );
            return '<script type="application/ld+json" class="organik-events-schema">' . json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . '</script>';
        }
    }
}
