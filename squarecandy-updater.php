<?php
// Allow updating from the github repository
add_action( 'init', 'squarecandy_youtube_github_plugin_updater_test_init' );
function squarecandy_youtube_github_plugin_updater_test_init() {

	include_once('updater.php');

	if ( !defined('WP_GITHUB_FORCE_UPDATE') ) {
		define( 'WP_GITHUB_FORCE_UPDATE', true );
	}

	if ( is_admin() ) { // note the use of is_admin() to double check that this is happening in the admin

		$config = array(
			'slug' => plugin_basename( __FILE__ ),
			'proper_folder_name' => 'squarecandy-better-youtube',
			'api_url' => 'https://api.github.com/repos/squarecandy/squarecandy-better-youtube',
			'raw_url' => 'https://raw.github.com/squarecandy/squarecandy-better-youtubes/master',
			'github_url' => 'https://github.com/squarecandy/squarecandy-better-youtube',
			'zip_url' => 'https://github.com/squarecandy/squarecandy-better-youtube/archive/master.zip',
			'sslverify' => true,
			'requires' => '4.0',
			'tested' => '4.9.8',
			'readme' => 'README.md',
			// 'access_token' => '', // only needed for private repository
		);

		$youtube_updater = new WP_GitHub_Updater( $config );
	}
}
