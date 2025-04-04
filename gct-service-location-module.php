<?php
/**
 * Plugin Name: GCT Service Location Module
 * Plugin URI: 
 * Description: Custom Divi module for Service Selection & Dynamic Location Display
 * Version: 1.0.0
 * Author: 
 * Author URI: 
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: gct-service-location-module
 * Domain Path: /languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register the custom module
 */
function gct_service_location_module_init() {
    if (class_exists('ET_Builder_Module')) {
        require_once plugin_dir_path(__FILE__) . 'includes/modules/ServiceLocationModule/ServiceLocationModule.php';
    }
}
add_action('et_builder_ready', 'gct_service_location_module_init');

/**
 * Load plugin textdomain
 */
function gct_service_location_module_load_textdomain() {
    load_plugin_textdomain('gct-service-location-module', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'gct_service_location_module_load_textdomain');

/**
 * Enqueue scripts and styles
 */
function gct_service_location_module_enqueue_scripts() {
    wp_enqueue_style(
        'gct-service-location-module-styles',
        plugin_dir_url(__FILE__) . 'assets/css/style.css',
        array(),
        '1.0.0'
    );
    
    wp_enqueue_script(
        'gct-service-location-module-scripts',
        plugin_dir_url(__FILE__) . 'assets/js/script.js',
        array('jquery'),
        '1.0.0',
        true
    );
    
    wp_localize_script(
        'gct-service-location-module-scripts',
        'gctServiceLocationModule',
        array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('gct_service_location_module_nonce')
        )
    );
}
add_action('wp_enqueue_scripts', 'gct_service_location_module_enqueue_scripts');

/**
 * AJAX handler for getting service data
 */
function gct_service_location_module_get_service_data() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'gct_service_location_module_nonce')) {
        wp_send_json_error('Invalid nonce');
    }
    
    $service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;
    
    if (!$service_id) {
        wp_send_json_error('Invalid service ID');
    }
    
    // Get service data
    $service = get_post($service_id);
    
    if (!$service) {
        wp_send_json_error('Service not found');
    }
    
    // Get service image
    $image = get_the_post_thumbnail_url($service_id, 'full');
    
    // Get service locations
    $locations = array();
    $location_terms = get_the_terms($service_id, 'location_category');
    
    if ($location_terms && !is_wp_error($location_terms)) {
        foreach ($location_terms as $term) {
            $locations[] = array(
                'id' => $term->term_id,
                'name' => $term->name,
                'slug' => $term->slug
            );
        }
    }
    
    // Prepare response
    $response = array(
        'id' => $service->ID,
        'title' => $service->post_title,
        'content' => $service->post_content,
        'image' => $image,
        'locations' => $locations
    );
    
    wp_send_json_success($response);
}
add_action('wp_ajax_gct_service_location_module_get_service_data', 'gct_service_location_module_get_service_data');
add_action('wp_ajax_nopriv_gct_service_location_module_get_service_data', 'gct_service_location_module_get_service_data'); 