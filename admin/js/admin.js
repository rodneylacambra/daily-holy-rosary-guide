/**
 * Holy Rosary - Admin JavaScript
 *
 * @package HolyRosary
 */

/* global holyRosaryAdmin, jQuery */
( function ( $ ) {
	'use strict';

	$( document ).ready( function () {

		// Confirm before moderating intentions.
		$( document ).on( 'submit', '.holy-rosary-admin form', function ( e ) {
			const action = $( this ).find( '[name="intention_action"]' ).val();
			if ( ! action ) return;

			const msg = action === 'approved'
				? holyRosaryAdmin.i18n.confirmApprove
				: holyRosaryAdmin.i18n.confirmReject;

			if ( ! window.confirm( msg ) ) {
				e.preventDefault();
			}
		} );

	} );

} )( jQuery );
