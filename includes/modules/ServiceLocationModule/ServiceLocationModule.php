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
        $this->name = 'Service & Location Selector';
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
            'service_type' => array(
                'label'           => esc_html__('Service Type', 'gct-service-location-module'),
                'type'            => 'select',
                'option_category' => 'basic_option',
                'options'         => $this->get_service_type_options(),
                'default'         => '',
                'description'     => esc_html__('Filter services by this service type.', 'gct-service-location-module'),
                'toggle_slug'     => 'main_content',
                'affects'         => array('default_service'),
            ),
            'default_service' => array(
                'label'           => esc_html__('Default Service', 'gct-service-location-module'),
                'type'            => 'select',
                'option_category' => 'basic_option',
                'options'         => array(),  // Will be populated dynamically based on service_type
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
     * Get service type options for the dropdown
     */
    private function get_service_type_options() {
        $options = array(
            '' => esc_html__('Select a Service Type', 'gct-service-location-module'),
        );
        
        $service_types = get_terms(array(
            'taxonomy'   => 'service-type',
            'hide_empty' => true,
        ));
        
        if (!is_wp_error($service_types) && !empty($service_types)) {
            foreach ($service_types as $service_type) {
                $options[$service_type->slug] = $service_type->name;
            }
        }
        
        return $options;
    }
    
    /**
     * Get service options for the dropdown based on service type
     */
    public function get_service_options($service_type = '') {
        $options = array(
            '' => esc_html__('Select a Service', 'gct-service-location-module'),
        );
        
        $args = array(
            'post_type'      => 'service',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'orderby'        => 'title',
            'order'          => 'ASC',
        );
        
        // Filter by service type if specified
        if (!empty($service_type)) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'service-type',
                    'field'    => 'slug',
                    'terms'    => $service_type,
                ),
            );
        }
        
        $services = get_posts($args);
        
        if (!empty($services)) {
            foreach ($services as $service) {
                $options[$service->ID] = $service->post_title;
            }
        }
        
        return $options;
    }
    
    /**
     * Process dynamic fields before render
     */
    public function process_dynamic_content($content, $args) {
        if (isset($args['field']) && $args['field'] === 'default_service' && isset($args['attrs']['service_type'])) {
            $service_type = $args['attrs']['service_type'];
            return wp_json_encode($this->get_service_options($service_type));
        }
        
        return parent::process_dynamic_content($content, $args);
    }
    
    /**
     * Additional method for Visual Builder compatibility
     */
    public function get_fields_sanitized() {
        $fields = $this->get_fields();
        
        // Ensure default_service options are available
        if (isset($fields['default_service'])) {
            $fields['default_service']['options'] = $this->get_service_options();
        }
        
        return $fields;
    }
    
    /**
     * Render the module output
     */
    public function render($attrs, $content = null, $render_slug) {
        // Get the service type name
        $service_type_name = '';
        $service_type = $this->props['service_type'];
        if (!empty($service_type)) {
            $service_type_term = get_term_by('slug', $service_type, 'service-type');
            if ($service_type_term && !is_wp_error($service_type_term)) {
                $service_type_name = $service_type_term->name;
            }
        }
        
        // Get props with defaults
        $module_title = isset($this->props['module_title']) ? $this->props['module_title'] : esc_html__('Browse Locations by Service', 'gct-service-location-module');
        $service_selector_label = $this->props['service_selector_label'];
        $location_section_title = $this->props['location_section_title'];
        $default_service = $this->props['default_service'];
        
        // Get service options based on service type
        $service_options = $this->get_service_options($service_type);
        
        // Check if we're in preview mode
        $is_preview = function_exists('gct_is_divi_preview_mode') ? gct_is_divi_preview_mode() : false;
        
        // If in preview mode, create a fake service object for display purposes
        if ($is_preview) {
            // Create a sample dropdown of services
            $service_options = array(
                '' => 'Select a service',
                'sample-service-1' => 'Rose Memorials',
                'sample-service-2' => 'Granite Memorials',
                'sample-service-3' => 'Bronze Plaques'
            );
            
            // Default selected service for preview
            $default_service = 'sample-service-1';
        }
        
        // Create the select element HTML
        $service_select_html = sprintf(
            '<select name="gct-service-select" class="gct-service-select" data-service-type="%s">',
            esc_attr($service_type)
        );
        
        // Add options
        foreach ($service_options as $value => $label) {
            $selected = $value === $default_service ? 'selected' : '';
            $service_select_html .= sprintf(
                '<option value="%s" %s>%s</option>',
                esc_attr($value),
                $selected,
                esc_html($label)
            );
        }
        
        $service_select_html .= '</select>';
        
        // Get default service data for initial load if not in preview mode
        $default_service_object = null;
        $default_service_image = '';
        $default_service_title = '';
        $default_service_content = '';
        $default_service_permalink = '#';
        $default_locations = array();
        
        if ($is_preview) {
            // Use sample data for preview
            $default_service_title = 'Rose Memorials';
            $default_service_content = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.';
            $default_service_image = 'https://placehold.co/600x400/C8E2D4/254B45?text=Service+Image';
            
            // Sample locations for preview
            $default_locations = array(
                array('id' => 1, 'name' => 'Location Name', 'slug' => 'location-name'),
                array('id' => 2, 'name' => 'Location Name', 'slug' => 'location-name'),
                array('id' => 3, 'name' => 'Location Name', 'slug' => 'location-name'),
                array('id' => 4, 'name' => 'Location Name', 'slug' => 'location-name'),
                array('id' => 5, 'name' => 'Location Name', 'slug' => 'location-name'),
                array('id' => 6, 'name' => 'Location Name', 'slug' => 'location-name'),
                array('id' => 7, 'name' => 'Location Name', 'slug' => 'location-name'),
                array('id' => 8, 'name' => 'Location Name', 'slug' => 'location-name'),
            );
        } elseif (!empty($default_service)) {
            // Get service object
            $default_service_object = get_post($default_service);
            
            if ($default_service_object) {
                $default_service_title = $default_service_object->post_title;
                $default_service_content = apply_filters('the_content', $default_service_object->post_content);
                $default_service_permalink = get_permalink($default_service_object->ID);
                
                // Get service image
                $default_service_image = get_the_post_thumbnail_url($default_service_object->ID, 'full');
                if (!$default_service_image) {
                    // Use a placeholder if no image is set
                    $default_service_image = 'https://placehold.co/600x400/C8E2D4/254B45?text=Service+Image';
                }
                
                // Get locations for this service
                $location_terms = get_the_terms($default_service_object->ID, 'location-category');
                if ($location_terms && !is_wp_error($location_terms)) {
                    foreach ($location_terms as $term) {
                        $default_locations[] = array(
                            'id' => $term->term_id,
                            'name' => $term->name,
                            'slug' => $term->slug
                        );
                    }
                }
            }
        }
        
        // Build location buttons HTML
        $location_buttons_html = '';
        if (!empty($default_locations)) {
            foreach ($default_locations as $location) {
                $location_url = home_url('/resource/location/' . $location['slug'] . '/');
                $location_buttons_html .= sprintf(
                    '<a href="%s" class="gct-location-button" data-location-id="%d">%s</a>',
                    esc_url($location_url),
                    esc_attr($location['id']),
                    esc_html($location['name'])
                );
            }
        } else {
            $location_buttons_html = '<p>No locations available for this service.</p>';
        }
        
        // Build the module HTML - Updated to match the structure in preview.html
        $output = '';
        
        // Add module title if present
        if (!empty($module_title)) {
            $output .= sprintf(
                '<h2 class="gct-module-title">%s</h2>',
                esc_html($module_title)
            );
        }
        
        $output .= sprintf(
            '<div class="gct-service-location-module" data-nonce="%s" data-service-type="%s">
                <div class="gct-service-dropdown-container">
                    %s
                </div>
                <div class="gct-service-content-container">
                    <div class="gct-service-info-container">
                        <div class="gct-service-content-wrapper">
                            <div class="gct-service-image-container">
                                <img src="%s" alt="%s" class="gct-service-image">
                            </div>
                            <div class="gct-service-content">
                                <h3 class="gct-service-title">%s</h3>
                                <div class="gct-service-description">%s</div>
                                
                                <h3 class="gct-location-section-title">%s</h3>
                                <div class="gct-location-buttons">%s</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>',
            wp_create_nonce('gct_service_location_module_nonce'),
            esc_attr($service_type),
            $service_select_html,
            esc_url($default_service_image),
            esc_attr($default_service_title),
            esc_html($default_service_title),
            $default_service_content,
            esc_html($location_section_title),
            $location_buttons_html
        );
        
        return $output;
    }
}

new GCT_Service_Location_Module; 