<?php
/*
Plugin Name: More MEC Shortocdes
Plugin URI: https://github.com/susanwrotethis/more-mec-shortcodes
GitHub Plugin URI: https://github.com/susanwrotethis/more-mec-shortcodes
Description: Extends Modern Events Calendar Lite by adding a shortcode to display an events list in a non-caledar format. The shortcode is for the specific case of classes or activities posted as recurring events, one or more days a week over a series of several weeks.
Version: 1.1
Author: Susan Walker
Author URI: https://susanwrotethis.com
License: GPL v2 or later
Text Domain: swt-mec
Domain Path: /lang/
*/

// Exit if loaded from outside of WP
if ( !defined( 'ABSPATH' ) ) exit;

// LANGUAGE SUPPORT SETUP BEGINS HERE ////////////////////////////////////////////////////
// Load plugin textdomain
function swt_mec_load_textdomain()
{
	load_plugin_textdomain( 'swt-mec', false, dirname( plugin_basename( __FILE__ ) ).'/lang/' );
}
add_action( 'plugins_loaded', 'swt_mec_load_textdomain' );

// POST TYPE AND TAXONOMY EXTENSION FUNCTIONS BEGIN HERE /////////////////////////////////
// Add page attributes to Modern Events Calendar events so menu order can be set.
function swt_mec_menu_order() {
	add_post_type_support( 'mec-events', 'page-attributes' );
}
add_action( 'init', 'swt_mec_menu_order' );

// SHORTCODE CONTENT FORMATTING FUNCTIONS BEGIN HERE /////////////////////////////////////
// Set string value of 'noon' or 'midnight' if the time in seconds matches.
function swt_mec_set_noon_midnight( $seconds )
{
	switch( $seconds ) {
		case 0:
			return 'midnight';
		case 43200:
			return 'noon';
		default:
			return date( 'g:i a', $seconds );
	}
}

// Format the start and end times of an event as a strng.
function swt_mec_start_end_times( $event )
{
	$start = (int)$event->mec_start_day_seconds;
	$end = (int)$event->mec_end_day_seconds;
	
	// Correct for midnight. This plugin sets the value in seconds at the end of the day.
	$start = ( $start == 86400 ? 0 : $start );
	$end = ( $end == 86400 ? 0 : $end );
	
	// Both times are after noon
	if ( $start > 43200 && $end > 43200 ) {
		return sprintf( date( 'g:i', $start ).'-'.date( 'g:i a', $end ), $start, $end );
	}
	
	// Both times are between midnight and 11:59 am
	else if ( $start > 0 && $start < 43200 && $end > 0 && $end < 43200 ) {
		return sprintf( date( 'g:i', $start ).'-'.date( 'g:i a', $end ), $start, $end );
	}
	
	// Mixed tmes with custom formatting
	else {
		return swt_mec_set_noon_midnight( $start ).'-'.swt_mec_set_noon_midnight( $end );
	}
}

// Change integer to day of week
function swt_mec_set_dow_name( &$item, $key )
{
	$dow = array( 
		__( 'Sundays', 'swt-mec' ), 
		__( 'Mondays', 'swt-mec' ), 
		__( 'Tuesdays', 'swt-mec' ), 
		__( 'Wednesdays', 'swt-mec' ), 
		__( 'Thursdays', 'swt-mec' ), 
		__( 'Fridays', 'swt-mec' ), 
		__( 'Saturdays', 'swt-mec' ),
		__( 'Sundays', 'swt-mec' ) // Correct for MEC setting Sunday value as 7 
	);
	$item = $dow[$item];
}

// Parse out the weekdays and return as string.
function swt_mec_parse_days( $repeat )
{
	if ( !isset( $repeat['type'] ) || 'certain_weekdays' != $repeat['type'] ) {
		return '';
	}
	
	$days = $repeat['certain_weekdays'];
	array_walk( $days, 'swt_mec_set_dow_name' );
	
	switch( count($days) ) {
		
		case 1:
			return $days[0].', ';
			
		case 2:
			return $days[0].' and '.$days[1].', ';
			
		default:
			$last = array_pop( $days );
			$all = implode( ', ', $days );
			return $all.' and '.$last.', ';
			
	} // End switch
}

// Calculate end date for recurring sessions
function swt_mec_format_recurring( $date, $repeat )
{
	$initial = strtotime( $date );

	$start = date( 'F j', $initial );
	$final = $initial+(($repeat-1)*604800); // add on # of weeks in seconds
	$end = date( 'F j', $final );
	
	return '('.$start.'-'.$end.')';
}

// SHORTCODE FUNCTIONS BEGIN HERE ////////////////////////////////////////////////////////
// Shortcode to list events by category.
function swt_mec_list_events( $atts, $content=null )
{
	global $post;
	
	$o_loop = ''; // Outer loop contains full string to be returned
	
	// Get event category terms
	$terms = get_terms( array(
    	'taxonomy' => 'mec_category',
    	'orderby' => 'slug',
	) );
	
	// Exit if no terms found
	if ( !$terms ) {
		return '';
	}

	// Loop through terms
	foreach( $terms as $term ):
	
		$term_id = $term->term_id;
	
		// Set up the query and get posts for current term
		$args = array(
			'post_type'			=> 'mec-events',
			'tax_query' => array(
    			array(
					'taxonomy' => 'mec_category',
					'field'    => 'term_id',
					'terms'    => $term->term_id
				) ),
			'orderby'			=> 'menu_order',
			'order'				=> 'ASC',
		);
		$events = get_posts( $args );
		
		// Output something for the term only if posts found
		if ( $events ):
		
			// Concat term as header and description if found
			$c_slug = $term->slug;
			$section = '<h2 class="cat-'.$c_slug.'">'.trim( $term->name )."</h2>\n";
		
			if ( $desc = trim( term_description( $term_id ) ) ) { 
				$section .= '<div class="session-cat-desc">'.$desc."</div>\n";
			}
			
			$i_loop = ''; // Inner loop contains events by category
		
			// Loop through posts for term
			foreach ( $events as $post ): 
			
				setup_postdata( $post );
				
				// Event name
				$title = get_the_title();
				$string = '<h3>'.$title.'</h3><div class="event-session in-'.$c_slug.'">';
				
				// Days of event
				$repeat = maybe_unserialize( $post->mec_repeat );
				$string .= '<div class="event-details"><p>'.swt_mec_parse_days( $repeat );
				
				// Start and end times of session
				$string .= str_replace( ':00', '', swt_mec_start_end_times( $post ) ).', ';
				
				// Event location (a taxonomy term)
				$location = get_term_by( 'id', $post->mec_location_id, 'mec_location' );
				$string .= trim( $location->name );
				
				// Address (term meta)
				if ( $address = get_term_meta( $location->term_id, 'address', true ) ) {
					$string .= ', '.trim( $address );
				}
				$string .= '<br />';
				
				// Cost
				$string .= trim( $post->mec_cost ).' for ';
				
				// Number of sessions for this class
				$string .= $repeat['end_at_occurrences'].' weeks ';
				
				// concat the start and end dates
				$start = trim( $post->mec_start_date );
				$string .= swt_mec_format_recurring( $start, $repeat['end_at_occurrences'] );
				
				// Escape string built so far
				//$string = esc_attr( $string );
				$button_text = __( 'Sign Up', 'swt-mec' );
				
				// Signup URL with button class
				$string .= '</p></div><div class="event-signup"><a class="gobutton"';
				$string .= ' href="'.urlencode( trim( $post->mec_read_more ) ).'">';
				$string .= $button_text.'<span class="screen-reader-text"> ';
				$string .= sprintf( __('for %s', 'swt-mec' ), $title );
				$string .= "</span></a></div></div>\n";
             
            	// Pass this iteration's string before resetting for next
            	$i_loop .= $string;
            	
        	endforeach; // Next post
        	
        		// Concat section info and related iterations
        		$o_loop .= $section.$i_loop;
        	
        	// Reset the query
        	wp_reset_postdata();
        	
        endif; // End check for posts
	endforeach; // Next term
	
	return $o_loop;
}
add_shortcode( 'custom-event-list', 'swt_mec_list_events');