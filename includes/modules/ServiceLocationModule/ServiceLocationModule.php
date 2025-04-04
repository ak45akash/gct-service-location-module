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
    }
    
    /**
     * Get module settings fields
     */
    public function get_fields() {
        return array(
            'default_service_type' => array(
                'label'           => esc_html__('Default Service Type', 'gct-service-location-module'),
                'type'            => 'select',
                'option_category' => 'configuration',
                'options'         => $this->get_service_type_options(),
                'default'         => '',
                'description'     => esc_html__('Select the default service type to display when the page loads.', 'gct-service-location-module'),
                'toggle_slug'     => 'general',
            ),
            'service_selector_label' => array(
                'label'           => esc_html__('Service Selector Label', 'gct-service-location-module'),
                'type'            => 'text',
                'option_category' => 'configuration',
                'default'         => esc_html__('Service', 'gct-service-location-module'),
                'description'     => esc_html__('The label displayed above the service selector.', 'gct-service-location-module'),
                'toggle_slug'     => 'general',
            ),
            'location_section_title' => array(
                'label'           => esc_html__('Location Section Title', 'gct-service-location-module'),
                'type'            => 'text',
                'option_category' => 'configuration',
                'default'         => esc_html__('Locations', 'gct-service-location-module'),
                'description'     => esc_html__('The title for the location buttons section.', 'gct-service-location-module'),
                'toggle_slug'     => 'general',
            ),
            'read_more_text' => array(
                'label'           => esc_html__('Read More Button Text', 'gct-service-location-module'),
                'type'            => 'text',
                'option_category' => 'configuration',
                'default'         => esc_html__('Read more about', 'gct-service-location-module'),
                'description'     => esc_html__('The text for the read more button. The service name will be appended.', 'gct-service-location-module'),
                'toggle_slug'     => 'general',
            ),
            'module_title' => array(
                'label'           => esc_html__('Module Title', 'gct-service-location-module'),
                'type'            => 'text',
                'option_category' => 'configuration',
                'default'         => esc_html__('Browse locations by service', 'gct-service-location-module'),
                'description'     => esc_html__('The main title displayed above the module.', 'gct-service-location-module'),
                'toggle_slug'     => 'general',
            ),
            'default_image' => array(
                'label'           => esc_html__('Default Service Image', 'gct-service-location-module'),
                'type'            => 'upload',
                'option_category' => 'basic_option',
                'upload_button_text' => esc_attr__('Upload an image', 'gct-service-location-module'),
                'choose_text'     => esc_attr__('Choose an Image', 'gct-service-location-module'),
                'update_text'     => esc_attr__('Set As Image', 'gct-service-location-module'),
                'description'     => esc_html__('Upload a default image for services without featured images.', 'gct-service-location-module'),
                'toggle_slug'     => 'general',
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
            'taxonomy'   => 'service_type',
            'hide_empty' => true,
        ));
        
        if (!is_wp_error($service_types) && !empty($service_types)) {
            foreach ($service_types as $service_type) {
                $options[$service_type->term_id] = $service_type->name;
            }
        }
        
        return $options;
    }
    
    /**
     * Render the module output
     */
    public function render($attrs, $content = null, $render_slug) {
        $default_service_type   = $this->props['default_service_type'];
        $service_selector_label = $this->props['service_selector_label'];
        $location_section_title = $this->props['location_section_title'];
        $read_more_text         = $this->props['read_more_text'];
        $module_title           = $this->props['module_title'];
        $default_image          = $this->props['default_image'];
        
        // Get services based on taxonomy
        $services = $this->get_services();
        
        // Start output buffering
        ob_start();
        
        ?>
        <?php if (!empty($module_title)) : ?>
        <h1 class="gct-module-title"><?php echo esc_html($module_title); ?></h1>
        <?php endif; ?>
        
        <div class="gct-service-location-module">
            <!-- Service Info Container (Left side) -->
            <div class="gct-service-info-container">
                <!-- Service info will be dynamically loaded via JS, but provide default for first load -->
                <?php if (!empty($services)) : 
                    $first_service = $services[0];
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
                            <option value="<?php echo esc_attr($service->ID); ?>" <?php selected($default_service_type, $service->ID); ?>>
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
                    <?php if (!empty($services)) : 
                        $first_service = $services[0];
                        $location_terms = get_the_terms($first_service->ID, 'location_category');
                        if ($location_terms && !is_wp_error($location_terms)) :
                            foreach ($location_terms as $term) : ?>
                                <a href="#" class="gct-location-button" data-location-id="<?php echo esc_attr($term->term_id); ?>"><?php echo esc_html($term->name); ?></a>
                            <?php endforeach;
                        endif;
                    endif; ?>
                </div>
            </div>
        </div>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Get services
     */
    private function get_services() {
        $args = array(
            'post_type'      => 'service',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'orderby'        => 'title',
            'order'          => 'ASC',
        );
        
        if (!empty($this->props['default_service_type'])) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'service_type',
                    'field'    => 'term_id',
                    'terms'    => $this->props['default_service_type'],
                ),
            );
        }
        
        $services = get_posts($args);
        
        return $services;
    }
}

new GCT_Service_Location_Module; 