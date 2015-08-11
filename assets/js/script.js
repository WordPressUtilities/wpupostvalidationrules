jQuery(document).ready(function validator_ready() {
    jQuery('#post').submit(validator_ready_submit);
});

/* ----------------------------------------------------------
  Executed at post form validation
---------------------------------------------------------- */

function validator_ready_submit(e) {
    var $form = jQuery(this);
    if ($form.attr('data-validator-can-submit') == '1') {
        $form.attr('data-validator-can-submit', '0');
        return true;
    }
    e.preventDefault();
    var form_data = $form.serializeArray();
    var data = {
        action: 'wpupostvalidationrules_ajax_hook',
        security: window.wpupostvalidationrulesnonce,
        form_data: jQuery.param(form_data)
    };
    jQuery.post(ajaxurl, data, function(response) {
        jQuery('#ajax-loading').hide();
        jQuery('.wpupostvalidationrules-message').remove();
        jQuery('#publish').removeClass('button-primary-disabled');
        var sep = '<br />• ';
        if (response.indexOf('wpupostvalidationrules_notok1') > -1) {
            response = response.replace('\/*wpupostvalidationrules_notok1*\/', '');
            message = window.wpupostvalidationrules__message + sep + jQuery.parseJSON(response).join(sep);
            jQuery('<div onclick="jQuery(this).remove();" class="wpupostvalidationrules-message error notice notice-error below-h2"><p>' + message + '</p></div>').insertAfter('#local-storage-notice');
        }
        else {
            $form.attr('data-validator-can-submit', '1').submit();
        }
    });
}