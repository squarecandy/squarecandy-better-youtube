# Changelog

All notable changes to this project will be documented in this file. See [standard-version](https://github.com/conventional-changelog/standard-version) for commit guidelines.

## [1.3.0](https://github.com/squarecandy/squarecandy-better-youtube/compare/v1.2.0-rc1...v1.3.0) (2024-09-19)


### Features

* add lazy load to iframe on single view ([6c88550](https://github.com/squarecandy/squarecandy-better-youtube/commit/6c8855004e62ab432cded3cc8b837b53f3d1f5dd))


### Bug Fixes

* accessibility (keyboard nav) for playlist nav ([a8294a4](https://github.com/squarecandy/squarecandy-better-youtube/commit/a8294a47e3b4df00992d6850b36c5c4fc73f6a65))
* avoid php error ([2e70f90](https://github.com/squarecandy/squarecandy-better-youtube/commit/2e70f9006403c8ef706bc542014f5f18716d7ece))
* cleanup ([7f7f206](https://github.com/squarecandy/squarecandy-better-youtube/commit/7f7f20688900ea2df5dced41a5802e9d43700487))
* cover popup modifications ([fa2e1f0](https://github.com/squarecandy/squarecandy-better-youtube/commit/fa2e1f041e67488c2c161edbfb48568f01681496))
* escape the generated URL in the right spot ([5f0ded5](https://github.com/squarecandy/squarecandy-better-youtube/commit/5f0ded501fa80dfdc26fcd479074078d25d47f22))
* hide the scroll bar if it's not too many items ([9ed22b2](https://github.com/squarecandy/squarecandy-better-youtube/commit/9ed22b207d32b593eae75c5d0ed16caf3d3db24f))
* higher breakpoint before going to two col playlist format ([3883246](https://github.com/squarecandy/squarecandy-better-youtube/commit/3883246ec8a257e548e7a354004527ac4123b17e))
* php notices ([fc8ee59](https://github.com/squarecandy/squarecandy-better-youtube/commit/fc8ee5962fdefc71ca16e3344616b343ea8653e1))
* re-initiate nav buttons on new popup ([30ca94f](https://github.com/squarecandy/squarecandy-better-youtube/commit/30ca94fea9188f7e749365981c2aefa73acfb420))
* refactor to remove depreciated htmlspecialchars ([6748ca2](https://github.com/squarecandy/squarecandy-better-youtube/commit/6748ca2aeb43c949737314c9ea7d6d2abbd1465a))
* regex for youtu.be broken ([47be74b](https://github.com/squarecandy/squarecandy-better-youtube/commit/47be74b3a10a58b75147ff96471529a3a91b8574))
* remove unneeded Google libraries ([7be2c2a](https://github.com/squarecandy/squarecandy-better-youtube/commit/7be2c2a029d72edccd8ecffee1b42efe27c1f8b0))
* return false if no playlist ([9377ccf](https://github.com/squarecandy/squarecandy-better-youtube/commit/9377ccf508e95080952ea782a40dd0efe5408bcc))
* single popup UI fixes ([80b1b34](https://github.com/squarecandy/squarecandy-better-youtube/commit/80b1b347cd12df45e54a6e6d7ab4eda0b9fed872))
* version numbering error ([017336d](https://github.com/squarecandy/squarecandy-better-youtube/commit/017336d58c659342972ab38c77c44d86e0772780))

## [1.2.0-rc1](https://github.com/squarecandy/squarecandy-better-youtube/compare/v1.1.7...v1.2.0-rc1) (2021-04-22)


### Features

* allow new js to be used by single video popup ([db200db](https://github.com/squarecandy/squarecandy-better-youtube/commit/db200dba2ab9599b1ff89d18872c1990be840b11))
* implement api playlists ([af26cec](https://github.com/squarecandy/squarecandy-better-youtube/commit/af26cec366a9c8dfbdbab9f0b7e8bebb23ec140e))
* open existing iframes up to js interactions ([ba2ce07](https://github.com/squarecandy/squarecandy-better-youtube/commit/ba2ce074c3cff9f58efa16caf2cc5c9bd5c0c5d4))


### Bug Fixes

* add all CI system; use updated Google library via composer; linting ([39e052e](https://github.com/squarecandy/squarecandy-better-youtube/commit/39e052e407c9b7866b3298585a859398db38d7d7))
* autoplay for play in place with autoload ([e53623b](https://github.com/squarecandy/squarecandy-better-youtube/commit/e53623bf0a20c620fd212a24d7c12063318d0015))
* check if values array values are set ([3b32293](https://github.com/squarecandy/squarecandy-better-youtube/commit/3b32293852eced1b71067d44be160a84530e5a90))
* debug off by default, adjust nav button show/hide ([4a7ba83](https://github.com/squarecandy/squarecandy-better-youtube/commit/4a7ba83c357e413d06f2e6a3a7e17d1840e3fa23))
* the_excerpt avoid div inside p ([7fa3e8b](https://github.com/squarecandy/squarecandy-better-youtube/commit/7fa3e8b1e08916e18fc4f5704d7c91b19a00a288))
* use fitvdids for responsive iframe, change player size, use maxres for yt thumbs ([3144285](https://github.com/squarecandy/squarecandy-better-youtube/commit/31442853558f6b1597145f68d7149278d1faccf3))

## v1.1.7

* fix: apply fitvids to the_excerpt

## v1.1.6

* fix: improve youtube/vimeo fitvids display in TinyMCE

## v1.1.5

* avoid double fitvids wrapper

## v1.1.4

* Fix css conflict with magnific popup image mode

## v1.1.3

* Fix missing fitvids wrapper issue

## v1.1.2

* Bugfix: correct the magnific popup urls
* Depreciated features such as showinfo=0 and theme=dark finally stopped working. Bummer! Removed these from the code.
* Added default spacing for fitvids div
* Remove the updater script (use [GitHub Updater](https://github.com/afragen/github-updater) instead)

## v1.1.1

* [skip] was used for testing...

## v1.1.0

* Add the playlist functionality
* Add updating via WP updater

## v1.0.0

* Initial Plugin build
