<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Admin_Clean_Up
 */

// Composer autoloader
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// Define WordPress constants if not defined
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', '/tmp/wordpress/' );
}

if ( ! defined( 'ADMIN_CLEAN_UP_PLUGIN_DIR' ) ) {
	define( 'ADMIN_CLEAN_UP_PLUGIN_DIR', dirname( __DIR__ ) . '/' );
}

if ( ! defined( 'ADMIN_CLEAN_UP_PLUGIN_URL' ) ) {
	define( 'ADMIN_CLEAN_UP_PLUGIN_URL', 'http://localhost/wp-content/plugins/admin-clean-up/' );
}

if ( ! defined( 'ADMIN_CLEAN_UP_VERSION' ) ) {
	define( 'ADMIN_CLEAN_UP_VERSION', '1.0.0-test' );
}

// Brain\Monkey setup
use Brain\Monkey;

// PHPUnit lifecycle hooks
if ( class_exists( 'PHPUnit\Runner\BeforeFirstTestHook' ) ) {
	// PHPUnit 9.x
}

/**
 * WordPress function stubs for unit tests
 * These are basic implementations for testing without WordPress
 */

if ( ! function_exists( 'remove_accents' ) ) {
	/**
	 * Stub for WordPress remove_accents() function
	 * Simplified version for testing
	 */
	function remove_accents( $string ) {
		// Basic transliteration for testing
		$accents = [
			'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A',
			'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a',
			'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E',
			'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
			'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
			'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
			'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O',
			'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
			'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U',
			'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
			'Ñ' => 'N', 'ñ' => 'n',
			'Ç' => 'C', 'ç' => 'c',
		];
		return strtr( $string, $accents );
	}
}
