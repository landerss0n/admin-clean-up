<?php
/**
 * Comments Class
 *
 * Handles disabling comments site-wide
 *
 * @package WP_Clean_Up
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Clean_Up_Comments {

    /**
     * Constructor
     */
    public function __construct() {
        $options = WP_Clean_Up::get_options();

        if ( ! empty( $options['comments']['disable_comments'] ) ) {
            $this->disable_comments();
        }
    }

    /**
     * Disable comments completely
     */
    private function disable_comments() {
        // Disable support for comments and trackbacks in post types
        add_action( 'admin_init', [ $this, 'disable_comments_post_types_support' ] );

        // Close comments on the front-end
        add_filter( 'comments_open', '__return_false', 20, 2 );
        add_filter( 'pings_open', '__return_false', 20, 2 );

        // Hide existing comments
        add_filter( 'comments_array', '__return_empty_array', 10, 2 );

        // Return 0 for comment count
        add_filter( 'get_comments_number', '__return_zero' );

        // Remove comments page in menu
        add_action( 'admin_menu', [ $this, 'remove_comments_admin_menu' ] );

        // Remove comments links from admin bar
        add_action( 'wp_before_admin_bar_render', [ $this, 'remove_comments_admin_bar' ] );

        // Remove comments feed links from head
        add_filter( 'feed_links_show_comments_feed', '__return_false' );

        // Disable comment feeds entirely
        add_action( 'template_redirect', [ $this, 'disable_comment_feeds' ] );

        // Remove X-Pingback header
        add_filter( 'wp_headers', [ $this, 'remove_pingback_header' ] );

        // Disable all xmlrpc comment methods
        add_filter( 'xmlrpc_methods', [ $this, 'disable_xmlrpc_comments' ] );

        // Redirect comments page and discussion settings in admin
        add_action( 'admin_init', [ $this, 'redirect_comments_admin_pages' ] );

        // Remove comments metabox from dashboard
        add_action( 'admin_init', [ $this, 'remove_dashboard_comments_metabox' ] );

        // Remove recent comments from dashboard
        add_action( 'admin_init', [ $this, 'remove_recent_comments_widget' ] );

        // Hide comments column in posts list
        add_filter( 'manage_posts_columns', [ $this, 'remove_comments_column' ] );
        add_filter( 'manage_pages_columns', [ $this, 'remove_comments_column' ] );

        // Remove discussion meta box from classic editor
        add_action( 'admin_menu', [ $this, 'remove_discussion_meta_box' ] );

        // Remove Discussion settings page from Settings menu
        add_action( 'admin_menu', [ $this, 'remove_discussion_menu' ], 999 );

        // Disable comments in REST API
        add_filter( 'rest_endpoints', [ $this, 'disable_comments_rest_api' ] );

        // Remove comment reply script
        add_action( 'wp_enqueue_scripts', [ $this, 'remove_comment_reply_script' ] );

        // Hide quick edit comments options
        add_action( 'admin_head', [ $this, 'hide_quick_edit_comments' ] );

        // Disable Gutenberg discussion panel
        add_action( 'enqueue_block_editor_assets', [ $this, 'disable_gutenberg_discussion_panel' ] );

        // Remove comment form from frontend
        add_filter( 'comments_template', [ $this, 'disable_comments_template' ] );

        // Disable comment-related rewrite rules
        add_filter( 'rewrite_rules_array', [ $this, 'remove_comment_rewrite_rules' ] );

        // Remove comments from admin bar "New" menu
        add_action( 'admin_bar_menu', [ $this, 'remove_comments_from_new_menu' ], 999 );
    }

    /**
     * Disable comments support for all post types
     */
    public function disable_comments_post_types_support() {
        $post_types = get_post_types();

        foreach ( $post_types as $post_type ) {
            if ( post_type_supports( $post_type, 'comments' ) ) {
                remove_post_type_support( $post_type, 'comments' );
                remove_post_type_support( $post_type, 'trackbacks' );
            }
        }
    }

    /**
     * Remove comments from admin menu
     */
    public function remove_comments_admin_menu() {
        remove_menu_page( 'edit-comments.php' );
    }

    /**
     * Remove Discussion settings page from Settings menu
     */
    public function remove_discussion_menu() {
        remove_submenu_page( 'options-general.php', 'options-discussion.php' );
    }

    /**
     * Remove comments from admin bar
     */
    public function remove_comments_admin_bar() {
        global $wp_admin_bar;
        $wp_admin_bar->remove_node( 'comments' );
    }

    /**
     * Remove comments option from admin bar "New" menu
     *
     * @param WP_Admin_Bar $wp_admin_bar Admin bar instance
     */
    public function remove_comments_from_new_menu( $wp_admin_bar ) {
        $wp_admin_bar->remove_node( 'new-comment' );
    }

    /**
     * Remove X-Pingback header
     *
     * @param array $headers HTTP headers
     * @return array Modified headers
     */
    public function remove_pingback_header( $headers ) {
        unset( $headers['X-Pingback'] );
        return $headers;
    }

    /**
     * Disable all xmlrpc comment and pingback methods
     *
     * @param array $methods XMLRPC methods
     * @return array Modified methods
     */
    public function disable_xmlrpc_comments( $methods ) {
        // Pingback methods
        unset( $methods['pingback.ping'] );
        unset( $methods['pingback.extensions.getPingbacks'] );

        // Comment methods
        unset( $methods['wp.getCommentCount'] );
        unset( $methods['wp.getComment'] );
        unset( $methods['wp.getComments'] );
        unset( $methods['wp.newComment'] );
        unset( $methods['wp.editComment'] );
        unset( $methods['wp.deleteComment'] );
        unset( $methods['wp.getCommentStatusList'] );

        return $methods;
    }

    /**
     * Redirect comments admin page and discussion settings to dashboard
     */
    public function redirect_comments_admin_pages() {
        global $pagenow;

        // Redirect comments page
        if ( 'edit-comments.php' === $pagenow ) {
            wp_safe_redirect( admin_url() );
            exit;
        }

        // Redirect discussion settings page
        if ( 'options-discussion.php' === $pagenow ) {
            wp_safe_redirect( admin_url() );
            exit;
        }
    }

    /**
     * Disable comment feeds
     */
    public function disable_comment_feeds() {
        if ( is_comment_feed() ) {
            wp_safe_redirect( home_url() );
            exit;
        }
    }

    /**
     * Remove comments metabox from dashboard
     */
    public function remove_dashboard_comments_metabox() {
        remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
    }

    /**
     * Remove recent comments widget
     */
    public function remove_recent_comments_widget() {
        unregister_widget( 'WP_Widget_Recent_Comments' );
    }

    /**
     * Remove comments column from posts/pages list
     *
     * @param array $columns Table columns
     * @return array Modified columns
     */
    public function remove_comments_column( $columns ) {
        unset( $columns['comments'] );
        return $columns;
    }

    /**
     * Remove discussion meta box from classic editor
     */
    public function remove_discussion_meta_box() {
        $post_types = get_post_types();

        foreach ( $post_types as $post_type ) {
            remove_meta_box( 'commentstatusdiv', $post_type, 'normal' );
            remove_meta_box( 'commentsdiv', $post_type, 'normal' );
            remove_meta_box( 'trackbacksdiv', $post_type, 'normal' );
        }
    }

    /**
     * Disable comments endpoints in REST API
     *
     * @param array $endpoints REST API endpoints
     * @return array Modified endpoints
     */
    public function disable_comments_rest_api( $endpoints ) {
        if ( isset( $endpoints['/wp/v2/comments'] ) ) {
            unset( $endpoints['/wp/v2/comments'] );
        }
        if ( isset( $endpoints['/wp/v2/comments/(?P<id>[\d]+)'] ) ) {
            unset( $endpoints['/wp/v2/comments/(?P<id>[\d]+)'] );
        }
        return $endpoints;
    }

    /**
     * Remove comment reply JavaScript
     */
    public function remove_comment_reply_script() {
        wp_deregister_script( 'comment-reply' );
    }

    /**
     * Hide quick edit comments options with CSS
     */
    public function hide_quick_edit_comments() {
        echo '<style>
            .inline-edit-row .inline-edit-col-right .inline-edit-group:has([name="comment_status"]),
            .inline-edit-row .inline-edit-col-right .inline-edit-group:has([name="ping_status"]),
            .inline-edit-row fieldset.inline-edit-col-right label:has([name="comment_status"]),
            .inline-edit-row fieldset.inline-edit-col-right label:has([name="ping_status"]) {
                display: none !important;
            }
        </style>';
    }

    /**
     * Disable Gutenberg discussion panel
     */
    public function disable_gutenberg_discussion_panel() {
        wp_add_inline_script(
            'wp-edit-post',
            "wp.domReady(function() {
                if (wp.data && wp.data.dispatch) {
                    var editor = wp.data.dispatch('core/editor');
                    if (editor && editor.removeEditorPanel) {
                        wp.data.dispatch('core/editor').removeEditorPanel('discussion-panel');
                    }
                    var editPost = wp.data.dispatch('core/edit-post');
                    if (editPost && editPost.removeEditorPanel) {
                        wp.data.dispatch('core/edit-post').removeEditorPanel('discussion-panel');
                    }
                }
            });"
        );
    }

    /**
     * Return empty comments template
     *
     * @param string $template Comments template path
     * @return string Empty template or original
     */
    public function disable_comments_template( $template ) {
        // Return the plugin's empty template if it exists, or just close comments
        return dirname( __DIR__ ) . '/templates/empty-comments.php';
    }

    /**
     * Remove comment-related rewrite rules
     *
     * @param array $rules Rewrite rules
     * @return array Modified rules
     */
    public function remove_comment_rewrite_rules( $rules ) {
        foreach ( $rules as $rule => $rewrite ) {
            if ( preg_match( '/comment|trackback|pingback/i', $rule ) ) {
                unset( $rules[ $rule ] );
            }
        }
        return $rules;
    }
}
