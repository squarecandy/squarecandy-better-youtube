<?php
/*
Plugin Name: Square Candy Better YouTube
Plugin URI: https://github.com/squarecandy/squarecandy-better-youtube
GitHub Plugin URI: https://github.com/squarecandy/squarecandy-better-youtube
Description: A plugin to improve the look and behavior of YouTube videos on WordPress
Version: 1.1.3
Author: Peter Wise
Author URI: http://squarecandydesign.com
Text Domain: squarecandy-better-youtube
*/

// Enqueue scripts required
if ( ! function_exists( 'squarecandy_video_scripts' ) ) :
	function squarecandy_video_scripts() {
		// apply fitvids
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'squarecandy-fitvids', plugins_url('/jquery.fitvids.js', __FILE__), array('jquery'), '20170517', true );
		wp_enqueue_script( 'squarecandy-better-youtube', plugins_url('/better-youtube.js', __FILE__), array('jquery', 'squarecandy-fitvids'), '20170517', true );

		wp_enqueue_script( 'squarecandy-magnific-popup', plugins_url('/vendor/jquery.magnific-popup.min.js', __FILE__), array('jquery'), '20170517', true );
		wp_enqueue_style( 'squarecandy-magnific-popup-style', plugins_url('/vendor/magnific-popup.css', __FILE__) );

		wp_enqueue_style( 'squarecandy-better-youtube-css', plugins_url('/better-youtube.css', __FILE__) );
	}
endif;
add_action( 'wp_enqueue_scripts', 'squarecandy_video_scripts' );

// use to wrap youtube iframes anywhere in the code for nicer output
if ( !function_exists('better_youtube_iframe') ) :
	function better_youtube_iframe($iframe) {
		// use preg_match to find iframe src
		preg_match('/src="(.+?)"/', $iframe, $matches);
		$src = $matches[1];

		$playlist = explode('list=',$src);
		if ( !isset($playlist[1]) ) {
			$playlist = false;
		}
		else {
			$playlist = explode('&',$playlist[1]);
			$playlist = $playlist[0];
		}

		if ( isset($playlist) && !empty($playlist) && defined('YOUTUBE_API_KEY') ) {

			// Google API for building custom YouTube Playlists
			require_once plugin_dir_path(__FILE__) . 'vendor/google-api-php-client/vendor/autoload.php';
			try {
				$client = new Google_Client();
				$client->setDeveloperKey(YOUTUBE_API_KEY);
				$client->setScopes('https://www.googleapis.com/auth/youtube');
				$redirect = filter_var('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'], FILTER_SANITIZE_URL);
				$client->setRedirectUri($redirect);

				// Define an object that will be used to make all API requests.
				$service = new Google_Service_YouTube($client);

				$params = array('maxResults' => 49, 'playlistId' => $playlist);
				$params = array_filter($params);
				$response = $service->playlistItems->listPlaylistItems('snippet', $params);

				if (isset($response->items[0]->snippet->thumbnails->standard->url)) {
					$large_thumb = $response->items[0]->snippet->thumbnails->standard->url;
				}
				elseif (isset($response->items[0]->snippet->thumbnails->high->url)) {
					$large_thumb = $response->items[0]->snippet->thumbnails->high->url;
				}
				elseif (isset($response->items[0]->snippet->thumbnails->medium->url)) {
					$large_thumb = $response->items[0]->snippet->thumbnails->medium->url;
				}

				$link = 'https://www.youtube.com/embed/' .
					$response->items[0]->snippet->resourceId->videoId .
					'?feature=oembed&amp;rel=0&amp;' .
					'controls=1&amp;modestbranding=1&amp;' .
					'hd=1&amp;autoplay=1';

				$output = '<div class="custom-playlist">';

				$output .= '<div class="playlist-preview-first"><a href="' . $link . '">
					<div class="playlist-thumb" style="background-image:url(' .
						$large_thumb . ')"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path d="M549.655 124.083c-6.281-23.65-24.787-42.276-48.284-48.597C458.781 64 288 64 288 64S117.22 64 74.629 75.486c-23.497 6.322-42.003 24.947-48.284 48.597-11.412 42.867-11.412 132.305-11.412 132.305s0 89.438 11.412 132.305c6.281 23.65 24.787 41.5 48.284 47.821C117.22 448 288 448 288 448s170.78 0 213.371-11.486c23.497-6.321 42.003-24.171 48.284-47.821 11.412-42.867 11.412-132.305 11.412-132.305s0-89.438-11.412-132.305zm-317.51 213.508V175.185l142.739 81.205-142.739 81.201z"/></svg></div>
					</a></div>';

				$output .= '<div class="playlist-list"><ul>';

				$i = 1;
				foreach ($response->items as $item) {

					$link = 'https://www.youtube.com/embed/' .
						$item->snippet->resourceId->videoId .
						'?feature=oembed&amp;rel=0&amp;' .
						'controls=1&amp;modestbranding=1&amp;' .
						'hd=1&amp;autoplay=1';

					if ( isset($item->snippet->thumbnails) ) {
						$small_thumb = $item->snippet->thumbnails->default->url;

						if (isset($item->snippet->thumbnails->standard->url)) {
							$large_thumb = $item->snippet->thumbnails->standard->url;
						}
						elseif (isset($item->snippet->thumbnails->high->url)) {
							$large_thumb = $item->snippet->thumbnails->high->url;
						}
						elseif (isset($item->snippet->thumbnails->medium->url)) {
							$large_thumb = $item->snippet->thumbnails->medium->url;
						}
					} else {
						$small_thumb = false;
						$large_thumb = false;
					}

					$output .= '<li><a href="' . $link . '" ' .
						'rel="videogroup_' . $playlist[1] . '" ' .
						'title="' . esc_attr($item->snippet->title) . '" ' .
						'data-large-thumb="' . $large_thumb . '">' .
						'<div class="playlist-small-thumb"><div class="playlist-thumb" style="background-image:url(' .
						$small_thumb . ')"></div></div>' .
						'<div class="playlist-num">' . $i . '</div>' .
						'<div class="playlist-item-title">' . $item->snippet->title . '</div>' .
						'</a></li>';

					$i++;
				}
				$output .= '</ul></div>';
				$output .= '</div>';
			} catch (Exception $e) {
				$error = json_decode($e->getMessage());
				$output = '<div class="error">Error: <br>' .  $error->error->message . '</div>';
			}
			return shortcode_unautop($output);
		}
		else {
			// add extra params to iframe src
			$params = array(
				'rel' => 0,
				'controls' => 1,
				'modestbranding' => 1,
				'hd' => 1
			);
			$new_src = add_query_arg($params, $src);
			$iframe = str_replace($src, $new_src, $iframe);
			$iframe = str_replace('allow="autoplay; encrypted-media"', '', $iframe);
			$iframe = str_replace('frameborder="0"', '', $iframe);
			$iframe = shortcode_unautop($iframe);
			// $iframe = '<div class="fitvids">' . $iframe . '</div>';
			return $iframe;
		}
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
	add_filter('oembed_result', 'squarecandy_custom_youtube_querystring', 99, 3);
	add_filter('embed_oembed_html', 'squarecandy_custom_youtube_querystring', 99, 3);
endif;


// fix wpautop crap
// https://wordpress.stackexchange.com/a/217304/41488
add_filter('the_content','shortcode_fix',110);
function shortcode_fix($content) {
	return str_replace('<p></a></li>','</a></li>',$content);
}
