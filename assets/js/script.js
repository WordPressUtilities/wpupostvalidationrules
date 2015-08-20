jQuery(document).ready(function validator_ready() {
    jQuery('#post').attr('data-validator-can-submit', '0');
    jQuery('#post').on('submit',wpupostvalidationrules_ready_submit);
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
        var sep = window.wpupostvalidationrules__separator;
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
        button = window.wpupostvalidationrules__msgbutton;
    block_message.addClass(window.wpupostvalidationrules__msgclasses);
    block_message.on('click', '.notice-dismiss', function(e) {
        e.preventDefault();
        block_message.remove();
    });
    block_message.html('<p>' + message + '</p>');
    block_message.append(button);
    block_message.insertAfter('#local-storage-notice');
}