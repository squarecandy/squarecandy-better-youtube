<?php

define( 'SQUARECANDY_BYT_PATH', plugin_dir_path( __FILE__ ) );
define( 'SQUARECANDY_BYT_URL', plugin_dir_url( __FILE__ ) );
define( 'SQUARECANDY_BYT_VERSION', 'version-1.3.1-dev.2' );

// Square Candy Common files
require_once SQUARECANDY_BYT_PATH . '/inc/sqcdy-common.php';
require_once SQUARECANDY_BYT_PATH . '/inc/sqcdy-plugin.php';

// Enqueue scripts required
if ( ! function_exists( 'squarecandy_video_scripts' ) ) :
	function squarecandy_video_scripts() {
		// apply fitvids
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'squarecandy-fitvids', SQUARECANDY_BYT_URL . 'dist/js/vendor/fitvids.min.js', array( 'jquery' ), SQUARECANDY_BYT_VERSION, true );
		wp_enqueue_script( 'squarecandy-better-youtube', SQUARECANDY_BYT_URL . 'dist/js/better-youtube.min.js', array( 'jquery', 'squarecandy-fitvids' ), SQUARECANDY_BYT_VERSION, true );

		wp_enqueue_script( 'squarecandy-magnific-popup', SQUARECANDY_BYT_URL . 'dist/js/vendor/jquery.magnific-popup.min.js', array( 'jquery' ), SQUARECANDY_BYT_VERSION, true );
		wp_enqueue_style( 'squarecandy-magnific-popup-style', SQUARECANDY_BYT_URL . 'dist/css/vendor/magnific-popup.css', array(), SQUARECANDY_BYT_VERSION );

		wp_enqueue_style( 'squarecandy-better-youtube-css', SQUARECANDY_BYT_URL . 'dist/css/better-youtube.min.css', array(), SQUARECANDY_BYT_VERSION );
	}
	add_action( 'wp_enqueue_scripts', 'squarecandy_video_scripts' );
endif;

// Add styles to the TinyMCE editor window to make it look more like your site's front end
function squarecandy_better_youtube_tinymce_styles() {
	add_editor_style( SQUARECANDY_BYT_URL . 'dist/css/better-youtube.min.css' );
}
add_action( 'admin_init', 'squarecandy_better_youtube_tinymce_styles' );

function squarecandy_video_admin_scripts() {
	wp_enqueue_style( 'squarecandy-better-youtube-css', SQUARECANDY_BYT_URL . 'dist/css/better-youtube.min.css', array(), SQUARECANDY_BYT_VERSION );
}
add_action( 'admin_enqueue_scripts', 'squarecandy_video_admin_scripts' );

// override default oEmbed size
function squarecandy_own_embed_size() {
	$width    = (int) get_option( 'sqcdy_theme_colwidth', 620 ) - 20;
	$height   = 0.5625 * $width; // 16:9 aspect ratio
	$settings = array(
		'width'  => $width,
		'height' => $height,
	);
	return $settings;
}
add_filter( 'embed_defaults', 'squarecandy_own_embed_size' );


/*
 * Uses regex turn a YT url into an embed url
 * https://stackoverflow.com/questions/28894116/regex-youtube-url-for-embed-with-or-without-playlist
*/
function better_youtube_get_embed_url( $url ) {
	$regex = '~^(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((?:\w|-){11})(?:&list=(\S+))?$~';
	preg_match( $regex, $url, $matches );
	if ( $matches ) {
		$new_url = 'https://www.youtube.com/embed/' . rawurlencode( $matches[1] );
		return $new_url;
	} else {
		return false;
	}
}


function better_youtube_get_youtube_iframe_from_embed( $embed ) {
	// use preg_match to find iframe src
	preg_match( '/src="(.+?)"/', $embed, $matches );
	$src = isset( $matches[1] ) ? $matches[1] : null;
	return $src;
}


function better_youtube_get_youtube_playlist_from_src( $src ) {
	$playlist = explode( 'list=', $src );
	if ( ! isset( $playlist[1] ) ) {
		$playlist = false;
	} else {
		$playlist = explode( '&', $playlist[1] );
		$playlist = $playlist[0];
	}
	return $playlist;
}


function better_youtube_get_large_youtube_thumbnail( $thumbnails ) {
	if ( isset( $thumbnails->maxres->url ) ) {
		$large_thumb = $thumbnails->maxres->url;
	} elseif ( isset( $thumbnails->standard->url ) ) {
		$large_thumb = $thumbnails->standard->url;
	} elseif ( isset( $thumbnails->high->url ) ) {
		$large_thumb = $thumbnails->high->url;
	} elseif ( isset( $thumbnails->medium->url ) ) {
		$large_thumb = $thumbnails->medium->url;
	} else {
		$large_thumb = false;
	}
	return $large_thumb;
}


/**
 * Output embed url parameters as string or array
 * @param $as_array bool opt default false
 * @param $autoplay bool opt default true
 *
 * @return string|array
 */
function better_youtube_url_parameters( $as_array = false, $autoplay = true ) {
	//previus parameters: '?feature=oembed&rel=0&controls=1&modestbranding=1&hd=1&autoplay=1'
	$parameters  = '?&mute=0&controls=1&playsinline=1&showinfo=0&rel=0&iv_load_policy=3&modestbranding=1';
	$parameters .= '&enablejsapi=1'; // enable js interaction
	if ( $autoplay ) {
		$parameters .= '&autoplay=1';
	} else {
		$parameters .= '&autoplay=0';
	}

	if ( $as_array ) {
		$output     = array();
		$parameters = trim( $parameters, '?' );
		$parameters = explode( '&', $parameters );

		foreach ( $parameters as $parameter ) {
			$values = explode( '=', $parameter );
			if ( isset( $values[0] ) && isset( $values[1] ) ) {
				$output[ $values[0] ] = $values[1];
			}
		}
		$parameters = $output;
	}

	return $parameters;
}

function better_youtube_api_playlist( $input ) {

	$playlist = better_youtube_get_youtube_playlist_from_src( $input );

	if ( ! $playlist || ! defined( 'YOUTUBE_API_KEY' ) ) {
		return false;
	}

	$post_id = get_the_id();

	// Google API for building custom YouTube Playlists
	require_once SQUARECANDY_BYT_PATH . 'vendor/autoload.php';
	try {
		$client = new Google_Client();
		$client->setDeveloperKey( YOUTUBE_API_KEY );
		$client->setScopes( 'https://www.googleapis.com/auth/youtube' );
		$redirect = filter_var( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'], FILTER_SANITIZE_URL );
		$client->setRedirectUri( $redirect );

		// Define an object that will be used to make all API requests.
		$service = new Google_Service_YouTube( $client );

		//get all items in the playlist via API
		$params = array(
			'maxResults' => 49,
			'playlistId' => $playlist,
		);
		$params = array_filter( $params );

		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$response = $service->playlistItems->listPlaylistItems( 'snippet', $params );

		$large_thumb = better_youtube_get_large_youtube_thumbnail( $response->items[0]->snippet->thumbnails );

		//set up the html for the first item (large display)
		$first_id = $response->items[0]->snippet->resourceId->videoId;

		$output = '<div id="playlist-' . $playlist . '" class="custom-api-playlist" data-playlist-id="' . $playlist . '" data-post-id="' . $post_id . '">';

		$output .= '<div class="playlist-preview-first"><div id="player-' . $playlist . '" class="playlist-preview" data-video-index="0">
			<div class="playlist-thumb" style="background-image:url(' .
				$large_thumb . ')"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path d="M549.655 124.083c-6.281-23.65-24.787-42.276-48.284-48.597C458.781 64 288 64 288 64S117.22 64 74.629 75.486c-23.497 6.322-42.003 24.947-48.284 48.597-11.412 42.867-11.412 132.305-11.412 132.305s0 89.438 11.412 132.305c6.281 23.65 24.787 41.5 48.284 47.821C117.22 448 288 448 288 448s170.78 0 213.371-11.486c23.497-6.321 42.003-24.171 48.284-47.821 11.412-42.867 11.412-132.305 11.412-132.305s0-89.438-11.412-132.305zm-317.51 213.508V175.185l142.739 81.205-142.739 81.201z"/></svg></div>
			</div></div>';
		$output .= '<div class="nav-buttons"><span class="nav-button previous mfp-arrow mfp-arrow-left mfp-prevent-close" data-video-index="">Previous</span><span class="nav-button next mfp-arrow mfp-arrow-right mfp-prevent-close" data-video-index="1">Next</span></div>';

		//set up the html for all items (small display)
		$output .= '<div class="playlist-list"><ul>';

		$i = 1;
		foreach ( $response->items as $item ) {

			$video_id = $item->snippet->resourceId->videoId;

			if ( isset( $item->snippet->thumbnails ) ) {
				if ( isset( $item->snippet->thumbnails->default->url ) ) {
					$small_thumb = $item->snippet->thumbnails->default->url;
				} else {
					$small_thumb = better_youtube_get_large_youtube_thumbnail( $item->snippet->thumbnails );
				}
				$large_thumb = better_youtube_get_large_youtube_thumbnail( $item->snippet->thumbnails );
			} else {
				$small_thumb = false;
				$large_thumb = false;
			}

			$index   = $i - 1;
			$output .= '<li class="playlist-preview" data-video-id="' . $video_id . '" data-video-index="' . $index . '" tabindex="0" role="button">' .
				'<div class="playlist-small-thumb"><div class="playlist-thumb" style="background-image:url(' .
				$small_thumb . ')"></div></div>' .
				'<div class="playlist-num">' . $i . '</div>' .
				'<div class="playlist-item-title">' . $item->snippet->title . '</div>' .
				'</li>';

			$i++;
		}
		$output .= '</ul></div>';
		$output .= '</div>';
	} catch ( Exception $e ) {
		$error  = json_decode( $e->getMessage() );
		$output = '<div class="error">Error: <br>' . $error->error->message . '</div>';
	}
	return shortcode_unautop( $output );

}


// use to wrap youtube iframes anywhere in the code for nicer output
if ( ! function_exists( 'better_youtube_iframe' ) ) :
	function better_youtube_iframe( $iframe ) {

		if ( ! is_string( $iframe ) ) {
			return;
		}
		//find iframe src
		$src      = better_youtube_get_youtube_iframe_from_embed( $iframe );
		$playlist = better_youtube_get_youtube_playlist_from_src( $src );

		if ( isset( $playlist ) && ! empty( $playlist ) && defined( 'YOUTUBE_API_KEY' ) ) {

			// Google API for building custom YouTube Playlists
			require_once SQUARECANDY_BYT_PATH . 'vendor/autoload.php';
			try {
				$client = new Google_Client();
				$client->setDeveloperKey( YOUTUBE_API_KEY );
				$client->setScopes( 'https://www.googleapis.com/auth/youtube' );
				$redirect = filter_var( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'], FILTER_SANITIZE_URL );
				$client->setRedirectUri( $redirect );

				// Define an object that will be used to make all API requests.
				$service = new Google_Service_YouTube( $client );

				//get all items in the playlist via API
				$params      = array(
					'maxResults' => 49,
					'playlistId' => $playlist,
				);
				$params      = array_filter( $params );
				$response    = $service->playlistItems->listPlaylistItems( 'snippet', $params );
				$large_thumb = better_youtube_get_large_youtube_thumbnail( $response->items[0]->snippet->thumbnails );

				//set up the html for the first item (large display)
				$link = 'https://www.youtube.com/embed/' .
					$response->items[0]->snippet->resourceId->videoId . better_youtube_url_parameters();

				$output = '<div class="custom-playlist">';

				$output .= '<div class="playlist-preview-first"><a href="' . $link . '">
					<div class="playlist-thumb" style="background-image:url(' .
						$large_thumb . ')"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path d="M549.655 124.083c-6.281-23.65-24.787-42.276-48.284-48.597C458.781 64 288 64 288 64S117.22 64 74.629 75.486c-23.497 6.322-42.003 24.947-48.284 48.597-11.412 42.867-11.412 132.305-11.412 132.305s0 89.438 11.412 132.305c6.281 23.65 24.787 41.5 48.284 47.821C117.22 448 288 448 288 448s170.78 0 213.371-11.486c23.497-6.321 42.003-24.171 48.284-47.821 11.412-42.867 11.412-132.305 11.412-132.305s0-89.438-11.412-132.305zm-317.51 213.508V175.185l142.739 81.205-142.739 81.201z"/></svg></div>
					</a></div>';

				//set up the html for all items (small display)
				$output .= '<div class="playlist-list"><ul>';

				$i = 1;
				foreach ( $response->items as $item ) {

					$link = 'https://www.youtube.com/embed/' .
						$item->snippet->resourceId->videoId . better_youtube_url_parameters();

					if ( isset( $item->snippet->thumbnails ) ) {
						$small_thumb = isset( $item->snippet->thumbnails->default->url ) ? $item->snippet->thumbnails->default->url : false;
						$large_thumb = better_youtube_get_large_youtube_thumbnail( $item->snippet->thumbnails );
					} else {
						$small_thumb = false;
						$large_thumb = false;
					}

					$output .= '<li><a href="' . $link . '" ' .
						'rel="videogroup_' . $playlist . '" ' .
						'title="' . esc_attr( $item->snippet->title ) . '" ' .
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
			} catch ( Exception $e ) {
				$error  = json_decode( $e->getMessage() );
				$output = '<div class="error">Error: <br>' . $error->error->message . '</div>';
			}
			return shortcode_unautop( $output );
		} else {
			// add extra params to iframe src
			$params  = better_youtube_url_parameters( true, false ); // should return an array
			$new_src = add_query_arg( $params, $src ); // returns a string
			$new_src = esc_url( $new_src );
			$iframe  = str_replace( $src, $new_src, $iframe );
			$iframe  = str_replace( 'allow="autoplay; encrypted-media"', '', $iframe );
			$iframe  = str_replace( 'frameborder="0"', '', $iframe );
			if ( ! strpos( $iframe, 'loading=' ) ) {
				$iframe = str_replace( '<iframe ', '<iframe loading="lazy" ', $iframe );
			}
			$iframe = shortcode_unautop( $iframe );
			if ( ! strpos( $iframe, 'fitvids' ) ) {
				$iframe = '<div class="fitvids">' . $iframe . '</div>';
			}
			return $iframe;
		}
	}

endif;

// apply the better_youtube_iframe improvements to the automatic WordPress oembed
if ( ! function_exists( 'squarecandy_custom_youtube_querystring' ) ) :
	function squarecandy_custom_youtube_querystring( $html, $url, $args ) {
		if ( strpos( $html, 'youtube' ) || strpos( $html, 'youtu.be' ) ) {
			$html = better_youtube_iframe( $html );
		}
		// apply fitvids container to vimeo
		if ( strpos( $html, 'vimeo' ) && ! strpos( $html, 'fitvids' ) ) {
			$html = '<div class="fitvids">' . $html . '</div>';
		}
		return $html;
	}
	add_filter( 'oembed_result', 'squarecandy_custom_youtube_querystring', 99, 3 );
	add_filter( 'embed_oembed_html', 'squarecandy_custom_youtube_querystring', 99, 3 );
endif;

// Apply fitvids to the_excerpt
// Not sure why oembed_result is not being applied here already. Is there a better way to do this?
// str_replace for iframe is a bit broad and could cause issues with non-video iframe content
add_filter( 'get_the_excerpt', 'squarecandy_custom_youtube_excerpt', 999 );
function squarecandy_custom_youtube_excerpt( $content ) {
	$content = str_replace( '<p><iframe', '<iframe', $content );
	$content = str_replace( '</iframe></p>', '</iframe>', $content );
	$content = str_replace( '<iframe', '<div class="fitvids"><iframe', $content );
	$content = str_replace( '</iframe>', '</iframe></div>', $content );
	return $content;
}


add_filter( 'the_content', 'better_youtube_the_content', 110 );
function better_youtube_the_content( $content ) {
	// shortcode_fix / fix wpautop crap
	// https://wordpress.stackexchange.com/a/217304/41488
	$content = str_replace( '<p></a></li>', '</a></li>', $content );
	// wrap video iframes in fitvids div
	// fetches iframes already wrapped in a <p> by WP - strips the containing <p> and adds our <div> instead
	// this happens after the oembed filter but this ignore the embeds bc they aren't wrapped in <p>
	$content = preg_replace( '/<p.*>(<iframe.*src=\S.*(?:youtu|vimeo).*\/iframe>)<\/p>/', '<div class="fitvids">$1</div>', $content );
	return $content;
}
