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

// Load plugin to access OPTION_KEY constant
require_once __DIR__ . '/admin-clean-up.php';

// Delete plugin options
delete_option( WP_Clean_Up::OPTION_KEY );
