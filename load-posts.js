jQuery(document).ready(function($) {

	// The number of the next page to load
	var pageNum = parseInt(load_posts.startPage) + 1;

	// The maximum number of pages the current query can return.
	var max = parseInt(load_posts.maxPages);

	// The link of the next page of posts.
	var nextLink = load_posts.nextLink;//.replace(/page\/[0-9]/, '/?page='+ pageNum);

	var $container = $( '.rewards-holder' );
	// $container.imagesLoaded( function(){
		$container.isotope({
            itemSelector : '.reward',
            // sortBy : 'random',
            layoutMode: 'masonry',
            getSortData: {
            	ending: '[data-date-ending]',
            	latest: '[data-date-latest]',
            	type: '[data-type]',
            }
		});
	// });

	if(pageNum <= max ) {
		// Insert the "More Posts" link.
		$('#rewards .block-footer')
		.append('<a href="#"><h3 id="load-posts">See More</h3></a>');
	} else {
		$('#rewards .block-footer')
		.append('<h3 id="load-posts">No more Rewards</h3>');
	}

	// Load new posts when the link is clicked.
	$('a #load-posts').on( "click", function() {

		// Are there more post to load?
		if(pageNum <= max) {
			// Show that we are working.
			$(this).text('Loading posts...');

			$('#rewards .rewards-holder')
			.after('<div class="rewards-holder-temp"></div>');

			$( '.rewards-holder-temp' ).load(nextLink.replace(/page\/[0-9]/, '/?page='+ pageNum) + ' .rewards-holder .reward',
				function() {

					// update page number and next link.
					pageNum++;

					$( '.rewards-holder-temp' ).children().css({
						opacity: 0
					});

					var newItems = $( '.rewards-holder-temp' ).html();

					$container.isotope( 'insert', $(newItems), function() {
						$container.children().css({
							opacity: 1
						});
					});

					$( '.rewards-holder-temp' ).remove();

					// Update the button message.
					if(pageNum <= max ) {
						$('a #load-posts').text('See More');
					} else {
						$('a #load-posts').remove();
						$('#rewards .block-footer')
						.append('<h3 id="load-posts">No more Rewards.</h3>');
					}
				}
			);
		} else {
			$('a #load-posts').append('.');
		}

		return false;
	});
});
