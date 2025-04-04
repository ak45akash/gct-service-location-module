<?php
/**
 * GCT Service Location Module
 */
class GCT_Service_Location_Module extends ET_Builder_Module {
    
    public $slug       = 'gct_service_location_module';
    public $vb_support = 'on';
    
    /**
     * Module initialization
     */
    public function init() {
        $this->name = esc_html__('Service & Location Selector', 'gct-service-location-module');
        $this->icon = 'A'; // Custom icon in a future version
        $this->main_css_element = '%%order_class%%';
        
        // Define settings tabs and sections
        $this->settings_modal_toggles = array(
            'general' => array(
                'toggles' => array(
                    'main_content' => esc_html__('Module Settings', 'gct-service-location-module'),
                    'elements' => esc_html__('Elements', 'gct-service-location-module'),
                ),
            ),
            'advanced' => array(
                'toggles' => array(
                    'text' => esc_html__('Text', 'gct-service-location-module'),
                    'layout' => esc_html__('Layout', 'gct-service-location-module'),
                ),
            ),
        );
    }
    
    /**
     * Get module settings fields
     */
    public function get_fields() {
        return array(
            'module_title' => array(
                'label'           => esc_html__('Module Title', 'gct-service-location-module'),
                'type'            => 'text',
                'option_category' => 'basic_option',
                'default'         => esc_html__('Browse locations by service', 'gct-service-location-module'),
                'description'     => esc_html__('The main title displayed above the module.', 'gct-service-location-module'),
                'toggle_slug'     => 'main_content',
            ),
            'default_service' => array(
                'label'           => esc_html__('Default Service', 'gct-service-location-module'),
                'type'            => 'select',
                'option_category' => 'basic_option',
                'options'         => $this->get_service_options(),
                'default'         => '',
                'description'     => esc_html__('Select the default service to display when the page loads.', 'gct-service-location-module'),
                'toggle_slug'     => 'main_content',
            ),
            'service_selector_label' => array(
                'label'           => esc_html__('Service Selector Label', 'gct-service-location-module'),
                'type'            => 'text',
                'option_category' => 'basic_option',
                'default'         => esc_html__('Service', 'gct-service-location-module'),
                'description'     => esc_html__('The label displayed above the service selector.', 'gct-service-location-module'),
                'toggle_slug'     => 'elements',
            ),
            'location_section_title' => array(
                'label'           => esc_html__('Location Section Title', 'gct-service-location-module'),
                'type'            => 'text',
                'option_category' => 'basic_option',
                'default'         => esc_html__('Locations', 'gct-service-location-module'),
                'description'     => esc_html__('The title for the location buttons section.', 'gct-service-location-module'),
                'toggle_slug'     => 'elements',
            ),
            'read_more_text' => array(
                'label'           => esc_html__('Read More Button Text', 'gct-service-location-module'),
                'type'            => 'text',
                'option_category' => 'basic_option',
                'default'         => esc_html__('Read more about', 'gct-service-location-module'),
                'description'     => esc_html__('The text for the read more button. The service name will be appended.', 'gct-service-location-module'),
                'toggle_slug'     => 'elements',
            ),
            'default_image' => array(
                'label'           => esc_html__('Default Service Image', 'gct-service-location-module'),
                'type'            => 'upload',
                'option_category' => 'basic_option',
                'upload_button_text' => esc_attr__('Upload an image', 'gct-service-location-module'),
                'choose_text'     => esc_attr__('Choose an Image', 'gct-service-location-module'),
                'update_text'     => esc_attr__('Set As Image', 'gct-service-location-module'),
                'description'     => esc_html__('Upload a default image for services without featured images.', 'gct-service-location-module'),
                'toggle_slug'     => 'main_content',
            ),
        );
    }
    
    /**
     * Get service options for the dropdown
     */
    private function get_service_options() {
        $options = array(
            '' => esc_html__('Select a Service', 'gct-service-location-module'),
        );
        
        $services = get_posts(array(
            'post_type'      => 'service',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'orderby'        => 'title',
            'order'          => 'ASC',
        ));
        
        if (!empty($services)) {
            foreach ($services as $service) {
                $options[$service->ID] = $service->post_title;
            }
        }
        
        return $options;
    }
    
    /**
     * Render the module output
     */
    public function render($attrs, $content = null, $render_slug) {
        $default_service        = $this->props['default_service'];
        $service_selector_label = $this->props['service_selector_label'];
        $location_section_title = $this->props['location_section_title'];
        $read_more_text         = $this->props['read_more_text'];
        $module_title           = $this->props['module_title'];
        $default_image          = $this->props['default_image'];
        
        // Get all services
        $services = get_posts(array(
            'post_type'      => 'service',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'orderby'        => 'title',
            'order'          => 'ASC',
        ));
        
        // Get default service for initial display
        $first_service = null;
        
        if (!empty($default_service)) {
            // If a default service is selected, use that
            $first_service = get_post($default_service);
        } elseif (!empty($services)) {
            // Otherwise, use the first service in the list
            $first_service = $services[0];
        }
        
        // Start output buffering
        ob_start();
        
        ?>
        <?php if (!empty($module_title)) : ?>
        <h1 class="gct-module-title"><?php echo esc_html($module_title); ?></h1>
        <?php endif; ?>
        
        <div class="gct-service-location-module" data-nonce="<?php echo wp_create_nonce('gct_service_location_module_nonce'); ?>">
            <!-- Service Info Container (Left side) -->
            <div class="gct-service-info-container">
                <!-- Service info will be dynamically loaded via JS, but provide default for first load -->
                <?php if ($first_service) : 
                    $title = $first_service->post_title;
                    $content = $first_service->post_content;
                    $image = get_the_post_thumbnail_url($first_service->ID, 'full');
                    if (empty($image) && !empty($default_image)) {
                        $image = $default_image;
                    }
                ?>
                <div class="gct-service-title"><?php echo esc_html($title); ?></div>
                <?php if (!empty($image)) : ?>
                <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($title); ?>" class="gct-service-image">
                <?php endif; ?>
                <div class="gct-service-description"><?php echo wp_kses_post($content); ?></div>
                <a href="<?php echo esc_url(get_permalink($first_service->ID)); ?>" class="gct-read-more-button"><?php echo esc_html($read_more_text . ' ' . $title); ?></a>
                <?php endif; ?>
            </div>
            
            <!-- Service Selection Container (Right side) -->
            <div class="gct-service-selection-container">
                <!-- Service Type Selector -->
                <div class="gct-service-selector">
                    <label for="gct-service-select-<?php echo esc_attr($this->order_class_name); ?>"><?php echo esc_html($service_selector_label); ?></label>
                    <select id="gct-service-select-<?php echo esc_attr($this->order_class_name); ?>" class="gct-service-select">
                        <option value=""><?php esc_html_e('Select a service', 'gct-service-location-module'); ?></option>
                        <?php foreach ($services as $service) : ?>
                            <option value="<?php echo esc_attr($service->ID); ?>" <?php selected($first_service && $first_service->ID == $service->ID); ?>>
                                <?php echo esc_html($service->post_title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Location Section Title -->
                <h3 class="gct-location-section-title"><?php echo esc_html($location_section_title); ?></h3>
                
                <!-- Location Buttons -->
                <div class="gct-location-buttons">
                    <!-- Location buttons will be dynamically loaded via JS -->
                    <?php if ($first_service) : 
                        $location_terms = get_the_terms($first_service->ID, 'location_category');
                        if ($location_terms && !is_wp_error($location_terms) && !empty($location_terms)) :
                            foreach ($location_terms as $term) : ?>
                                <a href="#" class="gct-location-button" data-location-id="<?php echo esc_attr($term->term_id); ?>"><?php echo esc_html($term->name); ?></a>
                            <?php endforeach;
                        else: ?>
                            <p><?php esc_html_e('No locations available for this service.', 'gct-service-location-module'); ?></p>
                        <?php endif;
                    endif; ?>
                </div>
            </div>
        </div>
        <?php
        
        // Enqueue the JavaScript for frontend functionality
        wp_enqueue_script('jquery');
        
        $script = "
            (function($) {
                $(document).ready(function() {
                    const ajaxurl = '" . esc_url(admin_url('admin-ajax.php')) . "';
                    const nonce = $('.gct-service-location-module').data('nonce');
                    
                    $('.gct-service-select').on('change', function() {
                        const serviceId = $(this).val();
                        if (!serviceId) return;
                        
                        const $module = $(this).closest('.gct-service-location-module');
                        const $serviceInfo = $module.find('.gct-service-info-container');
                        const $locationButtons = $module.find('.gct-location-buttons');
                        
                        // Add loading state
                        $module.addClass('gct-loading');
                        
                        // AJAX request to get service data
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'gct_service_location_module_get_service_data',
                                service_id: serviceId,
                                nonce: nonce
                            },
                            success: function(response) {
                                if (response.success && response.data) {
                                    // Update service info
                                    let imageHtml = '';
                                    if (response.data.image) {
                                        imageHtml = `<img src=\"${response.data.image}\" alt=\"${response.data.title}\" class=\"gct-service-image\">`;
                                    }
                                    
                                    $serviceInfo.html(`
                                        <div class=\"gct-service-title\">${response.data.title}</div>
                                        ${imageHtml}
                                        <div class=\"gct-service-description\">${response.data.content}</div>
                                        <a href=\"${response.data.permalink}\" class=\"gct-read-more-button\">" . esc_js($read_more_text) . " ${response.data.title}</a>
                                    `);
                                    
                                    // Update location buttons
                                    if (response.data.locations && response.data.locations.length > 0) {
                                        let buttonsHtml = '';
                                        response.data.locations.forEach(function(location) {
                                            buttonsHtml += `<a href=\"#\" class=\"gct-location-button\" data-location-id=\"${location.id}\">${location.name}</a>`;
                                        });
                                        $locationButtons.html(buttonsHtml);
                                    } else {
                                        $locationButtons.html('<p>No locations available for this service.</p>');
                                    }
                                }
                            },
                            error: function() {
                                console.error('Error fetching service data');
                            },
                            complete: function() {
                                // Remove loading state
                                $module.removeClass('gct-loading');
                            }
                        });
                    });
                });
            })(jQuery);
        ";
        
        wp_add_inline_script('jquery', $script);
        
        return ob_get_clean();
    }
}

new GCT_Service_Location_Module;

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
        'content' => apply_filters('the_content', $service->post_content),
        'permalink' => get_permalink($service->ID),
        'image' => $image,
        'locations' => $locations
    );
    
    wp_send_json_success($response);
}
add_action('wp_ajax_gct_service_location_module_get_service_data', 'gct_service_location_module_get_service_data');
add_action('wp_ajax_nopriv_gct_service_location_module_get_service_data', 'gct_service_location_module_get_service_data'); 