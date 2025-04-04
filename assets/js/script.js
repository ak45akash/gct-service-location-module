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
        $('.gct-service-location-module').each(function() {
            const $module = $(this);
            const $serviceSelect = $module.find('.gct-service-select');
            const nonce = $module.data('nonce');
            const serviceType = $module.data('service-type');

            // Handle service selection change
            $serviceSelect.on('change', function() {
                const serviceId = $(this).val();
                if (!serviceId) return;
                
                updateServiceData($module, serviceId, nonce, serviceType);
            });

            // Set the first option as selected if none is selected
            if (!$serviceSelect.val() && $serviceSelect.find('option').length > 1) {
                $serviceSelect.find('option:eq(1)').prop('selected', true);
                $serviceSelect.trigger('change');
            }
        });
    }

    /**
     * Update service data based on selection
     * 
     * @param {jQuery} $module The module element
     * @param {number} serviceId The selected service ID
     * @param {string} nonce Security nonce
     * @param {string} serviceType The service type slug
     */
    function updateServiceData($module, serviceId, nonce, serviceType) {
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
                service_type: serviceType,
                nonce: nonce || gctServiceLocationModule.nonce
            },
            success: function(response) {
                if (response.success && response.data) {
                    // Update service info
                    updateServiceInfo($serviceInfo, response.data, serviceType);
                    
                    // Update location buttons
                    updateLocationButtons($locationButtons, response.data.locations);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching service data:', error);
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
     * @param {string} serviceType The service type slug
     */
    function updateServiceInfo($serviceInfo, data, serviceType) {
        let imageHtml = '';
        
        if (data.image) {
            imageHtml = `
                <div class="gct-service-image-container">
                    <img src="${data.image}" alt="${data.title}" class="gct-service-image">
                </div>`;
        }
        
        $serviceInfo.html(`
            <div class="gct-service-info-content">
                <div class="gct-service-text-content">
                    <h4 class="gct-service-title">${data.title}</h4>
                    <div class="gct-service-description">${data.content}</div>
                    <a href="${data.permalink}" class="gct-read-more-button">Read more about ${data.title}</a>
                </div>
                ${imageHtml}
            </div>
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
            const locationUrl = gctServiceLocationModule.siteurl + '/resource/location/' + location.slug + '/';
            buttonsHtml += `<a href="${locationUrl}" class="gct-location-button" data-location-id="${location.id}">${location.name}</a>`;
        });
        
        $locationButtons.html(buttonsHtml);
    }

})(jQuery); 