jQuery(document).ready(function validator_ready() {
    jQuery('#post').submit(wpupostvalidationrules_ready_submit);
});

/* ----------------------------------------------------------
  Executed at post form validation
---------------------------------------------------------- */

function wpupostvalidationrules_ready_submit(e) {
    var $form = jQuery(this);
    if ($form.attr('data-validator-can-submit') == '1') {
        $form.attr('data-validator-can-submit', '0');
        return true;
    }
    e.preventDefault();
    var data = {
        action: 'wpupostvalidationrules_ajax_hook',
        security: window.wpupostvalidationrulesnonce,
        form_data: jQuery.param($form.serializeArray())
    };
    jQuery.post(ajaxurl, data, function(response) {
        if (!response.hasOwnProperty('success')) {
            return false;
        }
        jQuery('#ajax-loading').hide();
        jQuery('.wpupostvalidationrules-message').remove();
        jQuery('#publish').removeClass('button-primary-disabled');
        var sep = '<br />• ';
        if (!response.success) {
            message = window.wpupostvalidationrules__message + sep + response.data.join(sep);
            wpupostvalidationrules_insert_error_message(message);
        }
        else {
            $form.attr('data-validator-can-submit', '1').submit();
        }
    });
}

/* ----------------------------------------------------------
  Insert error message
---------------------------------------------------------- */

function wpupostvalidationrules_insert_error_message(message) {
    var block_message = jQuery('<div></div>'),
        button = '<button type="button" class="notice-dismiss"><span class="screen-reader-text">&times;</span></button>';
    block_message.addClass('wpupostvalidationrules-message error notice notice-error is-dismissible below-h2');
    block_message.on('click', '.notice-dismiss', function(e) {
        e.preventDefault();
        block_message.remove();
    });
    block_message.html('<p>' + message + '</p>');
    block_message.append(button);
    block_message.insertAfter('#local-storage-notice');
}