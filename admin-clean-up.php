<?php
/**
 * Plugin Name: Admin Clean Up
 * Plugin URI: https://developer.suspended.se/admin-clean-up
 * Description: Clean up and simplify the WordPress admin interface by removing unnecessary elements.
 * Version: 1.0.7
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: Digiwise
 * Author URI: https://digiwise.se
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: admin-clean-up
 * Domain Path: /languages
 *
 * @package Admin_Clean_Up
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants
define( 'ADMIN_CLEAN_UP_VERSION', '1.0.7' );
define( 'ADMIN_CLEAN_UP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ADMIN_CLEAN_UP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Main plugin class
 */
class WP_Clean_Up {

    /**
     * Single instance of the class
     */
    private static $instance = null;

    /**
     * Option key for storing plugin settings.
     */
    const OPTION_KEY = 'wp_clean_up_options';

    /**
     * Get the singleton instance
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->load_dependencies();
        $this->init_modules();
    }

    /**
     * Load required files
     */
    private function load_dependencies() {
        // UI components for premium settings
        require_once ADMIN_CLEAN_UP_PLUGIN_DIR . 'includes/class-components.php';
        require_once ADMIN_CLEAN_UP_PLUGIN_DIR . 'includes/class-admin-page.php';
        require_once ADMIN_CLEAN_UP_PLUGIN_DIR . 'includes/class-admin-bar.php';
        require_once ADMIN_CLEAN_UP_PLUGIN_DIR . 'includes/class-comments.php';
        require_once ADMIN_CLEAN_UP_PLUGIN_DIR . 'includes/class-dashboard.php';
        require_once ADMIN_CLEAN_UP_PLUGIN_DIR . 'includes/class-admin-menus.php';
        require_once ADMIN_CLEAN_UP_PLUGIN_DIR . 'includes/class-footer.php';
        require_once ADMIN_CLEAN_UP_PLUGIN_DIR . 'includes/class-notices.php';
        require_once ADMIN_CLEAN_UP_PLUGIN_DIR . 'includes/class-site-health.php';
        require_once ADMIN_CLEAN_UP_PLUGIN_DIR . 'includes/class-clean-filenames.php';
        require_once ADMIN_CLEAN_UP_PLUGIN_DIR . 'includes/class-plugin-notices.php';
        require_once ADMIN_CLEAN_UP_PLUGIN_DIR . 'includes/class-updates.php';
    }

    /**
     * Initialize all modules
     */
    private function init_modules() {
        // Initialize admin page (settings)
        new WP_Clean_Up_Admin_Page();

        // Initialize admin bar cleanup
        new WP_Clean_Up_Admin_Bar();

        // Initialize comments cleanup
        new WP_Clean_Up_Comments();

        // Initialize dashboard cleanup
        new WP_Clean_Up_Dashboard();

        // Initialize admin menus cleanup
        new WP_Clean_Up_Admin_Menus();

        // Initialize footer customization
        new WP_Clean_Up_Footer();

        // Initialize notices cleanup
        new WP_Clean_Up_Notices();

        // Initialize site health disable
        new WP_Clean_Up_Site_Health();

        // Initialize clean filenames
        new WP_Clean_Up_Clean_Filenames();

        // Initialize plugin notices cleanup
        new WP_Clean_Up_Plugin_Notices();

        // Initialize updates control
        new WP_Clean_Up_Updates();
    }

    /**
     * Recursive version of wp_parse_args() for nested arrays.
     *
     * @param array $args     User-defined arguments.
     * @param array $defaults Default parameters.
     * @return array Merged array.
     */
    private static function parse_args_recursive( $args, $defaults ) {
        $args     = (array) $args;
        $defaults = (array) $defaults;
        $result   = $defaults;

        foreach ( $args as $key => $value ) {
            if ( is_array( $value ) && isset( $result[ $key ] ) && is_array( $result[ $key ] ) ) {
                $result[ $key ] = self::parse_args_recursive( $value, $result[ $key ] );
            } else {
                $result[ $key ] = $value;
            }
        }

        return $result;
    }

    /**
     * Get plugin options with defaults
     */
    public static function get_options() {
        $defaults = [
            'adminbar' => [
                'remove_wp_logo'        => false,
                'remove_site_menu'      => false,
                'remove_new_content'    => false,
                'remove_search'         => false,
                'remove_howdy_frontend' => false,
            ],
            'comments' => [
                'disable_comments' => false,
            ],
            'dashboard' => [
                'remove_welcome_panel' => false,
                'remove_at_a_glance'   => false,
                'remove_activity'      => false,
                'remove_quick_draft'   => false,
                'remove_wp_events'     => false,
                'remove_site_health'   => false,
                'disable_site_health'  => false,
            ],
            'menus' => [
                'remove_posts'          => false,
                'remove_posts_for'      => 'non_admin',
                'remove_media'          => false,
                'remove_media_for'      => 'non_admin',
                'remove_pages'          => false,
                'remove_pages_for'      => 'non_admin',
                'remove_appearance'     => false,
                'remove_appearance_for' => 'non_admin',
                'remove_plugins'        => false,
                'remove_plugins_for'    => 'non_admin',
                'remove_users'          => false,
                'remove_users_for'      => 'non_admin',
                'remove_tools'          => false,
                'remove_tools_for'      => 'non_admin',
                'remove_settings'       => false,
                'remove_settings_for'   => 'non_admin',
            ],
            'footer' => [
                'remove_footer_text'  => false,
                'custom_footer_text'  => '',
                'remove_version'      => false,
                'custom_version_text' => '',
            ],
            'notices' => [
                'hide_update_notices' => false,
                'hide_all_notices'    => false,
                'hide_screen_options' => false,
                'hide_help_tab'       => false,
            ],
            'media' => [
                'clean_filenames'       => false,
                'clean_filenames_types' => 'all',
            ],
            'plugins' => [
                'hide_pixelyoursite_notices' => false,
                'hide_elementor_notices'     => false,
                'hide_yoast_notices'         => false,
                'hide_complianz_comments'    => false,
            ],
            'updates' => [
                'core_updates'           => 'default',
                'disable_plugin_updates' => false,
                'disable_theme_updates'  => false,
                'disable_update_emails'  => false,
                'hide_update_nags'       => false,
            ],
        ];

        $options = get_option( self::OPTION_KEY, [] );

        return self::parse_args_recursive( $options, $defaults );
    }
}

/**
 * Initialize the plugin
 */
function wp_clean_up_init() {
    return WP_Clean_Up::get_instance();
}

// Start the plugin
add_action( 'plugins_loaded', 'wp_clean_up_init' );

/**
 * Activation hook
 */
function wp_clean_up_activate() {
    // Set default options on activation
    if ( false === get_option( WP_Clean_Up::OPTION_KEY ) ) {
        add_option( WP_Clean_Up::OPTION_KEY, [
            'adminbar' => [
                'remove_wp_logo'        => false,
                'remove_site_menu'      => false,
                'remove_new_content'    => false,
                'remove_search'         => false,
                'remove_howdy_frontend' => false,
            ],
            'comments' => [
                'disable_comments' => false,
            ],
            'dashboard' => [
                'remove_welcome_panel' => false,
                'remove_at_a_glance'   => false,
                'remove_activity'      => false,
                'remove_quick_draft'   => false,
                'remove_wp_events'     => false,
                'remove_site_health'   => false,
                'disable_site_health'  => false,
            ],
            'menus' => [
                'remove_posts'          => false,
                'remove_posts_for'      => 'non_admin',
                'remove_media'          => false,
                'remove_media_for'      => 'non_admin',
                'remove_pages'          => false,
                'remove_pages_for'      => 'non_admin',
                'remove_appearance'     => false,
                'remove_appearance_for' => 'non_admin',
                'remove_plugins'        => false,
                'remove_plugins_for'    => 'non_admin',
                'remove_users'          => false,
                'remove_users_for'      => 'non_admin',
                'remove_tools'          => false,
                'remove_tools_for'      => 'non_admin',
                'remove_settings'       => false,
                'remove_settings_for'   => 'non_admin',
            ],
            'footer' => [
                'remove_footer_text'  => false,
                'custom_footer_text'  => '',
                'remove_version'      => false,
                'custom_version_text' => '',
            ],
            'notices' => [
                'hide_update_notices' => false,
                'hide_all_notices'    => false,
                'hide_screen_options' => false,
                'hide_help_tab'       => false,
            ],
            'media' => [
                'clean_filenames'       => false,
                'clean_filenames_types' => 'all',
            ],
            'plugins' => [
                'hide_pixelyoursite_notices' => false,
                'hide_elementor_notices'     => false,
                'hide_yoast_notices'         => false,
                'hide_complianz_comments'    => false,
            ],
            'updates' => [
                'core_updates'           => 'default',
                'disable_plugin_updates' => false,
                'disable_theme_updates'  => false,
                'disable_update_emails'  => false,
                'hide_update_nags'       => false,
            ],
        ] );
    }
}
register_activation_hook( __FILE__, 'wp_clean_up_activate' );

/**
 * Initialize plugin update checker for GitHub releases
 */
function wp_clean_up_update_checker() {
    // Load the update checker library
    require_once ADMIN_CLEAN_UP_PLUGIN_DIR . 'includes/plugin-update-checker/plugin-update-checker.php';

    $update_checker = YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
        'https://github.com/landerss0n/admin-clean-up/',
        __FILE__,
        'admin-clean-up'
    );

    // Set the branch that contains the stable release
    $update_checker->setBranch( 'main' );

    // Enable release assets (downloads the zip from GitHub releases)
    $update_checker->getVcsApi()->enableReleaseAssets();

    // If the repo is private, use a GitHub token
    // Define ADMIN_CLEAN_UP_GITHUB_TOKEN in wp-config.php
    if ( defined( 'ADMIN_CLEAN_UP_GITHUB_TOKEN' ) && ADMIN_CLEAN_UP_GITHUB_TOKEN ) {
        $update_checker->setAuthentication( ADMIN_CLEAN_UP_GITHUB_TOKEN );
    }

    // Add plugin icons
    $update_checker->addResultFilter( function ( $info ) {
        $info->icons = [
            '1x'      => 'https://raw.githubusercontent.com/landerss0n/admin-clean-up/main/assets/images/icon-128x128.png',
            '2x'      => 'https://raw.githubusercontent.com/landerss0n/admin-clean-up/main/assets/images/icon-256x256.png',
            'default' => 'https://raw.githubusercontent.com/landerss0n/admin-clean-up/main/assets/images/icon-256x256.png',
        ];
        return $info;
    } );
}
add_action( 'init', 'wp_clean_up_update_checker' );

/**
 * Add settings link to plugin action links
 *
 * @param array $links Plugin action links.
 * @return array
 */
function wp_clean_up_plugin_action_links( $links ) {
    $settings_link = '<a href="' . admin_url( 'options-general.php?page=admin-clean-up' ) . '">' . __( 'Inställningar', 'admin-clean-up' ) . '</a>';
    array_unshift( $links, $settings_link );
    return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wp_clean_up_plugin_action_links' );
