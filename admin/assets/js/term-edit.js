/*globals jQuery:false,cutemi_tax_script:false,wp:false*/
(function () {
	'use strict';
	jQuery( document ).ready(
		function ( $ ) {

			$( '#edittag .term-slug-wrap' )
				.show()
				.css( 'max-height', 'auto' )
				.find( 'input' )
				.attr( 'readOnly', true );

			if ( typeof wp.media !== 'undefined' ) {
				var frame = false;
				$( '.cutemi-tax-media' ).click(
					function () {
						var id           = $( this ).data( 'for' );
						var imgInput     = $( 'input#' + id );
						var imgContainer = $( this ).parent().find( '.custom-img-container' );

						imgInput.on(
							'change',
							function () {
									var $url = imgInput.val();
									imgContainer.html( '' );
									imgContainer.append(
										jQuery( '<img alt="" />' )
											.attr( 'src', $url )
											.css( { "max-width": "100%", "margin-top": "1em" } )
									);
							}
						);

						if ( frame ) {
							frame.open();
							return;
						}

						frame = wp.media(
							{
								title: cutemi_tax_script.select_or_upload_media, button: {
									text: cutemi_tax_script.use_this_media
								}, multiple: false
							}
						);

						frame.on(
							'select',
							function () {
								var attachment = frame.state().get( 'selection' ).first().toJSON();
								imgInput.val( attachment.url ).trigger( 'change' );
							}
						);
						frame.open();
						return false;
					}
				);
			}
		}
	);
})();
