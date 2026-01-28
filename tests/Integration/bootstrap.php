<?php
/**
 * WordPress Integration Tests Bootstrap
 *
 * This bootstrap loads WordPress test framework for integration testing.
 * Requires WordPress test library to be installed.
 *
 * @package Admin_Clean_Up
 */

// Check for WordPress test library path
$wp_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $wp_tests_dir ) {
	// Try common locations
	$possible_paths = [
		'/tmp/wordpress-tests-lib',
		dirname( __DIR__, 4 ) . '/tests/phpunit', // Inside WP installation
		getenv( 'HOME' ) . '/wordpress-tests-lib',
	];

	foreach ( $possible_paths as $path ) {
		if ( file_exists( $path . '/includes/functions.php' ) ) {
			$wp_tests_dir = $path;
			break;
		}
	}
}

if ( ! $wp_tests_dir || ! file_exists( $wp_tests_dir . '/includes/functions.php' ) ) {
	echo "WordPress test library not found.\n\n";
	echo "Run the setup script first:\n";
	echo "  bash tests/bin/install-wp-tests.sh <db-name> <db-user> <db-pass> [db-host] [wp-version]\n\n";
	echo "Or set WP_TESTS_DIR environment variable.\n";
	exit( 1 );
}

// Give access to tests_add_filter() function
require_once $wp_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested
 */
function _manually_load_plugin() {
	require dirname( __DIR__, 2 ) . '/admin-clean-up.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment
require $wp_tests_dir . '/includes/bootstrap.php';
