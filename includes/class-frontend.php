<?php
/**
 * Frontend cleanup functionality
 *
 * @package Admin_Clean_Up
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP_Clean_Up_Frontend class
 */
class WP_Clean_Up_Frontend {

	/**
	 * Constructor
	 */
	public function __construct() {
		$options  = WP_Clean_Up::get_options();
		$frontend = isset( $options['frontend'] ) ? $options['frontend'] : [];

		// Hide jQuery Migrate console message
		if ( ! empty( $frontend['hide_jquery_migrate_notice'] ) ) {
			add_action( 'wp_default_scripts', [ $this, 'hide_jquery_migrate_notice' ] );
		}
	}

	/**
	 * Hide jQuery Migrate console notice
	 *
	 * @param WP_Scripts $scripts WordPress scripts object.
	 */
	public function hide_jquery_migrate_notice( $scripts ) {
		if ( isset( $scripts->registered['jquery-migrate'] ) ) {
			// Temporarily mute console.log before jQuery Migrate runs
			$scripts->registered['jquery-migrate']->extra['before'][] =
				'var acu_original_console_log = console.log;' .
				'console.log = function(msg) {' .
				'  if (typeof msg === "string" && msg.indexOf("JQMIGRATE") === 0) return;' .
				'  acu_original_console_log.apply(console, arguments);' .
				'};';
			// Restore console.log and mute future warnings
			$scripts->registered['jquery-migrate']->extra['after'][] =
				'console.log = acu_original_console_log;' .
				'jQuery.migrateMute = true;';
		}
	}
}
