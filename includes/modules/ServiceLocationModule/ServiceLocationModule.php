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
        $service_type           = $this->props['service_type'];
        $default_service        = $this->props['default_service'];
        $service_selector_label = $this->props['service_selector_label'];
        $location_section_title = $this->props['location_section_title'];
        $read_more_text         = $this->props['read_more_text'];
        $module_title           = $this->props['module_title'];
        $default_image          = $this->props['default_image'];
        
        // Get services based on service type
        $args = array(
            'post_type'      => 'service',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'orderby'        => 'title',
            'order'          => 'ASC',
        );
        
        // Filter services by service type if specified
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
        
        // Get default service for initial display
        $first_service = null;
        
        if (!empty($default_service)) {
            // If a default service is selected, use that if it belongs to the current service type
            $temp_service = get_post($default_service);
            
            if ($temp_service) {
                // Check if it belongs to the selected service type
                if (empty($service_type)) {
                    $first_service = $temp_service;
                } else {
                    $service_terms = get_the_terms($temp_service->ID, 'service-type');
                    if ($service_terms && !is_wp_error($service_terms)) {
                        foreach ($service_terms as $term) {
                            if ($term->slug === $service_type) {
                                $first_service = $temp_service;
                                break;
                            }
                        }
                    }
                }
            }
        }
        
        // If no valid default, use first filtered service
        if (!$first_service && !empty($services)) {
            $first_service = $services[0];
        }
        
        // Get the service type name for display
        $service_type_name = '';
        if (!empty($service_type)) {
            $service_type_term = get_term_by('slug', $service_type, 'service-type');
            if ($service_type_term && !is_wp_error($service_type_term)) {
                $service_type_name = $service_type_term->name;
            }
        } else if ($first_service) {
            // If no service type specified but we have a service, get type from the service
            $service_terms = get_the_terms($first_service->ID, 'service-type');
            if ($service_terms && !is_wp_error($service_terms) && !empty($service_terms)) {
                $service_type_name = $service_terms[0]->name;
            }
        }
        
        // Start output buffering
        ob_start();
        
        ?>
        <?php if (!empty($module_title)) : ?>
        <h1 class="gct-module-title"><?php echo esc_html($module_title); ?></h1>
        <?php endif; ?>
        
        <div class="gct-service-location-module" data-nonce="<?php echo wp_create_nonce('gct_service_location_module_nonce'); ?>" data-service-type="<?php echo esc_attr($service_type); ?>">
            <!-- Service Info Container (Left side) -->
            <div class="gct-service-info-container">
                <!-- Service info will be dynamically loaded via JS, but provide default for first load -->
                <?php if (!empty($service_type_name)) : ?>
                <div class="gct-service-type-label"><?php echo esc_html($service_type_name); ?></div>
                <?php endif; ?>
                
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
                <div class="gct-service-image-container">
                    <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($title); ?>" class="gct-service-image">
                </div>
                <?php endif; ?>
                <div class="gct-service-description"><?php echo wp_kses_post(wpautop($content)); ?></div>
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
                        $location_terms = get_the_terms($first_service->ID, 'location-category');
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
        
        return ob_get_clean();
    }
}

new GCT_Service_Location_Module; 