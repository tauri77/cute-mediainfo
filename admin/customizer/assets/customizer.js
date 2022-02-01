/*globals wp:false*/
(function ( api ) {
	'use strict';
	api.bind(
		'saved',
		function ( r ) {
			if ( r.cutemi && r.cutemi.message ) {
				api.notifications.add(
					new api.Notification(
						r.cutemi.message,
						{
							message: r.cutemi.message, type: 'error', dismissible: true,
						}
					)
				);
			}
		}
	);
})( wp.customize );
