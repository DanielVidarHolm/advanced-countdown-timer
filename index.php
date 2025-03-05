<?php
/*
 * Plugin Name:       Teledele Hurrytimer
 * Description:       Custom plugin for Teledele that displays a timer at the top of the webpage with customizable options.
 * Version:           1.0.0
 * Requires at least: 5.0
 * Requires PHP:      7.4.10
 * Author:            Daniel Holm
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       teledele-hurrytimer
 * Domain Path:       /languages
 */

// Prevent direct access to the file

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Add a Settings link to the plugin on the Plugins page.
 */
function teledele_hurrytimer_add_settings_link( $links ) {
    $settings_link = '<a href="' . admin_url( 'admin.php?page=teledele-hurrytimer' ) . '">' . __( 'Settings', 'teledele-hurrytimer' ) . '</a>';
    $links[] = $settings_link;
    return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'teledele_hurrytimer_add_settings_link' );

// Use plugin_dir_path() for absolute paths to include files.
include_once( plugin_dir_path( __FILE__ ) . 'includes/options-menu.php' );
include_once( plugin_dir_path( __FILE__ ) . 'includes/hurrytimer.php' );

// Uncomment this line if your plugin needs to enqueue assets.
// include_once( plugin_dir_path( __FILE__ ) . 'includes/enqueue-assets.php' );

?>
