( function( $ ){

	function reloadPage() {
		window.location.href = window.location.href;
	};

	function pauseUpgrade() {
		reloadPage();
	};

	function processNextItem() {
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				_ajax_nonce: CBOXUpgrades.nonce,
				action: 'cbox_handle_upgrade',
				upgrade: CBOXUpgrades.upgrade
			},
			beforeSend: function() {
				$('#cbox-upgrade-start')
					.text( CBOXUpgrades.text.processing )
					.prop( 'disabled', true );
			},
			success: function( response ) {
				$(document).trigger( 'itemprocessed', [ response ] )

				if ( ! response.data.is_finished ) {
					processNextItem();
				} else {
					$('#cbox-upgrade-start').text( CBOXUpgrades.text.start );
				}
			},
			error: function( error ) {
				console.log( error );
			},
		});

	}

	$(document).on( 'itemprocessed', function( event, response ) {
		var data = response.data;
		var percentage = data.percentage;

		$('.cbox-upgrade').find('h3').text( data.name );
		$('.cbox-upgrade-progress-bar-inner').css( 'width', percentage +'%' );
		$('#cbox-upgrade-total').text( data.total_items );
		$('#cbox-upgrade-processed').text( data.total_processed );
		$('#cbox-upgrade-percentage').text( '(' +percentage+ '%)' );

		$('.cbox-upgrade-current-item').html(data.message);

		if ( data.is_finished ) {
			$('#cbox-upgrade-start, #cbox-upgrade-pause').prop( 'disabled', true );
		}
	} );

	$(document).on( 'click', '#cbox-upgrade-start', processNextItem );
	$(document).on( 'click', '#cbox-upgrade-pause', pauseUpgrade );
} )( jQuery );
