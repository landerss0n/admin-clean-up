<?php
/**
 * Plugin Notices Class
 *
 * Handles hiding of annoying notices from specific plugins
 *
 * @package WP_Clean_Up
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Clean_Up_Plugin_Notices {

    /**
     * Plugin options
     */
    private $options;

    /**
     * Constructor
     */
    public function __construct() {
        $this->options = WP_Clean_Up::get_options();
        $plugins_options = isset( $this->options['plugins'] ) ? $this->options['plugins'] : [];

        // Hide PixelYourSite notices (only if Free is active and Pro is NOT active)
        if ( ! empty( $plugins_options['hide_pixelyoursite_notices'] )
            && $this->is_plugin_active( 'pixelyoursite/facebook-pixel-master.php' )
            && ! $this->is_plugin_active( 'pixelyoursite-pro/pixelyoursite-pro.php' ) ) {
            add_action( 'admin_head', [ $this, 'hide_pixelyoursite_notices' ] );
        }

        // Clean up Elementor admin
        if ( ! empty( $plugins_options['hide_elementor_notices'] )
            && $this->is_plugin_active( 'elementor/elementor.php' ) ) {
            add_action( 'wp_dashboard_setup', [ $this, 'remove_elementor_dashboard_widget' ], 40 );
            add_action( 'admin_head', [ $this, 'hide_elementor_admin_clutter' ] );
            add_action( 'admin_menu', [ $this, 'clean_elementor_admin_menu' ], 999 );
            add_action( 'admin_enqueue_scripts', [ $this, 'override_elementor_sidebar_upgrade' ], 999 );
            add_action( 'admin_init', [ $this, 'block_elementor_redirect' ], 1 );
            add_action( 'admin_init', [ $this, 'redirect_elementor_home_to_settings' ] );
            add_filter( 'elementor/admin-top-bar/is-active', '__return_false' );
            add_action( 'elementor/editor/after_enqueue_styles', [ $this, 'hide_elementor_editor_clutter' ] );
            add_action( 'elementor/editor/after_enqueue_scripts', [ $this, 'disable_elementor_ai' ], 999 );
            add_action( 'elementor/editor/v2/scripts/enqueue', [ $this, 'disable_elementor_ai' ], 999 );

            // Hide pro widget promotions
            add_filter( 'elementor/editor/panel/get_pro_details', '__return_empty_array' );
            add_filter( 'elementor/editor/panel/get_pro_details-sticky', '__return_empty_array' );

            // Disable generator meta tag
            add_filter( 'pre_option_elementor_meta_generator_tag', function () { return '1'; } );

            // Disable data sharing / usage tracking
            add_filter( 'pre_option_elementor_allow_tracking', function () { return 'no'; } );

            // Remove "Get Elementor Pro" plugin action link
            add_filter( 'plugin_action_links_elementor/elementor.php', [ $this, 'remove_elementor_go_pro_link' ], 999 );

            // Disable deactivation feedback dialog
            add_action( 'admin_enqueue_scripts', [ $this, 'disable_elementor_feedback' ], 999 );
        }

        // Remove Complianz HTML comments from frontend (supports both free and premium)
        if ( ! empty( $plugins_options['hide_complianz_comments'] )
            && ( $this->is_plugin_active( 'complianz-gdpr/complianz-gpdr.php' )
                || $this->is_plugin_active( 'complianz-gdpr-premium/complianz-gpdr-premium.php' ) ) ) {
            // Use Complianz's own filter (more efficient than full output buffering)
            add_filter( 'cmplz_cookie_blocker_output', [ $this, 'remove_complianz_comments' ] );
            // Fallback: catch any comments outside cookie blocker output
            add_filter( 'cmplz_banner_html', [ $this, 'remove_complianz_comments' ] );
        }

        // Remove GTM4WP HTML comments from frontend
        if ( ! empty( $plugins_options['hide_gtm4wp_comments'] )
            && $this->is_plugin_active( 'duracelltomi-google-tag-manager/duracelltomi-google-tag-manager-for-wordpress.php' ) ) {
            // Use output buffering with callback - captures and filters all output
            add_action( 'template_redirect', [ $this, 'start_gtm4wp_output_buffer' ] );
        }

        // Clean up WooCommerce
        if ( ! empty( $plugins_options['hide_woocommerce_clutter'] )
            && $this->is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
            // Remove generator meta tag
            remove_filter( 'get_the_generator_html', 'wc_generator_tag', 10 );
            remove_filter( 'get_the_generator_xhtml', 'wc_generator_tag', 10 );

            // Disable usage tracking
            add_filter( 'woocommerce_allow_tracking', '__return_false' );

            // Remove HTML comments from frontend
            add_action( 'template_redirect', [ $this, 'start_woocommerce_output_buffer' ] );
        }

        // Clean up Yoast SEO admin
        if ( ! empty( $plugins_options['hide_yoast_notices'] )
            && $this->is_plugin_active( 'wordpress-seo/wp-seo.php' ) ) {
            add_action( 'wp_dashboard_setup', [ $this, 'remove_yoast_dashboard_widget' ], 40 );
            add_action( 'admin_head', [ $this, 'hide_yoast_admin_clutter' ] );
            add_action( 'admin_menu', [ $this, 'clean_yoast_admin_menu' ], 999 );
            add_action( 'admin_init', [ $this, 'block_yoast_redirect' ], 1 );
            add_action( 'wp_before_admin_bar_render', [ $this, 'remove_yoast_admin_bar' ] );

            // Disable usage tracking
            add_filter( 'wpseo_enable_tracking', '__return_false' );

            // Remove Yoast HTML comments from frontend
            add_filter( 'wpseo_debug_markers', '__return_false' );

            // Remove premium upsell submenus via filter
            add_filter( 'wpseo_submenu_pages', [ $this, 'filter_yoast_submenu_pages' ], 9999 );

            // Block promotional alerts from being registered
            add_filter( 'wpseo_allowed_dismissable_alerts', '__return_empty_array' );

            // Override Yoast React dashboard data (hide upsells and alerts)
            add_action( 'admin_enqueue_scripts', [ $this, 'override_yoast_dashboard_data' ], 9999 );

            // Dismiss promotional notifications on page load
            add_action( 'admin_init', [ $this, 'dismiss_yoast_promotional_notifications' ], 20 );

            // Hide Yoast upsells in Elementor editor
            add_action( 'elementor/editor/after_enqueue_styles', [ $this, 'hide_yoast_elementor_upsells' ] );

            // Remove Yoast admin columns and filters
            add_action( 'admin_init', [ $this, 'remove_yoast_admin_columns_and_filters' ] );
        }
    }

    /**
     * Check if a plugin is active
     *
     * @param string $plugin Plugin path (folder/file.php)
     * @return bool
     */
    private function is_plugin_active( $plugin ) {
        if ( ! function_exists( 'is_plugin_active' ) ) {
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        return is_plugin_active( $plugin );
    }

    /**
     * Hide PixelYourSite promotional notices via CSS
     */
    public function hide_pixelyoursite_notices() {
        ?>
        <style>
            /* Hide PixelYourSite opt-in/email signup notices */
            .pys-optin-notice,
            .pys-notice-wrapper,
            /* Hide PixelYourSite fixed notices (tips, videos, etc.) */
            .pys-fixed-notice,
            .pys-chain-fixed-notice,
            .pys-promo-fixed-notice,
            /* Hide PixelYourSite promo notices */
            .notice.pys-promo-notice,
            /* Hide red "PixelYourSite Tip" notices (CAPI, GA4, etc.) */
            .notice.notice-error[class*="pys_"],
            .pys_core_CAPI_notice,
            .pys_facebook_CAPI_notice,
            .pys_ga_UA_notice,
            /* Hide expiration notices */
            div[class*="pys_"][class*="_expiration_notice"] {
                display: none !important;
            }

            /* PixelYourSite plugin page promotional elements */
            /* Hide blue "Upgrade to PRO" box in sidebar */
            #pys .upgrade-card,
            .wrap#pys .upgrade-card,
            .card-blue.upgrade-card,
            /* Hide orange "ConsentMagic" promo box in sidebar */
            #pys .card-orange,
            .card-orange,
            /* Hide "Subscribe to YouTube" section */
            #pys .link_youtube,
            .link_youtube,
            /* Hide "Upgrade to PRO version" link in footer */
            #pys .bottom-upgrade,
            .bottom-upgrade,
            .bottom-upgrade-link,
            /* Hide "5 stars review" link in footer */
            #pys .save-settings .video-link,
            .save-settings .video-link,
            /* Hide "PRO Feature" and "Purchase Addon" badges */
            #pys .badge-pro,
            .badge-pro,
            .badge-pill.badge-pro {
                display: none !important;
            }

            /* Hide addon promo boxes in sidebar (WooCommerce Product Catalog, Super Pack, Cost of Goods, etc.) */
            #pys .sidebar-col .item-wrap:has(a[href*="product-catalog-facebook"]),
            #pys .sidebar-col .item-wrap:has(a[href*="easy-digital-downloads-product-catalog"]),
            #pys .sidebar-col .item-wrap:has(a[href*="super-pack"]),
            #pys .sidebar-col .item-wrap:has(a[href*="woocommerce-cost-of-goods"]),
            #pys .sidebar-col .item-wrap:has(a[href*="value-based-facebook-lookalike"]),
            /* Hide "Queue Settings PRO" submenu item */
            #adminmenu a[href*="page=pixelyoursite_queue"],
            #adminmenu li a[href*="pixelyoursite_queue"] {
                display: none !important;
            }

            /* Hide pro addon promo sections in main content (Google Ads, TikTok, Pinterest, Bing, Reddit) */
            #pys .pixel-wrap:has(a[href*="google-ads-tag"]),
            #pys .pixel-wrap:has(a[href*="tiktok-tag"]),
            #pys .pixel-wrap:has(a[href*="pinterest-tag"]),
            #pys .pixel-wrap:has(a[href*="bing-tag"]),
            #pys .pixel-wrap:has(a[href*="reddit-wordpress-plugin"]),
            /* Hide the line separator before these sections */
            #pys .line:has(+ .pixel-wrap a[href*="google-ads-tag"]),
            #pys .line:has(+ .pixel-wrap a[href*="tiktok-tag"]),
            #pys .line:has(+ .pixel-wrap a[href*="pinterest-tag"]),
            #pys .line:has(+ .pixel-wrap a[href*="bing-tag"]),
            #pys .line:has(+ .pixel-wrap a[href*="reddit-wordpress-plugin"]) {
                display: none !important;
            }
        </style>
        <?php
    }

    /**
     * Remove Elementor dashboard widget
     */
    public function remove_elementor_dashboard_widget() {
        remove_meta_box( 'e-dashboard-overview', 'dashboard', 'normal' );
    }

    /**
     * Hide Elementor promotional elements via CSS
     */
    public function hide_elementor_admin_clutter() {
        // Remove "Uppgradera" submenu (added by Elementor at PHP_INT_MAX priority, so we remove in admin_head).
        remove_submenu_page( 'elementor-home', 'elementor-one-upgrade' );
        ?>
        <style>
            /* Hide Elementor admin notices */
            .e-notice,
            .e-notice--dismissible,
            .notice[data-notice_id*="elementor"],
            #message[data-notice_id*="elementor"],
            .elementor-message,
            .elementor-notice,
            /* Hide old Elementor top bar promotional elements */
            .e-admin-top-bar__bar-button.accent,
            .e-admin-top-bar__bar-button:has(.crown-icon),
            .e-admin-top-bar__bar-button:has(.eicon-speakerphone),
            .e-admin-top-bar__bar-button:has(.eicon-user-circle-o),
            /* Hide editor-one top bar: What's New (notification badge) */
            #editor-one-top-bar .MuiBadge-root,
            #editor-one-top-bar button:has(.MuiBadge-badge),
            #elementor-home-app-top-bar .MuiBadge-root,
            #elementor-home-app-top-bar button:has(.MuiBadge-badge),
            /* Hide editor-one top bar: Help button */
            #editor-one-top-bar a[href*="elementor.com/help"],
            #elementor-home-app-top-bar a[href*="elementor.com/help"],
            /* Hide editor-one top bar: Upgrade / Go Pro buttons */
            #editor-one-top-bar a[href*="go.elementor.com"],
            #elementor-home-app-top-bar a[href*="go.elementor.com"],
            /* Hide "Uppgradera" in WP admin sidebar */
            #adminmenu a[href*="elementor-one-upgrade"],
            #adminmenu li a[href*="go.elementor.com/go-pro"],
            /* Hide "Uppgradera paket" in Elementor internal sidebar */
            .e-sidebar-upgrade,
            a[href*="go.elementor.com/go-pro-upgrade"][class*="upgrade"] {
                display: none !important;
            }
        </style>
        <?php
    }

    /**
     * Clean up Elementor admin menu items
     */
    public function clean_elementor_admin_menu() {
        // Note: We keep "Hem" (Home) submenu - removing it breaks the parent menu link
        // with Elementor Pro (goes to Theme Builder instead). The redirect_elementor_home_to_settings()
        // function handles redirecting users to Settings when they click on Home.

        // Remove "Snabbstart" (Quick start / Editor) submenu
        remove_submenu_page( 'elementor-home', 'elementor' );

        // Remove "Get Help" submenu
        remove_submenu_page( 'elementor', 'go_knowledge_base_site' );
        remove_submenu_page( 'elementor-home', 'go_knowledge_base_site' );
    }

    /**
     * Block Elementor activation redirect to onboarding
     */
    public function block_elementor_redirect() {
        delete_transient( 'elementor_activation_redirect' );
    }

    /**
     * Redirect Elementor Home page to Settings page
     */
    public function redirect_elementor_home_to_settings() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only check of page parameter for redirect.
        if ( isset( $_GET['page'] ) && in_array( $_GET['page'], [ 'elementor', 'elementor-home' ], true ) ) {
            wp_safe_redirect( admin_url( 'admin.php?page=elementor-settings' ) );
            exit;
        }
    }

    /**
     * Hide clutter in the Elementor editor
     */
    public function hide_elementor_editor_clutter() {
        $css = '
            /* Hide Pro widget panel promotions */
            #elementor-panel-get-pro-elements,
            #elementor-panel-get-pro-elements-sticky,
            .elementor-panel-heading-promotion,
            .elementor-nerd-box,
            /* Hide Pro category and upgrade crown */
            #elementor-panel-category-pro-elements,
            .eicon-upgrade-crown,
            /* Hide "Go Pro" buttons in editor */
            .e-promotion-go-pro,
            .elementor-clickable.e-promotion-go-pro,
            /* Hide promotion controls (switchers with lock) */
            .e-control-promotion__wrapper,
            .e-promotion-react-wrapper,
            /* Hide AI buttons and controls */
            .e-ai-button,
            .e-btn--ai,
            [class*="elementor-control-ai_"],
            [class*="elementor-control-ai-"],
            /* Hide promotional hints/notices in widget panels (Ally, etc.) */
            .elementor-control-notice,
            /* Hide What\'s New (speakerphone) in app bar */
            #app-bar-menu-item-whats-new,
            [data-testid="app-bar-menu-item-whats-new"],
            .e-has-notification .eicon-speakerphone,
            /* Hide notification badge in panel menu */
            #elementor-panel-header-menu-button .eicon-speakerphone {
                display: none !important;
            }
        ';

        wp_add_inline_style( 'elementor-editor', $css );
    }

    /**
     * Override Elementor sidebar config to hide upgrade button
     */
    public function override_elementor_sidebar_upgrade() {
        $js  = 'if(typeof editorOneSidebarConfig!=="undefined"){';
        $js .= 'editorOneSidebarConfig.hasPro=true;';
        $js .= 'if(editorOneSidebarConfig.menuItems){';
        $js .= 'editorOneSidebarConfig.menuItems=editorOneSidebarConfig.menuItems.filter(function(item){return item.slug!=="elementor";});';
        $js .= '}}';
        wp_add_inline_script( 'editor-one-sidebar-navigation', $js, 'before' );
    }

    /**
     * Disable Elementor AI and notifications in the editor
     */
    public function disable_elementor_ai() {
        // Remove AI
        wp_dequeue_script( 'elementor-ai' );
        wp_deregister_script( 'elementor-ai' );
        wp_register_script( 'elementor-ai', false, array(), false, true ); // Prevent re-enqueue.
        wp_dequeue_style( 'elementor-ai-editor' );
        wp_dequeue_style( 'elementor-ai-layout-preview' );

        // Remove What's New / notifications
        wp_dequeue_script( 'e-editor-notifications' );
        wp_deregister_script( 'e-editor-notifications' );
        wp_register_script( 'e-editor-notifications', false, array(), false, true ); // Prevent re-enqueue.

        // Block What's New from registering in the app bar (patch registerLink after app bar loads).
        $js = 'if(typeof elementorV2!=="undefined"&&elementorV2.editorAppBar&&elementorV2.editorAppBar.utilitiesMenu){';
        $js .= 'var _oRL=elementorV2.editorAppBar.utilitiesMenu.registerLink;';
        $js .= 'elementorV2.editorAppBar.utilitiesMenu.registerLink=function(c){';
        $js .= 'if(c&&c.id==="app-bar-menu-item-whats-new")return;';
        $js .= 'return _oRL.apply(this,arguments);};';
        $js .= '}';
        wp_add_inline_script( 'elementor-v2-editor-app-bar', $js, 'after' );
    }

    /**
     * Remove "Get Elementor Pro" link from plugin action links
     *
     * @param array $links Plugin action links.
     * @return array
     */
    public function remove_elementor_go_pro_link( $links ) {
        unset( $links['go_pro'] );
        return $links;
    }

    /**
     * Disable Elementor deactivation feedback dialog
     */
    public function disable_elementor_feedback() {
        wp_dequeue_script( 'elementor-admin-feedback' );
    }

    /**
     * Remove Yoast SEO dashboard widget (Wincher)
     */
    public function remove_yoast_dashboard_widget() {
        remove_meta_box( 'wpseo-dashboard-overview', 'dashboard', 'normal' );
        remove_meta_box( 'wpseo-wincher-dashboard-overview', 'dashboard', 'normal' );
    }

    /**
     * Hide Yoast SEO promotional elements via CSS
     */
    public function hide_yoast_admin_clutter() {
        ?>
        <style>
            /* Hide Yoast admin notices */
            .yoast-notice,
            .notice[id*="wpseo-"],
            .notice[class*="yoast"],
            .yoast-issue-added,
            /* Hide first-time configuration notice */
            #wpseo-first-time-configuration-notice,
            /* Hide premium deactivated notice */
            .notice.yoast-premium-deactivated,
            /* Hide notification counter in admin menu */
            #toplevel_page_wpseo_dashboard .update-plugins,
            #toplevel_page_wpseo_dashboard .awaiting-mod,
            /* Hide notification counter badge in admin bar */
            #wpadminbar .yoast-issue-counter,
            /* Hide premium badges in submenu items */
            .yoast-badge.yoast-premium-badge,
            /* Hide AI Brand Insights gradient button in submenu */
            .yoast-brand-insights-gradient-border,
            /* Hide help beacon/support widget */
            #yoast-helpscout-beacon,
            .yoast-help-center__button,
            /* Hide premium sidebar and upsell boxes */
            #sidebar-container,
            .yoast_premium_upsell,
            /* Hide webinar promo notification */
            #webinar-promo-notification,
            /* Hide upgrade banner at bottom of Yoast pages */
            .wpseo-premium-upsell,
            [class*="UpsellCard"],
            .yoast-upsell,
            /* Hide promotional elements on Yoast pages */
            .wpseo-tab-video-container,
            .yoast-sidebar__section--buy-premium,
            /* Hide promotional alerts on Yoast pages (keep errors like noindex) */
            .yoast-alert--info,
            .yoast-alert--warning,
            /* Hide alerts inside Yoast React dashboard */
            #yoast-seo-general [class*="alert-"],
            #yoast-seo-general [class*="Alert"],
            /* Hide "Aviseringar" sidebar item on Yoast dashboard */
            #yoast-seo-general nav a[href*="alert"],
            /* Hide promotional alerts in Yoast editor sidebar */
            .yoast-alert,
            .components-panel .yoast-notification,
            [class*="yoast"] .notice,
            .wpseo-metabox-sidebar .yoast-alert,
            #yoast-seo-sidebar .yoast-alert,
            /* Hide upsell buttons in editor and metabox */
            .yoast-button-upsell,
            .yoast-button-upsell__caret,
            /* Hide FeatureUpsell card overlays (social preview, etc.) */
            .yst-feature-upsell--card,
            /* Hide premium upsell sections in Yoast metabox */
            #wpseo-metabox-root [class*="UpsellCard"],
            /* Hide premium lock badges */
            .yst-badge--upsell {
                display: none !important;
            }
            /* Keep error alerts visible (noindex warnings, etc.) */
            .yoast-alert--error {
                display: flex !important;
            }
        </style>
        <?php
    }

    /**
     * Clean up Yoast SEO admin menu items
     */
    public function clean_yoast_admin_menu() {
        // Remove premium-only submenu pages
        remove_submenu_page( 'wpseo_dashboard', 'wpseo_redirects' );
        remove_submenu_page( 'wpseo_dashboard', 'wpseo_workouts' );
        remove_submenu_page( 'wpseo_dashboard', 'wpseo_brand_insights' );

        // Remove Academy, Paket (licenses), Support, and Upgrade submenus
        remove_submenu_page( 'wpseo_dashboard', 'wpseo_page_academy' );
        remove_submenu_page( 'wpseo_dashboard', 'wpseo_licenses' );
        remove_submenu_page( 'wpseo_dashboard', 'wpseo_page_support' );
        remove_submenu_page( 'wpseo_dashboard', 'wpseo_upgrade_sidebar' );
    }

    /**
     * Filter out premium upsell submenu pages from Yoast
     *
     * @param array $submenu_pages The submenu pages.
     * @return array
     */
    public function filter_yoast_submenu_pages( $submenu_pages ) {
        $remove_slugs = [
            'wpseo_redirects',
            'wpseo_workouts',
            'wpseo_brand_insights',
            'wpseo_page_academy',
            'wpseo_licenses',
            'wpseo_page_support',
            'wpseo_upgrade_sidebar',
        ];

        return array_filter( $submenu_pages, function ( $page ) use ( $remove_slugs ) {
            return ! isset( $page[4] ) || ! in_array( $page[4], $remove_slugs, true );
        } );
    }

    /**
     * Block Yoast SEO activation redirect
     */
    public function block_yoast_redirect() {
        if ( ! function_exists( 'YoastSEO' ) ) {
            return;
        }
        $options = get_option( 'wpseo', [] );
        if ( ! empty( $options['should_redirect_after_install_free'] ) ) {
            $options['should_redirect_after_install_free'] = false;
            update_option( 'wpseo', $options );
        }
    }

    /**
     * Dismiss promotional Yoast notifications (keep errors like noindex warnings)
     */
    public function dismiss_yoast_promotional_notifications() {
        if ( ! class_exists( 'Yoast_Notification_Center' ) ) {
            return;
        }
        $center = Yoast_Notification_Center::get();
        $notifications = $center->get_notifications();

        foreach ( $notifications as $notification ) {
            $type = $notification->get_type();
            // Keep error notifications (important ones like noindex warnings)
            if ( 'error' === $type ) {
                continue;
            }
            // Dismiss all non-error notifications (info, warning = promotional)
            $center->remove_notification( $notification );
        }
    }

    /**
     * Override Yoast React data to hide upsells, alerts, and premium locks
     */
    public function override_yoast_dashboard_data() {
        // Set isPremium=true and define wpseoPremiumMetaboxData to disable all upsell gates
        // - isPremium=true: hides isPremium-gated elements in React components
        // - wpseoPremiumMetaboxData: makes shouldUpsell=false (hides locks, upsell panels, related keyphrase)
        $js  = 'if(typeof wpseoScriptData!=="undefined"){';
        $js .= 'if(wpseoScriptData.preferences)wpseoScriptData.preferences.isPremium=true;';
        $js .= 'if(wpseoScriptData.metabox)wpseoScriptData.metabox.isPremium=true;';
        $js .= 'if("alerts" in wpseoScriptData)wpseoScriptData.alerts=[];';
        $js .= 'if("currentPromotions" in wpseoScriptData)wpseoScriptData.currentPromotions=[];';
        $js .= '}';
        $js .= 'window.wpseoPremiumMetaboxData=window.wpseoPremiumMetaboxData||{};';
        wp_add_inline_script( 'yoast-seo-general-page', $js, 'before' );
        wp_add_inline_script( 'yoast-seo-new-settings', $js, 'before' );
        wp_add_inline_script( 'yoast-seo-post-edit', $js, 'before' );
        wp_add_inline_script( 'yoast-seo-post-edit-classic', $js, 'before' );
        wp_add_inline_script( 'yoast-seo-elementor', $js, 'before' );
        wp_add_inline_script( 'yoast-seo-indexation', $js, 'before' );

        // Fix editor sidebar title: isPremium causes "Yoast SEO Premium" title and labels
        $fix  = 'setInterval(function(){';
        $fix .= 'document.querySelectorAll("strong,.components-menu-item__item,.yoast-analysis-check span").forEach(function(el){';
        $fix .= 'if(el.textContent.indexOf("Yoast SEO Premium")!==-1)el.textContent=el.textContent.replace("Yoast SEO Premium","Yoast SEO");';
        $fix .= 'if(el.textContent.indexOf("Premium SEO")!==-1)el.textContent=el.textContent.replace("Premium SEO","SEO");';
        $fix .= '});';
        $fix .= '},500);';
        wp_add_inline_script( 'yoast-seo-post-edit', $fix, 'after' );
        wp_add_inline_script( 'yoast-seo-post-edit-classic', $fix, 'after' );
        wp_add_inline_script( 'yoast-seo-elementor', $fix, 'after' );

        // Add CSS for Elementor editor (social preview upsell has hardcoded shouldUpsell:true)
        $elementor_css  = '.yst-feature-upsell--card,';
        $elementor_css .= '[class*="webinar-promo"],';
        $elementor_css .= '.yoast-notification.yoast-alert--info,';
        $elementor_css .= '.yoast-button-upsell,';
        $elementor_css .= '.yst-badge--upsell{display:none!important;}';
        wp_add_inline_script(
            'yoast-seo-elementor',
            'document.head.insertAdjacentHTML("beforeend","<style>' . $elementor_css . '</style>");',
            'after'
        );
    }

    /**
     * Remove Yoast SEO admin bar menu
     */
    public function remove_yoast_admin_bar() {
        global $wp_admin_bar;
        $wp_admin_bar->remove_node( 'wpseo-menu' );
    }

    /**
     * Hide Yoast upsells in Elementor editor via CSS
     */
    public function hide_yoast_elementor_upsells() {
        $css = '
            /* Hide social preview upsell overlay */
            .yst-feature-upsell--card,
            /* Hide webinar promo notification */
            [class*="webinar-promo"],
            .yoast-notification.yoast-alert--info,
            /* Hide upsell buttons and badges */
            .yoast-button-upsell,
            .yst-badge--upsell {
                display: none !important;
            }
        ';
        wp_add_inline_style( 'yoast-seo-elementor', $css );
    }

    /**
     * Remove Yoast admin columns and filters from post lists
     */
    public function remove_yoast_admin_columns_and_filters() {
        // Remove SEO/Readability columns from all post types
        $post_types = get_post_types( [ 'public' => true ], 'names' );
        foreach ( $post_types as $post_type ) {
            add_filter( 'manage_edit-' . $post_type . '_columns', [ $this, 'remove_yoast_columns' ], 99 );
        }

        // Remove filter dropdowns - use load-edit.php hook to ensure Yoast has registered them
        add_action( 'load-edit.php', [ $this, 'remove_yoast_filter_dropdowns' ] );
    }

    /**
     * Remove Yoast filter dropdowns from post list
     */
    public function remove_yoast_filter_dropdowns() {
        global $wpseo_meta_columns;
        if ( $wpseo_meta_columns && is_object( $wpseo_meta_columns ) ) {
            remove_action( 'restrict_manage_posts', [ $wpseo_meta_columns, 'posts_filter_dropdown' ] );
            remove_action( 'restrict_manage_posts', [ $wpseo_meta_columns, 'posts_filter_dropdown_readability' ] );
        }
    }

    /**
     * Remove Yoast columns from post list
     *
     * @param array $columns The columns array.
     * @return array
     */
    public function remove_yoast_columns( $columns ) {
        unset( $columns['wpseo-score'] );
        unset( $columns['wpseo-score-readability'] );
        unset( $columns['wpseo-title'] );
        unset( $columns['wpseo-metadesc'] );
        unset( $columns['wpseo-focuskw'] );
        unset( $columns['wpseo-links'] );
        unset( $columns['wpseo-linked'] );
        return $columns;
    }

    /**
     * Remove Complianz HTML comments from output
     *
     * Uses Complianz's own filters instead of full output buffering for better performance.
     *
     * @param string $html The HTML output.
     * @return string The filtered HTML.
     */
    public function remove_complianz_comments( $html ) {
        // Remove Complianz HTML comments (case-insensitive)
        // Matches: <!-- Consent Management powered by Complianz ... -->
        // Matches: <!-- End Complianz ... -->
        // Matches: <!-- Complianz ... -->
        // Matches: <!-- Statistics script Complianz GDPR/CCPA -->
        $pattern = '/<!--\s*(?:Consent Management powered by Complianz|End Complianz|Complianz|Statistics script Complianz)[^>]*-->/i';
        return preg_replace( $pattern, '', $html );
    }

    /**
     * Start output buffering for GTM4WP comment removal
     */
    public function start_gtm4wp_output_buffer() {
        ob_start( [ $this, 'filter_gtm4wp_comments' ] );
    }

    /**
     * Filter GTM4WP HTML comments from output
     *
     * @param string $html The HTML output.
     * @return string The filtered HTML.
     */
    public function filter_gtm4wp_comments( $html ) {
        // Remove GTM4WP HTML comments
        // Matches: <!-- Google Tag Manager for WordPress by gtm4wp.com -->
        // Matches: <!-- End Google Tag Manager for WordPress by gtm4wp.com -->
        // Matches: <!-- GTM Container placement set to ... -->
        // Matches: <!-- Google Tag Manager (noscript) -->
        // Matches: <!-- End Google Tag Manager (noscript) -->
        $pattern = '/<!--\s*(?:End )?(?:Google Tag Manager(?: \(noscript\))?(?: for WordPress by gtm4wp\.com)?|GTM Container placement set to[^>]*)\s*-->/i';
        return preg_replace( $pattern, '', $html );
    }

    /**
     * Start output buffering for WooCommerce comment removal
     */
    public function start_woocommerce_output_buffer() {
        ob_start( [ $this, 'filter_woocommerce_comments' ] );
    }

    /**
     * Filter WooCommerce HTML comments from output
     *
     * @param string $html The HTML output.
     * @return string The filtered HTML.
     */
    public function filter_woocommerce_comments( $html ) {
        // Remove WooCommerce HTML comments
        // Matches: <!-- WooCommerce ... -->
        // Matches: <!-- End WooCommerce ... -->
        $pattern = '/<!--\s*(?:End )?WooCommerce[^>]*-->/i';
        return preg_replace( $pattern, '', $html );
    }

}
