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
            'nonce' => wp_create_nonce('gct_service_location_module_nonce'),
            'siteurl' => home_url()
        )
    );
}
add_action('wp_enqueue_scripts', 'gct_service_location_module_enqueue_scripts');

/**
 * Check if we're in the Divi Builder preview mode
 */
function gct_is_divi_preview_mode() {
    return (
        (isset($_GET['et_fb']) && $_GET['et_fb'] === '1') || 
        (isset($_GET['et_pb_preview']) && $_GET['et_pb_preview'] === 'true') ||
        (isset($_GET['et_bfb']) && $_GET['et_bfb'] === '1')
    );
}

/**
 * Get placeholder data for the Divi Builder preview mode
 */
function gct_get_preview_service_data() {
    return array(
        'id' => 0,
        'title' => 'Sample Service',
        'content' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p><p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>',
        'permalink' => '#',
        'image' => 'https://placehold.co/800x600/C8E2D4/254B45?text=Service+Image',
        'locations' => array(
            array('id' => 1, 'name' => 'Location Name', 'slug' => 'location-name'),
            array('id' => 2, 'name' => 'Location Name', 'slug' => 'location-name'),
            array('id' => 3, 'name' => 'Location Name', 'slug' => 'location-name'),
            array('id' => 4, 'name' => 'Location Name', 'slug' => 'location-name'),
            array('id' => 5, 'name' => 'Location Name', 'slug' => 'location-name'),
            array('id' => 6, 'name' => 'Location Name', 'slug' => 'location-name'),
            array('id' => 7, 'name' => 'Location Name', 'slug' => 'location-name'),
            array('id' => 8, 'name' => 'Location Name', 'slug' => 'location-name'),
        ),
        'service_type_name' => 'Service Type'
    );
}

/**
 * AJAX handler for getting service data
 */
function gct_service_location_module_get_service_data() {
    // Check if we're in preview mode
    if (gct_is_divi_preview_mode()) {
        wp_send_json_success(gct_get_preview_service_data());
        return;
    }
    
    // Normal AJAX handling
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'gct_service_location_module_nonce')) {
        wp_send_json_error('Invalid nonce');
    }
    
    $service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;
    $service_type = isset($_POST['service_type']) ? sanitize_text_field($_POST['service_type']) : '';
    
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
    $location_terms = get_the_terms($service_id, 'location-category');
    
    if ($location_terms && !is_wp_error($location_terms)) {
        foreach ($location_terms as $term) {
            $locations[] = array(
                'id' => $term->term_id,
                'name' => $term->name,
                'slug' => $term->slug
            );
        }
    }
    
    // Get service type name
    $service_type_name = '';
    if (!empty($service_type)) {
        $service_type_term = get_term_by('slug', $service_type, 'service-type');
        if ($service_type_term && !is_wp_error($service_type_term)) {
            $service_type_name = $service_type_term->name;
        } else {
            // If specific service type not found, try to get the service's own service type
            $service_terms = get_the_terms($service_id, 'service-type');
            if ($service_terms && !is_wp_error($service_terms) && !empty($service_terms)) {
                $service_type_name = $service_terms[0]->name;
            }
        }
    } else {
        // If no service type specified, get from the service itself
        $service_terms = get_the_terms($service_id, 'service-type');
        if ($service_terms && !is_wp_error($service_terms) && !empty($service_terms)) {
            $service_type_name = $service_terms[0]->name;
        }
    }
    
    // Prepare response
    $response = array(
        'id' => $service->ID,
        'title' => $service->post_title,
        'content' => apply_filters('the_content', $service->post_content),
        'permalink' => get_permalink($service->ID),
        'image' => $image,
        'locations' => $locations,
        'service_type_name' => $service_type_name
    );
    
    wp_send_json_success($response);
}
add_action('wp_ajax_gct_service_location_module_get_service_data', 'gct_service_location_module_get_service_data');
add_action('wp_ajax_nopriv_gct_service_location_module_get_service_data', 'gct_service_location_module_get_service_data'); 