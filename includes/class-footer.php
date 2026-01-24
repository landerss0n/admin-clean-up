<?php
/**
 * Footer Class
 *
 * Handles admin footer text customization
 *
 * @package WP_Clean_Up
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Clean_Up_Footer {

    /**
     * Plugin options
     */
    private $options;

    /**
     * Constructor
     */
    public function __construct() {
        $this->options = WP_Clean_Up::get_options();
        $footer_options = isset( $this->options['footer'] ) ? $this->options['footer'] : [];

        // Remove footer text
        if ( ! empty( $footer_options['remove_footer_text'] ) ) {
            add_filter( 'admin_footer_text', '__return_empty_string', 999 );
        } elseif ( ! empty( $footer_options['custom_footer_text'] ) ) {
            add_filter( 'admin_footer_text', [ $this, 'custom_footer_text' ], 999 );
        }

        // Remove version number
        if ( ! empty( $footer_options['remove_version'] ) ) {
            add_filter( 'update_footer', '__return_empty_string', 999 );
        } elseif ( ! empty( $footer_options['custom_version_text'] ) ) {
            add_filter( 'update_footer', [ $this, 'custom_version_text' ], 999 );
        }
    }

    /**
     * Return custom footer text
     *
     * @return string Custom footer text
     */
    public function custom_footer_text() {
        $footer_options = isset( $this->options['footer'] ) ? $this->options['footer'] : [];
        return wp_kses_post( $footer_options['custom_footer_text'] );
    }

    /**
     * Return custom version text
     *
     * @return string Custom version text
     */
    public function custom_version_text() {
        $footer_options = isset( $this->options['footer'] ) ? $this->options['footer'] : [];
        return esc_html( $footer_options['custom_version_text'] );
    }
}
