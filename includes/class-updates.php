<?php
/**
 * Updates Class
 *
 * Handles control of automatic updates for WordPress core, plugins, and themes
 *
 * @package WP_Clean_Up
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Clean_Up_Updates {

    /**
     * Plugin options
     */
    private $options;

    /**
     * Constructor
     */
    public function __construct() {
        $this->options = WP_Clean_Up::get_options();
        $updates_options = isset( $this->options['updates'] ) ? $this->options['updates'] : [];

        // WordPress Core updates
        if ( ! empty( $updates_options['core_updates'] ) ) {
            $this->handle_core_updates( $updates_options['core_updates'] );
        }

        // Plugin auto-updates
        if ( ! empty( $updates_options['disable_plugin_updates'] ) ) {
            add_filter( 'auto_update_plugin', '__return_false' );
            add_filter( 'plugins_auto_update_enabled', '__return_false' );
        }

        // Theme auto-updates
        if ( ! empty( $updates_options['disable_theme_updates'] ) ) {
            add_filter( 'auto_update_theme', '__return_false' );
            add_filter( 'themes_auto_update_enabled', '__return_false' );
        }

        // Disable update emails
        if ( ! empty( $updates_options['disable_update_emails'] ) ) {
            add_filter( 'auto_core_update_send_email', '__return_false' );
            add_filter( 'auto_plugin_update_send_email', '__return_false' );
            add_filter( 'auto_theme_update_send_email', '__return_false' );
            add_filter( 'send_core_update_notification_email', '__return_false' );
        }

        // Hide update nags in admin
        if ( ! empty( $updates_options['hide_update_nags'] ) ) {
            add_action( 'admin_init', [ $this, 'hide_update_nags' ] );
        }
    }

    /**
     * Handle WordPress core update settings
     *
     * @param string $setting The core update setting
     */
    private function handle_core_updates( $setting ) {
        switch ( $setting ) {
            case 'disable_all':
                // Disable all core auto-updates
                add_filter( 'auto_update_core', '__return_false' );
                add_filter( 'allow_dev_auto_core_updates', '__return_false' );
                add_filter( 'allow_minor_auto_core_updates', '__return_false' );
                add_filter( 'allow_major_auto_core_updates', '__return_false' );
                break;

            case 'security_only':
                // Only security/minor updates (WordPress default behavior)
                add_filter( 'allow_dev_auto_core_updates', '__return_false' );
                add_filter( 'allow_minor_auto_core_updates', '__return_true' );
                add_filter( 'allow_major_auto_core_updates', '__return_false' );
                break;

            case 'minor_only':
                // Minor updates only (security + minor releases)
                add_filter( 'allow_dev_auto_core_updates', '__return_false' );
                add_filter( 'allow_minor_auto_core_updates', '__return_true' );
                add_filter( 'allow_major_auto_core_updates', '__return_false' );
                break;

            case 'all_updates':
                // Enable all auto-updates (major + minor)
                add_filter( 'allow_dev_auto_core_updates', '__return_false' );
                add_filter( 'allow_minor_auto_core_updates', '__return_true' );
                add_filter( 'allow_major_auto_core_updates', '__return_true' );
                break;

            default:
                // 'default' - don't modify WordPress behavior
                break;
        }
    }

    /**
     * Hide update nags from non-admin users or completely
     */
    public function hide_update_nags() {
        // Remove core update nag
        remove_action( 'admin_notices', 'update_nag', 3 );
        remove_action( 'network_admin_notices', 'update_nag', 3 );

        // Remove maintenance mode nag
        remove_action( 'admin_notices', 'maintenance_nag', 10 );

        // Hide update count in admin menu
        add_action( 'admin_head', [ $this, 'hide_update_count_css' ] );
    }

    /**
     * Hide update count badges via CSS
     */
    public function hide_update_count_css() {
        ?>
        <style>
            #wp-admin-bar-updates,
            .update-plugins,
            .update-count {
                display: none !important;
            }
        </style>
        <?php
    }
}
