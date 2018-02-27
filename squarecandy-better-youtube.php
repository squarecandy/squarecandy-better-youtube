<?php
/*
Plugin Name: Square Candy Better YouTube
Plugin URI:  http://squarecandy.net
Description: A plugin to improve the look and behavior of YouTube videos on WordPress
Version:	 0.1
Author:	  Peter Wise
Author URI:  http://squarecandy.net
Text Domain: squarecandy-better-youtube
*/


// Enqueue scripts required
if ( ! function_exists( 'squarecandy_video_scripts' ) ) :
	function squarecandy_video_scripts() {
		// apply fitvids
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'squarecandy-fitvids', plugins_url('/jquery.fitvids.js', __FILE__), array('jquery'), '20170517', true );
		wp_enqueue_script( 'squarecandy-better-youtube', plugins_url('/better-youtube.js', __FILE__), array('jquery', 'squarecandy-fitvids'), '20170517', true );
	}
endif;
add_action( 'wp_enqueue_scripts', 'squarecandy_video_scripts' );

// use to wrap youtube iframes anywhere in the code for nicer output
if ( !function_exists('better_youtube_iframe') ) :
	function better_youtube_iframe($iframe) {
		// use preg_match to find iframe src
		preg_match('/src="(.+?)"/', $iframe, $matches);
		$src = $matches[1];

		// add extra params to iframe src
		$params = array(
			'color' => 'white',
			'theme' => 'dark',
			'rel' => 0,
			'autohide' => 1,
			'showinfo' => 0,
			'controls' => 1,
			'modestbranding' => 1,
			'hd' => 1
		);
		$new_src = add_query_arg($params, $src);
		$iframe = str_replace($src, $new_src, $iframe);

		$iframe = str_replace('allow="autoplay; encrypted-media"', '', $iframe);
		$iframe = str_replace('frameborder="0"', '', $iframe);

		return $iframe;
	}
endif;

// apply the better_youtube_iframe improvements to the automatic WordPress oembed
if ( !function_exists('squarecandy_custom_youtube_querystring') ) :
	function squarecandy_custom_youtube_querystring( $html, $url, $args ) {
		if ( strpos($html, 'youtube') || strpos($html, 'youtu.be') ) {
			$html = better_youtube_iframe($html);
		}
		// apply fitvids responsive video to both youtube and vimeo
		if ( strpos($html, 'youtube') || strpos($html, 'youtu.be') || strpos($html, 'vimeo') ) {
			$html = '<div class="fitvids">' . $html . '</div>';
		}
		return $html;
	}
	add_filter('oembed_result', 'squarecandy_custom_youtube_querystring', 10, 3);
endif;
