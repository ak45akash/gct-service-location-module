/**
 * GCT Service Location Module Scripts
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        // Initialize the module
        initServiceLocationModule();
    });

    /**
     * Initialize the Service Location Module
     */
    function initServiceLocationModule() {
        const $modules = $('.gct-service-location-module');

        if (!$modules.length) {
            return;
        }

        $modules.each(function() {
            const $module = $(this);
            const $serviceSelect = $module.find('.gct-service-selector select');
            const $serviceInfo = $module.find('.gct-service-info-container');
            const $locationButtons = $module.find('.gct-location-buttons');

            // Initialize with default service
            if ($serviceSelect.val()) {
                updateServiceData($module, $serviceSelect.val());
            }

            // Handle service selection change
            $serviceSelect.on('change', function() {
                const serviceId = $(this).val();
                updateServiceData($module, serviceId);
            });
        });
    }

    /**
     * Update service data based on selection
     * 
     * @param {jQuery} $module The module element
     * @param {number} serviceId The selected service ID
     */
    function updateServiceData($module, serviceId) {
        if (!serviceId) {
            return;
        }

        const $serviceInfo = $module.find('.gct-service-info-container');
        const $locationButtons = $module.find('.gct-location-buttons');

        // Add loading state
        $module.addClass('gct-loading');

        // Get service data via AJAX
        $.ajax({
            url: gctServiceLocationModule.ajaxurl,
            type: 'POST',
            data: {
                action: 'gct_service_location_module_get_service_data',
                service_id: serviceId,
                nonce: gctServiceLocationModule.nonce
            },
            success: function(response) {
                if (response.success && response.data) {
                    // Update service info
                    updateServiceInfo($serviceInfo, response.data);
                    
                    // Update location buttons
                    updateLocationButtons($locationButtons, response.data.locations);
                    
                    // Apply fade transition
                    applyFadeTransition($module);
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
    }

    /**
     * Update service info section
     * 
     * @param {jQuery} $serviceInfo The service info container
     * @param {Object} data The service data
     */
    function updateServiceInfo($serviceInfo, data) {
        let imageHtml = '';
        
        if (data.image) {
            imageHtml = `<img src="${data.image}" alt="${data.title}" class="gct-service-image">`;
        }
        
        $serviceInfo.html(`
            <div class="gct-service-title">${data.title}</div>
            ${imageHtml}
            <div class="gct-service-description">${data.content}</div>
            <a href="#" class="gct-read-more-button">Read more about ${data.title}</a>
        `);
    }

    /**
     * Update location buttons
     * 
     * @param {jQuery} $locationButtons The location buttons container
     * @param {Array} locations The locations array
     */
    function updateLocationButtons($locationButtons, locations) {
        if (!locations || !locations.length) {
            $locationButtons.html('<p>No locations available for this service.</p>');
            return;
        }
        
        let buttonsHtml = '';
        
        locations.forEach(function(location) {
            buttonsHtml += `<a href="#" class="gct-location-button" data-location-id="${location.id}">${location.name}</a>`;
        });
        
        $locationButtons.html(buttonsHtml);
    }

    /**
     * Apply fade transition effect
     * 
     * @param {jQuery} $module The module element
     */
    function applyFadeTransition($module) {
        $module.addClass('gct-fade-transition');
        setTimeout(function() {
            $module.removeClass('gct-fade-transition');
        }, 300);
    }

})(jQuery); 