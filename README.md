# GCT Service & Location Selector Module

A custom Divi module for Service Selection & Dynamic Location Display.

## Overview

This module creates a fully responsive interface that allows users to:
- Select a service from a dropdown
- View service details including an image and description
- See available locations for the selected service as clickable buttons

The module dynamically updates the content based on the user's service selection.

## Installation

1. Download and extract the plugin files
2. Upload the `gct-service-location-module` folder to your `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. The module will now be available in the Divi Builder as "Service & Location Selector"

## Usage

### Adding the Module to Your Page

1. Edit a page with the Divi Builder
2. Click the "+" button to add a new module
3. Search for "Service & Location Selector" in the modules list
4. Click on the module to add it to your layout

### Configuring Module Settings

The module provides the following settings in the Divi Builder:

- **Default Service Type**: Choose which service type to display by default when the page loads
- **Service Selector Label**: Customize the label shown above the service selector dropdown
- **Location Section Title**: Set the title for the location buttons section
- **Read More Button Text**: Customize the text for the "Read more" button (service name will be appended)

You can also use Divi's standard design settings to customize the module's appearance.

### Working with Services

The module works with the existing "Service" Custom Post Type. To add or edit services:

1. Go to "Services" in the WordPress admin menu
2. Add a new service or edit an existing one
3. Set the service title and content
4. Add a featured image to display with the service
5. Assign the service to appropriate Service Types and Location Categories using the taxonomies on the right

### Custom Styling

You can add custom CSS to style the module using Divi's Custom CSS feature or your theme's stylesheet. The main CSS classes are:

```css
.gct-service-location-module       /* Main module container */
.gct-service-info-container        /* Left side with image and description */
.gct-service-image                 /* Service image */
.gct-service-title                 /* Service title */
.gct-service-description           /* Service description */
.gct-read-more-button              /* Read more button */
.gct-service-selection-container   /* Right side with selector and buttons */
.gct-service-selector              /* Service dropdown container */
.gct-location-section-title        /* Title above location buttons */
.gct-location-buttons              /* Container for location buttons */
.gct-location-button               /* Individual location button */
```

## Preview

To view a static preview of the module design and functionality, open the `preview.html` file in a web browser. This preview page includes:

- A mockup of the module with sample content
- Simulated admin controls to test different settings
- Mock data representing services and locations

## Support and Troubleshooting

If you encounter any issues with the module:

1. Ensure the plugin is properly activated
2. Check that you have services created with the necessary taxonomies
3. Verify that your services have featured images and content

## Future Expansion

The module is designed to support future expansion. To add additional service types:

1. Go to "Services" > "Service Types" in the WordPress admin
2. Add new service types as needed
3. Assign services to these new types

The module will automatically include the new service types in its dropdown. 