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
            WP_Clean_Up::OPTION_KEY,
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
        $existing = get_option( WP_Clean_Up::OPTION_KEY, [] );
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
                'hide_elementor_notices'     => ! empty( $plugins['hide_elementor_notices'] ),
                'hide_yoast_notices'         => ! empty( $plugins['hide_yoast_notices'] ),
                'hide_complianz_comments'    => ! empty( $plugins['hide_complianz_comments'] ),
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
                'name'      => 'PixelYourSite',
                'check'     => 'pixelyoursite/facebook-pixel-master.php',
                'pro_check' => 'pixelyoursite-pro/pixelyoursite-pro.php',
                'option'    => 'hide_pixelyoursite_notices',
            ],
            'elementor' => [
                'name'   => 'Elementor',
                'check'  => 'elementor/elementor.php',
                'option' => 'hide_elementor_notices',
            ],
            'yoast' => [
                'name'   => 'Yoast SEO',
                'check'  => 'wordpress-seo/wp-seo.php',
                'option' => 'hide_yoast_notices',
            ],
            'complianz' => [
                'name'   => 'Complianz',
                'check'  => [ 'complianz-gdpr/complianz-gpdr.php', 'complianz-gdpr-premium/complianz-gpdr-premium.php' ],
                'option' => 'hide_complianz_comments',
            ],
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
            // Support both single string and array of plugin paths
            $checks = (array) $plugin['check'];
            $is_active = false;
            foreach ( $checks as $check ) {
                if ( is_plugin_active( $check ) ) {
                    $is_active = true;
                    break;
                }
            }
            if ( $is_active ) {
                // If plugin has a pro version, exclude when pro is active
                if ( ! empty( $plugin['pro_check'] ) && is_plugin_active( $plugin['pro_check'] ) ) {
                    continue;
                }
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
        <div class="wrap acu-settings-wrap">
            <div class="acu-settings">
                <nav class="acu-sidebar">
                    <ul class="acu-sidebar__nav">
                        <?php foreach ( $tabs as $tab_id => $tab ) : ?>
                            <?php if ( $tab['active'] ) : ?>
                                <li>
                                    <a href="<?php echo esc_url( add_query_arg( 'tab', $tab_id, admin_url( 'options-general.php?page=admin-clean-up' ) ) ); ?>"
                                       class="acu-sidebar__link<?php echo $current_tab === $tab_id ? ' acu-sidebar__link--active' : ''; ?>">
                                        <?php echo esc_html( $tab['title'] ); ?>
                                    </a>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </nav>

                <div class="acu-content">
                    <form method="post" action="options.php">
                        <?php settings_fields( 'wp_clean_up_options_group' ); ?>
                        <input type="hidden" name="<?php echo esc_attr( WP_Clean_Up::OPTION_KEY ); ?>[_current_tab]" value="<?php echo esc_attr( $current_tab ); ?>">

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

                        <div class="acu-submit">
                            <button type="submit" class="acu-button-primary"><?php esc_html_e( 'Save Settings', 'admin-clean-up' ); ?></button>
                        </div>
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
        $adminbar = isset( $options['adminbar'] ) ? $options['adminbar'] : [];

        ob_start();
        WP_Clean_Up_Components::render_setting_group( [
            'settings' => [
                [
                    'name'        => WP_Clean_Up::OPTION_KEY . '[adminbar][remove_wp_logo]',
                    'checked'     => ! empty( $adminbar['remove_wp_logo'] ),
                    'label'       => __( 'WordPress Logo', 'admin-clean-up' ),
                    'description' => __( 'Removes the WordPress logo and its submenu from the admin bar.', 'admin-clean-up' ),
                ],
                [
                    'name'        => WP_Clean_Up::OPTION_KEY . '[adminbar][remove_site_menu]',
                    'checked'     => ! empty( $adminbar['remove_site_menu'] ),
                    'label'       => __( 'Site Name Submenu', 'admin-clean-up' ),
                    'description' => __( 'Keeps the main link to the site but removes the submenu.', 'admin-clean-up' ),
                ],
                [
                    'name'        => WP_Clean_Up::OPTION_KEY . '[adminbar][remove_new_content]',
                    'checked'     => ! empty( $adminbar['remove_new_content'] ),
                    'label'       => __( 'New Content Button', 'admin-clean-up' ),
                    'description' => __( 'Removes the "+New" button from the admin bar.', 'admin-clean-up' ),
                ],
                [
                    'name'        => WP_Clean_Up::OPTION_KEY . '[adminbar][remove_search]',
                    'checked'     => ! empty( $adminbar['remove_search'] ),
                    'label'       => __( 'Search Field', 'admin-clean-up' ),
                    'description' => __( 'Removes the search field from the admin bar.', 'admin-clean-up' ),
                ],
                [
                    'name'        => WP_Clean_Up::OPTION_KEY . '[adminbar][remove_howdy_frontend]',
                    'checked'     => ! empty( $adminbar['remove_howdy_frontend'] ),
                    'label'       => __( 'Account Menu on Frontend', 'admin-clean-up' ),
                    'description' => __( 'Hides the "Howdy, username" menu from the admin bar on the frontend. Still visible in admin.', 'admin-clean-up' ),
                ],
            ],
        ] );
        $content = ob_get_clean();

        WP_Clean_Up_Components::render_card( [
            'title'       => __( 'Admin Bar Elements', 'admin-clean-up' ),
            'description' => __( 'Select which elements to remove from the admin bar.', 'admin-clean-up' ),
            'content'     => $content,
        ] );
    }

    /**
     * Render Comments tab content
     */
    private function render_comments_tab( $options ) {
        $comments_options = isset( $options['comments'] ) ? $options['comments'] : [];

        // Build toggle content using output buffering
        ob_start();
        WP_Clean_Up_Components::render_toggle(
            [
                'name'        => WP_Clean_Up::OPTION_KEY . '[comments][disable_comments]',
                'checked'     => ! empty( $comments_options['disable_comments'] ),
                'label'       => __( 'Disable comments completely', 'admin-clean-up' ),
                'description' => __( 'Removes all comment functionality: closes comments on all posts, hides existing comments, removes Comments and Discussion from admin menus, disables pingbacks, trackbacks, REST API comments, and XML-RPC comment methods.', 'admin-clean-up' ),
            ]
        );
        $content = ob_get_clean();

        // Render card with toggle
        WP_Clean_Up_Components::render_card(
            [
                'title'       => __( 'Comments', 'admin-clean-up' ),
                'description' => __( 'Completely disable comments functionality on your website.', 'admin-clean-up' ),
                'content'     => $content,
            ]
        );
    }

    /**
     * Render Dashboard tab content
     */
    private function render_dashboard_tab( $options ) {
        $dashboard = isset( $options['dashboard'] ) ? $options['dashboard'] : [];

        ob_start();
        WP_Clean_Up_Components::render_setting_group( [
            'title'    => __( 'Widgets', 'admin-clean-up' ),
            'settings' => [
                [
                    'name'    => WP_Clean_Up::OPTION_KEY . '[dashboard][remove_welcome_panel]',
                    'checked' => ! empty( $dashboard['remove_welcome_panel'] ),
                    'label'   => __( 'Welcome Panel', 'admin-clean-up' ),
                ],
                [
                    'name'    => WP_Clean_Up::OPTION_KEY . '[dashboard][remove_at_a_glance]',
                    'checked' => ! empty( $dashboard['remove_at_a_glance'] ),
                    'label'   => __( 'At a Glance', 'admin-clean-up' ),
                ],
                [
                    'name'    => WP_Clean_Up::OPTION_KEY . '[dashboard][remove_activity]',
                    'checked' => ! empty( $dashboard['remove_activity'] ),
                    'label'   => __( 'Activity', 'admin-clean-up' ),
                ],
                [
                    'name'    => WP_Clean_Up::OPTION_KEY . '[dashboard][remove_quick_draft]',
                    'checked' => ! empty( $dashboard['remove_quick_draft'] ),
                    'label'   => __( 'Quick Draft', 'admin-clean-up' ),
                ],
                [
                    'name'    => WP_Clean_Up::OPTION_KEY . '[dashboard][remove_wp_events]',
                    'checked' => ! empty( $dashboard['remove_wp_events'] ),
                    'label'   => __( 'WordPress Events and News', 'admin-clean-up' ),
                ],
            ],
        ] );
        WP_Clean_Up_Components::render_setting_group( [
            'title'    => __( 'Site Health', 'admin-clean-up' ),
            'settings' => [
                [
                    'name'        => WP_Clean_Up::OPTION_KEY . '[dashboard][remove_site_health]',
                    'checked'     => ! empty( $dashboard['remove_site_health'] ),
                    'label'       => __( 'Remove Site Health Widget', 'admin-clean-up' ),
                    'description' => __( 'Removes the Site Health Status widget from the dashboard.', 'admin-clean-up' ),
                ],
                [
                    'name'        => WP_Clean_Up::OPTION_KEY . '[dashboard][disable_site_health]',
                    'checked'     => ! empty( $dashboard['disable_site_health'] ),
                    'label'       => __( 'Disable Site Health Completely', 'admin-clean-up' ),
                    'description' => __( 'Removes Site Health from Tools menu, disables all tests, stops scheduled background checks, disables REST API endpoints, and stops email reports.', 'admin-clean-up' ),
                ],
            ],
        ] );
        $content = ob_get_clean();

        WP_Clean_Up_Components::render_card( [
            'title'       => __( 'Dashboard', 'admin-clean-up' ),
            'description' => __( 'Select which dashboard widgets to remove.', 'admin-clean-up' ),
            'content'     => $content,
        ] );
    }

    /**
     * Render Menus tab content
     */
    private function render_menus_tab( $options ) {
        $menus = isset( $options['menus'] ) ? $options['menus'] : [];
        $menu_items = [
            'posts'      => __( 'Posts', 'admin-clean-up' ),
            'media'      => __( 'Media', 'admin-clean-up' ),
            'pages'      => __( 'Pages', 'admin-clean-up' ),
            'appearance' => __( 'Appearance', 'admin-clean-up' ),
            'plugins'    => __( 'Plugins', 'admin-clean-up' ),
            'users'      => __( 'Users', 'admin-clean-up' ),
            'tools'      => __( 'Tools', 'admin-clean-up' ),
            'settings'   => __( 'Settings', 'admin-clean-up' ),
        ];
        $role_options = [
            [ 'value' => 'non_admin',  'label' => __( 'All except administrators', 'admin-clean-up' ) ],
            [ 'value' => 'non_editor', 'label' => __( 'All except administrators & editors', 'admin-clean-up' ) ],
            [ 'value' => 'all',        'label' => __( 'All users', 'admin-clean-up' ) ],
        ];

        ob_start();
        foreach ( $menu_items as $key => $label ) {
            $is_hidden = ! empty( $menus[ 'remove_' . $key ] );
            $hide_for  = isset( $menus[ 'remove_' . $key . '_for' ] ) ? $menus[ 'remove_' . $key . '_for' ] : 'non_admin';

            echo '<div class="acu-menu-item">';
            WP_Clean_Up_Components::render_toggle( [
                'name'    => WP_Clean_Up::OPTION_KEY . '[menus][remove_' . $key . ']',
                'checked' => $is_hidden,
                /* translators: %s: menu item name (e.g., "Posts", "Media") */
                'label'   => sprintf( __( 'Hide "%s" menu', 'admin-clean-up' ), $label ),
            ] );
            WP_Clean_Up_Components::render_select( [
                'name'     => WP_Clean_Up::OPTION_KEY . '[menus][remove_' . $key . '_for]',
                'value'    => $hide_for,
                'options'  => $role_options,
                'disabled' => ! $is_hidden,
                'id'       => 'menu-role-' . $key,
            ] );
            if ( 'settings' === $key ) {
                echo '<p class="acu-text-warning">';
                esc_html_e( 'Warning: If you hide this for all users, you cannot access this plugin\'s settings via the menu.', 'admin-clean-up' );
                echo '</p>';
            }
            echo '</div>';
        }
        $content = ob_get_clean();

        WP_Clean_Up_Components::render_card( [
            'title'       => __( 'Admin Menus', 'admin-clean-up' ),
            'description' => __( 'Select which admin menus to hide and for which user roles.', 'admin-clean-up' ),
            'content'     => $content,
        ] );

        // JavaScript for toggle-select interaction
        ?>
        <script>
        jQuery(document).ready(function($) {
            $('.acu-menu-item .acu-toggle__input').on('change', function() {
                var $select = $(this).closest('.acu-menu-item').find('.acu-select');
                $select.prop('disabled', !this.checked);
            });
        });
        </script>
        <?php
    }

    /**
     * Render Footer tab content
     */
    private function render_footer_tab( $options ) {
        $footer = isset( $options['footer'] ) ? $options['footer'] : [];

        // Card 1: Footer Text
        ob_start();
        WP_Clean_Up_Components::render_toggle( [
            'name'        => WP_Clean_Up::OPTION_KEY . '[footer][remove_footer_text]',
            'checked'     => ! empty( $footer['remove_footer_text'] ),
            'label'       => __( 'Remove Footer Text', 'admin-clean-up' ),
            'description' => __( 'Removes "Thank you for creating with WordPress" from the admin footer.', 'admin-clean-up' ),
        ] );
        WP_Clean_Up_Components::render_text_input( [
            'name'        => WP_Clean_Up::OPTION_KEY . '[footer][custom_footer_text]',
            'value'       => $footer['custom_footer_text'] ?? '',
            'label'       => __( 'Custom Footer Text', 'admin-clean-up' ),
            'placeholder' => __( 'E.g. Developed by Digiwise', 'admin-clean-up' ),
            'description' => __( 'Leave empty for WordPress default. Ignored if "Remove Footer Text" is enabled.', 'admin-clean-up' ),
        ] );
        $content1 = ob_get_clean();

        WP_Clean_Up_Components::render_card( [
            'title'   => __( 'Footer Text', 'admin-clean-up' ),
            'content' => $content1,
        ] );

        // Card 2: Version Number
        ob_start();
        WP_Clean_Up_Components::render_toggle( [
            'name'        => WP_Clean_Up::OPTION_KEY . '[footer][remove_version]',
            'checked'     => ! empty( $footer['remove_version'] ),
            'label'       => __( 'Remove Version Number', 'admin-clean-up' ),
            'description' => __( 'Removes the WordPress version number from the admin footer.', 'admin-clean-up' ),
        ] );
        WP_Clean_Up_Components::render_text_input( [
            'name'        => WP_Clean_Up::OPTION_KEY . '[footer][custom_version_text]',
            'value'       => $footer['custom_version_text'] ?? '',
            'label'       => __( 'Custom Version Text', 'admin-clean-up' ),
            'placeholder' => __( 'E.g. Version 2.0', 'admin-clean-up' ),
            'description' => __( 'Leave empty to show WordPress version. Ignored if "Remove Version Number" is enabled.', 'admin-clean-up' ),
        ] );
        $content2 = ob_get_clean();

        WP_Clean_Up_Components::render_card( [
            'title'   => __( 'Version Number', 'admin-clean-up' ),
            'content' => $content2,
        ] );
    }

    /**
     * Render Notices tab content
     */
    private function render_notices_tab( $options ) {
        $notices = isset( $options['notices'] ) ? $options['notices'] : [];

        ob_start();
        WP_Clean_Up_Components::render_setting_group( [
            'settings' => [
                [
                    'name'        => WP_Clean_Up::OPTION_KEY . '[notices][hide_update_notices]',
                    'checked'     => ! empty( $notices['hide_update_notices'] ),
                    'label'       => __( 'Hide Update Notices', 'admin-clean-up' ),
                    'description' => __( 'Hides notices about WordPress, plugin and theme updates.', 'admin-clean-up' ),
                ],
                [
                    'name'        => WP_Clean_Up::OPTION_KEY . '[notices][hide_all_notices]',
                    'checked'     => ! empty( $notices['hide_all_notices'] ),
                    'label'       => __( 'Hide All Admin Notices', 'admin-clean-up' ),
                    'description' => __( 'Warning: This hides ALL notices, including important error messages and warnings.', 'admin-clean-up' ),
                ],
                [
                    'name'        => WP_Clean_Up::OPTION_KEY . '[notices][hide_screen_options]',
                    'checked'     => ! empty( $notices['hide_screen_options'] ),
                    'label'       => __( 'Hide Screen Options Tab', 'admin-clean-up' ),
                ],
                [
                    'name'        => WP_Clean_Up::OPTION_KEY . '[notices][hide_help_tab]',
                    'checked'     => ! empty( $notices['hide_help_tab'] ),
                    'label'       => __( 'Hide Help Tab', 'admin-clean-up' ),
                ],
            ],
        ] );
        $content = ob_get_clean();

        WP_Clean_Up_Components::render_card( [
            'title'       => __( 'Notices & UI Elements', 'admin-clean-up' ),
            'description' => __( 'Control which notices and interface elements are displayed in admin.', 'admin-clean-up' ),
            'content'     => $content,
        ] );
    }

    /**
     * Render Media tab content
     */
    private function render_media_tab( $options ) {
        $media = isset( $options['media'] ) ? $options['media'] : [];

        ob_start();
        WP_Clean_Up_Components::render_toggle( [
            'name'        => WP_Clean_Up::OPTION_KEY . '[media][clean_filenames]',
            'checked'     => ! empty( $media['clean_filenames'] ),
            'label'       => __( 'Clean Filenames on Upload', 'admin-clean-up' ),
            'description' => __( 'Converts special characters to ASCII, replaces spaces with dashes, removes accents, and converts to lowercase. Original title preserved in media library.', 'admin-clean-up' ),
        ] );
        WP_Clean_Up_Components::render_radio_group( [
            'name'    => WP_Clean_Up::OPTION_KEY . '[media][clean_filenames_types]',
            'value'   => $media['clean_filenames_types'] ?? 'all',
            'options' => [
                [
                    'value' => 'all',
                    'label' => __( 'All file types', 'admin-clean-up' ),
                ],
                [
                    'value' => 'images',
                    'label' => __( 'Images only', 'admin-clean-up' ),
                    'description' => __( 'JPG, PNG, GIF, WebP, etc.', 'admin-clean-up' ),
                ],
            ],
        ] );
        $content = ob_get_clean();

        WP_Clean_Up_Components::render_card( [
            'title'       => __( 'Media Library', 'admin-clean-up' ),
            'description' => __( 'Settings for file uploads and the media library.', 'admin-clean-up' ),
            'content'     => $content,
        ] );
    }

    /**
     * Render Plugins tab content
     */
    private function render_plugins_tab( $options ) {
        $plugins = isset( $options['plugins'] ) ? $options['plugins'] : [];
        $installed_plugins = $this->get_installed_supported_plugins();

        $plugin_descriptions = [
            'pixelyoursite' => __( 'Hides the "Free PIXELYOURSITE HACKS" email signup, video tips, and other promotional notices.', 'admin-clean-up' ),
            'elementor'     => __( 'Hides promotional notices, dashboard widget, "What\'s New" and "Get Help" icons, and replaces the Home screen with Settings.', 'admin-clean-up' ),
            'yoast'         => __( 'Hides premium upsell pages, promotional notices, dashboard widget, notification badges, and disables usage tracking.', 'admin-clean-up' ),
            'complianz'     => __( 'Removes Complianz HTML comments from the frontend source code, such as "Consent Management powered by Complianz".', 'admin-clean-up' ),
        ];

        $plugin_labels = [
            'pixelyoursite' => __( 'Hide PixelYourSite Promotional Notices', 'admin-clean-up' ),
            'elementor'     => __( 'Clean Up Elementor Admin', 'admin-clean-up' ),
            'yoast'         => __( 'Clean Up Yoast SEO Admin', 'admin-clean-up' ),
            'complianz'     => __( 'Remove Complianz HTML Comments', 'admin-clean-up' ),
        ];

        ob_start();
        foreach ( $installed_plugins as $key => $plugin ) {
            WP_Clean_Up_Components::render_toggle( [
                'name'        => WP_Clean_Up::OPTION_KEY . '[plugins][' . $plugin['option'] . ']',
                'checked'     => ! empty( $plugins[ $plugin['option'] ] ),
                /* translators: %s: plugin name */
                'label'       => $plugin_labels[ $key ] ?? sprintf( __( 'Hide %s notices', 'admin-clean-up' ), $plugin['name'] ),
                'description' => $plugin_descriptions[ $key ] ?? '',
            ] );
        }
        $content = ob_get_clean();

        WP_Clean_Up_Components::render_card( [
            'title'       => __( 'Plugins', 'admin-clean-up' ),
            'description' => __( 'Hide promotional notices and nag screens from specific plugins.', 'admin-clean-up' ),
            'content'     => $content,
        ] );
    }

    /**
     * Render Updates tab content
     */
    private function render_updates_tab( $options ) {
        $updates = isset( $options['updates'] ) ? $options['updates'] : [];

        // Card 1: Core Updates (radio group)
        ob_start();
        WP_Clean_Up_Components::render_radio_group( [
            'name'    => WP_Clean_Up::OPTION_KEY . '[updates][core_updates]',
            'value'   => $updates['core_updates'] ?? 'default',
            'options' => [
                [
                    'value' => 'default',
                    'label' => __( 'WordPress Default', 'admin-clean-up' ),
                    'description' => __( 'Minor updates only (recommended)', 'admin-clean-up' ),
                ],
                [
                    'value' => 'security_only',
                    'label' => __( 'Security Updates Only', 'admin-clean-up' ),
                ],
                [
                    'value' => 'minor_only',
                    'label' => __( 'Minor Updates Only', 'admin-clean-up' ),
                    'description' => __( 'E.g., 6.4.1 to 6.4.2', 'admin-clean-up' ),
                ],
                [
                    'value' => 'all_updates',
                    'label' => __( 'All Updates', 'admin-clean-up' ),
                    'description' => __( 'Major + minor (e.g., 6.4 to 6.5)', 'admin-clean-up' ),
                ],
                [
                    'value' => 'disable_all',
                    'label' => __( 'Disable All Core Updates', 'admin-clean-up' ),
                ],
            ],
        ] );
        $content1 = ob_get_clean();

        WP_Clean_Up_Components::render_card( [
            'title'       => __( 'WordPress Core Updates', 'admin-clean-up' ),
            'description' => __( 'Control automatic update behavior for WordPress core.', 'admin-clean-up' ),
            'content'     => $content1,
        ] );

        // Card 2: Auto-Update Controls (toggles)
        ob_start();
        WP_Clean_Up_Components::render_setting_group( [
            'settings' => [
                [
                    'name'        => WP_Clean_Up::OPTION_KEY . '[updates][disable_plugin_updates]',
                    'checked'     => ! empty( $updates['disable_plugin_updates'] ),
                    'label'       => __( 'Disable Plugin Auto-Updates', 'admin-clean-up' ),
                    'description' => __( 'Prevents plugins from updating automatically. Manual updates still available.', 'admin-clean-up' ),
                ],
                [
                    'name'        => WP_Clean_Up::OPTION_KEY . '[updates][disable_theme_updates]',
                    'checked'     => ! empty( $updates['disable_theme_updates'] ),
                    'label'       => __( 'Disable Theme Auto-Updates', 'admin-clean-up' ),
                    'description' => __( 'Prevents themes from updating automatically. Manual updates still available.', 'admin-clean-up' ),
                ],
                [
                    'name'        => WP_Clean_Up::OPTION_KEY . '[updates][disable_update_emails]',
                    'checked'     => ! empty( $updates['disable_update_emails'] ),
                    'label'       => __( 'Disable Update Emails', 'admin-clean-up' ),
                    'description' => __( 'Stops WordPress from sending emails about automatic updates.', 'admin-clean-up' ),
                ],
                [
                    'name'        => WP_Clean_Up::OPTION_KEY . '[updates][hide_update_nags]',
                    'checked'     => ! empty( $updates['hide_update_nags'] ),
                    'label'       => __( 'Hide Update Nags', 'admin-clean-up' ),
                    'description' => __( 'Hides "WordPress X.X is available!" messages and update count badges.', 'admin-clean-up' ),
                ],
            ],
        ] );
        $content2 = ob_get_clean();

        WP_Clean_Up_Components::render_card( [
            'title'       => __( 'Auto-Update Controls', 'admin-clean-up' ),
            'content'     => $content2,
        ] );
    }
}
