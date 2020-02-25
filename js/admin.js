jQuery( function( $ ) {
	var $progressIndicators = $( '.converter .progress-indicator' );
	var inProgress = {};

	function converterButtonClick( event ) {
		var $this  = $( this );
		var $block = $this.parents( '.converter' );
		var id     = $block.attr( 'id' );

		// If a conversion job is already in progress, treat this as a request to stop it.
		if ( isInProgress( id ) ) {
			stopConversion( id );
			return;
		}

		// Otherwise, it is a request to start the conversion process.
		startConversion( id );
	}

	function startConversion( id ) {
		var $block = $( '#' + id );
		var $button = $block.find( 'button.run-converter' );

		inProgress[ id ] = true;
		$button.text( tecHappyConverter.stop );
		$block.addClass( 'running' );
		updateLoop( id );
	}

	function stopConversion( id ) {
		var $block = $( '#' + id );
		var $button = $block.find( 'button.run-converter' );

		inProgress[ id ] = false;
		$button.text( tecHappyConverter.run );
		$block.removeClass( 'running' );
	}

	function updateLoop( id ) {
		if ( ! isInProgress( id ) ) {
			return;
		}

		jQuery.post( ajaxurl, {
			'action': 'tec_hc.run_converter',
			'check':  tecHappyConverter.check,
			'id':     id,
		}, function( response ) {
			// Bad or unexpected response?
			if (
				'object' !== typeof response
				|| 'boolean' !== typeof response.success
				|| 'object' !== typeof response.data
				|| ! response.success
			) {
				flag( id, 'error', tecHappyConverter.failed );
				stopConversion( id );
			}

			// Always update the counts
			updateCounts( id, response.data.counts );

			// If we should continue, take a breather and initiate a fresh batch.
			if ( response.data.continue ) {
				setTimeout(
					function() { updateLoop( id ); },
					50 // Let's not hammer the site unnecessarily
				);
			}
			// Or if complete is not set/false, we're all done.
			else {
				flag( id, 'success', tecHappyConverter.complete );
				stopConversion( id );
				$( '#' + id ).removeClass( 'active' ).addClass( 'inactive' );
			}
		} );
	}

	function isInProgress( id ) {
		return 'boolean' === typeof inProgress[ id ] && inProgress[ id ];
	}

	function animateProgressIndicators() {
		$progressIndicators.toggleClass( 'alt' );
	}

	function flag( id, type, message ) {
		var $flag = $( '#' + id + ' .flag' );

		$flag.attr( 'class', 'flag ' + type );
		$flag.html( message );
	}

	function updateCounts( id, counts ) {
		$( '#' + id + ' .total-nodes .count-value' ).html( parseInt( counts.total, 10 ) );
		$( '#' + id + ' .converted-nodes .count-value' ).html( parseInt( counts.converted, 10 ) );
		$( '#' + id + ' .unconverted-nodes .count-value' ).html( parseInt( counts.unconverted, 10 ) );
	}

	$( '.tec-happy-converter button.run-converter' ).click( converterButtonClick );
	setInterval( animateProgressIndicators, 900 );
} );