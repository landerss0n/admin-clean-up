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
}
