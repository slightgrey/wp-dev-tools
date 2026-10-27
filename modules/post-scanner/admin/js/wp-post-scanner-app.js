/* global wppsConfig, wp */
( function () {
	'use strict';

	const {
		createElement: h,
		useState,
		useEffect,
		useCallback,
		useRef,
	} = wp.element;

	// -------------------------------------------------------------------------
	// API helpers
	// -------------------------------------------------------------------------

	async function apiFetch( endpoint ) {
		const response = await fetch( wppsConfig.apiUrl + endpoint, {
			headers: {
				'X-WP-Nonce': wppsConfig.nonce,
				'Content-Type': 'application/json',
			},
		} );

		if ( ! response.ok ) {
			const body = await response.json().catch( () => ( {} ) );
			throw new Error( body.message || 'Request failed: ' + response.status );
		}

		return response.json();
	}

	// -------------------------------------------------------------------------
	// UI Primitives (shadcn-style via Tailwind)
	// -------------------------------------------------------------------------

	function Card( { children, className } ) {
		return h(
			'div',
			{ className: 'rounded-lg border border-gray-200 bg-white shadow-sm ' + ( className || '' ) },
			children
		);
	}

	function Badge( { children, className } ) {
		return h(
			'span',
			{
				className:
					'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ' +
					( className || 'bg-gray-100 text-gray-700' ),
			},
			children
		);
	}

	// -------------------------------------------------------------------------
	// Generic filter tab row — shared by type and status filters.
	// Props:
	//   tabs      — [{ value, label, count }]
	//   active    — currently active value string
	//   onChange  — called with the new value
	//   colorMap  — optional { value: 'tailwind classes for active state' }
	// -------------------------------------------------------------------------

	var DEFAULT_ACTIVE_CLASSES   = 'bg-blue-600 text-white border-blue-600';
	var DEFAULT_INACTIVE_CLASSES = 'bg-white text-gray-600 border-gray-300 hover:bg-gray-50';
	var DEFAULT_COUNT_ACTIVE     = 'bg-blue-500 text-white';
	var DEFAULT_COUNT_INACTIVE   = 'bg-gray-100 text-gray-500';

	function FilterTabRow( { tabs, active, onChange, colorMap } ) {
		return h(
			'div',
			{ className: 'flex flex-wrap gap-1' },
			tabs.map( function ( tab ) {
				var isActive     = active === tab.value;
				var customActive = colorMap && colorMap[ tab.value ];
				var btnClass     = isActive
					? ( customActive ? customActive.btn  : DEFAULT_ACTIVE_CLASSES )
					: DEFAULT_INACTIVE_CLASSES;
				var cntClass     = isActive
					? ( customActive ? customActive.cnt  : DEFAULT_COUNT_ACTIVE )
					: DEFAULT_COUNT_INACTIVE;

				return h(
					'button',
					{
						key: tab.value,
						onClick: function () { onChange( tab.value ); },
						className:
							'inline-flex items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-medium ' +
							'border transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 ' +
							btnClass,
					},
					tab.label,
					h(
						'span',
						{ className: 'rounded-full px-1.5 py-0.5 text-[10px] font-semibold ' + cntClass },
						tab.count
					)
				);
			} )
		);
	}

	// -------------------------------------------------------------------------
	// Type Filter Tabs
	// Derives tabs dynamically from all post types present in the fetched list.
	// Counts are based on all posts (independent of the status filter).
	// -------------------------------------------------------------------------

	function TypeFilterTabs( { allPosts, activeType, onChange } ) {
		var typeSet = {};
		allPosts.forEach( function ( p ) { typeSet[ p.type ] = true; } );
		var types = Object.keys( typeSet ).sort( function ( a, b ) {
			var order = { post: 0, page: 1 };
			var ao = order[ a ] !== undefined ? order[ a ] : 2;
			var bo = order[ b ] !== undefined ? order[ b ] : 2;
			if ( ao !== bo ) return ao - bo;
			return a.localeCompare( b );
		} );

		var tabs = [ { value: 'all', label: 'All', count: allPosts.length } ].concat(
			types.map( function ( type ) {
				return {
					value: type,
					label: type,
					count: allPosts.filter( function ( p ) { return p.type === type; } ).length,
				};
			} )
		);

		return h(
			'div',
			{ className: 'flex flex-col gap-1' },
			h( 'span', { className: 'text-[10px] font-semibold uppercase tracking-wide text-gray-400' }, 'Post Type' ),
			h( FilterTabRow, { tabs: tabs, active: activeType, onChange: onChange } )
		);
	}

	// -------------------------------------------------------------------------
	// Status Filter Tabs
	// Derives tabs dynamically from all statuses present in the fetched list.
	// Counts are based on all posts (independent of the type filter).
	// -------------------------------------------------------------------------

	var STATUS_COLORS = {
		publish: { btn: 'bg-green-600 text-white border-green-600',   cnt: 'bg-green-500 text-white' },
		draft:   { btn: 'bg-amber-500 text-white border-amber-500',   cnt: 'bg-amber-400 text-white' },
		pending: { btn: 'bg-orange-500 text-white border-orange-500', cnt: 'bg-orange-400 text-white' },
		private: { btn: 'bg-purple-600 text-white border-purple-600', cnt: 'bg-purple-500 text-white' },
		future:  { btn: 'bg-sky-500 text-white border-sky-500',       cnt: 'bg-sky-400 text-white' },
		trash:   { btn: 'bg-red-600 text-white border-red-600',       cnt: 'bg-red-500 text-white' },
	};

	function StatusFilterTabs( { allPosts, activeStatus, onChange } ) {
		var statusSet = {};
		allPosts.forEach( function ( p ) { statusSet[ p.status ] = true; } );
		var statuses = Object.keys( statusSet ).sort( function ( a, b ) {
			var order = { publish: 0, draft: 1, pending: 2, private: 3, future: 4, trash: 5 };
			var ao = order[ a ] !== undefined ? order[ a ] : 6;
			var bo = order[ b ] !== undefined ? order[ b ] : 6;
			if ( ao !== bo ) return ao - bo;
			return a.localeCompare( b );
		} );

		var tabs = [ { value: 'all', label: 'All', count: allPosts.length } ].concat(
			statuses.map( function ( status ) {
				return {
					value: status,
					label: status.charAt( 0 ).toUpperCase() + status.slice( 1 ),
					count: allPosts.filter( function ( p ) { return p.status === status; } ).length,
				};
			} )
		);

		return h(
			'div',
			{ className: 'flex flex-col gap-1' },
			h( 'span', { className: 'text-[10px] font-semibold uppercase tracking-wide text-gray-400' }, 'Status' ),
			h( FilterTabRow, { tabs: tabs, active: activeStatus, onChange: onChange, colorMap: STATUS_COLORS } )
		);
	}

	// -------------------------------------------------------------------------
	// Post Combobox — searchable dropdown
	// -------------------------------------------------------------------------

	function PostCombobox( { posts, selectedId, onChange, disabled } ) {
		var containerRef            = useRef( null );
		var inputRef                = useRef( null );
		const [ query, setQuery ]   = useState( '' );
		const [ isOpen, setIsOpen ] = useState( false );

		var selectedPost = selectedId
			? posts.find( function ( p ) { return p.id === selectedId; } ) || null
			: null;

		// Filter list by query — match ID or title.
		var q        = query.trim().toLowerCase();
		var filtered = q
			? posts.filter( function ( p ) {
				return String( p.id ).includes( q ) || p.title.toLowerCase().includes( q );
			  } )
			: posts;

		// Close on outside click.
		useEffect( function () {
			function onMouseDown( e ) {
				if ( containerRef.current && ! containerRef.current.contains( e.target ) ) {
					setIsOpen( false );
					setQuery( '' );
				}
			}
			document.addEventListener( 'mousedown', onMouseDown );
			return function () { document.removeEventListener( 'mousedown', onMouseDown ); };
		}, [] );

		// When selectedId is reset externally, clear query.
		useEffect( function () {
			if ( ! selectedId ) setQuery( '' );
		}, [ selectedId ] );

		function handleFocus() {
			setQuery( '' );
			setIsOpen( true );
		}

		function handleChange( e ) {
			setQuery( e.target.value );
			setIsOpen( true );
		}

		function handleSelect( post ) {
			onChange( post.id );
			setQuery( '' );
			setIsOpen( false );
		}

		function handleKeyDown( e ) {
			if ( e.key === 'Escape' ) {
				setIsOpen( false );
				setQuery( '' );
				inputRef.current && inputRef.current.blur();
			}
		}

		// What the input shows: query while open, selected label while closed.
		var inputValue = isOpen
			? query
			: ( selectedPost ? '[' + selectedPost.id + '] ' + selectedPost.title : '' );

		return h(
			'div',
			{ ref: containerRef, className: 'relative flex-1' },

			// Input wrapper
			h(
				'div',
				{ className: 'relative' },
				h( 'input', {
					ref: inputRef,
					type: 'text',
					value: inputValue,
					onFocus: handleFocus,
					onChange: handleChange,
					onKeyDown: handleKeyDown,
					disabled: disabled,
					placeholder: '\u2014 Search and select a post \u2014',
					autoComplete: 'off',
					className:
						'flex h-10 w-full rounded-md border border-gray-300 bg-white pl-3 pr-8 py-2 text-sm ' +
						'placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 ' +
						'focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
				} ),
				// Chevron icon
				h(
					'span',
					{ className: 'pointer-events-none absolute inset-y-0 right-2.5 flex items-center text-gray-400' },
					h(
						'svg',
						{ xmlns: 'http://www.w3.org/2000/svg', className: 'h-4 w-4', viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', strokeWidth: 2 },
						h( 'polyline', { points: '6 9 12 15 18 9' } )
					)
				)
			),

			// Dropdown list
			isOpen && h(
				'ul',
				{
					className:
						'absolute z-50 mt-1 max-h-64 w-full overflow-auto rounded-md border border-gray-200 ' +
						'bg-white py-1 shadow-lg',
				},
				filtered.length === 0
					? h( 'li', { className: 'px-3 py-2 text-sm text-gray-400 italic' }, 'No posts found.' )
					: filtered.map( function ( post ) {
						var isSelected = post.id === selectedId;
						return h(
							'li',
							{
								key: post.id,
								onMouseDown: function ( e ) {
									e.preventDefault(); // keep input focused until selection
									handleSelect( post );
								},
								className:
									'flex cursor-pointer items-baseline gap-2 px-3 py-2 text-sm ' +
									( isSelected
										? 'bg-blue-50 text-blue-700'
										: 'text-gray-700 hover:bg-gray-50' ),
							},
							h( 'span', { className: 'shrink-0 font-mono text-xs text-gray-400' }, '#' + post.id ),
							h( 'span', { className: 'flex-1 truncate' }, post.title ),
							isSelected && h(
								'svg',
								{ xmlns: 'http://www.w3.org/2000/svg', className: 'ml-auto h-3.5 w-3.5 shrink-0 text-blue-600', viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', strokeWidth: 2.5 },
								h( 'polyline', { points: '20 6 9 17 4 12' } )
							)
						);
					  } )
			)
		);
	}

	// -------------------------------------------------------------------------
	// Run Debug Button
	// -------------------------------------------------------------------------

	function RunButton( { onClick, disabled, loading } ) {
		return h(
			'button',
			{
				onClick: onClick,
				disabled: disabled || loading,
				className:
					'inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium ' +
					'text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 ' +
					'focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 transition-colors',
			},
			loading
				? h(
					'span',
					{ className: 'flex items-center gap-2' },
					h( 'span', {
						className:
							'h-3.5 w-3.5 animate-spin rounded-full border-2 border-white border-t-transparent',
					} ),
					'Loading\u2026'
				  )
				: 'Run Debug'
		);
	}

	// -------------------------------------------------------------------------
	// JSON View
	// -------------------------------------------------------------------------

	function JsonView( { data } ) {
		const [ copied, setCopied ] = useState( false );
		const jsonString = JSON.stringify( data, null, 2 );

		const handleCopy = useCallback( function () {
			navigator.clipboard.writeText( jsonString ).then( function () {
				setCopied( true );
				setTimeout( function () {
					setCopied( false );
				}, 2000 );
			} );
		}, [ jsonString ] );

		return h(
			Card,
			{ className: 'p-4 flex flex-col gap-3' },
			h(
				'div',
				{ className: 'flex items-center justify-between' },
				h(
					'h3',
					{ className: 'text-xs font-semibold uppercase tracking-wide text-gray-500' },
					'JSON View'
				),
				h(
					'button',
					{
						onClick: handleCopy,
						className:
							'inline-flex items-center rounded border border-gray-300 bg-white px-3 py-1 ' +
							'text-xs font-medium text-gray-700 hover:bg-gray-50 focus:outline-none ' +
							'focus:ring-2 focus:ring-blue-500 transition-colors',
					},
					copied ? '\u2713 Copied!' : 'Copy JSON'
				)
			),
			h(
				'pre',
				{
					className:
						'overflow-auto max-h-96 rounded-md bg-gray-950 p-4 text-xs leading-relaxed ' +
						'text-green-400 font-mono',
				},
				jsonString
			)
		);
	}

	// -------------------------------------------------------------------------
	// Data Table
	// -------------------------------------------------------------------------

	const SECTION_COLORS = {
		'Post Fields': 'bg-blue-100 text-blue-800',
		'Post Meta':   'bg-purple-100 text-purple-800',
		'Author':      'bg-green-100 text-green-800',
		'Thumbnail':   'bg-yellow-100 text-yellow-800',
		'Terms':       'bg-orange-100 text-orange-800',
		'Attachments': 'bg-pink-100 text-pink-800',
		'Comments':    'bg-gray-100 text-gray-700',
	};

	function flattenScanResult( data ) {
		var rows = [];

		function addSection( section, obj ) {
			if ( ! obj || typeof obj !== 'object' ) return;
			Object.entries( obj ).forEach( function ( entry ) {
				var key   = entry[ 0 ];
				var value = entry[ 1 ];
				rows.push( {
					section: section,
					key: key,
					value: value === null ? 'null' : ( typeof value === 'object' ? JSON.stringify( value ) : String( value ) ),
				} );
			} );
		}

		addSection( 'Post Fields', data.post );
		addSection( 'Post Meta', data.meta );
		addSection( 'Author', data.author );
		addSection( 'Thumbnail', data.thumbnail );
		addSection( 'Comments', { comments_count: data.comments_count } );

		if ( data.terms && typeof data.terms === 'object' ) {
			Object.entries( data.terms ).forEach( function ( entry ) {
				rows.push( {
					section: 'Terms',
					key: entry[ 0 ],
					value: JSON.stringify( entry[ 1 ] ),
				} );
			} );
		}

		if ( Array.isArray( data.attachments ) ) {
			data.attachments.forEach( function ( att, i ) {
				rows.push( {
					section: 'Attachments',
					key: 'attachment_' + i,
					value: JSON.stringify( att ),
				} );
			} );
		}

		return rows;
	}

	function DataTable( { data } ) {
		const [ filter, setFilter ] = useState( '' );
		const allRows = flattenScanResult( data );

		const filteredRows = filter.trim()
			? allRows.filter( function ( row ) {
				var q = filter.toLowerCase();
				return (
					row.key.toLowerCase().includes( q ) ||
					row.value.toLowerCase().includes( q ) ||
					row.section.toLowerCase().includes( q )
				);
			  } )
			: allRows;

		return h(
			Card,
			{ className: 'p-4 flex flex-col gap-3' },
			h(
				'div',
				{ className: 'flex items-center justify-between' },
				h(
					'h3',
					{ className: 'text-xs font-semibold uppercase tracking-wide text-gray-500' },
					'Data Table'
				),
				h(
					Badge,
					{ className: 'bg-gray-100 text-gray-600' },
					filteredRows.length + ' rows'
				)
			),
			h( 'input', {
				type: 'text',
				placeholder: 'Filter by section, key, or value\u2026',
				value: filter,
				onChange: function ( e ) {
					setFilter( e.target.value );
				},
				className:
					'flex h-9 w-full rounded-md border border-gray-300 bg-white px-3 py-1 text-sm ' +
					'placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 ' +
					'focus:ring-offset-2',
			} ),
			h(
				'div',
				{ className: 'overflow-auto max-h-96 rounded-md border border-gray-200' },
				h(
					'table',
					{ className: 'w-full text-sm border-collapse' },
					h(
						'thead',
						{ className: 'sticky top-0 z-10 bg-gray-50' },
						h(
							'tr',
							null,
							h( 'th', { className: 'w-32 border-b border-gray-200 px-3 py-2 text-left text-xs font-semibold text-gray-600' }, 'Section' ),
							h( 'th', { className: 'w-52 border-b border-gray-200 px-3 py-2 text-left text-xs font-semibold text-gray-600' }, 'Key' ),
							h( 'th', { className: 'border-b border-gray-200 px-3 py-2 text-left text-xs font-semibold text-gray-600' }, 'Value' )
						)
					),
					h(
						'tbody',
						null,
						filteredRows.length === 0
							? h(
								'tr',
								null,
								h( 'td', { colSpan: 3, className: 'px-3 py-8 text-center text-sm text-gray-400' }, 'No rows match your filter.' )
							  )
							: filteredRows.map( function ( row, i ) {
								return h(
									'tr',
									{
										key: row.section + ':' + row.key + ':' + i,
										className: i % 2 === 0 ? 'bg-white' : 'bg-gray-50',
									},
									h(
										'td',
										{ className: 'px-3 py-2 align-top' },
										h(
											Badge,
											{ className: SECTION_COLORS[ row.section ] || 'bg-gray-100 text-gray-700' },
											row.section
										)
									),
									h( 'td', { className: 'break-all px-3 py-2 align-top font-mono text-xs font-medium text-gray-700' }, row.key ),
									h( 'td', { className: 'break-all px-3 py-2 align-top font-mono text-xs text-gray-600' }, row.value )
								);
							  } )
					)
				)
			)
		);
	}

	// -------------------------------------------------------------------------
	// Error Alert
	// -------------------------------------------------------------------------

	function ErrorAlert( { message } ) {
		return h(
			'div',
			{ className: 'rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700' },
			h( 'strong', null, 'Error: ' ),
			message
		);
	}

	// -------------------------------------------------------------------------
	// Post Info Bar
	// -------------------------------------------------------------------------

	function PostInfoBar( { result } ) {
		var post = result.post;
		return h(
			'div',
			{ className: 'flex flex-wrap items-center gap-2 rounded-md border border-blue-100 bg-blue-50 px-4 py-2 text-sm text-blue-800' },
			h( 'span', { className: 'font-semibold' }, '#' + post.ID + ' \u2013 ' + post.post_title ),
			h( Badge, { className: 'bg-blue-200 text-blue-900' }, post.post_type ),
			h( Badge, { className: 'bg-blue-200 text-blue-900' }, post.post_status ),
			h( 'span', { className: 'ml-auto text-xs text-blue-600' }, result.comments_count + ' comment(s)' )
		);
	}

	// -------------------------------------------------------------------------
	// Root App
	// -------------------------------------------------------------------------

	function App() {
		const [ posts, setPosts ]                   = useState( [] );
		const [ activeType, setActiveType ]         = useState( 'all' );
		const [ activeStatus, setActiveStatus ]     = useState( 'all' );
		const [ selectedId, setSelectedId ]         = useState( null );
		const [ scanResult, setScanResult ]         = useState( null );
		const [ isLoading, setIsLoading ]           = useState( false );
		const [ isLoadingPosts, setIsLoadingPosts ] = useState( true );
		const [ error, setError ]                   = useState( null );

		useEffect( function () {
			apiFetch( '/posts' )
				.then( function ( data ) {
					setPosts( data );
					setIsLoadingPosts( false );
				} )
				.catch( function ( err ) {
					setError( 'Failed to load posts: ' + err.message );
					setIsLoadingPosts( false );
				} );
		}, [] );

		// Posts after each individual filter — fed to the opposite tab row
		// so its counts reflect what's actually available under that selection.
		var postsByType   = activeType   === 'all' ? posts : posts.filter( function ( p ) { return p.type   === activeType;   } );
		var postsByStatus = activeStatus === 'all' ? posts : posts.filter( function ( p ) { return p.status === activeStatus; } );

		// Intersection of type + status filters.
		var visiblePosts = postsByType.filter( function ( p ) {
			return activeStatus === 'all' || p.status === activeStatus;
		} );

		const resetSelection = useCallback( function () {
			setSelectedId( null );
			setScanResult( null );
			setError( null );
		}, [] );

		const handleTypeChange = useCallback( function ( type ) {
			setActiveType( type );
			resetSelection();
		}, [ resetSelection ] );

		const handleStatusChange = useCallback( function ( status ) {
			setActiveStatus( status );
			resetSelection();
		}, [ resetSelection ] );

		const handleRunDebug = useCallback( function () {
			if ( ! selectedId ) return;
			setIsLoading( true );
			setScanResult( null );
			setError( null );

			apiFetch( '/scan/' + selectedId )
				.then( function ( data ) {
					setScanResult( data );
					setIsLoading( false );
				} )
				.catch( function ( err ) {
					setError( err.message );
					setIsLoading( false );
				} );
		}, [ selectedId ] );

		return h(
			'div',
			{ className: 'mx-auto max-w-screen-xl py-6 px-4' },

			// Page header
			h(
				'div',
				{ className: 'mb-6' },
				h( 'h1', { className: 'text-2xl font-bold text-gray-900' }, 'Single Post Debug' ),
				h( 'p', { className: 'mt-1 text-sm text-gray-500' }, 'Select any post to inspect its complete data: fields, meta, terms, thumbnail, attachments, and more.' )
			),

			// Controls card
			h(
				Card,
				{ className: 'mb-6 p-4' },
				h(
					'div',
					{ className: 'flex flex-col gap-3' },

					// Filter rows — only shown once posts are loaded.
					! isLoadingPosts && posts.length > 0 && h(
						'div',
						{ className: 'flex flex-col gap-2 rounded-md border border-gray-100 bg-gray-50 p-3' },
						h( TypeFilterTabs, {
							allPosts: postsByStatus,
							activeType: activeType,
							onChange: handleTypeChange,
						} ),
						h( 'div', { className: 'border-t border-gray-200' } ),
						h( StatusFilterTabs, {
							allPosts: postsByType,
							activeStatus: activeStatus,
							onChange: handleStatusChange,
						} )
					),

					// Combobox + run button row
					h(
						'div',
						{ className: 'flex flex-col gap-3 sm:flex-row sm:items-center' },
						isLoadingPosts
							? h( 'p', { className: 'flex-1 text-sm text-gray-400' }, 'Loading posts\u2026' )
							: h( PostCombobox, {
								posts: visiblePosts,
								selectedId: selectedId,
								onChange: function ( id ) {
									setSelectedId( id );
									setScanResult( null );
									setError( null );
								},
								disabled: isLoading,
							  } ),
						h( RunButton, {
							onClick: handleRunDebug,
							disabled: ! selectedId || isLoadingPosts,
							loading: isLoading,
						} )
					)
				)
			),

			// Error
			error && h( 'div', { className: 'mb-6' }, h( ErrorAlert, { message: error } ) ),

			// Post info bar
			scanResult && h( 'div', { className: 'mb-4' }, h( PostInfoBar, { result: scanResult } ) ),

			// Results grid
			scanResult && h(
				'div',
				{ className: 'grid grid-cols-1 gap-6 xl:grid-cols-2' },
				h( JsonView, { data: scanResult } ),
				h( DataTable, { data: scanResult } )
			)
		);
	}

	// -------------------------------------------------------------------------
	// Mount
	// -------------------------------------------------------------------------

	function mount() {
		var container = document.getElementById( 'wpps-app' );
		if ( ! container ) return;

		// React 18 (WP 6.2+) — createRoot
		if ( wp.element.createRoot ) {
			wp.element.createRoot( container ).render( h( App, null ) );
		} else {
			// React 17 fallback
			wp.element.render( h( App, null ), container );
		}
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', mount );
	} else {
		mount();
	}

} )();
