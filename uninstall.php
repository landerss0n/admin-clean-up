<?php
/**
 * Uninstall WP Clean Up
 *
 * Removes all plugin data when the plugin is deleted.
 *
 * @package WP_Clean_Up
 */

// If uninstall not called from WordPress, exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Delete plugin options
delete_option( 'wp_clean_up_options' );
