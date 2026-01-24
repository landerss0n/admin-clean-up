<?php
/**
 * Admin Menus Class
 *
 * Handles admin menu cleanup based on user roles
 *
 * @package WP_Clean_Up
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Clean_Up_Admin_Menus {

    /**
     * Plugin options
     */
    private $options;

    /**
     * Menu page slugs mapping
     */
    private $menu_slugs = [
        'posts'      => 'edit.php',
        'media'      => 'upload.php',
        'pages'      => 'edit.php?post_type=page',
        'appearance' => 'themes.php',
        'plugins'    => 'plugins.php',
        'users'      => 'users.php',
        'tools'      => 'tools.php',
        'settings'   => 'options-general.php',
    ];

    /**
     * Constructor
     */
    public function __construct() {
        $this->options = WP_Clean_Up::get_options();
        $menus_options = isset( $this->options['menus'] ) ? $this->options['menus'] : [];

        // Check if any menu is set to be removed
        $has_menus_to_remove = false;
        foreach ( array_keys( $this->menu_slugs ) as $key ) {
            if ( ! empty( $menus_options[ 'remove_' . $key ] ) ) {
                $has_menus_to_remove = true;
                break;
            }
        }

        if ( $has_menus_to_remove ) {
            add_action( 'admin_menu', [ $this, 'remove_menu_items' ], 999 );
        }
    }

    /**
     * Check if menu should be hidden for current user
     *
     * @param string $hide_for The role setting (all, non_admin, non_editor)
     * @return bool
     */
    private function should_hide_for_current_user( $hide_for ) {
        // If not set or empty, default to non_admin
        if ( empty( $hide_for ) ) {
            $hide_for = 'non_admin';
        }

        switch ( $hide_for ) {
            case 'all':
                // Hide for everyone
                return true;

            case 'non_admin':
                // Hide for everyone except administrators
                return ! current_user_can( 'administrator' );

            case 'non_editor':
                // Hide for everyone except administrators and editors
                return ! current_user_can( 'administrator' ) && ! current_user_can( 'editor' );

            default:
                return false;
        }
    }

    /**
     * Remove menu items based on settings and user role
     */
    public function remove_menu_items() {
        $menus_options = isset( $this->options['menus'] ) ? $this->options['menus'] : [];

        foreach ( $this->menu_slugs as $key => $slug ) {
            // Check if this menu should be removed
            if ( ! empty( $menus_options[ 'remove_' . $key ] ) ) {
                // Get the role setting for this menu
                $hide_for = isset( $menus_options[ 'remove_' . $key . '_for' ] ) ? $menus_options[ 'remove_' . $key . '_for' ] : 'non_admin';

                // Check if current user should have this menu hidden
                if ( $this->should_hide_for_current_user( $hide_for ) ) {
                    remove_menu_page( $slug );
                }
            }
        }
    }
}
