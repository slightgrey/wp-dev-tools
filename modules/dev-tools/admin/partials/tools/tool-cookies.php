<?php defined( 'ABSPATH' ) || exit;

$rows = array();
foreach ( $_COOKIE as $name => $value ) {
	$display = is_array( $value ) ? wp_json_encode( $value ) : (string) $value;
	$rows[]  = array(
		'cells'  => array(
			'<code style="font-size:11px;">' . esc_html( $name ) . '</code>',
			'<code style="font-size:11px; word-break:break-all;">' . esc_html( strlen( $display ) > 150 ? substr( $display, 0, 150 ) . '…' : $display ) . '</code>',
		),
		'action' => 'delete_cookie_js',
		'data'   => array( 'cookie-name' => $name ),
	);
}
?>

<?php at_dt_card( 'Cookies', function() use ( $rows ) { ?>
	<p style="font-size:12px; color:#6b7280; margin:0 0 12px;">
		<?php echo esc_html( count( $rows ) . ' cookies set for this domain' ); ?> — Deletion is client-side; the page reloads after each delete.
	</p>
	<?php if ( empty( $rows ) ) {
		at_dt_empty( 'No cookies found.' );
		return;
	} ?>
	<div style="overflow-x:auto;">
		<table style="width:100%; border-collapse:collapse; font-size:12px;">
			<thead>
				<tr style="background:#f9fafb; border-bottom:1px solid #e5e7eb;">
					<th style="padding:8px 12px; text-align:left; font-weight:600; color:#374151;">Name</th>
					<th style="padding:8px 12px; text-align:left; font-weight:600; color:#374151;">Value</th>
					<th style="padding:8px 12px;"></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $rows as $row ) : ?>
					<tr style="border-bottom:1px solid #f3f4f6;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background=''">
						<td style="padding:7px 12px;"><?php echo wp_kses_post( $row['cells'][0] ); ?></td>
						<td style="padding:7px 12px; max-width:400px; overflow-wrap:break-word;"><?php echo wp_kses_post( $row['cells'][1] ); ?></td>
						<td style="padding:7px 12px; text-align:right;">
							<button
								class="at-dt-delete-cookie at-dt-action-btn"
								data-name="<?php echo esc_attr( $row['data']['cookie-name'] ); ?>"
								style="padding:3px 10px; background:#fff; border:1px solid #e5e7eb; border-radius:4px; font-size:11px; color:#dc2626; cursor:pointer;"
							>Delete</button>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
<?php } ); ?>
