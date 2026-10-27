/* Dev Tools Admin */
/* global atDt, jQuery */
(function ($) {
	'use strict';

	/* ── Table filter ─────────────────────────────────────────── */
	window.atDtFilterTable = function (input) {
		var tableId = input.getAttribute('data-table');
		var table   = document.getElementById(tableId);
		if (!table) return;
		var filter  = input.value.toLowerCase();
		table.querySelectorAll('tbody tr').forEach(function (row) {
			row.style.display = row.textContent.toLowerCase().indexOf(filter) === -1 ? 'none' : '';
		});
	};

	/* ── Row action dispatcher ────────────────────────────────── */
	window.atDtAction = function (btn) {
		var action = btn.getAttribute('data-action');

		var handlers = {
			delete_transient: function () {
				atDtAjax(
					{ action_type: 'delete_option', name: btn.getAttribute('data-name') },
					function () { removeRow(btn); }
				);
			},
			delete_option: function () {
				if (!confirm('Delete this option? This cannot be undone.')) return;
				atDtAjax(
					{ action_type: 'delete_option', name: btn.getAttribute('data-name') },
					function () { removeRow(btn); }
				);
			},
			delete_post: function () {
				if (!confirm('Permanently delete this item?')) return;
				atDtAjax(
					{ action_type: 'delete_post', post_id: btn.getAttribute('data-post-id') },
					function () { removeRow(btn); }
				);
			},
			delete_meta: function () {
				atDtAjax(
					{
						action_type: 'delete_meta',
						meta_type:   btn.getAttribute('data-meta-type'),
						object_id:   btn.getAttribute('data-object-id'),
						meta_key:    btn.getAttribute('data-meta-key'),
					},
					function () { removeRow(btn); }
				);
			},
		};

		if (handlers[action]) {
			handlers[action]();
		}
	};

	function removeRow(btn) {
		var row = btn.closest('tr');
		if (row) row.remove();
	}

	/* ── AJAX helper ──────────────────────────────────────────── */
	function atDtAjax(data, onSuccess) {
		$.ajax({
			url:    atDt.ajaxUrl,
			method: 'POST',
			data:   $.extend({ action: 'at_dt_action', nonce: atDt.nonce }, data),
			success: function (res) {
				if (res.success) {
					if (onSuccess) onSuccess(res.data);
				} else {
					atDtNotice(res.data && res.data.message ? res.data.message : 'An error occurred.', 'error');
				}
			},
			error: function () {
				atDtNotice('Request failed. Please try again.', 'error');
			},
		});
	}

	window.atDtAjax = atDtAjax;

	/* ── Delete cookie (client-side) ──────────────────────────── */
	$(document).on('click', '.at-dt-delete-cookie', function () {
		var name   = $(this).data('name');
		var domain = window.location.hostname;
		document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; domain=' + domain + ';';
		document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
		$(this).closest('tr').remove();
	});

	/* ── Destroy session ──────────────────────────────────────── */
	$(document).on('click', '#at-dt-destroy-session', function (e) {
		e.preventDefault();
		if (!confirm('Destroy the current PHP session?')) return;
		atDtAjax({ action_type: 'destroy_session' }, function () {
			atDtNotice('Session destroyed.', 'success');
			$('#at-dt-session-content').html('<em style="color:#6b7280">Session cleared.</em>');
		});
	});

	/* ── File editor save ─────────────────────────────────────── */
	$(document).on('click', '.at-dt-save-file-btn', function (e) {
		e.preventDefault();
		var $btn      = $(this);
		var $textarea = $('#' + $btn.data('target'));
		var filePath  = $btn.data('file');

		$btn.prop('disabled', true).text('Saving…');

		$.ajax({
			url:    atDt.ajaxUrl,
			method: 'POST',
			data:   {
				action:      'at_dt_action',
				nonce:       atDt.nonce,
				action_type: 'save_file',
				file:        filePath,
				content:     $textarea.val(),
			},
			success: function (res) {
				$btn.prop('disabled', false).text('Save File');
				if (res.success) {
					atDtNotice('File saved successfully.', 'success');
				} else {
					atDtNotice(res.data && res.data.message ? res.data.message : 'Save failed.', 'error');
				}
			},
			error: function () {
				$btn.prop('disabled', false).text('Save File');
				atDtNotice('Request failed.', 'error');
			},
		});
	});

	/* ── Log: clear ───────────────────────────────────────────── */
	$(document).on('click', '#at-dt-clear-log', function (e) {
		e.preventDefault();
		if (!confirm('Clear the debug log? This cannot be undone.')) return;

		atDtAjax({ action_type: 'clear_log' }, function () {
			$('#at-dt-log-content').text('Log cleared.');
			atDtNotice('Log cleared.', 'success');
		});
	});

	/* ── Log: download ────────────────────────────────────────── */
	$(document).on('click', '#at-dt-download-log', function (e) {
		e.preventDefault();
		$.ajax({
			url:    atDt.ajaxUrl,
			method: 'POST',
			data:   { action: 'at_dt_action', nonce: atDt.nonce, action_type: 'download_log' },
			success: function (res) {
				if (res.success && res.data && res.data.content) {
					var blob = new Blob([res.data.content], { type: 'text/plain' });
					var a    = document.createElement('a');
					a.href     = URL.createObjectURL(blob);
					a.download = 'debug.log';
					a.click();
					URL.revokeObjectURL(a.href);
				}
			},
		});
	});

	/* ── PHP testing sandbox ──────────────────────────────────── */
	$(document).on('click', '#at-dt-run-php', function (e) {
		e.preventDefault();
		var code = $('#at-dt-php-code').val();
		var $out = $('#at-dt-php-output');

		$out.text('Running…');

		atDtAjax({ action_type: 'run_php', code: code }, function (data) {
			var output = '';
			if (data.error) {
				output = '// Error:\n' + data.error;
			} else {
				if (data.output)                           output += data.output;
				if (data.return && data.return !== 'NULL') output += '\n// Return: ' + data.return;
			}
			$out.text(output || '// (no output)');
		});
	});

	/* ── Metadata tabs ────────────────────────────────────────── */
	$(document).on('click', '.at-dt-tab', function () {
		var target = $(this).data('target');
		$('.at-dt-tab').css({ borderBottom: '2px solid transparent', color: '#6b7280', fontWeight: '400' });
		$(this).css({ borderBottom: '2px solid #111827', color: '#111827', fontWeight: '600' });
		$('.at-dt-tab-panel').hide();
		$('#' + target).show();
	});

	/* ── Toast notice ─────────────────────────────────────────── */
	function atDtNotice(msg, type) {
		var ok = type === 'success';
		var $n = $('<div>').text(msg).css({
			position:     'fixed',
			top:          '32px',
			right:        '20px',
			zIndex:       99999,
			padding:      '10px 16px',
			borderRadius: '6px',
			fontSize:     '13px',
			background:   ok ? '#dcfce7' : '#fee2e2',
			color:        ok ? '#166534' : '#991b1b',
			border:       '1px solid ' + (ok ? '#bbf7d0' : '#fecaca'),
			boxShadow:    '0 2px 8px rgba(0,0,0,.1)',
		});
		$('body').append($n);
		setTimeout(function () { $n.fadeOut(300, function () { $(this).remove(); }); }, 3000);
	}

}(jQuery));
