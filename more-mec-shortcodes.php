<?php
/*
Plugin Name: More MEC Shortocdes
Plugin URI: https://github.com/susanwrotethis/more-mec-shortcodes
GitHub Plugin URI: https://github.com/susanwrotethis/more-mec-shortcodes
Description: Extends Modern Events Calendar Lite by adding shortcodes to display an events list in a non-caledar format. The shortcode is for the specific case of classes or activities posted as recurring events, one or more days a week over a series of several weeks.
Version: 1.2
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
			return __( 'midnight', 'swt-mec' );
		case 43200:
			return __( 'noon', 'swt-mec' );
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
	
	$conj = __( ' and ', 'swt-mec' );
	$days = $repeat['certain_weekdays'];
	array_walk( $days, 'swt_mec_set_dow_name' );
	
	switch( count($days) ) {
		
		case 1:
			return $days[0];
			
		case 2:
			return $days[0].$conj.$days[1];
			
		default:
			$last = array_pop( $days );
			$all = implode( ', ', $days );
			return $all.$conj.$last;
			
	} // End switch
}

// Calculate end date for recurring sessions
function swt_mec_format_recurring( $date, $repeat )
{
	$initial = strtotime( $date );

	// Return the start date for one-time recurring?
	$start = date( 'F j', $initial );
	if ( '1' == $repeat ) {
		return $start;
	}
	
	$final = $initial+(($repeat-1)*604800); // add on # of weeks in seconds
	$end = date( 'F j', $final );
	
	return $start.'-'.$end;
}

// SHORTCODE EVENTS LIST FORMAT FUNCTIONS BEGIN HERE /////////////////////////////////////
// Concat and return the event data in the default style.
function swt_mec_default_style( $data )
{
	$for = __( 'for', 'swt-mec' );
	$weeks = __( 'weeks', 'swt-mec' );
	$week = __( 'week', 'swt-mec' );
	$btn = __( 'Sign Up', 'swt-mec' );
	
	$w = ( $data['weeks'] == '1' ? $week : $weeks );
	
	$a = sprintf( '<h3>%s</h3>', $data['title'] );
	$b = sprintf( '<div class="event-session in-%s">', $data['cslug'] );
	$c = sprintf( '<div class="event-details"><p>%1$s, %2$s, %3$s', $data['days'], $data['times'], $data['loc'] );
	$d = sprintf( '%s<br />', $data['addr'] );
	$e = sprintf( '%1$s %2$s %3$s %4$s ', $data['cost'], $for, $data['weeks'], $w );
	$f = sprintf( '(%s)</p></div>', $data['dates'] );
	$g = sprintf( '<div class="event-signup"><a class="gobutton" href="%s">', $data['href'] );
	$h = sprintf( '%1$s<span class="screen-reader-text">%2$s %3$s</span></a></div>', $btn, $for, $data['title'] );
	return apply_filters( 'swt_mec_default_style', $a.$b.$c.$d.$e.$f.$g.$h."</div>\n", $data );
}

// Concat and return the event data in the basic list style.
function swt_mec_list_style( $data )
{
	$for = __( 'for', 'swt-mec' );
	$weeks = __( 'weeks', 'swt-mec' );
	$week = __( 'week', 'swt-mec' );
	$btn = __( 'Sign Up', 'swt-mec' );
	
	$w = ( $data['weeks'] == '1' ? $week : $weeks );
	
	$a = sprintf( '<li class="event-session in-%s">', $data['cslug'] );
	$b = sprintf( '<p class="event-list-title"><strong>%s</strong><br />', $data['title'] );
	$c = sprintf( '%1$s, %2$s, %3$s', $data['days'], $data['times'], $data['loc'] );
	$d = sprintf( '%s<br />', $data['addr'] );
	$e = sprintf( '%1$s %2$s %3$s %4$s ', $data['cost'], $for, $data['weeks'], $w );
	$f = sprintf( '(%s)</p>', $data['dates'] );
	$g = sprintf( '<div class="event-signup"><a class="gobutton" href="%s">', $data['href'] );
	$h = sprintf( '%1$s<span class="screen-reader-text">%2$s %3$s</span></a></div>', $btn, $for, $data['title'] );
	return apply_filters( 'swt_mec_list_style', $a.$b.$c.$d.$e.$f.$g.$h."</li>\n", $data );
}

// SHORTCODE PROCESSING FUNCTION BEGINS HERE /////////////////////////////////////////////
// Get format argument from wrapper function and assemble the list.
function swt_mec_list_events( $format = 'default' )
{
	// Setup
	global $post;
	
	$format = ( $format == 'list' ? 'list' : 'default' );
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
				
				// Set the array to collect formatted event data
				$data = array();
				
				// Category slug and event title
				$data['term'] = $term;
				$data['cslug'] = $c_slug;
				$data['title'] = get_the_title();
				
				// Days of event, start and end times of sesson
				$repeat = maybe_unserialize( $post->mec_repeat );
				$data['days'] = swt_mec_parse_days( $repeat );
				$data['times'] = str_replace( ':00', '', swt_mec_start_end_times( $post ) );
				
				// Event location (a taxonomy term) and address
				$location = get_term_by( 'id', $post->mec_location_id, 'mec_location' );
				$data['loc'] = trim( $location->name );
				$data['addr'] = '';
				if ( $address = get_term_meta( $location->term_id, 'address', true ) ) {
					$data['addr'] = ', '.trim( $address );
				}
				
				// Cost, number of sessions, start and end dates, signup URL
				$data['cost'] = trim( $post->mec_cost );
				$data['weeks'] = $repeat['end_at_occurrences'];
				$start = trim( $post->mec_start_date );
				$data['dates'] = swt_mec_format_recurring( $start, $repeat['end_at_occurrences'] );
				$data['href'] = trim( $post->mec_read_more );
             
            	// Format data and pass to loop
            	switch($format) {
            		case 'list':
            			$i_loop .= swt_mec_list_style( $data );
            			break;
            		default:
            			$i_loop .= swt_mec_default_style( $data );
            	} // End switch
            		
        	endforeach; // Next post
        	
        		// Pass section info and entries to outer loop
            	switch($format) {
            		case 'list':
            			$o_loop .= $section.'<ul class="event-list">'.$i_loop.'</ul>';
            			break;
            		default:
            			$o_loop .= $section.$i_loop;
            	} // End switch
        	
        	// Reset the query
        	wp_reset_postdata();
        	
        endif; // End check for posts
	endforeach; // Next term
	
	return $o_loop;
}

// SHORTCODE FUNCTIONS BEGIN HERE ////////////////////////////////////////////////////////
// Returns the series of events formatted in div tags.
function swt_mec_default_format()
{
	return swt_mec_list_events( 'default' );
}
add_shortcode( 'custom-event-list', 'swt_mec_default_format');

// Returns the series of events as unordered lists for each category.
function swt_mec_list_format()
{
	return swt_mec_list_events( 'list' );
}
add_shortcode( 'custom-event-ul', 'swt_mec_list_format');