<?php
/**
 * Site Health Class
 *
 * Handles complete disabling of WordPress Site Health feature
 *
 * @package WP_Clean_Up
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Clean_Up_Site_Health {

    /**
     * Constructor
     */
    public function __construct() {
        $options = WP_Clean_Up::get_options();

        if ( ! empty( $options['dashboard']['disable_site_health'] ) ) {
            $this->disable_site_health();
        }
    }

    /**
     * Completely disable Site Health
     */
    private function disable_site_health() {
        // Remove Site Health from Tools menu
        add_action( 'admin_menu', [ $this, 'remove_site_health_menu' ], 999 );

        // Remove Site Health dashboard widget
        add_action( 'wp_dashboard_setup', [ $this, 'remove_site_health_dashboard_widget' ], 999 );

        // Disable Site Health tests completely
        add_filter( 'site_status_tests', '__return_empty_array', 999 );

        // Disable async Site Health tests
        add_filter( 'site_status_test_result', '__return_empty_array', 999 );

        // Remove Site Health from admin bar
        add_action( 'wp_before_admin_bar_render', [ $this, 'remove_site_health_admin_bar' ] );

        // Disable Site Health cron job
        add_action( 'init', [ $this, 'disable_site_health_cron' ] );

        // Redirect Site Health pages
        add_action( 'admin_init', [ $this, 'redirect_site_health_pages' ] );

        // Remove Site Health REST API endpoints
        add_filter( 'rest_endpoints', [ $this, 'disable_site_health_rest_api' ] );

        // Disable Site Health check in background
        add_filter( 'wp_fatal_error_handler_enabled', '__return_false' );

        // Remove recovery mode email
        add_filter( 'recovery_mode_email', '__return_empty_array' );

        // Disable Site Health admin notices
        add_action( 'admin_head', [ $this, 'hide_site_health_notices' ] );

        // Remove Site Health scripts
        add_action( 'admin_enqueue_scripts', [ $this, 'dequeue_site_health_scripts' ], 999 );

        // Disable email notifications about site health
        add_filter( 'site_status_should_suggest_persistent_object_cache', '__return_false' );
        add_filter( 'send_site_health_report_email', '__return_false' );
    }

    /**
     * Remove Site Health from Tools menu
     */
    public function remove_site_health_menu() {
        remove_submenu_page( 'tools.php', 'site-health.php' );
    }

    /**
     * Remove Site Health dashboard widget
     */
    public function remove_site_health_dashboard_widget() {
        remove_meta_box( 'dashboard_site_health', 'dashboard', 'normal' );
    }

    /**
     * Remove Site Health from admin bar
     */
    public function remove_site_health_admin_bar() {
        global $wp_admin_bar;
        $wp_admin_bar->remove_node( 'site-health' );
    }

    /**
     * Disable Site Health scheduled cron events
     */
    public function disable_site_health_cron() {
        // Remove scheduled Site Health check
        if ( wp_next_scheduled( 'wp_site_health_scheduled_check' ) ) {
            wp_clear_scheduled_hook( 'wp_site_health_scheduled_check' );
        }

        // Remove async Site Health update
        remove_action( 'wp_site_health_scheduled_check', 'wp_site_health_scheduled_check' );

        // Disable the background updates test
        remove_action( 'admin_init', '_maybe_update_core' );
        remove_action( 'admin_init', '_maybe_update_plugins' );
        remove_action( 'admin_init', '_maybe_update_themes' );
    }

    /**
     * Redirect Site Health pages to dashboard
     */
    public function redirect_site_health_pages() {
        global $pagenow;

        if ( 'site-health.php' === $pagenow ) {
            wp_safe_redirect( admin_url() );
            exit;
        }

        // Also handle site-health-info.php
        if ( 'site-health-info.php' === $pagenow ) {
            wp_safe_redirect( admin_url() );
            exit;
        }
    }

    /**
     * Disable Site Health REST API endpoints
     *
     * @param array $endpoints REST API endpoints
     * @return array Modified endpoints
     */
    public function disable_site_health_rest_api( $endpoints ) {
        // Remove all site-health endpoints
        foreach ( $endpoints as $route => $endpoint ) {
            if ( strpos( $route, '/wp-site-health/' ) !== false ) {
                unset( $endpoints[ $route ] );
            }
        }

        return $endpoints;
    }

    /**
     * Hide Site Health related admin notices
     */
    public function hide_site_health_notices() {
        echo '<style>
            .site-health-progress,
            .health-check-accordion,
            .site-health-issues-wrapper,
            #health-check-issues-critical,
            #health-check-issues-recommended,
            .site-health-view-more,
            #wp-admin-bar-site-health,
            .update-nag.notice-warning[data-slug="site-health"],
            .notice.site-health,
            .health-check-site-status-test,
            tr.plugin-update-tr .update-message.notice-warning:has(a[href*="site-health"]) {
                display: none !important;
            }
        </style>';
    }

    /**
     * Dequeue Site Health scripts and styles
     *
     * @param string $hook Current admin page
     */
    public function dequeue_site_health_scripts( $hook ) {
        wp_dequeue_script( 'site-health' );
        wp_dequeue_style( 'site-health' );
        wp_deregister_script( 'site-health' );
        wp_deregister_style( 'site-health' );
    }
}
