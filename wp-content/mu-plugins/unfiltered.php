<?php
/**
 * Plugin Name: Unfiltered HTML access modification
 **/

function allow_unfiltered_html_multisite( $caps, $cap, $user_id ) {	
	if ( 'unfiltered_html' === $cap && user_can( $user_id, 'editor' ) ) {
		$caps = array( 'unfiltered_html' );
	}
	else if ( 'unfiltered_html' === $cap && user_can( $user_id, 'administrator' ) ) {
		$caps = array( 'unfiltered_html' );
	}

	return $caps;
}

add_filter( 'map_meta_cap', 'allow_unfiltered_html_multisite', 10, 3 );
