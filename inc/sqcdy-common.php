<?php
// PHP code that should be distributed to all themes & plugins goes here.

// for debugging

if ( ! function_exists( 'sqcdy_is_debug' ) ) :
	function sqcdy_is_debug() {
		// allow these debug functions to be used outside of WordPress
		if ( defined( 'WPINC' ) ) {
			$debug_mode = defined( 'WP_DEBUG' ) && WP_DEBUG;
		} else {
			$debug_mode = defined( 'DEBUG' ) && DEBUG;
		}
		return $debug_mode;
	}
endif;

if ( ! function_exists( 'pre_r' ) ) :
	function pre_r( $array ) {
		if ( sqcdy_is_debug() ) {
			print '<pre class="squarecandy-pre-r">';
			print_r( $array ); // phpcs:ignore
			print '</pre>';
		}
	}
endif;

//utility log function to handle arrays, objects etc.
//phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_error_log, WordPress.PHP.DevelopmentFunctions.error_log_print_r
if ( ! function_exists( 'sqcdy_log' ) ) :
	function sqcdy_log( $object, $message = '', $override_debug = false ) {
		$debug_mode = sqcdy_is_debug();
		$is_string  = is_string( $object );
		$is_numeric = is_numeric( $object );

		if ( $debug_mode || $override_debug ) :
			if ( ! is_string( $message ) ) {
				$message = '';
			}
			if ( $message && ( $is_string || $is_numeric ) ) {
				error_log( trim( $message ) . ': ' . trim( $object ) );
				return;
			}
			if ( $message ) {
				error_log( $message . ':' );
			}
			if ( $is_string || $is_numeric ) {
				error_log( $object );
			} else {
				error_log( print_r( $object, true ) );
			}
		endif;
	}
endif;
//phpcs:enable WordPress.PHP.DevelopmentFunctions.error_log_error_log, WordPress.PHP.DevelopmentFunctions.error_log_print_r

// Temporary shim for str_contains until all sites have php updated
// based on original work from the PHP Laravel framework
if ( ! function_exists( 'str_contains' ) ) {
	function str_contains( $haystack, $needle ) {
		return $needle !== '' && mb_strpos( $haystack, $needle ) !== false; // phpcs:ignore WordPress.PHP.YodaConditions.NotYoda
	}
}

// common function to check if our major 2024 accessibility update "views 2" is enabled
if ( ! function_exists( 'sqcdy_is_views2' ) ) :
	function sqcdy_is_views2( $slug = 'my_plugin_or_theme_slug' ) {

		// allow forcing Views 2 across all of our themes and plugins
		// by defining SQCDY_VIEWS2 as true in wp-config.php
		if ( defined( 'SQCDY_VIEWS2' ) && SQCDY_VIEWS2 ) {
			return true;
		}

		// allowing adding an ACF checkbox to enable Views 2 per theme/plugin
		if ( get_option( 'options_' . $slug . '_views2' ) ) {
			return true;
		}

		return false;
	}
endif;

// alternative names for the misleading is_front_page and is_home functions
if ( ! function_exists( 'sqcdy_is_homepage' ) ) :
	function sqcdy_is_homepage() {
		return is_front_page();
	}
endif;

if ( ! function_exists( 'sqcdy_is_blog_home' ) ) :
	function sqcdy_is_blog_home() {
		return is_home();
	}
endif;
