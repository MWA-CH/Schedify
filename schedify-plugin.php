<?php
/*
Plugin Name: Schedify
Plugin URI: https://reyman.net/
Description: Schedify is a comprehensive booking and appointment scheduling plugin, designed to streamline bookings for service-based businesses.
Version: 1.0
Author: Reyman Tech Corp
Author URI: https://reyman.net/
License: GPL2
*/

// Function to run on plugin activation
function schedify_activate_plugin() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'schedify_employees';
    
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        first_name varchar(255) NOT NULL,
        last_name varchar(255) NOT NULL,
        email varchar(100) NOT NULL,
        phone varchar(20),
        wordpress_user_id bigint(20) unsigned,
        timezone varchar(50),
        employee_badge varchar(50),
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'schedify_activate_plugin');

// Function to run on plugin deactivation
function schedify_deactivate_plugin() {
    // Code to execute on deactivation if needed
}
register_deactivation_hook(__FILE__, 'schedify_deactivate_plugin');

require_once plugin_dir_path(__FILE__) . 'employees-admin.php';

// Register the Schedify menu in the WordPress admin panel
function schedify_register_menu() {
    add_menu_page(
        'Bookings Dashboard',   // Page title
        'Schedify',             // Menu title
        'manage_options',       // Capability
        'schedify-dashboard',   // Menu slug
        'schedify_dashboard_page', // Callback function
        'dashicons-calendar-alt', // Icon (WordPress Dashicons for calendar)
        26                      // Position in menu
    );
}
add_action('admin_menu', 'schedify_register_menu');

// Register all submenu pages under Schedify
function schedify_register_submenus() {
    $submenus = [
        'Dashboard'     => 'schedify_dashboard_page',
        'Calendar'      => 'schedify_calendar_page',
        'Services'      => 'schedify_services_page',
        'Employees'     => 'schedify_employees_page',
        'Locations'     => 'schedify_locations_page',
        'Appointments'  => 'schedify_appointments_page',
        'Events'        => 'schedify_events_page',
        'Customers'     => 'schedify_customers_page',
        'Finance'       => 'schedify_finance_page',
        'Notifications' => 'schedify_notifications_page',
        'Customize'     => 'schedify_customize_page',
        'Custom Fields' => 'schedify_custom_fields_page',
        'Settings'      => 'schedify_settings_page',
        'License'       => 'schedify_license_page',
    ];

    foreach ($submenus as $title => $function) {
        add_submenu_page(
            'schedify-dashboard', // Parent slug
            $title,               // Page title
            $title,               // Menu title
            'manage_options',     // Capability
            'schedify-' . strtolower(str_replace(' ', '-', $title)), // Menu slug
            $function             // Callback function
        );
    }
}
add_action('admin_menu', 'schedify_register_submenus');

// Callback functions for each submenu page
function schedify_dashboard_page() { echo '<h1>Dashboard</h1><p>Overview of your booking system.</p>'; }
function schedify_calendar_page() { echo '<h1>Calendar</h1><p>View all bookings in a calendar format.</p>'; }
function schedify_services_page() { echo '<h1>Services</h1><p>Manage services offered for bookings.</p>'; }
function schedify_employees_page() { schedify_render_employee_form(); }
function schedify_locations_page() { echo '<h1>Locations</h1><p>Manage service locations.</p>'; }
function schedify_appointments_page() { echo '<h1>Appointments</h1><p>View and manage individual appointments.</p>'; }
function schedify_events_page() { echo '<h1>Events</h1><p>Manage events for bookings.</p>'; }
function schedify_customers_page() { echo '<h1>Customers</h1><p>Manage your customer information.</p>'; }
function schedify_finance_page() { echo '<h1>Finance</h1><p>View and manage financial information.</p>'; }
function schedify_notifications_page() { echo '<h1>Notifications</h1><p>Set up email and SMS notifications.</p>'; }
function schedify_customize_page() { echo '<h1>Customize</h1><p>Customize booking forms and settings.</p>'; }
function schedify_custom_fields_page() { echo '<h1>Custom Fields</h1><p>Manage custom fields for bookings.</p>'; }
function schedify_settings_page() { echo '<h1>Settings</h1><p>Configure general plugin settings.</p>'; }
function schedify_license_page() { echo '<h1>License</h1><p>Manage plugin licensing information.</p>'; }

// AJAX handler for saving employee data
function schedify_handle_employee_ajax() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'schedify_employees';

    $employee_id = isset($_POST['employee_id']) ? intval($_POST['employee_id']) : null;
    $first_name = sanitize_text_field($_POST['first_name']);
    $last_name = sanitize_text_field($_POST['last_name']);
    $email = sanitize_email($_POST['email']);
    $phone = sanitize_text_field($_POST['phone']);
    $timezone = sanitize_text_field($_POST['timezone']);
    $employee_badge = sanitize_text_field($_POST['employee_badge']);

    if ($employee_id) {
        // Update employee
        $updated = $wpdb->update(
            $table_name,
            [
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'phone' => $phone,
                'timezone' => $timezone,
                'employee_badge' => $employee_badge,
            ],
            ['id' => $employee_id],
            ['%s', '%s', '%s', '%s', '%s', '%s'],
            ['%d']
        );

        if ($updated !== false) {
            wp_send_json_success();
        } else {
            wp_send_json_error(['message' => 'Failed to update employee.']);
        }
    } else {
        // Insert new employee
        $inserted = $wpdb->insert(
            $table_name,
            [
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'phone' => $phone,
                'timezone' => $timezone,
                'employee_badge' => $employee_badge,
            ],
            ['%s', '%s', '%s', '%s', '%s', '%s']
        );

        if ($inserted) {
            wp_send_json_success();
        } else {
            wp_send_json_error(['message' => 'Failed to add new employee.']);
        }
    }
}
add_action('wp_ajax_schedify_save_employee', 'schedify_handle_employee_ajax');

// Enqueue JavaScript and localize ajaxurl for the admin page
function schedify_enqueue_scripts() {
    wp_enqueue_script('jquery'); // Ensure jQuery is available
    wp_localize_script('jquery', 'ajaxurl', admin_url('admin-ajax.php'));
}
add_action('admin_enqueue_scripts', 'schedify_enqueue_scripts');
?>
