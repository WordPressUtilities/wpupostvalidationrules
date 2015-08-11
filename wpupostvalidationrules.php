<?php

/*
Plugin Name: WPU Post Validation Rules
Plugin URI: http://github.com/Darklg/WPUtilities
Version: 0.1
Description: Add validation rules before saving a WordPress post
Author: Darklg
Author URI: http://darklg.me/
License: MIT License
License URI: http://opensource.org/licenses/MIT
*/

class WPUPostValidationRules {

    public $options = array(
        'plugin_version' => '0.1'
    );

    function __construct() {
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

    function wp_enqueue_scripts() {
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
            exit('/*wpupostvalidationrules_notok1*/' . json_encode($messages));
        }

        // Or return an ok string
        else {
            echo "/*wpupostvalidationrules_isok1*/";
        }
    }

    function publish_hook() {
        $validation_nonce = wp_create_nonce('wpupostvalidationrules_prepublishnonce');
        echo '<script type="text/javascript">/* <![CDATA[ */';
        echo 'window.wpupostvalidationrulesnonce="' . $validation_nonce . '";';
        echo 'window.wpupostvalidationrules__message="' . esc_attr("Please correct the following errors:") . '";';
        echo '/* ]]> */</script>';
    }
}

$WPUPostValidationRules = new WPUPostValidationRules();

// Prevent the word "az" in a post content
add_filter('wpupostvalidationrules_ruleslist', 'myproject_neveraz', 10, 2);
function myproject_neveraz($messages, $content) {
    if (strpos($content,'az') !== false) {
        $messages[] = 'The content should not contain az.';
    }
    return $messages;
}