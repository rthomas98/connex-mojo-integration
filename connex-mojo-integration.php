<?php
/*
Plugin Name: Connex Mojo Integration
Description: Integrates Connex Mojo middleware into WordPress.
Version: 1.0
Author: ConnexFm
*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define constants
define( 'CONNEX_MOJO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

// Include required files
require_once CONNEX_MOJO_PLUGIN_DIR . 'includes/class-connex-mojo-api.php';
require_once CONNEX_MOJO_PLUGIN_DIR . 'includes/class-connex-mojo-auth.php';
require_once CONNEX_MOJO_PLUGIN_DIR . 'includes/class-connex-mojo-endpoints.php';
require_once CONNEX_MOJO_PLUGIN_DIR . 'includes/class-connex-mojo-shortcodes.php';

// Initialize the plugin
function connex_mojo_integration_init() {
    $connex_mojo_api = new Connex_Mojo_API();
    $connex_mojo_auth = new Connex_Mojo_Auth( $connex_mojo_api );
    $connex_mojo_endpoints = new Connex_Mojo_Endpoints( $connex_mojo_api );
    $connex_mojo_shortcodes = new Connex_Mojo_Shortcodes( $connex_mojo_api );
}
add_action( 'plugins_loaded', 'connex_mojo_integration_init' );


// Enqueue plugin styles
function connex_mojo_enqueue_styles() {
    wp_enqueue_style( 'connex-mojo-styles', plugins_url( 'includes/assets/css/connex-mojo-styles.css', __FILE__ ) );
}
add_action( 'wp_enqueue_scripts', 'connex_mojo_enqueue_styles' );



// Add admin menu
function connex_mojo_add_admin_menu() {
    add_menu_page(
        'Connex Mojo Shortcodes',
        'Connex Mojo',
        'manage_options',
        'connex-mojo',
        'connex_mojo_display_shortcodes_page',
        'dashicons-shortcode',
        20
    );
}
add_action( 'admin_menu', 'connex_mojo_add_admin_menu' );

// Display shortcodes list
function connex_mojo_display_shortcodes_page() {
    ?>
    <div class="wrap">
        <h1>Connex Mojo Shortcodes</h1>
        <p>Use the following shortcodes to display different data:</p>
        <ul>
            <li><code>[connex_mojo_login]</code> - Display login form.</li>
            <li><code>[connex_mojo_committee_members committee_id="YOUR_COMMITTEE_ID"]</code> - Display committee members.</li>
            <li><code>[connex_mojo_all_events]</code> - Display all events.</li>
            <li><code>[connex_mojo_member_details customer_id="YOUR_CUSTOMER_ID"]</code> - Display member details.</li>
            <li><code>[connex_mojo_member_details_updated_since updated_since="MM-DD-YYYY"]</code> - Display member details updated since a specific date.</li>
            <li><code>[connex_mojo_all_members offset="0" limit="1000"]</code> - Display all members.</li>
            <li><code>[connex_mojo_all_committee_members]</code> - Display all committee members.</li>
        </ul>
    </div>
    <?php
}
