# squarecandy-better-youtube

A WordPress plugin to improve the look and behavior of YouTube videos on WordPress.

- hide title and text info
- simplify controls
- don't show suggested videos on completion
- "modest branding"
- "dark" theme
- use HD if available
- apply responsive iframe via fitvids.js

We filter `oembed_result` & `embed_oembed_html` to: 
- replace any youtube playlist iframes with our own playlist code (looks for youtube links like https://www.youtube.com/watch?v=xxx&amp;list=yyy), or 
- wrap youtube iframes in fitvid divs & add extra parameters, or 
- wrap vimeo iframes in .fitvid divs

We also filter `get_the_excerpt` to wrap *all iframes* in fitvid divs.

Then we have js to:
- run fitvids (responsive size) on .fitvid divs
- make .custom-playlist a magnific popup
- control display/interaction of playlists

## Playlist Option

To enable the nice visual playlist generator, you must [obtain a google API key](https://console.cloud.google.com/cloud-resource-manager) that has the YouTube Data API v3 enabled and place it in your wp-config.php file:

`define('YOUTUBE_API_KEY', 'yourAPIkey-XX0XX0XX0XX0-000-XXXXXXXXX-00)`


## History

### v1.1.7

* fix: apply fitvids to the_excerpt

### v1.1.6

* fix: improve youtube/vimeo fitvids display in TinyMCE

### v1.1.5

* avoid double fitvids wrapper

### v1.1.4

* Fix css conflict with magnific popup image mode

### v1.1.3

* Fix missing fitvids wrapper issue

### v1.1.2

* Bugfix: correct the magnific popup urls
* Depreciated features such as showinfo=0 and theme=dark finally stopped working. Bummer! Removed these from the code.
* Added default spacing for fitvids div
* Remove the updater script (use [GitHub Updater](https://github.com/afragen/github-updater) instead)

### v1.1.1

* [skip] was used for testing...

### v1.1.0

* Add the playlist functionality
* Add updating via WP updater

### v1.0.0

* Initial Plugin build

## Roadmap

* Add options screen to enable/disable playlist feature, choose options for player display, etc.
