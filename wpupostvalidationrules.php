<?php

/*
Plugin Name: WPU Post Validation Rules
Plugin URI: http://github.com/Darklg/WPUtilities
Version: 0.2
Description: Add validation rules before saving a WordPress post
Author: Darklg
Author URI: http://darklg.me/
License: MIT License
License URI: http://opensource.org/licenses/MIT
*/

class WPUPostValidationRules {

    public $options = array(
        'plugin_version' => '0.2'
    );

    function __construct() {
        if (!is_admin()) {
            return;
        }
        add_action('init', array(&$this,
            'load_plugin_textdomain'
        ));
        add_action('init', array(&$this,
            'init'
        ));
    }

    /* Init */

    function init() {
        add_action('in_admin_footer', array(&$this,
            'publish_hook'
        ));
        add_action('wp_ajax_wpupostvalidationrules_ajax_hook', array(&$this,
            'wpupostvalidationrules_ajax_hook'
        ));
        add_action('admin_enqueue_scripts', array(&$this,
            'wp_enqueue_scripts'
        ));
    }

    /* Plugin assets */

    function load_plugin_textdomain() {
        load_plugin_textdomain('wpupostvalidationrules', false, dirname(plugin_basename(__FILE__)) . '/lang/');
    }

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

    function wpupostvalidationrules_ajax_hook() {

        // Security check
        check_ajax_referer('wpupostvalidationrules_prepublishnonce', 'security');

        // Apply user filters
        $messages = array();
        if (isset($_POST['form_data'])) {
            parse_str($_POST['form_data'], $datas);
            if (isset($datas['content'])) {
                $messages = apply_filters('wpupostvalidationrules_ruleslist', $messages, $datas['content']);
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
        echo 'window.wpupostvalidationrules__message="' . esc_attr(__('Please correct the following errors:', 'wpupostvalidationrules')) . '";';
        echo '/* ]]> */</script>';
    }
}

$WPUPostValidationRules = new WPUPostValidationRules();

