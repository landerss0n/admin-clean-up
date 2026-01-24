<?php
/**
 * Admin Bar Class
 *
 * Handles removal of admin bar items
 *
 * @package WP_Clean_Up
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Clean_Up_Admin_Bar {

    /**
     * Plugin options
     */
    private $options;

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'wp_before_admin_bar_render', [ $this, 'remove_admin_bar_items' ] );
    }

    /**
     * Remove admin bar items based on settings
     */
    public function remove_admin_bar_items() {
        global $wp_admin_bar;

        $this->options = WP_Clean_Up::get_options();
        $adminbar_options = isset( $this->options['adminbar'] ) ? $this->options['adminbar'] : [];

        // Remove WordPress logo
        if ( ! empty( $adminbar_options['remove_wp_logo'] ) ) {
            $wp_admin_bar->remove_node( 'wp-logo' );
        }

        // Remove site name submenus (keep main link)
        if ( ! empty( $adminbar_options['remove_site_menu'] ) ) {
            $this->remove_site_submenus( $wp_admin_bar );
        }

        // Remove "New" content menu
        if ( ! empty( $adminbar_options['remove_new_content'] ) ) {
            $wp_admin_bar->remove_node( 'new-content' );
        }

        // Remove search
        if ( ! empty( $adminbar_options['remove_search'] ) ) {
            $wp_admin_bar->remove_node( 'search' );
        }

        // Remove "Howdy, username" on frontend only
        if ( ! empty( $adminbar_options['remove_howdy_frontend'] ) && ! is_admin() ) {
            $wp_admin_bar->remove_node( 'my-account' );
        }
    }

    /**
     * Remove submenus under site name while keeping the main link
     *
     * @param WP_Admin_Bar $wp_admin_bar The admin bar instance
     */
    private function remove_site_submenus( $wp_admin_bar ) {
        // Get all nodes and remove any that have 'site-name' as parent
        $nodes = $wp_admin_bar->get_nodes();

        if ( ! empty( $nodes ) ) {
            foreach ( $nodes as $node ) {
                if ( isset( $node->parent ) && 'site-name' === $node->parent ) {
                    $wp_admin_bar->remove_node( $node->id );
                }
            }
        }
    }

}
