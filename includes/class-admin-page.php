<?php
/**
 * Admin Page Class
 *
 * Handles the settings page with tabs
 *
 * @package WP_Clean_Up
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Clean_Up_Admin_Page {

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'admin_menu', [ $this, 'register_settings_page' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_styles' ] );
    }

    /**
     * Register the settings page under Settings menu
     */
    public function register_settings_page() {
        add_options_page(
            __( 'Admin Clean Up', 'admin-clean-up' ),
            __( 'Admin Clean Up', 'admin-clean-up' ),
            'manage_options',
            'admin-clean-up',
            [ $this, 'render_settings_page' ]
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting(
            'wp_clean_up_options_group',
            'wp_clean_up_options',
            [
                'type'              => 'array',
                'sanitize_callback' => [ $this, 'sanitize_options' ],
                'default'           => [],
            ]
        );
    }

    /**
     * Enqueue admin styles
     */
    public function enqueue_admin_styles( $hook ) {
        if ( 'settings_page_admin-clean-up' !== $hook ) {
            return;
        }

        wp_enqueue_style(
            'admin-clean-up-admin',
            ADMIN_CLEAN_UP_PLUGIN_URL . 'assets/css/admin.css',
            [],
            ADMIN_CLEAN_UP_VERSION
        );
    }

    /**
     * Sanitize options before saving
     */
    public function sanitize_options( $input ) {
        // Get existing options to preserve settings from other tabs
        $existing = get_option( 'wp_clean_up_options', [] );
        $sanitized = is_array( $existing ) ? $existing : [];

        // Get the current tab being saved
        $current_tab = isset( $input['_current_tab'] ) ? sanitize_key( $input['_current_tab'] ) : '';

        // Sanitize adminbar options
        if ( 'adminbar' === $current_tab ) {
            $adminbar = isset( $input['adminbar'] ) && is_array( $input['adminbar'] ) ? $input['adminbar'] : [];
            $sanitized['adminbar'] = [
                'remove_wp_logo'        => ! empty( $adminbar['remove_wp_logo'] ),
                'remove_site_menu'      => ! empty( $adminbar['remove_site_menu'] ),
                'remove_new_content'    => ! empty( $adminbar['remove_new_content'] ),
                'remove_search'         => ! empty( $adminbar['remove_search'] ),
                'remove_howdy_frontend' => ! empty( $adminbar['remove_howdy_frontend'] ),
            ];
        }

        // Sanitize comments options
        if ( 'comments' === $current_tab ) {
            $comments = isset( $input['comments'] ) && is_array( $input['comments'] ) ? $input['comments'] : [];
            $sanitized['comments'] = [
                'disable_comments' => ! empty( $comments['disable_comments'] ),
            ];
        }

        // Sanitize dashboard options
        if ( 'dashboard' === $current_tab ) {
            $dashboard = isset( $input['dashboard'] ) && is_array( $input['dashboard'] ) ? $input['dashboard'] : [];
            $sanitized['dashboard'] = [
                'remove_welcome_panel' => ! empty( $dashboard['remove_welcome_panel'] ),
                'remove_at_a_glance'   => ! empty( $dashboard['remove_at_a_glance'] ),
                'remove_activity'      => ! empty( $dashboard['remove_activity'] ),
                'remove_quick_draft'   => ! empty( $dashboard['remove_quick_draft'] ),
                'remove_wp_events'     => ! empty( $dashboard['remove_wp_events'] ),
                'remove_site_health'   => ! empty( $dashboard['remove_site_health'] ),
                'disable_site_health'  => ! empty( $dashboard['disable_site_health'] ),
            ];
        }

        // Sanitize menus options
        if ( 'menus' === $current_tab ) {
            $menus = isset( $input['menus'] ) && is_array( $input['menus'] ) ? $input['menus'] : [];
            $valid_roles = [ 'all', 'non_admin', 'non_editor' ];
            $menu_keys = [ 'posts', 'media', 'pages', 'appearance', 'plugins', 'users', 'tools', 'settings' ];

            $sanitized['menus'] = [];
            foreach ( $menu_keys as $key ) {
                $sanitized['menus'][ 'remove_' . $key ] = ! empty( $menus[ 'remove_' . $key ] );
                $role_value = isset( $menus[ 'remove_' . $key . '_for' ] ) ? $menus[ 'remove_' . $key . '_for' ] : 'non_admin';
                $sanitized['menus'][ 'remove_' . $key . '_for' ] = in_array( $role_value, $valid_roles, true ) ? $role_value : 'non_admin';
            }
        }

        // Sanitize footer options
        if ( 'footer' === $current_tab ) {
            $footer = isset( $input['footer'] ) && is_array( $input['footer'] ) ? $input['footer'] : [];
            $sanitized['footer'] = [
                'remove_footer_text'   => ! empty( $footer['remove_footer_text'] ),
                'custom_footer_text'   => isset( $footer['custom_footer_text'] ) ? wp_kses_post( $footer['custom_footer_text'] ) : '',
                'remove_version'       => ! empty( $footer['remove_version'] ),
                'custom_version_text'  => isset( $footer['custom_version_text'] ) ? sanitize_text_field( $footer['custom_version_text'] ) : '',
            ];
        }

        // Sanitize notices options
        if ( 'notices' === $current_tab ) {
            $notices = isset( $input['notices'] ) && is_array( $input['notices'] ) ? $input['notices'] : [];
            $sanitized['notices'] = [
                'hide_update_notices' => ! empty( $notices['hide_update_notices'] ),
                'hide_all_notices'    => ! empty( $notices['hide_all_notices'] ),
                'hide_screen_options' => ! empty( $notices['hide_screen_options'] ),
                'hide_help_tab'       => ! empty( $notices['hide_help_tab'] ),
            ];
        }

        // Sanitize media options
        if ( 'media' === $current_tab ) {
            $media = isset( $input['media'] ) && is_array( $input['media'] ) ? $input['media'] : [];
            $sanitized['media'] = [
                'clean_filenames'       => ! empty( $media['clean_filenames'] ),
                'clean_filenames_types' => isset( $media['clean_filenames_types'] ) && in_array( $media['clean_filenames_types'], [ 'all', 'images' ], true ) ? $media['clean_filenames_types'] : 'all',
            ];
        }

        // Sanitize plugins options
        if ( 'plugins' === $current_tab ) {
            $plugins = isset( $input['plugins'] ) && is_array( $input['plugins'] ) ? $input['plugins'] : [];
            $sanitized['plugins'] = [
                'hide_pixelyoursite_notices' => ! empty( $plugins['hide_pixelyoursite_notices'] ),
            ];
        }

        // Sanitize updates options
        if ( 'updates' === $current_tab ) {
            $updates = isset( $input['updates'] ) && is_array( $input['updates'] ) ? $input['updates'] : [];
            $sanitized['updates'] = [
                'core_updates'           => isset( $updates['core_updates'] ) && in_array( $updates['core_updates'], [ 'default', 'disable_all', 'security_only', 'minor_only', 'all_updates' ], true ) ? $updates['core_updates'] : 'default',
                'disable_plugin_updates' => ! empty( $updates['disable_plugin_updates'] ),
                'disable_theme_updates'  => ! empty( $updates['disable_theme_updates'] ),
                'disable_update_emails'  => ! empty( $updates['disable_update_emails'] ),
                'hide_update_nags'       => ! empty( $updates['hide_update_nags'] ),
            ];
        }

        return $sanitized;
    }

    /**
     * Get list of supported plugins that we can hide notices for
     */
    private function get_supported_plugins() {
        return [
            'pixelyoursite' => [
                'name'   => 'PixelYourSite',
                'check'  => 'pixelyoursite/facebook-pixel-master.php',
                'option' => 'hide_pixelyoursite_notices',
            ],
            // Add more plugins here as needed:
            // 'plugin_key' => [
            //     'name'   => 'Plugin Name',
            //     'check'  => 'plugin-folder/plugin-file.php',
            //     'option' => 'hide_pluginname_notices',
            // ],
        ];
    }

    /**
     * Get installed supported plugins
     */
    private function get_installed_supported_plugins() {
        if ( ! function_exists( 'is_plugin_active' ) ) {
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $supported = $this->get_supported_plugins();
        $installed = [];

        foreach ( $supported as $key => $plugin ) {
            if ( is_plugin_active( $plugin['check'] ) ) {
                $installed[ $key ] = $plugin;
            }
        }

        return $installed;
    }

    /**
     * Get available tabs
     */
    private function get_tabs() {
        $tabs = [
            'adminbar' => [
                'title'    => __( 'Admin Bar', 'admin-clean-up' ),
                'active'   => true,
            ],
            'comments' => [
                'title'    => __( 'Comments', 'admin-clean-up' ),
                'active'   => true,
            ],
            'dashboard' => [
                'title'    => __( 'Dashboard', 'admin-clean-up' ),
                'active'   => true,
            ],
            'menus' => [
                'title'    => __( 'Admin Menus', 'admin-clean-up' ),
                'active'   => true,
            ],
            'footer' => [
                'title'    => __( 'Footer', 'admin-clean-up' ),
                'active'   => true,
            ],
            'notices' => [
                'title'    => __( 'Notices', 'admin-clean-up' ),
                'active'   => true,
            ],
            'media' => [
                'title'    => __( 'Media', 'admin-clean-up' ),
                'active'   => true,
            ],
            'updates' => [
                'title'    => __( 'Updates', 'admin-clean-up' ),
                'active'   => true,
            ],
        ];

        // Only show Plugins tab if any supported plugins are installed
        $installed_plugins = $this->get_installed_supported_plugins();
        if ( ! empty( $installed_plugins ) ) {
            $tabs['plugins'] = [
                'title'    => __( 'Plugins', 'admin-clean-up' ),
                'active'   => true,
            ];
        }

        return $tabs;
    }

    /**
     * Render the settings page
     */
    public function render_settings_page() {
        // Check user capabilities
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $tabs = $this->get_tabs();
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Tab parameter is for display only and validated against allowed values.
        $current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'adminbar';

        // Only allow active tabs
        if ( ! isset( $tabs[ $current_tab ] ) || ! $tabs[ $current_tab ]['active'] ) {
            $current_tab = 'adminbar';
        }

        $options = WP_Clean_Up::get_options();
        ?>
        <div class="wrap wp-clean-up-settings-wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

            <div class="wp-clean-up-settings">
                <nav class="wp-clean-up-sidebar">
                    <ul class="wp-clean-up-nav">
                        <?php foreach ( $tabs as $tab_id => $tab ) : ?>
                            <?php
                            $link_class = '';
                            if ( $current_tab === $tab_id ) {
                                $link_class = 'active';
                            }
                            if ( ! $tab['active'] ) {
                                $link_class = 'disabled';
                            }
                            ?>
                            <li>
                                <?php if ( $tab['active'] ) : ?>
                                    <a href="<?php echo esc_url( add_query_arg( 'tab', $tab_id, admin_url( 'options-general.php?page=admin-clean-up' ) ) ); ?>"
                                       class="<?php echo esc_attr( $link_class ); ?>">
                                        <?php echo esc_html( $tab['title'] ); ?>
                                    </a>
                                <?php else : ?>
                                    <a class="disabled">
                                        <?php echo esc_html( $tab['title'] ); ?>
                                        <span class="badge"><?php esc_html_e( 'Soon', 'admin-clean-up' ); ?></span>
                                    </a>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </nav>

                <div class="wp-clean-up-content">
                    <form method="post" action="options.php">
                        <?php settings_fields( 'wp_clean_up_options_group' ); ?>
                        <input type="hidden" name="wp_clean_up_options[_current_tab]" value="<?php echo esc_attr( $current_tab ); ?>">

                        <?php
                        switch ( $current_tab ) {
                            case 'adminbar':
                                $this->render_adminbar_tab( $options );
                                break;
                            case 'comments':
                                $this->render_comments_tab( $options );
                                break;
                            case 'dashboard':
                                $this->render_dashboard_tab( $options );
                                break;
                            case 'menus':
                                $this->render_menus_tab( $options );
                                break;
                            case 'footer':
                                $this->render_footer_tab( $options );
                                break;
                            case 'notices':
                                $this->render_notices_tab( $options );
                                break;
                            case 'media':
                                $this->render_media_tab( $options );
                                break;
                            case 'plugins':
                                $this->render_plugins_tab( $options );
                                break;
                            case 'updates':
                                $this->render_updates_tab( $options );
                                break;
                            default:
                                $this->render_adminbar_tab( $options );
                                break;
                        }
                        ?>

                        <?php submit_button( __( 'Save Settings', 'admin-clean-up' ) ); ?>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render Admin Bar tab content
     */
    private function render_adminbar_tab( $options ) {
        $adminbar_options = isset( $options['adminbar'] ) ? $options['adminbar'] : [];
        ?>
        <h2><?php esc_html_e( 'Admin Bar Settings', 'admin-clean-up' ); ?></h2>
        <p class="description">
            <?php esc_html_e( 'Select which elements to remove from the admin bar.', 'admin-clean-up' ); ?>
        </p>

        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row"><?php esc_html_e( 'WordPress Logo', 'admin-clean-up' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox"
                                   name="wp_clean_up_options[adminbar][remove_wp_logo]"
                                   value="1"
                                   <?php checked( ! empty( $adminbar_options['remove_wp_logo'] ) ); ?>>
                            <?php esc_html_e( 'Remove WordPress logo', 'admin-clean-up' ); ?>
                        </label>
                        <p class="description">
                            <?php esc_html_e( 'Removes the WordPress logo and its submenu from the admin bar.', 'admin-clean-up' ); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Site Name', 'admin-clean-up' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox"
                                   name="wp_clean_up_options[adminbar][remove_site_menu]"
                                   value="1"
                                   <?php checked( ! empty( $adminbar_options['remove_site_menu'] ) ); ?>>
                            <?php esc_html_e( 'Remove submenus under site name', 'admin-clean-up' ); ?>
                        </label>
                        <p class="description">
                            <?php esc_html_e( 'Keeps the main link to the site but removes the submenu.', 'admin-clean-up' ); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'New Content', 'admin-clean-up' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox"
                                   name="wp_clean_up_options[adminbar][remove_new_content]"
                                   value="1"
                                   <?php checked( ! empty( $adminbar_options['remove_new_content'] ) ); ?>>
                            <?php esc_html_e( 'Remove "New" (+New)', 'admin-clean-up' ); ?>
                        </label>
                        <p class="description">
                            <?php esc_html_e( 'Removes the "+New" button from the admin bar.', 'admin-clean-up' ); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Search Field', 'admin-clean-up' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox"
                                   name="wp_clean_up_options[adminbar][remove_search]"
                                   value="1"
                                   <?php checked( ! empty( $adminbar_options['remove_search'] ) ); ?>>
                            <?php esc_html_e( 'Remove search field', 'admin-clean-up' ); ?>
                        </label>
                        <p class="description">
                            <?php esc_html_e( 'Removes the search field from the admin bar.', 'admin-clean-up' ); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Account Menu (Frontend)', 'admin-clean-up' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox"
                                   name="wp_clean_up_options[adminbar][remove_howdy_frontend]"
                                   value="1"
                                   <?php checked( ! empty( $adminbar_options['remove_howdy_frontend'] ) ); ?>>
                            <?php esc_html_e( 'Hide "Howdy, user" on frontend', 'admin-clean-up' ); ?>
                        </label>
                        <p class="description">
                            <?php esc_html_e( 'Removes the account menu ("Howdy, username") from the admin bar on the frontend. Still visible in admin.', 'admin-clean-up' ); ?>
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php
    }

    /**
     * Render Comments tab content
     */
    private function render_comments_tab( $options ) {
        $comments_options = isset( $options['comments'] ) ? $options['comments'] : [];
        ?>
        <h2><?php esc_html_e( 'Comments Settings', 'admin-clean-up' ); ?></h2>
        <p class="description">
            <?php esc_html_e( 'Completely disable comments functionality on your website.', 'admin-clean-up' ); ?>
        </p>

        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Disable Comments', 'admin-clean-up' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox"
                                   name="wp_clean_up_options[comments][disable_comments]"
                                   value="1"
                                   <?php checked( ! empty( $comments_options['disable_comments'] ) ); ?>>
                            <?php esc_html_e( 'Disable comments completely', 'admin-clean-up' ); ?>
                        </label>
                        <p class="description">
                            <?php esc_html_e( 'This removes all comment functionality:', 'admin-clean-up' ); ?>
                        </p>
                        <ul class="description" style="list-style: disc; margin-left: 20px; margin-top: 8px;">
                            <li><?php esc_html_e( 'Closes comments on all posts and pages', 'admin-clean-up' ); ?></li>
                            <li><?php esc_html_e( 'Hides existing comments and returns 0 for count', 'admin-clean-up' ); ?></li>
                            <li><?php esc_html_e( 'Removes Comments from admin menu', 'admin-clean-up' ); ?></li>
                            <li><?php esc_html_e( 'Removes Discussion from Settings menu', 'admin-clean-up' ); ?></li>
                            <li><?php esc_html_e( 'Removes comment icon from admin bar', 'admin-clean-up' ); ?></li>
                            <li><?php esc_html_e( 'Removes discussion panel from Gutenberg and classic editor', 'admin-clean-up' ); ?></li>
                            <li><?php esc_html_e( 'Hides comment options in Quick Edit', 'admin-clean-up' ); ?></li>
                            <li><?php esc_html_e( 'Disables pingbacks and trackbacks', 'admin-clean-up' ); ?></li>
                            <li><?php esc_html_e( 'Disables comments in REST API and XML-RPC', 'admin-clean-up' ); ?></li>
                            <li><?php esc_html_e( 'Redirects comment feeds to homepage', 'admin-clean-up' ); ?></li>
                            <li><?php esc_html_e( 'Removes comment-related rewrite rules', 'admin-clean-up' ); ?></li>
                        </ul>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php
    }

    /**
     * Render Dashboard tab content
     */
    private function render_dashboard_tab( $options ) {
        $dashboard_options = isset( $options['dashboard'] ) ? $options['dashboard'] : [];
        ?>
        <h2><?php esc_html_e( 'Dashboard Settings', 'admin-clean-up' ); ?></h2>
        <p class="description">
            <?php esc_html_e( 'Select which dashboard widgets to remove.', 'admin-clean-up' ); ?>
        </p>

        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Welcome Panel', 'admin-clean-up' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox"
                                   name="wp_clean_up_options[dashboard][remove_welcome_panel]"
                                   value="1"
                                   <?php checked( ! empty( $dashboard_options['remove_welcome_panel'] ) ); ?>>
                            <?php esc_html_e( 'Remove Welcome Panel', 'admin-clean-up' ); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'At a Glance', 'admin-clean-up' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox"
                                   name="wp_clean_up_options[dashboard][remove_at_a_glance]"
                                   value="1"
                                   <?php checked( ! empty( $dashboard_options['remove_at_a_glance'] ) ); ?>>
                            <?php esc_html_e( 'Remove "At a Glance" widget', 'admin-clean-up' ); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Activity', 'admin-clean-up' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox"
                                   name="wp_clean_up_options[dashboard][remove_activity]"
                                   value="1"
                                   <?php checked( ! empty( $dashboard_options['remove_activity'] ) ); ?>>
                            <?php esc_html_e( 'Remove "Activity" widget', 'admin-clean-up' ); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Quick Draft', 'admin-clean-up' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox"
                                   name="wp_clean_up_options[dashboard][remove_quick_draft]"
                                   value="1"
                                   <?php checked( ! empty( $dashboard_options['remove_quick_draft'] ) ); ?>>
                            <?php esc_html_e( 'Remove "Quick Draft" widget', 'admin-clean-up' ); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'WordPress News', 'admin-clean-up' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox"
                                   name="wp_clean_up_options[dashboard][remove_wp_events]"
                                   value="1"
                                   <?php checked( ! empty( $dashboard_options['remove_wp_events'] ) ); ?>>
                            <?php esc_html_e( 'Remove "WordPress Events and News" widget', 'admin-clean-up' ); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Site Health', 'admin-clean-up' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox"
                                   name="wp_clean_up_options[dashboard][remove_site_health]"
                                   value="1"
                                   <?php checked( ! empty( $dashboard_options['remove_site_health'] ) ); ?>>
                            <?php esc_html_e( 'Remove "Site Health" widget', 'admin-clean-up' ); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Disable Site Health', 'admin-clean-up' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox"
                                   name="wp_clean_up_options[dashboard][disable_site_health]"
                                   value="1"
                                   <?php checked( ! empty( $dashboard_options['disable_site_health'] ) ); ?>>
                            <?php esc_html_e( 'Completely disable Site Health', 'admin-clean-up' ); ?>
                        </label>
                        <p class="description">
                            <?php esc_html_e( 'This completely disables Site Health functionality:', 'admin-clean-up' ); ?>
                        </p>
                        <ul class="description" style="list-style: disc; margin-left: 20px; margin-top: 8px;">
                            <li><?php esc_html_e( 'Removes Site Health from Tools menu', 'admin-clean-up' ); ?></li>
                            <li><?php esc_html_e( 'Removes Site Health dashboard widget', 'admin-clean-up' ); ?></li>
                            <li><?php esc_html_e( 'Disables all site health tests', 'admin-clean-up' ); ?></li>
                            <li><?php esc_html_e( 'Stops scheduled background checks (cron)', 'admin-clean-up' ); ?></li>
                            <li><?php esc_html_e( 'Disables Site Health REST API endpoints', 'admin-clean-up' ); ?></li>
                            <li><?php esc_html_e( 'Stops email reports about site health', 'admin-clean-up' ); ?></li>
                        </ul>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php
    }

    /**
     * Render Menus tab content
     */
    private function render_menus_tab( $options ) {
        $menus_options = isset( $options['menus'] ) ? $options['menus'] : [];

        // Define menu items
        $menu_items = [
            'posts'      => [ 'label' => __( 'Posts', 'admin-clean-up' ), 'menu_label' => __( 'Hide "Posts" menu', 'admin-clean-up' ) ],
            'media'      => [ 'label' => __( 'Media', 'admin-clean-up' ), 'menu_label' => __( 'Hide "Media" menu', 'admin-clean-up' ) ],
            'pages'      => [ 'label' => __( 'Pages', 'admin-clean-up' ), 'menu_label' => __( 'Hide "Pages" menu', 'admin-clean-up' ) ],
            'appearance' => [ 'label' => __( 'Appearance', 'admin-clean-up' ), 'menu_label' => __( 'Hide "Appearance" menu', 'admin-clean-up' ) ],
            'plugins'    => [ 'label' => __( 'Plugins', 'admin-clean-up' ), 'menu_label' => __( 'Hide "Plugins" menu', 'admin-clean-up' ) ],
            'users'      => [ 'label' => __( 'Users', 'admin-clean-up' ), 'menu_label' => __( 'Hide "Users" menu', 'admin-clean-up' ) ],
            'tools'      => [ 'label' => __( 'Tools', 'admin-clean-up' ), 'menu_label' => __( 'Hide "Tools" menu', 'admin-clean-up' ) ],
            'settings'   => [ 'label' => __( 'Settings', 'admin-clean-up' ), 'menu_label' => __( 'Hide "Settings" menu', 'admin-clean-up' ), 'warning' => true ],
        ];

        // Role options for dropdown
        $role_options = [
            'non_admin'  => __( 'All except administrators', 'admin-clean-up' ),
            'non_editor' => __( 'All except administrators & editors', 'admin-clean-up' ),
            'all'        => __( 'All users', 'admin-clean-up' ),
        ];
        ?>
        <h2><?php esc_html_e( 'Admin Menu Settings', 'admin-clean-up' ); ?></h2>
        <p class="description">
            <?php esc_html_e( 'Select which admin menus to hide and for which user roles.', 'admin-clean-up' ); ?>
        </p>

        <table class="form-table" role="presentation">
            <tbody>
                <?php foreach ( $menu_items as $key => $item ) : ?>
                    <?php
                    $is_hidden = ! empty( $menus_options[ 'remove_' . $key ] );
                    $hide_for  = isset( $menus_options[ 'remove_' . $key . '_for' ] ) ? $menus_options[ 'remove_' . $key . '_for' ] : 'non_admin';
                    ?>
                    <tr class="menu-item-row">
                        <th scope="row"><?php echo esc_html( $item['label'] ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox"
                                       name="wp_clean_up_options[menus][remove_<?php echo esc_attr( $key ); ?>]"
                                       value="1"
                                       class="menu-toggle"
                                       data-target="<?php echo esc_attr( $key ); ?>"
                                       <?php checked( $is_hidden ); ?>>
                                <?php echo esc_html( $item['menu_label'] ); ?>
                            </label>
                            <select name="wp_clean_up_options[menus][remove_<?php echo esc_attr( $key ); ?>_for]"
                                    id="menu-role-<?php echo esc_attr( $key ); ?>"
                                    class="menu-role-select"
                                    <?php echo ! $is_hidden ? 'disabled' : ''; ?>>
                                <?php foreach ( $role_options as $value => $label ) : ?>
                                    <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $hide_for, $value ); ?>>
                                        <?php echo esc_html( $label ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ( ! empty( $item['warning'] ) ) : ?>
                                <p class="description menu-warning">
                                    <?php esc_html_e( 'Warning: If you hide this for all users, you cannot access this plugin\'s settings via the menu.', 'admin-clean-up' ); ?>
                                </p>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <script>
        jQuery(document).ready(function($) {
            $('.menu-toggle').on('change', function() {
                var target = $(this).data('target');
                var $select = $('#menu-role-' + target);
                $select.prop('disabled', !$(this).is(':checked'));
            });
        });
        </script>
        <?php
    }

    /**
     * Render Footer tab content
     */
    private function render_footer_tab( $options ) {
        $footer_options = isset( $options['footer'] ) ? $options['footer'] : [];
        ?>
        <h2><?php esc_html_e( 'Footer Settings', 'admin-clean-up' ); ?></h2>
        <p class="description">
            <?php esc_html_e( 'Customize or remove the text in the admin footer.', 'admin-clean-up' ); ?>
        </p>

        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Footer Text', 'admin-clean-up' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox"
                                   name="wp_clean_up_options[footer][remove_footer_text]"
                                   value="1"
                                   <?php checked( ! empty( $footer_options['remove_footer_text'] ) ); ?>>
                            <?php esc_html_e( 'Remove footer text ("Thank you for creating with WordPress")', 'admin-clean-up' ); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Custom Footer Text', 'admin-clean-up' ); ?></th>
                    <td>
                        <input type="text"
                               name="wp_clean_up_options[footer][custom_footer_text]"
                               value="<?php echo esc_attr( $footer_options['custom_footer_text'] ?? '' ); ?>"
                               class="regular-text"
                               placeholder="<?php esc_attr_e( 'E.g. Developed by Digiwise', 'admin-clean-up' ); ?>">
                        <p class="description">
                            <?php esc_html_e( 'Leave empty to use WordPress default text, or enter custom text. Ignored if "Remove footer text" is enabled.', 'admin-clean-up' ); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Version Number', 'admin-clean-up' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox"
                                   name="wp_clean_up_options[footer][remove_version]"
                                   value="1"
                                   <?php checked( ! empty( $footer_options['remove_version'] ) ); ?>>
                            <?php esc_html_e( 'Remove WordPress version number', 'admin-clean-up' ); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Custom Version Text', 'admin-clean-up' ); ?></th>
                    <td>
                        <input type="text"
                               name="wp_clean_up_options[footer][custom_version_text]"
                               value="<?php echo esc_attr( $footer_options['custom_version_text'] ?? '' ); ?>"
                               class="regular-text"
                               placeholder="<?php esc_attr_e( 'E.g. Version 2.0', 'admin-clean-up' ); ?>">
                        <p class="description">
                            <?php esc_html_e( 'Leave empty to show WordPress version, or enter custom text. Ignored if "Remove version number" is enabled.', 'admin-clean-up' ); ?>
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php
    }

    /**
     * Render Notices tab content
     */
    private function render_notices_tab( $options ) {
        $notices_options = isset( $options['notices'] ) ? $options['notices'] : [];
        ?>
        <h2><?php esc_html_e( 'Notices Settings', 'admin-clean-up' ); ?></h2>
        <p class="description">
            <?php esc_html_e( 'Control which notices and elements are displayed in admin.', 'admin-clean-up' ); ?>
        </p>

        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Update Notices', 'admin-clean-up' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox"
                                   name="wp_clean_up_options[notices][hide_update_notices]"
                                   value="1"
                                   <?php checked( ! empty( $notices_options['hide_update_notices'] ) ); ?>>
                            <?php esc_html_e( 'Hide update notices', 'admin-clean-up' ); ?>
                        </label>
                        <p class="description">
                            <?php esc_html_e( 'Hides notices about WordPress, plugin and theme updates.', 'admin-clean-up' ); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'All Notices', 'admin-clean-up' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox"
                                   name="wp_clean_up_options[notices][hide_all_notices]"
                                   value="1"
                                   <?php checked( ! empty( $notices_options['hide_all_notices'] ) ); ?>>
                            <?php esc_html_e( 'Hide all admin notices', 'admin-clean-up' ); ?>
                        </label>
                        <p class="description" style="color: #d63638;">
                            <?php esc_html_e( 'Warning: This hides ALL notices, including important error messages and warnings.', 'admin-clean-up' ); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Screen Options', 'admin-clean-up' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox"
                                   name="wp_clean_up_options[notices][hide_screen_options]"
                                   value="1"
                                   <?php checked( ! empty( $notices_options['hide_screen_options'] ) ); ?>>
                            <?php esc_html_e( 'Hide "Screen Options" tab', 'admin-clean-up' ); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Help Tab', 'admin-clean-up' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox"
                                   name="wp_clean_up_options[notices][hide_help_tab]"
                                   value="1"
                                   <?php checked( ! empty( $notices_options['hide_help_tab'] ) ); ?>>
                            <?php esc_html_e( 'Hide "Help" tab', 'admin-clean-up' ); ?>
                        </label>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php
    }

    /**
     * Render Media tab content
     */
    private function render_media_tab( $options ) {
        $media_options = isset( $options['media'] ) ? $options['media'] : [];
        ?>
        <h2><?php esc_html_e( 'Media Settings', 'admin-clean-up' ); ?></h2>
        <p class="description">
            <?php esc_html_e( 'Settings for the media library and file uploads.', 'admin-clean-up' ); ?>
        </p>

        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Clean Filenames', 'admin-clean-up' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox"
                                   name="wp_clean_up_options[media][clean_filenames]"
                                   value="1"
                                   <?php checked( ! empty( $media_options['clean_filenames'] ) ); ?>>
                            <?php esc_html_e( 'Clean filenames automatically on upload', 'admin-clean-up' ); ?>
                        </label>
                        <p class="description">
                            <?php esc_html_e( 'This automatically cleans filenames when files are uploaded:', 'admin-clean-up' ); ?>
                        </p>
                        <ul class="description" style="list-style: disc; margin-left: 20px; margin-top: 8px;">
                            <li><?php esc_html_e( 'Converts special characters (å, ä, ö, etc.) to their ASCII equivalents', 'admin-clean-up' ); ?></li>
                            <li><?php esc_html_e( 'Replaces spaces and underscores with dashes', 'admin-clean-up' ); ?></li>
                            <li><?php esc_html_e( 'Removes special characters and accents', 'admin-clean-up' ); ?></li>
                            <li><?php esc_html_e( 'Converts to lowercase', 'admin-clean-up' ); ?></li>
                            <li><?php esc_html_e( 'Preserves the original title as the image title in the media library', 'admin-clean-up' ); ?></li>
                        </ul>
                        <p class="description" style="margin-top: 10px;">
                            <strong><?php esc_html_e( 'Example:', 'admin-clean-up' ); ?></strong><br>
                            <code>My Image_from Malmö (2024).jpg</code> → <code>my-image-from-malmo-2024.jpg</code>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'File Types to Clean', 'admin-clean-up' ); ?></th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="radio"
                                       name="wp_clean_up_options[media][clean_filenames_types]"
                                       value="all"
                                       <?php checked( ( $media_options['clean_filenames_types'] ?? 'all' ), 'all' ); ?>>
                                <?php esc_html_e( 'All file types', 'admin-clean-up' ); ?>
                            </label>
                            <br>
                            <label>
                                <input type="radio"
                                       name="wp_clean_up_options[media][clean_filenames_types]"
                                       value="images"
                                       <?php checked( ( $media_options['clean_filenames_types'] ?? 'all' ), 'images' ); ?>>
                                <?php esc_html_e( 'Images only (JPG, PNG, GIF, WebP, etc.)', 'admin-clean-up' ); ?>
                            </label>
                        </fieldset>
                        <p class="description">
                            <?php esc_html_e( 'Select which file types should have their filenames cleaned on upload.', 'admin-clean-up' ); ?>
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php
    }

    /**
     * Render Plugins tab content
     */
    private function render_plugins_tab( $options ) {
        $plugins_options = isset( $options['plugins'] ) ? $options['plugins'] : [];
        $installed_plugins = $this->get_installed_supported_plugins();

        // Plugin-specific descriptions
        $plugin_descriptions = [
            'pixelyoursite' => __( 'Hides the "Free PIXELYOURSITE HACKS" email signup, video tips, and other promotional notices from PixelYourSite plugin.', 'admin-clean-up' ),
        ];

        // Plugin-specific checkbox labels
        $plugin_labels = [
            'pixelyoursite' => __( 'Hide PixelYourSite promotional notices', 'admin-clean-up' ),
        ];
        ?>
        <h2><?php esc_html_e( 'Plugin Notices Settings', 'admin-clean-up' ); ?></h2>
        <p class="description">
            <?php esc_html_e( 'Hide annoying promotional notices and nag screens from specific plugins.', 'admin-clean-up' ); ?>
        </p>

        <table class="form-table" role="presentation">
            <tbody>
                <?php foreach ( $installed_plugins as $key => $plugin ) : ?>
                    <tr>
                        <th scope="row"><?php echo esc_html( $plugin['name'] ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox"
                                       name="wp_clean_up_options[plugins][<?php echo esc_attr( $plugin['option'] ); ?>]"
                                       value="1"
                                       <?php checked( ! empty( $plugins_options[ $plugin['option'] ] ) ); ?>>
                                <?php echo esc_html( $plugin_labels[ $key ] ?? sprintf( __( 'Hide %s notices', 'admin-clean-up' ), $plugin['name'] ) ); ?>
                            </label>
                            <?php if ( ! empty( $plugin_descriptions[ $key ] ) ) : ?>
                                <p class="description">
                                    <?php echo esc_html( $plugin_descriptions[ $key ] ); ?>
                                </p>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }

    /**
     * Render Updates tab content
     */
    private function render_updates_tab( $options ) {
        $updates_options = isset( $options['updates'] ) ? $options['updates'] : [];
        ?>
        <h2><?php esc_html_e( 'Automatic Updates Settings', 'admin-clean-up' ); ?></h2>
        <p class="description">
            <?php esc_html_e( 'Control automatic updates for WordPress core, plugins, and themes.', 'admin-clean-up' ); ?>
        </p>

        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row"><?php esc_html_e( 'WordPress Core Updates', 'admin-clean-up' ); ?></th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="radio"
                                       name="wp_clean_up_options[updates][core_updates]"
                                       value="default"
                                       <?php checked( ( $updates_options['core_updates'] ?? 'default' ), 'default' ); ?>>
                                <?php esc_html_e( 'WordPress default (minor updates only)', 'admin-clean-up' ); ?>
                            </label>
                            <br>
                            <label>
                                <input type="radio"
                                       name="wp_clean_up_options[updates][core_updates]"
                                       value="security_only"
                                       <?php checked( ( $updates_options['core_updates'] ?? 'default' ), 'security_only' ); ?>>
                                <?php esc_html_e( 'Security updates only (minor releases)', 'admin-clean-up' ); ?>
                            </label>
                            <br>
                            <label>
                                <input type="radio"
                                       name="wp_clean_up_options[updates][core_updates]"
                                       value="minor_only"
                                       <?php checked( ( $updates_options['core_updates'] ?? 'default' ), 'minor_only' ); ?>>
                                <?php esc_html_e( 'Minor updates only (e.g., 6.4.1 to 6.4.2)', 'admin-clean-up' ); ?>
                            </label>
                            <br>
                            <label>
                                <input type="radio"
                                       name="wp_clean_up_options[updates][core_updates]"
                                       value="all_updates"
                                       <?php checked( ( $updates_options['core_updates'] ?? 'default' ), 'all_updates' ); ?>>
                                <?php esc_html_e( 'All updates (major + minor, e.g., 6.4 to 6.5)', 'admin-clean-up' ); ?>
                            </label>
                            <br>
                            <label>
                                <input type="radio"
                                       name="wp_clean_up_options[updates][core_updates]"
                                       value="disable_all"
                                       <?php checked( ( $updates_options['core_updates'] ?? 'default' ), 'disable_all' ); ?>>
                                <?php esc_html_e( 'Disable all automatic core updates', 'admin-clean-up' ); ?>
                            </label>
                        </fieldset>
                        <p class="description" style="margin-top: 10px;">
                            <?php esc_html_e( 'Note: Security updates are recommended to keep your site safe.', 'admin-clean-up' ); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Plugin Auto-Updates', 'admin-clean-up' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox"
                                   name="wp_clean_up_options[updates][disable_plugin_updates]"
                                   value="1"
                                   <?php checked( ! empty( $updates_options['disable_plugin_updates'] ) ); ?>>
                            <?php esc_html_e( 'Disable automatic plugin updates', 'admin-clean-up' ); ?>
                        </label>
                        <p class="description">
                            <?php esc_html_e( 'Prevents plugins from updating automatically. You can still update them manually.', 'admin-clean-up' ); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Theme Auto-Updates', 'admin-clean-up' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox"
                                   name="wp_clean_up_options[updates][disable_theme_updates]"
                                   value="1"
                                   <?php checked( ! empty( $updates_options['disable_theme_updates'] ) ); ?>>
                            <?php esc_html_e( 'Disable automatic theme updates', 'admin-clean-up' ); ?>
                        </label>
                        <p class="description">
                            <?php esc_html_e( 'Prevents themes from updating automatically. You can still update them manually.', 'admin-clean-up' ); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Update Emails', 'admin-clean-up' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox"
                                   name="wp_clean_up_options[updates][disable_update_emails]"
                                   value="1"
                                   <?php checked( ! empty( $updates_options['disable_update_emails'] ) ); ?>>
                            <?php esc_html_e( 'Disable update notification emails', 'admin-clean-up' ); ?>
                        </label>
                        <p class="description">
                            <?php esc_html_e( 'Stops WordPress from sending emails about automatic updates (core, plugins, themes).', 'admin-clean-up' ); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Update Nags', 'admin-clean-up' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox"
                                   name="wp_clean_up_options[updates][hide_update_nags]"
                                   value="1"
                                   <?php checked( ! empty( $updates_options['hide_update_nags'] ) ); ?>>
                            <?php esc_html_e( 'Hide update nags in admin', 'admin-clean-up' ); ?>
                        </label>
                        <p class="description">
                            <?php esc_html_e( 'Hides the "WordPress X.X is available! Please update now" message and update count badges.', 'admin-clean-up' ); ?>
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php
    }
}
