/* global wps_ajax, jQuery */
( function ( $ ) {
	'use strict';

	var wpsData        = [];    // full dataset from last scan
	var wpsFiltered    = [];    // currently visible subset
	var wpsActiveFilter = 'all';

	// ---------------------------------------------------------------------------
	// Scan
	// ---------------------------------------------------------------------------

	$( '#wps-scan-btn' ).on( 'click', function () {
		var $btn = $( this );
		$btn.prop( 'disabled', true ).text( 'Scanning…' );
		$( '#wps-results' ).hide();
		$( '#wps-loading' ).show();

		$.post( wps_ajax.ajax_url, {
			action: 'at_wps_scan_plugins',
			nonce:  wps_ajax.nonce,
		} )
		.done( function ( response ) {
			if ( response.success && Array.isArray( response.data ) ) {
				wpsData = response.data;
				renderResults();
			} else {
				alert( 'Scan failed. Please try again.' );
			}
		} )
		.fail( function () {
			alert( 'Request failed. Please check your connection and try again.' );
		} )
		.always( function () {
			$( '#wps-loading' ).hide();
			$btn.prop( 'disabled', false ).html(
				'<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg> Run Scan'
			);
		} );
	} );

	// ---------------------------------------------------------------------------
	// Render
	// ---------------------------------------------------------------------------

	function renderResults() {
		updateCounts();
		applyFilter( wpsActiveFilter );
		$( '#wps-results' ).show();
	}

	function updateCounts() {
		var counts = {
			all:          wpsData.length,
			active:       0,
			inactive:     0,
			needs_update: 0,
			premium:      0,
			free:         0,
			custom:       0,
		};

		wpsData.forEach( function ( p ) {
			if ( p.is_active )    counts.active++;
			if ( ! p.is_active )  counts.inactive++;
			if ( p.needs_update ) counts.needs_update++;
			// free_or_premium counts toward both tabs.
			if ( p.type === 'premium' || p.type === 'free_or_premium' ) counts.premium++;
			if ( p.type === 'free'    || p.type === 'free_or_premium' ) counts.free++;
			if ( p.type === 'custom' ) counts.custom++;
		} );

		Object.keys( counts ).forEach( function ( key ) {
			$( '#count-' + key ).text( counts[ key ] );
		} );
	}

	// ---------------------------------------------------------------------------
	// Filter
	// ---------------------------------------------------------------------------

	$( document ).on( 'click', '.wps-tab', function () {
		var filter = $( this ).data( 'filter' );
		$( '.wps-tab' ).removeClass( 'active' );
		$( this ).addClass( 'active' );
		applyFilter( filter );
	} );

	function applyFilter( filter ) {
		wpsActiveFilter = filter;

		switch ( filter ) {
			case 'active':
				wpsFiltered = wpsData.filter( function ( p ) { return p.is_active; } );
				break;
			case 'inactive':
				wpsFiltered = wpsData.filter( function ( p ) { return ! p.is_active; } );
				break;
			case 'needs_update':
				wpsFiltered = wpsData.filter( function ( p ) { return p.needs_update; } );
				break;
			case 'premium':
				wpsFiltered = wpsData.filter( function ( p ) { return p.type === 'premium' || p.type === 'free_or_premium'; } );
				break;
			case 'free':
				wpsFiltered = wpsData.filter( function ( p ) { return p.type === 'free' || p.type === 'free_or_premium'; } );
				break;
			case 'custom':
				wpsFiltered = wpsData.filter( function ( p ) { return p.type === 'custom'; } );
				break;
			default:
				wpsFiltered = wpsData.slice();
		}

		renderTable();
	}

	function renderTable() {
		var $tbody = $( '#wps-table-body' );
		var $empty = $( '#wps-empty' );
		var $table = $( '#wps-results-table' );

		$tbody.empty();

		if ( wpsFiltered.length === 0 ) {
			$table.hide();
			$empty.show();
			return;
		}

		$empty.hide();
		$table.show();

		wpsFiltered.forEach( function ( p ) {
			$tbody.append( buildRow( p ) );
		} );
	}

	function buildRow( p ) {
		var statusBadge = p.is_active
			? '<span class="wps-badge wps-badge-active">Active</span>'
			: '<span class="wps-badge wps-badge-inactive">Inactive</span>';

		var typeBadgeMap = {
			free:            '<span class="wps-badge wps-badge-free">Free</span>',
			premium:         '<span class="wps-badge wps-badge-premium">Premium</span>',
			custom:          '<span class="wps-badge wps-badge-custom">Custom</span>',
			free_or_premium: '<span class="wps-badge wps-badge-free">Free</span> <span class="wps-badge wps-badge-premium">Premium</span>',
		};
		var typeBadge = typeBadgeMap[ p.type ] || '<span class="wps-badge wps-badge-custom">Custom</span>';

		var phpCell = p.requires_php
			? esc( p.requires_php ) + '+'
			: '<span class="wps-php-custom">Not specified</span>';

		var urlCell = p.plugin_uri
			? '<a href="' + esc( p.plugin_uri ) + '" class="wps-plugin-link" target="_blank" rel="noopener noreferrer">' + esc( truncate( p.plugin_uri, 40 ) ) + '</a>'
			: '<span class="wps-php-custom">—</span>';

		var notes = [];
		if ( ! p.is_active )    notes.push( '<span class="wps-note-delete">Safe to delete</span>' );
		if ( p.needs_update )   notes.push( '<span class="wps-badge wps-badge-yes">Update available</span>' );
		var noteCell = notes.join( ' ' );

		var nameCell = '<div class="wps-plugin-name">' + esc( p.name ) + '</div>'
			+ '<div class="wps-plugin-author">' + esc( p.author ) + '</div>';

		return '<tr>'
			+ '<td>' + nameCell + '</td>'
			+ '<td>' + esc( p.version ) + '</td>'
			+ '<td>' + statusBadge + '</td>'
			+ '<td>' + phpCell + '</td>'
			+ '<td>' + urlCell + '</td>'
			+ '<td>' + typeBadge + '</td>'
			+ '<td>' + noteCell + '</td>'
			+ '</tr>';
	}

	// ---------------------------------------------------------------------------
	// Export CSV
	// ---------------------------------------------------------------------------

	$( '#wps-export-btn' ).on( 'click', function () {
		if ( wpsFiltered.length === 0 ) {
			alert( 'Nothing to export. Run a scan first.' );
			return;
		}

		var headers = [ 'Name', 'Author', 'Version', 'Status', 'PHP Required', 'Plugin URL', 'Type', 'Notes' ];
		var rows    = [ headers ];

		wpsFiltered.forEach( function ( p ) {
			var csvNotes = [];
			if ( ! p.is_active )  csvNotes.push( 'Safe to delete' );
			if ( p.needs_update ) csvNotes.push( 'Update available' );

			rows.push( [
				p.name,
				p.author,
				p.version,
				p.is_active ? 'Active' : 'Inactive',
				p.requires_php ? p.requires_php + '+' : 'Not specified',
				p.plugin_uri || '',
				{ free: 'Free', premium: 'Premium', custom: 'Custom', free_or_premium: 'Free / Premium' }[ p.type ] || 'Custom',
				csvNotes.join( '; ' ),
			] );
		} );

		var csv  = rows.map( function ( row ) {
			return row.map( csvEscape ).join( ',' );
		} ).join( '\r\n' );

		var blob     = new Blob( [ csv ], { type: 'text/csv;charset=utf-8;' } );
		var url      = URL.createObjectURL( blob );
		var date     = new Date().toISOString().slice( 0, 10 );
		var filename = 'plugins-export-' + date + '.csv';

		var $a = $( '<a>' ).attr( { href: url, download: filename } ).appendTo( 'body' );
		$a[ 0 ].click();
		$a.remove();
		URL.revokeObjectURL( url );
	} );

	// ---------------------------------------------------------------------------
	// Utilities
	// ---------------------------------------------------------------------------

	function esc( str ) {
		return $( '<span>' ).text( String( str ) ).html();
	}

	function truncate( str, max ) {
		return str.length > max ? str.slice( 0, max ) + '…' : str;
	}

	function csvEscape( value ) {
		var v = String( value );
		if ( /[",\r\n]/.test( v ) ) {
			v = '"' + v.replace( /"/g, '""' ) + '"';
		}
		return v;
	}

} )( jQuery );
