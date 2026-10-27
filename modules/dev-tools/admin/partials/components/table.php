<?php
defined( 'ABSPATH' ) || exit;

/**
 * Renders a styled data table.
 *
 * Each row in $rows can be:
 *   - A flat array of cell values (no delete button)
 *   - An associative array with keys:
 *       'cells'  => []       cell HTML strings
 *       'id'     => string   HTML id for <tr>
 *       'action' => string   data-action value (renders Delete button)
 *       'data'   => []       extra data-* attributes on the button
 *
 * @param string[] $columns
 * @param array    $rows
 * @param array    $options  ['searchable'=>bool, 'actions_col'=>bool]
 */
function at_dt_table( array $columns, array $rows, array $options = [] ): void {
	$searchable  = ! empty( $options['searchable'] );
	$actions_col = ! empty( $options['actions_col'] );
	$id          = 'at-dt-tbl-' . absint( wp_rand( 1000, 9999 ) );
	$col_count   = count( $columns ) + ( $actions_col ? 1 : 0 );
	?>
	<?php if ( $searchable ) : ?>
		<div style="margin-bottom:10px;">
			<input
				type="text"
				placeholder="Filter…"
				data-table="<?php echo esc_attr( $id ); ?>"
				style="width:260px; padding:5px 10px; border:1px solid #d1d5db; border-radius:6px; font-size:12px; color:#374151;"
				onkeyup="atDtFilterTable(this)"
			>
		</div>
	<?php endif; ?>
	<div style="overflow-x:auto;">
		<table id="<?php echo esc_attr( $id ); ?>" style="width:100%; border-collapse:collapse; font-size:12px;">
			<thead>
				<tr style="background:#f9fafb; border-bottom:1px solid #e5e7eb;">
					<?php foreach ( $columns as $col ) : ?>
						<th style="padding:8px 12px; text-align:left; font-weight:600; color:#374151; white-space:nowrap;"><?php echo esc_html( $col ); ?></th>
					<?php endforeach; ?>
					<?php if ( $actions_col ) : ?>
						<th style="padding:8px 12px;"></th>
					<?php endif; ?>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $rows ) ) : ?>
					<tr>
						<td colspan="<?php echo esc_attr( (string) $col_count ); ?>" style="padding:24px 12px; text-align:center; color:#9ca3af; font-style:italic;">
							No records found.
						</td>
					</tr>
				<?php else : ?>
					<?php foreach ( $rows as $row ) :
						$cells      = isset( $row['cells'] ) ? $row['cells'] : (array) $row;
						$row_id     = $row['id']     ?? '';
						$action     = $row['action'] ?? null;
						$extra_data = $row['data']   ?? array();
						$data_attrs = '';
						foreach ( $extra_data as $k => $v ) {
							$data_attrs .= ' data-' . esc_attr( $k ) . '="' . esc_attr( $v ) . '"';
						}
					?>
						<tr
							<?php if ( $row_id ) echo 'id="' . esc_attr( $row_id ) . '"'; ?>
							style="border-bottom:1px solid #f3f4f6;"
							onmouseover="this.style.background='#f9fafb'"
							onmouseout="this.style.background=''"
						>
							<?php foreach ( $cells as $cell ) : ?>
								<td style="padding:7px 12px; color:#374151; max-width:320px; overflow-wrap:break-word;"><?php echo wp_kses_post( (string) $cell ); ?></td>
							<?php endforeach; ?>
							<?php if ( $action ) : ?>
								<td style="padding:7px 12px; text-align:right; white-space:nowrap;">
									<button
										class="at-dt-action-btn"
										data-action="<?php echo esc_attr( $action ); ?>"
										<?php echo $data_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
										onclick="atDtAction(this)"
										style="padding:3px 10px; background:#fff; border:1px solid #e5e7eb; border-radius:4px; font-size:11px; color:#dc2626; cursor:pointer;"
									>Delete</button>
								</td>
							<?php endif; ?>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
	<?php
}
