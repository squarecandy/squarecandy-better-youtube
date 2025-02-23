// Square Better Youtube

/* global YT, fitvids */
( function( $ ) {
	$( document ).ready( function() {
		// Apply fitvids (responsive YouTube videos) across site
		fitvids( '.fitvids' );

		// run again after 1 second, to account for Usercentrics consent
		setTimeout( function() {
			fitvids( '.fitvids' );

			$( '.uc-embedding-accept' ).on( 'click', function() {
				// run fitvids on youtube/vimeo content after the user accepts the Usercentrics consent
				fitvids( '.fitvids' );
			} );
		}, 1 * 1000 );

		$( '.custom-playlist' ).each( function() {
			// the containers for all your galleries
			$( this ).magnificPopup( {
				delegate: '.playlist-list ul li a', // the selector for gallery item
				type: 'iframe',
				iframe: {
					markup:
						'<div class="mfp-iframe-scaler">' +
						'<div class="mfp-close"></div>' +
						'<iframe id="player" class="mfp-iframe" frameborder="0" allowfullscreen></iframe>' +
						'<div class="mfp-bottom-bar">' +
						'<div class="mfp-title">...</div>' +
						'</div>' +
						'</div>', // HTML markup of popup, `mfp-close` will be replaced by the close button
					patterns: {
						youtube: {
							index: 'youtube.com', // String that detects type of video (in this case YouTube).
							id: null,
							src: '%id%',
						},
					},
				},
				gallery: { enabled: true },

				callbacks: {
					updateStatus() {
						const currentTitle = $( this )[ 0 ].currItem.el[ 0 ].title;
						setTimeout( function() {
							$( '.mfp-title' ).html( currentTitle );
						}, 200 );
					},
				},
			} );
		} );

		$( '.api-playlist-anchor' ).each( function() {
			// cover pictures for playlist popups
			$( this ).magnificPopup( {
				type: 'inline',
				callbacks: {
					open() {
						if ( window.players[ this.content.data( 'post-id' ) ] ) {
							window.players[ this.content.data( 'post-id' ) ].init();
						}
					},
					close() {
						// eslint-disable-next-line no-console
						console.log( this.content.data( 'post-id' ), window.players[ this.content.data( 'post-id' ) ] );
						if ( window.players[ this.content.data( 'post-id' ) ] ) {
							window.players[ this.content.data( 'post-id' ) ].close();
						}
					},
				},
			} );
		} );

		$( '.playlist-preview-first a' ).on( 'click', function( e ) {
			e.preventDefault();
			$( this )
				.parent()
				.next()
				.find( 'ul li:first-child a' )
				.click();
		} );
	} );

	/* new api playlists */
	if ( $( '.custom-api-playlist' ).length ) {
		class SCPlayer {
			playerId;
			playlistId;
			player;
			videoIframe;
			videoContainer;
			previews;
			thumbnails;
			videoList = [];
			navButtons;
			previousButton;
			nextButton;
			autoplay = false;
			autoload = true;
			popup = false;
			debug = false;
			isActivePlayer = false;
			startVideo = 0;
			inactiveButtonClass = 'hidden';
			postId;
			instantiated = false;
			actionsBound = false;
			loop = true;
			singleVideo = false;
			singleVideoUrl = '';
			singleVideoTitle = '';

			constructor(
				videoContainer,
				{ autoplay = false, autoload = true, debug = false, popup = false, loop = true, layout = 'single-popup' }
			) {
				this.videoContainer = $( videoContainer );
				this.playlistId = this.videoContainer.data( 'playlist-id' );
				this.playerId = this.playlistId ? 'player-' + this.videoContainer.data( 'playlist-id' ) : 'player';
				this.videoIframe = this.videoContainer.find( '#' + this.playerId );
				this.navButtons = this.videoContainer.find( '.nav-button' );
				this.previousButton = this.videoContainer.find( '.previous' );
				this.nextButton = this.videoContainer.find( '.next' );
				this.thumbnails = this.videoContainer.find( '.playlist-list li' );
				this.previews = this.videoContainer.find( '.playlist-preview' );
				this.autoplay = autoplay;
				this.autoload = autoload;
				this.popup = popup;
				this.debug = debug;
				this.layout = layout;
				this.loop = loop;
				this.firstPlay = true;
				this.postId = this.videoContainer.data( 'post-id' );
				this.videoContainer.addClass( layout );
				this.getVideoIds();

				//if autoloading, instantiate player, otherwise bind action that will trigger player
				if ( this.autoload ) {
					this.init();
				} else if ( ! this.popup ) {
					this.bindInstantiateAction();
				}
				//setup popups
				if ( this.popup === 'single' ) {
					this.addPlayerPopup();
				}
				//setup previous button
				if ( this.loop ) {
					this.previousButton.data( 'video-index', this.videoList.length - 1 );
				} else {
					this.previousButton.data( 'video-index', 0 );
				}
			}

			init() {
				this.consoleDebug( 'SCPlayer init()' );
				if ( ! this.actionsBound ) {
					this.bindActions();
				}
				if ( ! this.instantiated ) {
					this.instantiatePlayer();
				} else {
					this.onPlayerReady();
				}
			}

			addPlayerPopup() {
				//to enable single videos, grab the url from the link used in that layout
				//TODO: this should maybe go in an earlier function
				const iframeSrc = $( '#' + this.playerId ).prop( 'href' );
				if ( this.videoList.length === 0 && iframeSrc ) {
					this.singleVideo = true;
					this.singleVideoUrl = iframeSrc;
					this.singleVideoTitle = $( '#' + this.playerId ).data( 'video-title' );
				}

				//unset id of player-preview-first since we won't use that as the player
				$( '#' + this.playerId ).prop( 'id', '' );

				//add/rearrange elements for popup
				const popupId = 'player-popup-' + this.postId;
				const popupDiv = $( '<div/>', {
					id: popupId,
					class: 'mfp-hide player-popup single-popup',
					data: { 'post-id': this.postId },
				} );
				const scaler = $( '<div/>', {
					class: 'player-popup-inner',
				} );
				const scaler2 = $( '<div/>', {
					class: 'mfp-iframe-scaler',
				} );
				//make this a div, not an iframe, so when we destroy() the player we're still left with the player div
				const popupPlayer = $( '<div/>', {
					id: this.playerId,
					data: { 'video-index': this.startVideo },
				} );

				popupDiv.append( scaler );
				scaler.append( scaler2 );
				//set up nav arrows
				this.previousButton.addClass( 'mfp-arrow mfp-arrow-left mfp-prevent-close' );
				this.nextButton.addClass( 'mfp-arrow mfp-arrow-right mfp-prevent-close' );
				popupDiv.append( this.navButtons );
				scaler2.append( popupPlayer );
				this.videoContainer.append( popupDiv );
				//add title
				const bottomBar = '<div class="mfp-bottom-bar"><div class="mfp-title">...</div></div>';
				scaler2.append( bottomBar );

				//bind mfp open to all video previews
				const _this = this;
				this.previews.on( 'click', function( e ) {
					e.preventDefault();
					_this.startVideo = $( this ).data( 'video-index' );
					$.magnificPopup.open( {
						items: {
							src: '#' + popupId,
						},
						type: 'inline',
						callbacks: {
							open() {
								window.players[ this.content.data( 'post-id' ) ].init();
							},
							close() {
								window.players[ this.content.data( 'post-id' ) ].close();
							},
						},
					} );
				} );

				// accessibility for playlist nav
				this.previews.on( 'keydown', function( e ) {
					if (
						e.code === 'Enter' ||
						e.code === 'Space' ||
						e.keyCode === 13 ||
						e.keyCode === 32 ||
						e.which === 13 ||
						e.which === 32
					) {
						$( this ).trigger( 'click' );
					}
				} );
			}

			instantiatePlayer() {
				this.consoleDebug( 'instantiatePlayer' );
				this.consoleDebug( this );
				this.player = new YT.Player( this.playerId, {
					height: '506.25',
					width: '900',
					events: {
						onReady: this.onPlayerReady.bind( this ),
						onStateChange: this.onPlayerStateChange.bind( this ),
					},
					playerVars: {
						controls: 1,
						autoplay: this.autoplay,
						mute: 0,
						playsinline: 1,
						showinfo: 0,
						rel: 0,
						iv_load_policy: 3,
						modestbranding: 1,
					},
				} );
				this.instantiated = true;
				this.navButtons.show();
				fitvids( '.custom-api-playlist' );
			}

			getVideoIds() {
				//get the playlist ids
				const list = [];
				this.thumbnails.each( function() {
					const videoTitle = $( this )
						.find( '.playlist-item-title' )
						.html();
					const videoId = $( this ).data( 'video-id' );
					list.push( { title: videoTitle, id: videoId } );
				} );
				this.videoList = list;
			}

			onPlayerReady() {
				this.consoleDebug( 'onPlayerReady' );
				//onPlayerReady is also triggered when we hide a player, so filter for that to prevent zombie calls
				if ( this.instantiated ) {
					if ( this.singleVideo ) {
						//cue using url
						this.player.cueVideoByUrl( this.singleVideoUrl );
						this.setTitle( this.singleVideoTitle );
					} else {
						this.cueVideo( this.startVideo );
					}
				}
			}

			onPlayerStateChange( event ) {
				this.consoleDebug( 'event data: ' + event.data );
				if ( event.data === YT.PlayerState.PLAYING ) {
					this.consoleDebug( 'playing: ' + this.playerId );
					//whenever a video plays, pause other videos on the page
					SCPlaylistFactory.pauseOtherVideos( this.playerId );
					this.navButtons.show();
					this.firstPlay = false;
				}
				if ( event.data === YT.PlayerState.CUED ) {
					this.consoleDebug( 'cued: ' + this.playerId );
					this.consoleDebug(
						'firstPlay: ' + this.firstPlay + ' /isActivePlayer: ' + this.isActivePlayer + ' /autoplay: ' + this.autoplay
					);
					// start the player (like autoplay)
					if ( this.singleVideo || ( ! this.firstPlay && this.isActivePlayer ) || ( this.firstPlay && this.autoplay ) ) {
						event.target.playVideo();
					} else {
						this.consoleDebug( 'blocked cue' );
					}
				}
				if ( event.data === YT.PlayerState.ENDED ) {
					this.consoleDebug( 'ended: ' + this.playerId );
					//check if we ended bc another video on the page played
					if ( this.isActivePlayer ) {
						const playerIndex = this.videoIframe.data( 'video-index' );
						if ( playerIndex < this.videoList.length ) {
							this.consoleDebug( 'play next video' );
							this.cueVideo( playerIndex + 1 );
						} else if ( playerIndex >= this.videoList.length && this.loop ) {
							this.consoleDebug( 'loop back to end video' );
							this.cueVideo( this.videoList.length - 1 );
						}
					}
				}
			}

			//handle incrementing where we are in the array
			updateNavIndex( index ) {
				index = parseInt( index );
				this.consoleDebug( 'updateNavIndex ' + index );
				const previousIndex = index - 1;
				const nextIndex = index + 1;

				if ( previousIndex < 0 ) {
					if ( this.loop ) {
						this.previousButton.data( 'video-index', this.videoList.length - 1 );
					} else {
						this.previousButton.addClass( this.inactiveButtonClass );
						this.previousButton.data( 'video-index', '' );
					}
				} else {
					this.previousButton.removeClass( this.inactiveButtonClass );
					this.previousButton.data( 'video-index', previousIndex );
				}
				if ( nextIndex >= this.videoList.length ) {
					if ( this.loop ) {
						this.nextButton.data( 'video-index', 0 );
					} else {
						this.nextButton.addClass( this.inactiveButtonClass );
						this.nextButton.data( 'video-index', '' );
					}
				} else {
					this.nextButton.removeClass( this.inactiveButtonClass );
					this.nextButton.data( 'video-index', nextIndex );
				}
			}

			setTitle( title ) {
				if ( this.popup === 'single' ) {
					if ( ! title ) {
						title = '';
					}
					const titleDiv = $( '#player-popup-' + this.postId ).find( '.mfp-title' );
					titleDiv.html( title );
				}
			}

			//play a video
			cueVideo( index ) {
				this.consoleDebug( 'cueVideo' );
				this.consoleDebug( this.player );
				this.isActivePlayer = true;
				index = parseInt( index );
				this.consoleDebug( 'cuevideo ' + index );
				this.player.cueVideoById( this.videoList[ index ].id );
				this.videoIframe.data( 'video-index', index );
				this.updateNavIndex( index );
				this.setTitle( this.videoList[ index ].title );
			}

			bindActions() {
				this.consoleDebug( 'bind actions' );
				const _this = this;

				if ( this.popup !== 'single' ) {
					//handle clicks on the sidebar videos
					this.thumbnails.on( 'click', function() {
						const index = parseInt( $( this ).data( 'video-index' ) );
						_this.consoleDebug( 'clicked thumbnail index: ' + index );
						_this.firstPlay = false; //so it plays immediately
						_this.cueVideo( index );
					} );
				}

				this.navButtons.on( 'click', function() {
					const navIndex = parseInt( $( this ).data( 'video-index' ) );
					_this.consoleDebug( 'clicked button index: ' + navIndex );
					if ( ! isNaN( navIndex ) ) {
						_this.cueVideo( navIndex );
					} else {
						_this.consoleDebug( 'no navButton index' );
					}
				} );
				this.actionsBound = true;
			}

			//for non popup players that aren't autoloaded
			lazyLoadInstantiate( event ) {
				const _this = event.data.SCPlayer;
				_this.consoleDebug( 'lazyLoadInstantiate' );
				const index = parseInt( $( this ).data( 'video-index' ) );
				_this.consoleDebug( 'set start video to: ' + index );
				if ( ! isNaN( index ) ) {
					_this.startVideo = index;
					_this.bindActions();
					_this.autoplay = true; //so the video plays immediately
					_this.instantiatePlayer();
					//unhook the initial trigger actions
					_this.previews.off( 'click', _this.lazyLoadInstantiate );
					_this.navButtons.off( 'click', _this.lazyLoadInstantiate );
				} else {
					_this.consoleDebug( 'no lazyLoad index' );
				}
			}

			bindInstantiateAction() {
				// const _this = this;
				this.previews.on( 'click', { SCPlayer: this }, this.lazyLoadInstantiate );
				this.navButtons.on( 'click', { SCPlayer: this }, this.lazyLoadInstantiate );
			}

			close() {
				this.consoleDebug( 'close popup' );
				this.player.destroy();
				this.instantiated = false;
			}

			consoleDebug( message ) {
				if ( this.debug ) {
					// eslint-disable-next-line no-console
					console.log( message );
				}
			}
		}

		class SCPlaylistFactory {
			containersSelector;
			containers;
			defaultLayoutType = 'single-popup';
			layoutTypes = [ 'single-popup', 'cover-popup', 'load-all', 'load-first', 'load-none' ];
			layoutParameterSettings = {
				'single-popup': { popup: 'single', autoplay: true, autoload: false },
				'cover-popup': { popup: 'full', autoplay: true, autoload: false },
				'load-all': { popup: false, autoplay: false, autoload: 'all' },
				'load-first': { popup: false, autoplay: false, autoload: 'first' },
				'load-none': { popup: false, autoplay: false, autoload: false },
			};
			layoutParameters;

			constructor( selector ) {
				this.containersSelector = selector;
				this.containers = $( this.containersSelector );
				this.loadScript();
				this.getLayoutType();
				this.setupPlayers();
			}

			loadScript() {
				//load the youtube api
				const tag = document.createElement( 'script' );
				tag.src = 'https://www.youtube.com/iframe_api';
				const firstScriptTag = document.getElementsByTagName( 'script' )[ 0 ];
				firstScriptTag.parentNode.insertBefore( tag, firstScriptTag );
			}

			getLayoutType() {
				//get layout type from first parent element  with class video-payout-type
				this.layoutTypeElement = this.containers.parents( '.video-layout-type' );
				this.layoutType = this.defaultLayoutType;
				if ( this.layoutTypeElement.length ) {
					const layoutTypeSetting = this.layoutTypeElement.data( 'videolayout' );
					if ( this.layoutTypes.includes( layoutTypeSetting ) ) {
						this.layoutType = layoutTypeSetting;
					}
				}
				this.layoutParameters = this.layoutParameterSettings[ this.layoutType ];
				this.layoutParameters.layout = this.layoutType;
			}

			//loop through and set up player class objects
			setupPlayers() {
				const _this = this;
				window.onYouTubePlayerAPIReady = function() {
					window.players = [];
					let $i = 0;
					_this.containers.each( function() {
						if ( $i > 0 && this.layoutType !== 'load-all' ) {
							_this.layoutParameters.autoload = false;
						}
						const scPlayer = new SCPlayer( this, _this.layoutParameters );
						window.players[ $( this ).data( 'post-id' ) ] = scPlayer;
						$i++;
					} );
				};
			}

			//utility function to stop other videos on the page
			static pauseOtherVideos( playerId ) {
				window.players.forEach( function( element ) {
					if ( element.playerId !== playerId ) {
						element.isActivePlayer = false;
						if ( element.player && element.player.getPlayerState() === 1 ) {
							element.player.stopVideo();
						}
					}
				} );
			}
		}

		// set up playlists via factory class
		// eslint-disable-next-line no-undef
		playlists = new SCPlaylistFactory( '.custom-api-playlist' );
	}
} )( jQuery );
