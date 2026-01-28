<?php
/**
 * Integration tests for clean filenames feature
 *
 * Tests the full upload flow with WordPress.
 *
 * @package Admin_Clean_Up
 */

namespace AdminCleanUp\Tests\Integration;

use WP_UnitTestCase;

/**
 * Test clean filenames integration with WordPress upload
 */
class CleanFilenamesIntegrationTest extends WP_UnitTestCase {

	/**
	 * Test file path for uploads
	 */
	private string $test_file;

	/**
	 * Set up test fixtures
	 */
	public function setUp(): void {
		parent::setUp();

		// Enable clean filenames
		update_option( \WP_Clean_Up::OPTION_KEY, [
			'media' => [
				'clean_filenames'       => true,
				'clean_filenames_types' => 'all',
			],
		] );

		// Create a test image file
		$this->test_file = '/tmp/test-image-' . uniqid() . '.txt';
		file_put_contents( $this->test_file, 'test content' );
	}

	/**
	 * Test that clean_filename_on_upload filter is registered
	 */
	public function test_filter_is_registered_when_enabled(): void {
		// Re-instantiate the class to pick up enabled setting
		new \WP_Clean_Up_Clean_Filenames();

		$this->assertTrue(
			has_filter( 'wp_handle_upload_prefilter' ) !== false,
			'wp_handle_upload_prefilter filter should be registered'
		);
	}

	/**
	 * Test that filter is NOT registered when disabled
	 */
	public function test_filter_not_registered_when_disabled(): void {
		// Disable clean filenames
		update_option( \WP_Clean_Up::OPTION_KEY, [
			'media' => [
				'clean_filenames' => false,
			],
		] );

		// Remove existing filters
		remove_all_filters( 'wp_handle_upload_prefilter' );

		// Re-instantiate
		new \WP_Clean_Up_Clean_Filenames();

		// Check if our specific callback is registered (not just any filter)
		global $wp_filter;
		$has_our_filter = false;

		if ( isset( $wp_filter['wp_handle_upload_prefilter'] ) ) {
			foreach ( $wp_filter['wp_handle_upload_prefilter']->callbacks as $priority => $callbacks ) {
				foreach ( $callbacks as $callback ) {
					if ( is_array( $callback['function'] ) &&
					     $callback['function'][0] instanceof \WP_Clean_Up_Clean_Filenames ) {
						$has_our_filter = true;
						break 2;
					}
				}
			}
		}

		$this->assertFalse( $has_our_filter, 'Our filter should not be registered when disabled' );
	}

	/**
	 * Test clean_filename_on_upload method directly
	 */
	public function test_clean_filename_on_upload(): void {
		$instance = new \WP_Clean_Up_Clean_Filenames();

		// Simulate file upload data
		$file = [
			'name'     => 'Säsongens Rapport (Final).pdf',
			'type'     => 'application/pdf',
			'tmp_name' => $this->test_file,
			'error'    => 0,
			'size'     => 1024,
		];

		$result = $instance->clean_filename_on_upload( $file );

		$this->assertSame( 'sasongens-rapport-final.pdf', $result['name'] );
	}

	/**
	 * Test that extension is preserved
	 */
	public function test_extension_is_preserved(): void {
		$instance = new \WP_Clean_Up_Clean_Filenames();

		$file = [
			'name'     => 'TEST FILE.PDF',
			'type'     => 'application/pdf',
			'tmp_name' => $this->test_file,
			'error'    => 0,
			'size'     => 1024,
		];

		$result = $instance->clean_filename_on_upload( $file );

		// Extension should be lowercase
		$this->assertStringEndsWith( '.pdf', $result['name'] );
	}

	/**
	 * Test images-only mode skips non-images
	 */
	public function test_images_only_mode_skips_documents(): void {
		update_option( \WP_Clean_Up::OPTION_KEY, [
			'media' => [
				'clean_filenames'       => true,
				'clean_filenames_types' => 'images',
			],
		] );

		$instance = new \WP_Clean_Up_Clean_Filenames();

		$file = [
			'name'     => 'Säsongens Rapport.pdf',
			'type'     => 'application/pdf',
			'tmp_name' => $this->test_file,
			'error'    => 0,
			'size'     => 1024,
		];

		$result = $instance->clean_filename_on_upload( $file );

		// Should be unchanged (PDF is not an image)
		$this->assertSame( 'Säsongens Rapport.pdf', $result['name'] );
	}

	/**
	 * Test images-only mode processes images
	 */
	public function test_images_only_mode_processes_images(): void {
		update_option( \WP_Clean_Up::OPTION_KEY, [
			'media' => [
				'clean_filenames'       => true,
				'clean_filenames_types' => 'images',
			],
		] );

		$instance = new \WP_Clean_Up_Clean_Filenames();

		$file = [
			'name'     => 'Säsongens Bild.jpg',
			'type'     => 'image/jpeg',
			'tmp_name' => $this->test_file,
			'error'    => 0,
			'size'     => 1024,
		];

		$result = $instance->clean_filename_on_upload( $file );

		$this->assertSame( 'sasongens-bild.jpg', $result['name'] );
	}

	/**
	 * Test original title is stored in transient
	 */
	public function test_original_title_stored_in_transient(): void {
		$instance = new \WP_Clean_Up_Clean_Filenames();

		// Clear any existing transient
		delete_transient( '_admin_clean_up_original_filename' );

		$file = [
			'name'     => 'My Original Title.pdf',
			'type'     => 'application/pdf',
			'tmp_name' => $this->test_file,
			'error'    => 0,
			'size'     => 1024,
		];

		$instance->clean_filename_on_upload( $file );

		$stored = get_transient( '_admin_clean_up_original_filename' );

		$this->assertSame( 'My Original Title', $stored );
	}

	/**
	 * Test empty filename handling
	 */
	public function test_empty_filename_returns_unchanged(): void {
		$instance = new \WP_Clean_Up_Clean_Filenames();

		$file = [
			'name'     => '.pdf',
			'type'     => 'application/pdf',
			'tmp_name' => $this->test_file,
			'error'    => 0,
			'size'     => 1024,
		];

		$result = $instance->clean_filename_on_upload( $file );

		// Should return unchanged if filename part is empty
		$this->assertSame( '.pdf', $result['name'] );
	}

	/**
	 * Clean up after tests
	 */
	public function tearDown(): void {
		delete_option( \WP_Clean_Up::OPTION_KEY );
		delete_transient( '_admin_clean_up_original_filename' );

		if ( file_exists( $this->test_file ) ) {
			unlink( $this->test_file );
		}

		parent::tearDown();
	}
}
