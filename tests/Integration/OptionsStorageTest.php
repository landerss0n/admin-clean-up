<?php
/**
 * Integration tests for options storage
 *
 * Tests the full save/load cycle with WordPress database.
 *
 * @package Admin_Clean_Up
 */

namespace AdminCleanUp\Tests\Integration;

use WP_UnitTestCase;

/**
 * Test options storage integration
 */
class OptionsStorageTest extends WP_UnitTestCase {

	/**
	 * Test that plugin can be activated
	 */
	public function test_plugin_activated(): void {
		$this->assertTrue( class_exists( 'WP_Clean_Up' ) );
	}

	/**
	 * Test that get_options returns array with defaults
	 */
	public function test_get_options_returns_defaults(): void {
		// Clear any existing options
		delete_option( \WP_Clean_Up::OPTION_KEY );

		$options = \WP_Clean_Up::get_options();

		$this->assertIsArray( $options );
		$this->assertArrayHasKey( 'adminbar', $options );
		$this->assertArrayHasKey( 'comments', $options );
		$this->assertArrayHasKey( 'dashboard', $options );
		$this->assertArrayHasKey( 'menus', $options );
		$this->assertArrayHasKey( 'footer', $options );
		$this->assertArrayHasKey( 'notices', $options );
		$this->assertArrayHasKey( 'media', $options );
		$this->assertArrayHasKey( 'plugins', $options );
		$this->assertArrayHasKey( 'updates', $options );
		$this->assertArrayHasKey( 'frontend', $options );
	}

	/**
	 * Test that default values are correct types
	 */
	public function test_default_values_are_booleans(): void {
		delete_option( \WP_Clean_Up::OPTION_KEY );

		$options = \WP_Clean_Up::get_options();

		$this->assertFalse( $options['adminbar']['remove_wp_logo'] );
		$this->assertFalse( $options['comments']['disable_comments'] );
		$this->assertFalse( $options['dashboard']['remove_welcome_panel'] );
	}

	/**
	 * Test that saved options are preserved
	 */
	public function test_saved_options_are_preserved(): void {
		$saved = [
			'adminbar' => [
				'remove_wp_logo' => true,
			],
		];

		update_option( \WP_Clean_Up::OPTION_KEY, $saved );

		$options = \WP_Clean_Up::get_options();

		$this->assertTrue( $options['adminbar']['remove_wp_logo'] );
		// Other defaults should still be present
		$this->assertFalse( $options['adminbar']['remove_site_menu'] );
	}

	/**
	 * Test that new defaults are merged with existing saved options
	 */
	public function test_new_defaults_merged_with_saved(): void {
		// Simulate old saved options missing new keys
		$old_saved = [
			'adminbar' => [
				'remove_wp_logo' => true,
				// Missing new options that were added later
			],
		];

		update_option( \WP_Clean_Up::OPTION_KEY, $old_saved );

		$options = \WP_Clean_Up::get_options();

		// Saved value preserved
		$this->assertTrue( $options['adminbar']['remove_wp_logo'] );

		// New defaults filled in
		$this->assertArrayHasKey( 'remove_howdy_frontend', $options['adminbar'] );
		$this->assertFalse( $options['adminbar']['remove_howdy_frontend'] );
	}

	/**
	 * Test OPTION_KEY constant
	 */
	public function test_option_key_constant(): void {
		$this->assertSame( 'wp_clean_up_options', \WP_Clean_Up::OPTION_KEY );
	}

	/**
	 * Test that update_option works with plugin options
	 */
	public function test_can_save_and_retrieve_options(): void {
		$test_options = [
			'adminbar' => [
				'remove_wp_logo'   => true,
				'remove_site_menu' => false,
			],
			'comments' => [
				'disable_comments' => true,
			],
		];

		update_option( \WP_Clean_Up::OPTION_KEY, $test_options );
		$retrieved = get_option( \WP_Clean_Up::OPTION_KEY );

		$this->assertTrue( $retrieved['adminbar']['remove_wp_logo'] );
		$this->assertFalse( $retrieved['adminbar']['remove_site_menu'] );
		$this->assertTrue( $retrieved['comments']['disable_comments'] );
	}

	/**
	 * Clean up after each test
	 */
	public function tearDown(): void {
		delete_option( \WP_Clean_Up::OPTION_KEY );
		parent::tearDown();
	}
}
