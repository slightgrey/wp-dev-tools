(function ( $ ) {
    'use strict';

    $(document).ready(function () {
        var $radios = $('input[name="smwm_format"]');
        var $form   = $('#smwm-plain-editor').closest('form');

        function toggleEditors() {
            var format = $('input[name="smwm_format"]:checked').val();
            if ('html' === format) {
                $('#smwm-plain-editor').hide();
                $('#smwm-html-editor').show();
            } else {
                $('#smwm-html-editor').hide();
                $('#smwm-plain-editor').show();
            }
        }

        $radios.on('change', toggleEditors);
        toggleEditors();

        // Sync TinyMCE content back to the underlying textarea before submit,
        // otherwise the HTML editor content won't be included in the POST data.
        $form.on('submit', function () {
            if (typeof tinyMCE !== 'undefined') {
                tinyMCE.triggerSave();
            }
        });
    });

}(jQuery));
