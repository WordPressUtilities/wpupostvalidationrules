<?php

/*
Plugin Name: WPU Post Validation Rules
Plugin URI: http://github.com/Darklg/WPUtilities
Version: 0.3
Description: Add validation rules before saving a WordPress post
Author: Darklg
Author URI: http://darklg.me/
License: MIT License
License URI: http://opensource.org/licenses/MIT
Contributor: @boiteaweb
*/

class WPUPostValidationRules {

    public $options = array(
        'plugin_version' => '0.3'
    );

    /* Init */

    function init() {
        load_plugin_textdomain('wpupostvalidationrules', false, dirname(plugin_basename(__FILE__)) . '/lang/');

        add_action('init', array(&$this,
            'settings'
        ));
        add_action('in_admin_footer', array(&$this,
            'publish_hook'
        ));
        add_action('wp_ajax_wpupostvalidationrules_ajax_hook', array(&$this,
            'wpupostvalidationrules_ajax_hook'
        ));
        add_action('admin_enqueue_scripts', array(&$this,
            'wp_enqueue_scripts'
        ));
        add_filter('content_save_pre', array(&$this,
            'content_save_pre'
        ) , 10, 1);
        add_action('edit_form_top', array(&$this,
            'display_messages'
        ));
    }

    function settings() {
        $this->error_message = __('Please correct the following errors:', 'wpupostvalidationrules');
        $this->error_separator = '<br />• ';
        $this->errormsg_classes = 'wpupostvalidationrules-message error notice notice-error is-dismissible below-h2';
        $this->errormsg_button = '<button type="button" class="notice-dismiss"><span class="screen-reader-text">&times;</span></button>';
        $this->transient = 'wpupostvalidationrules_notices_' . get_current_user_id();
    }

    /* Plugin assets */

    function wp_enqueue_scripts() {
        $screen = get_current_screen();
        if ($screen->base != 'post') {
            return;
        }
        wp_enqueue_script('wpuoptions_scripts', plugins_url('assets/js/script.js', __FILE__) , array(
            'jquery',
        ) , $this->options['plugin_version']);
    }

    /* Code validation */

    function validate_content($content) {
        $messages = array();
        return apply_filters('wpupostvalidationrules_ruleslist', $messages, $content);
    }

    /* Manual hooks */

    function content_save_pre($content) {
        global $post;

        if (isset($_POST['data'], $_POST['data']['wp_autosave'], $_POST['data']['wp_autosave'], $_POST['data']['wp_autosave']['post_id'])) {
            $post = get_post($_POST['data']['wp_autosave']['post_id']);
        }

        if (is_object($post)) {
            $messages = $this->validate_content($content);
            if (!empty($messages)) {
                $content = $post->post_content;
                $this->add_message($this->set_error_message($messages));
            }
        }

        return $content;
    }

    /* AJAX */

    function wpupostvalidationrules_ajax_hook() {

        // Security check
        check_ajax_referer('wpupostvalidationrules_prepublishnonce', 'security');

        // Apply user filters
        $messages = array();
        if (isset($_POST['form_data'])) {
            parse_str($_POST['form_data'], $datas);
            if (isset($datas['content'])) {
                $messages = $this->validate_content($datas['content']);
            }
            else {
                $messages[] = __('There should be a content here.', 'wpupostvalidationrules');
            }
        }

        // If messages, return them
        if (!empty($messages)) {
            wp_send_json_error($messages);
        }
        wp_send_json_success();
    }

    function publish_hook() {
        $validation_nonce = wp_create_nonce('wpupostvalidationrules_prepublishnonce');
        echo '<script type="text/javascript">/* <![CDATA[ */';
        echo 'window.wpupostvalidationrulesnonce="' . $validation_nonce . '";';
        echo 'window.wpupostvalidationrules__message="' . esc_js($this->error_message) . '";';
        echo 'window.wpupostvalidationrules__separator="' . $this->error_separator . '";';
        echo 'window.wpupostvalidationrules__msgclasses="' . $this->errormsg_classes . '";';
        echo 'window.wpupostvalidationrules__msgbutton="' . addslashes($this->errormsg_button) . '";';
        echo '/* ]]> */</script>';
    }

    /* Notices */

    function set_error_message($messages) {
        return $this->error_message . $this->error_separator . implode($this->error_separator, $messages);
    }

    function add_message($content) {
        $messages = $this->get_messages();
        $messages[] = $content;
        set_transient($this->transient, $messages, 5 * MINUTE_IN_SECONDS);
    }

    function delete_messages() {
        delete_transient($this->transient);
    }

    function display_messages() {
        $messages = $this->get_messages();

        if (!empty($messages)) {
            foreach ($messages as $message) {
                echo '<div class="' . $this->errormsg_classes . '"><p>' . implode($messages) . '</p></div>';
            }
        }

        $this->delete_messages();
    }

    function get_messages() {
        $messages = get_transient($this->transient);
        if (!is_array($messages)) {
            $messages = array();
        }
        return $messages;
    }
}

$WPUPostValidationRules = new WPUPostValidationRules();
add_action('plugins_loaded', 'launch_WPUPostValidationRules');
function launch_WPUPostValidationRules() {
    global $WPUPostValidationRules;
    if (is_admin()) {
        $WPUPostValidationRules->init();
    }
}


