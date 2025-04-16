/**
 * GCT Service Location Module Scripts
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        // Initialize the module
        initServiceLocationModule();
        
        // Initialize mobile layout
        initMobileLayout();
        
        // Update on window resize
        $(window).on('resize', function() {
            initMobileLayout();
        });
    });

    /**
     * Initialize mobile layout adjustments
     */
    function initMobileLayout() {
        $('.gct-service-location-module').each(function() {
            const $module = $(this);
            const isMobile = window.matchMedia('(max-width: 767px)').matches;
            
            // If mobile and mobile buttons don't exist yet
            if (isMobile && $module.find('.gct-mobile-location-buttons').length === 0) {
                // Create location buttons for mobile
                const $mobileButtons = $('<div class="gct-mobile-location-buttons"></div>');
                $mobileButtons.insertAfter($module.find('.gct-service-dropdown-container'));
                
                // Clone current location buttons content
                updateMobileLocationButtons($module);
                
                // Hook into the AJAX response to update mobile buttons when content changes
                $(document).ajaxComplete(function(event, xhr, settings) {
                    if (settings.data && settings.data.indexOf('gct_service_location_module_get_service_data') !== -1) {
                        setTimeout(function() {
                            updateMobileLocationButtons($module);
                        }, 100);
                    }
                });
            }
            // If not mobile and mobile buttons exist, remove them
            else if (!isMobile) {
                $module.find('.gct-mobile-location-buttons').remove();
            }
        });
    }
    
    /**
     * Update the mobile location buttons to match the current content
     */
    function updateMobileLocationButtons($module) {
        const $mobileButtons = $module.find('.gct-mobile-location-buttons');
        const $originalButtons = $module.find('.gct-location-buttons');
        
        if ($mobileButtons.length && $originalButtons.length) {
            $mobileButtons.html($originalButtons.html());
        }
    }

    /**
     * Initialize the Service Location Module
     */
    function initServiceLocationModule() {
        $('.gct-service-location-module').each(function() {
            const $module = $(this);
            const $serviceSelect = $module.find('.gct-service-select');
            const nonce = $module.data('nonce');
            const serviceType = $module.data('service-type');

            // Initialize custom dropdown
            initCustomDropdown($module, $serviceSelect);

            // Handle service selection change
            $serviceSelect.on('change', function() {
                const serviceId = $(this).val();
                if (!serviceId) return;
                
                updateServiceData($module, serviceId, nonce, serviceType);
            });

            // Set the first option as selected if none is selected
            if (!$serviceSelect.val() && $serviceSelect.find('option').length > 1) {
                $serviceSelect.find('option:eq(1)').prop('selected', true);
                updateCustomDropdown($module);
                $serviceSelect.trigger('change');
            }
        });

        // Close custom dropdowns when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.gct-custom-select').length) {
                $('.gct-select-selected').removeClass('active').removeClass('select-arrow-active');
                $('.gct-select-items').hide();
            }
        });
    }

    /**
     * Initialize custom dropdown
     */
    function initCustomDropdown($module, $originalSelect) {
        const $dropdownContainer = $module.find('.gct-service-dropdown-container');
        
        // Create custom dropdown elements if they don't exist yet
        if ($dropdownContainer.find('.gct-custom-select').length === 0) {
            // Create the custom dropdown structure
            const $customSelect = $('<div class="gct-custom-select"></div>');
            const $selectedDiv = $('<div class="gct-select-selected"></div>');
            const $optionsDiv = $('<div class="gct-select-items"></div>');
            
            // Get currently selected value
            const selectedValue = $originalSelect.val();
            
            // Add options to custom dropdown
            $originalSelect.find('option').each(function() {
                const value = $(this).val();
                const text = $(this).text();
                
                if (value) { // Skip the empty placeholder option
                    // Only add to dropdown list if not currently selected
                    if (value !== selectedValue) {
                        const $option = $('<div data-value="' + value + '">' + text + '</div>');
                        $optionsDiv.append($option);
                        
                        // Add click event to each option
                        $option.on('click', function() {
                            const newValue = $(this).data('value');
                            
                            // Update the original select and trigger change
                            $originalSelect.val(newValue).trigger('change');
                            
                            // Update the custom dropdown display
                            $selectedDiv.text($(this).text());
                            
                            // Rebuild the dropdown options to exclude the newly selected option
                            rebuildDropdownOptions($optionsDiv, $originalSelect, newValue, $selectedDiv);
                            
                            // Close the dropdown
                            $selectedDiv.removeClass('active').removeClass('select-arrow-active');
                            $optionsDiv.hide();
                        });
                    }
                }
            });
            
            // Set initial selected text
            const selectedText = $originalSelect.find('option:selected').text() || 'Select an option';
            $selectedDiv.text(selectedText);
            
            // Toggle dropdown on click
            $selectedDiv.on('click', function() {
                // Check if dropdown is already open
                const isActive = $(this).hasClass('active');
                
                // Close all other dropdowns first
                $('.gct-select-selected').removeClass('active').removeClass('select-arrow-active');
                $('.gct-select-items').hide();
                
                // If this dropdown wasn't open, open it
                if (!isActive) {
                    $(this).addClass('active').addClass('select-arrow-active');
                    $optionsDiv.show();
                }
            });
            
            // Add custom dropdown to the container
            $customSelect.append($selectedDiv).append($optionsDiv);
            $dropdownContainer.append($customSelect);
        }
    }

    /**
     * Rebuild dropdown options to exclude the currently selected option
     */
    function rebuildDropdownOptions($optionsDiv, $originalSelect, selectedValue, $selectedDiv) {
        // Clear existing options
        $optionsDiv.empty();
        
        // Add options to custom dropdown
        $originalSelect.find('option').each(function() {
            const value = $(this).val();
            const text = $(this).text();
            
            if (value && value !== selectedValue) { // Skip empty and selected options
                const $option = $('<div data-value="' + value + '">' + text + '</div>');
                $optionsDiv.append($option);
                
                // Add click event to each option
                $option.on('click', function() {
                    const newValue = $(this).data('value');
                    
                    // Update the original select and trigger change
                    $originalSelect.val(newValue).trigger('change');
                    
                    // Update the custom dropdown display
                    $selectedDiv.text($(this).text());
                    
                    // Rebuild the dropdown options to exclude the newly selected option
                    rebuildDropdownOptions($optionsDiv, $originalSelect, newValue, $selectedDiv);
                    
                    // Close the dropdown
                    $selectedDiv.removeClass('active').removeClass('select-arrow-active');
                    $optionsDiv.hide();
                });
            }
        });
    }

    /**
     * Update the custom dropdown display to match the original select
     */
    function updateCustomDropdown($module) {
        const $originalSelect = $module.find('.gct-service-select');
        const $customSelect = $module.find('.gct-custom-select');
        
        if ($customSelect.length) {
            const selectedValue = $originalSelect.val();
            const selectedText = $originalSelect.find('option:selected').text();
            const $selectedDiv = $customSelect.find('.gct-select-selected');
            const $optionsDiv = $customSelect.find('.gct-select-items');
            
            // Update the selected display
            $selectedDiv.text(selectedText);
            
            // Rebuild the dropdown options to exclude the newly selected option
            rebuildDropdownOptions($optionsDiv, $originalSelect, selectedValue, $selectedDiv);
        }
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
        const $serviceInfoContainer = $module.find('.gct-service-info-container');

        // Add loading state
        $module.addClass('gct-loading');
        
        // Show loading state for locations
        const $locationButtons = $module.find('.gct-location-buttons');
        const $mobileButtons = $module.find('.gct-mobile-location-buttons');
        
        if ($locationButtons.length) {
            $locationButtons.html('<div class="gct-locations-loading"></div>');
        }
        
        if ($mobileButtons.length) {
            $mobileButtons.html('<div class="gct-locations-loading"></div>');
        }

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
                    updateServiceInfo($serviceInfoContainer, response.data);
                    // Update mobile buttons after service info is updated
                    setTimeout(function() {
                        updateMobileLocationButtons($module);
                    }, 100);
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
     * @param {jQuery} $serviceInfoContainer The service info container
     * @param {Object} data The service data
     */
    function updateServiceInfo($serviceInfoContainer, data) {
        // Use image from data, or placeholder if none available
        const imageUrl = data.image || 'https://placehold.co/600x400/C8E2D4/254B45?text=Service+Image';
        
        // Build location buttons HTML
        let locationButtonsHtml = '';
        if (data.locations && data.locations.length) {
            data.locations.forEach(function(location) {
                // Ensure the location.slug exists and use a default if not
                const locationSlug = location.slug || 'location';
                const locationUrl = gctServiceLocationModule.siteurl + '/resource/location/' + locationSlug + '/';
                locationButtonsHtml += `<a href="${locationUrl}" class="gct-location-button" data-location-id="${location.id || 0}">${location.name || 'Location'}</a>`;
            });
        } else {
            // Only show the message if we've confirmed there are no locations after loading
            locationButtonsHtml = ''; // Don't show any message
        }
        
        // Ensure content is sanitized and exists
        const content = data.content || '';
        const title = data.title || 'Service';
        
        try {
            const html = `
                <div class="gct-service-content-wrapper">
                    <div class="gct-service-image-container">
                        <img src="${imageUrl}" alt="${title}" class="gct-service-image">
                    </div>
                    <div class="gct-service-content">
                        <h3 class="gct-service-title">${title}</h3>
                        <div class="gct-service-description">${content}</div>
                        
                        <div class="gct-location-buttons">${locationButtonsHtml}</div>
                    </div>
                </div>
            `;
            
            $serviceInfoContainer.html(html);
        } catch (error) {
            console.error('Error updating service info:', error);
            // Fallback to a simple display in case of error
            $serviceInfoContainer.html(`
                <div class="gct-service-content-wrapper">
                    <div class="gct-service-content">
                        <h3 class="gct-service-title">${title}</h3>
                        <p>There was an error loading the service details. Please try again.</p>
                    </div>
                </div>
            `);
        }
    }

})(jQuery); 