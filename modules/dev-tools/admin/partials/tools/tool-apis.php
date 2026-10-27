<?php defined( 'ABSPATH' ) || exit;

// Initialise the REST server if it hasn't been already.
if ( ! did_action( 'rest_api_init' ) ) {
	do_action( 'rest_api_init' );
}

$server = rest_get_server();
$routes = $server->get_routes();
ksort( $routes );

// Group by namespace.
$grouped = array();
foreach ( $routes as $route => $handlers ) {
	$parts     = explode( '/', ltrim( $route, '/' ), 2 );
	$namespace = $parts[0] ?? 'root';
	$grouped[ $namespace ][] = array( 'route' => $route, 'handlers' => $handlers );
}

echo '<h2 style="font-size:14px; font-weight:700; color:#111827; margin:0 0 14px;">REST API Routes <span style="font-weight:400; font-size:12px; color:#6b7280;">(' . count( $routes ) . ')</span></h2>';

foreach ( $grouped as $ns => $items ) :
	at_dt_card( $ns, function() use ( $items ) {
		$rows = array();
		foreach ( $items as $item ) {
			$methods = array();
			foreach ( $item['handlers'] as $handler ) {
				foreach ( array_keys( $handler['methods'] ?? array() ) as $method ) {
					if ( ! in_array( $method, $methods, true ) ) {
						$methods[] = $method;
					}
				}
			}
			$method_badges = implode( ' ', array_map( function( $m ) {
				$color = ( 'GET' === $m ) ? 'green' : ( ( 'POST' === $m ) ? 'blue' : ( ( 'DELETE' === $m ) ? 'red' : 'gray' ) );
				ob_start(); at_dt_badge( $m, $color ); return ob_get_clean();
			}, $methods ) );

			$rows[] = array( 'cells' => array(
				'<code style="font-size:11px;">' . esc_html( $item['route'] ) . '</code>',
				$method_badges,
			) );
		}
		at_dt_table( array( 'Route', 'Methods' ), $rows );
	} );
endforeach;
