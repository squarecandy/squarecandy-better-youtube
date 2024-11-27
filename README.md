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

We filter `get_the_excerpt` to wrap *all iframes* in fitvid divs.

We filter `the_content` to replace `<p></a></li>`  with `</a></li>`

Then we have js to:
- run fitvids (responsive size) on .fitvid divs
- make .custom-playlist a magnific popup
- control display/interaction of playlists


To locate content to test for this plugin:
- Many sites have a page like 'page-for-testing' that often contains youtube/vimeo videos
- Mysql query for youtube/vimeo embeds not already wrapped in an inframe or `<a>`
```
SELECT ID, post_date, post_content, post_name, post_type FROM posts WHERE post_content REGEXP '(youtube|vimeo)[^[:space:]]+\?[^[:space:]]+' AND post_content NOT REGEXP '(src|href)=.*(youtube|vimeo)' AND post_status = 'publish' AND post_type NOT IN ('oembed_cache') ORDER BY post_date DESC;
```
- Mysql query for youtube playlists:
```
SELECT ID, post_date, post_content, post_name, post_type FROM 1Vb8MV_posts WHERE post_content REGEXP 'youtu[^[:space:]]+\?[^[:space:]]+list=[^[:space:]]' AND post_status = 'publish' AND post_type NOT IN ('oembed_cache') ORDER BY post_date DESC;
```

## Playlist Option

To enable the nice visual playlist generator, you must [obtain a google API key](https://console.cloud.google.com/cloud-resource-manager) that has the YouTube Data API v3 enabled and place it in your wp-config.php file:

`define('YOUTUBE_API_KEY', 'yourAPIkey-XX0XX0XX0XX0-000-XXXXXXXXX-00)`

## Roadmap

* Add options screen to enable/disable playlist feature, choose options for player display, etc.
