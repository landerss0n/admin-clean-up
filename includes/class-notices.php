<?php
/**
 * Notices Class
 *
 * Handles admin notices cleanup
 *
 * @package WP_Clean_Up
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Clean_Up_Notices {

    /**
     * Plugin options
     */
    private $options;

    /**
     * Constructor
     */
    public function __construct() {
        $this->options = WP_Clean_Up::get_options();
        $notices_options = isset( $this->options['notices'] ) ? $this->options['notices'] : [];

        // Hide update notices
        if ( ! empty( $notices_options['hide_update_notices'] ) ) {
            add_action( 'admin_head', [ $this, 'hide_update_notices' ] );
        }

        // Hide all admin notices
        if ( ! empty( $notices_options['hide_all_notices'] ) ) {
            add_action( 'admin_head', [ $this, 'hide_all_notices' ] );
        }

        // Hide "Screen Options" tab
        if ( ! empty( $notices_options['hide_screen_options'] ) ) {
            add_filter( 'screen_options_show_screen', '__return_false' );
        }

        // Hide "Help" tab
        if ( ! empty( $notices_options['hide_help_tab'] ) ) {
            add_action( 'admin_head', [ $this, 'remove_help_tab' ] );
        }
    }

    /**
     * Hide update-related notices
     */
    public function hide_update_notices() {
        // Remove update nag
        remove_action( 'admin_notices', 'update_nag', 3 );
        remove_action( 'network_admin_notices', 'update_nag', 3 );

        // Hide update notices with CSS as fallback
        echo '<style>
            .update-nag,
            .notice-warning.notice-alt,
            #wp-admin-bar-updates,
            .plugin-update-tr,
            .update-message {
                display: none !important;
            }
        </style>';
    }

    /**
     * Hide all admin notices
     */
    public function hide_all_notices() {
        echo '<style>
            .notice,
            .notice-error,
            .notice-warning,
            .notice-success,
            .notice-info,
            .update-nag,
            .updated,
            .error,
            .is-dismissible,
            div.error,
            div.updated {
                display: none !important;
            }
        </style>';
    }

    /**
     * Remove Help tab from admin screens
     */
    public function remove_help_tab() {
        $screen = get_current_screen();
        if ( $screen ) {
            $screen->remove_help_tabs();
        }
    }
}
