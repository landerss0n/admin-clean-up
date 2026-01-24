<?php
/**
 * Dashboard Class
 *
 * Handles dashboard widget cleanup
 *
 * @package WP_Clean_Up
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Clean_Up_Dashboard {

    /**
     * Plugin options
     */
    private $options;

    /**
     * Constructor
     */
    public function __construct() {
        $this->options = WP_Clean_Up::get_options();
        $dashboard_options = isset( $this->options['dashboard'] ) ? $this->options['dashboard'] : [];

        if ( ! empty( array_filter( $dashboard_options ) ) ) {
            add_action( 'wp_dashboard_setup', [ $this, 'remove_dashboard_widgets' ], 999 );
        }
    }

    /**
     * Remove dashboard widgets based on settings
     */
    public function remove_dashboard_widgets() {
        $dashboard_options = isset( $this->options['dashboard'] ) ? $this->options['dashboard'] : [];

        // Remove Welcome Panel
        if ( ! empty( $dashboard_options['remove_welcome_panel'] ) ) {
            remove_action( 'welcome_panel', 'wp_welcome_panel' );
        }

        // Remove At a Glance
        if ( ! empty( $dashboard_options['remove_at_a_glance'] ) ) {
            remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
        }

        // Remove Activity
        if ( ! empty( $dashboard_options['remove_activity'] ) ) {
            remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' );
        }

        // Remove Quick Draft
        if ( ! empty( $dashboard_options['remove_quick_draft'] ) ) {
            remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
        }

        // Remove WordPress Events and News
        if ( ! empty( $dashboard_options['remove_wp_events'] ) ) {
            remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
        }

        // Remove Site Health Status
        if ( ! empty( $dashboard_options['remove_site_health'] ) ) {
            remove_meta_box( 'dashboard_site_health', 'dashboard', 'normal' );
        }
    }
}
