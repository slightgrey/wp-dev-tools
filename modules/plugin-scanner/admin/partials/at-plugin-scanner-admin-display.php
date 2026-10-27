<?php defined( 'ABSPATH' ) || exit; ?>

<div class="wrap wps-wrap">
	<div class="wps-card">
		<div class="wps-card-header">
			<div>
				<h1 class="wps-title">Plugin Scanner</h1>
				<p class="wps-description">Scan all installed plugins and inspect their status, type, PHP compatibility, and more.</p>
			</div>
			<div class="wps-header-actions">
				<button id="wps-scan-btn" class="wps-btn wps-btn-primary">
					<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
					Run Scan
				</button>
			</div>
		</div>
		<div id="wps-loading" class="wps-loading" style="display:none;">
			<div class="wps-spinner"></div>
			<span class="wps-loading-text">Scanning plugins&hellip;</span>
		</div>
		<div id="wps-results" style="display:none;">
			<div class="wps-toolbar">
				<div id="wps-filters" class="wps-filter-tabs">
					<button class="wps-tab active" data-filter="all">All <span class="wps-count" id="count-all"></span></button>
					<button class="wps-tab" data-filter="active">Active <span class="wps-count" id="count-active"></span></button>
					<button class="wps-tab" data-filter="inactive">Inactive <span class="wps-count" id="count-inactive"></span></button>
					<button class="wps-tab" data-filter="needs_update">Needs Update <span class="wps-count" id="count-needs_update"></span></button>
					<button class="wps-tab" data-filter="premium">Premium <span class="wps-count" id="count-premium"></span></button>
					<button class="wps-tab" data-filter="free">Free <span class="wps-count" id="count-free"></span></button>
					<button class="wps-tab" data-filter="custom">Custom <span class="wps-count" id="count-custom"></span></button>
				</div>
				<button id="wps-export-btn" class="wps-btn wps-btn-outline">
					<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
					Export CSV
				</button>
			</div>
			<div class="wps-table-wrapper">
				<table id="wps-results-table" class="wps-table">
					<thead>
						<tr>
							<th>Plugin</th>
							<th>Version</th>
							<th>Status</th>
							<th>PHP Required</th>
							<th>URL</th>
							<th>Type</th>
							<th>Notes</th>
						</tr>
					</thead>
					<tbody id="wps-table-body"></tbody>
				</table>
				<div id="wps-empty" class="wps-empty" style="display:none;">No plugins match this filter.</div>
			</div>
		</div>
	</div>
</div>
