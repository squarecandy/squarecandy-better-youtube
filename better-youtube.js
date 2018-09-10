// Square Better Youtube
( function( $ ) {
	$(document).ready(function(){

		// Apply fitvids (responsive YouTube videos) across site
		$('.fitvids').fitVids();

		$('.custom-playlist').each(function() { // the containers for all your galleries
			$(this).magnificPopup({
				delegate: '.playlist-list ul li a', // the selector for gallery item
				type:'iframe',
				iframe: {
					markup: '<div class="mfp-iframe-scaler">'+
						'<div class="mfp-close"></div>'+
						'<iframe class="mfp-iframe" frameborder="0" allowfullscreen></iframe>'+
						'<div class="mfp-bottom-bar">'+
							'<div class="mfp-title">...</div>'+
						'</div>'+
					'</div>', // HTML markup of popup, `mfp-close` will be replaced by the close button
					patterns: {
						youtube: {
							index: 'youtube.com', // String that detects type of video (in this case YouTube). Simply via url.indexOf(index).
							id: null,
							src: '%id%'
						}
					}
				},
				gallery: {enabled:true},

				callbacks: {
					updateStatus: function(data) {
						var current_title = $(this)[0].currItem.el[0].title;
						// console.log('Status changed', data, current_video_title, $('.mfp-title').html() );
						setTimeout(function(){
							$('.mfp-title').html(current_title);
						},200);
					}
				}

			});
		});

		$('.playlist-preview-first a').on('click',function(e){
			e.preventDefault();
			$(this).parent().next().find('ul li:first-child a').click();
		});
		
	});
} )( jQuery );
