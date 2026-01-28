<?php
/**
 * Unit tests for sanitize_options() method
 *
 * @package Admin_Clean_Up
 */

namespace AdminCleanUp\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Test sanitize_options() logic
 *
 * Tests the sanitization logic for each tab's options.
 * Uses a standalone implementation that mirrors the actual code.
 */
class SanitizeOptionsTest extends TestCase {

	/**
	 * Existing options (simulates get_option result)
	 */
	private array $existing_options = [];

	/**
	 * Set up test fixtures
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->existing_options = [];
	}

	/**
	 * Sanitize options using the same logic as the plugin
	 *
	 * @param array $input Raw input from form
	 * @return array Sanitized options
	 */
	private function sanitize( array $input ): array {
		$existing = $this->existing_options;
		$sanitized = is_array( $existing ) ? $existing : [];

		$current_tab = isset( $input['_current_tab'] ) ? $this->sanitize_key( $input['_current_tab'] ) : '';

		// Adminbar tab
		if ( 'adminbar' === $current_tab ) {
			$adminbar = isset( $input['adminbar'] ) && is_array( $input['adminbar'] ) ? $input['adminbar'] : [];
			$sanitized['adminbar'] = [
				'remove_wp_logo'        => ! empty( $adminbar['remove_wp_logo'] ),
				'remove_site_menu'      => ! empty( $adminbar['remove_site_menu'] ),
				'remove_new_content'    => ! empty( $adminbar['remove_new_content'] ),
				'remove_search'         => ! empty( $adminbar['remove_search'] ),
				'remove_howdy_frontend' => ! empty( $adminbar['remove_howdy_frontend'] ),
			];
		}

		// Comments tab
		if ( 'comments' === $current_tab ) {
			$comments = isset( $input['comments'] ) && is_array( $input['comments'] ) ? $input['comments'] : [];
			$sanitized['comments'] = [
				'disable_comments' => ! empty( $comments['disable_comments'] ),
			];
		}

		// Dashboard tab
		if ( 'dashboard' === $current_tab ) {
			$dashboard = isset( $input['dashboard'] ) && is_array( $input['dashboard'] ) ? $input['dashboard'] : [];
			$sanitized['dashboard'] = [
				'remove_welcome_panel' => ! empty( $dashboard['remove_welcome_panel'] ),
				'remove_at_a_glance'   => ! empty( $dashboard['remove_at_a_glance'] ),
				'remove_activity'      => ! empty( $dashboard['remove_activity'] ),
				'remove_quick_draft'   => ! empty( $dashboard['remove_quick_draft'] ),
				'remove_wp_events'     => ! empty( $dashboard['remove_wp_events'] ),
				'remove_site_health'   => ! empty( $dashboard['remove_site_health'] ),
				'disable_site_health'  => ! empty( $dashboard['disable_site_health'] ),
			];
		}

		// Menus tab
		if ( 'menus' === $current_tab ) {
			$menus = isset( $input['menus'] ) && is_array( $input['menus'] ) ? $input['menus'] : [];
			$valid_roles = [ 'all', 'non_admin', 'non_editor' ];
			$menu_keys = [ 'posts', 'media', 'pages', 'appearance', 'plugins', 'users', 'tools', 'settings' ];

			$sanitized['menus'] = [];
			foreach ( $menu_keys as $key ) {
				$sanitized['menus'][ 'remove_' . $key ] = ! empty( $menus[ 'remove_' . $key ] );
				$role_value = isset( $menus[ 'remove_' . $key . '_for' ] ) ? $menus[ 'remove_' . $key . '_for' ] : 'non_admin';
				$sanitized['menus'][ 'remove_' . $key . '_for' ] = in_array( $role_value, $valid_roles, true ) ? $role_value : 'non_admin';
			}
		}

		// Footer tab
		if ( 'footer' === $current_tab ) {
			$footer = isset( $input['footer'] ) && is_array( $input['footer'] ) ? $input['footer'] : [];
			$sanitized['footer'] = [
				'remove_footer_text'  => ! empty( $footer['remove_footer_text'] ),
				'custom_footer_text'  => isset( $footer['custom_footer_text'] ) ? $this->wp_kses_post( $footer['custom_footer_text'] ) : '',
				'remove_version'      => ! empty( $footer['remove_version'] ),
				'custom_version_text' => isset( $footer['custom_version_text'] ) ? $this->sanitize_text_field( $footer['custom_version_text'] ) : '',
			];
		}

		// Notices tab
		if ( 'notices' === $current_tab ) {
			$notices = isset( $input['notices'] ) && is_array( $input['notices'] ) ? $input['notices'] : [];
			$sanitized['notices'] = [
				'hide_update_notices' => ! empty( $notices['hide_update_notices'] ),
				'hide_all_notices'    => ! empty( $notices['hide_all_notices'] ),
				'hide_screen_options' => ! empty( $notices['hide_screen_options'] ),
				'hide_help_tab'       => ! empty( $notices['hide_help_tab'] ),
			];
		}

		// Media tab
		if ( 'media' === $current_tab ) {
			$media = isset( $input['media'] ) && is_array( $input['media'] ) ? $input['media'] : [];
			$sanitized['media'] = [
				'clean_filenames'       => ! empty( $media['clean_filenames'] ),
				'clean_filenames_types' => isset( $media['clean_filenames_types'] ) && in_array( $media['clean_filenames_types'], [ 'all', 'images' ], true ) ? $media['clean_filenames_types'] : 'all',
			];
		}

		// Plugins tab
		if ( 'plugins' === $current_tab ) {
			$plugins = isset( $input['plugins'] ) && is_array( $input['plugins'] ) ? $input['plugins'] : [];
			$sanitized['plugins'] = [
				'hide_pixelyoursite_notices' => ! empty( $plugins['hide_pixelyoursite_notices'] ),
				'hide_elementor_notices'     => ! empty( $plugins['hide_elementor_notices'] ),
				'hide_yoast_notices'         => ! empty( $plugins['hide_yoast_notices'] ),
				'hide_complianz_comments'    => ! empty( $plugins['hide_complianz_comments'] ),
				'hide_gtm4wp_comments'       => ! empty( $plugins['hide_gtm4wp_comments'] ),
				'hide_woocommerce_clutter'   => ! empty( $plugins['hide_woocommerce_clutter'] ),
			];
		}

		// Updates tab
		if ( 'updates' === $current_tab ) {
			$updates = isset( $input['updates'] ) && is_array( $input['updates'] ) ? $input['updates'] : [];
			$valid_core_updates = [ 'default', 'disable_all', 'security_only', 'minor_only', 'all_updates' ];
			$sanitized['updates'] = [
				'core_updates'           => isset( $updates['core_updates'] ) && in_array( $updates['core_updates'], $valid_core_updates, true ) ? $updates['core_updates'] : 'default',
				'disable_plugin_updates' => ! empty( $updates['disable_plugin_updates'] ),
				'disable_theme_updates'  => ! empty( $updates['disable_theme_updates'] ),
				'disable_update_emails'  => ! empty( $updates['disable_update_emails'] ),
				'hide_update_nags'       => ! empty( $updates['hide_update_nags'] ),
			];
		}

		// Frontend tab
		if ( 'frontend' === $current_tab ) {
			$frontend = isset( $input['frontend'] ) && is_array( $input['frontend'] ) ? $input['frontend'] : [];
			$sanitized['frontend'] = [
				'hide_jquery_migrate_notice' => ! empty( $frontend['hide_jquery_migrate_notice'] ),
				'use_site_logo_on_login'     => ! empty( $frontend['use_site_logo_on_login'] ),
				'custom_login_logo'          => ! empty( $frontend['custom_login_logo'] ) ? abs( (int) $frontend['custom_login_logo'] ) : 0,
			];
		}

		return $sanitized;
	}

	/**
	 * Simple sanitize_key implementation
	 */
	private function sanitize_key( string $key ): string {
		return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( $key ) );
	}

	/**
	 * Simple wp_kses_post implementation (strips dangerous tags)
	 */
	private function wp_kses_post( string $text ): string {
		// Strip script and style tags for testing
		$text = preg_replace( '/<script\b[^>]*>.*?<\/script>/is', '', $text );
		$text = preg_replace( '/<style\b[^>]*>.*?<\/style>/is', '', $text );
		return $text;
	}

	/**
	 * Simple sanitize_text_field implementation
	 */
	private function sanitize_text_field( string $text ): string {
		return htmlspecialchars( strip_tags( trim( $text ) ), ENT_QUOTES, 'UTF-8' );
	}

	// =========================================================================
	// General behavior tests
	// =========================================================================

	/** @test */
	public function it_returns_empty_array_when_no_tab_specified(): void {
		$result = $this->sanitize( [] );
		$this->assertSame( [], $result );
	}

	/** @test */
	public function it_preserves_existing_options_from_other_tabs(): void {
		$this->existing_options = [
			'adminbar' => [ 'remove_wp_logo' => true ],
			'comments' => [ 'disable_comments' => true ],
		];

		$input = [
			'_current_tab' => 'dashboard',
			'dashboard'    => [ 'remove_welcome_panel' => '1' ],
		];

		$result = $this->sanitize( $input );

		// Existing options preserved
		$this->assertTrue( $result['adminbar']['remove_wp_logo'] );
		$this->assertTrue( $result['comments']['disable_comments'] );
		// New options added
		$this->assertTrue( $result['dashboard']['remove_welcome_panel'] );
	}

	/** @test */
	public function it_sanitizes_tab_key(): void {
		// sanitize_key converts to lowercase and removes invalid chars
		// 'ADMINBAR' becomes 'adminbar'
		$input = [
			'_current_tab' => 'ADMINBAR',
			'adminbar'     => [ 'remove_wp_logo' => '1' ],
		];

		$result = $this->sanitize( $input );

		$this->assertTrue( $result['adminbar']['remove_wp_logo'] );
	}

	/** @test */
	public function it_ignores_invalid_tab_key(): void {
		// If tab key is sanitized to something that doesn't match, nothing is saved
		$input = [
			'_current_tab' => '<script>alert(1)</script>',
			'adminbar'     => [ 'remove_wp_logo' => '1' ],
		];

		$result = $this->sanitize( $input );

		// sanitize_key produces 'scriptalert1script' which doesn't match any tab
		$this->assertArrayNotHasKey( 'adminbar', $result );
	}

	// =========================================================================
	// Boolean field tests
	// =========================================================================

	/** @test */
	public function it_converts_truthy_values_to_true(): void {
		$input = [
			'_current_tab' => 'adminbar',
			'adminbar'     => [
				'remove_wp_logo'   => '1',
				'remove_site_menu' => 'yes',
				'remove_search'    => true,
			],
		];

		$result = $this->sanitize( $input );

		$this->assertTrue( $result['adminbar']['remove_wp_logo'] );
		$this->assertTrue( $result['adminbar']['remove_site_menu'] );
		$this->assertTrue( $result['adminbar']['remove_search'] );
	}

	/** @test */
	public function it_converts_falsy_values_to_false(): void {
		$input = [
			'_current_tab' => 'adminbar',
			'adminbar'     => [
				'remove_wp_logo'   => '0',
				'remove_site_menu' => '',
				'remove_search'    => false,
				'remove_new_content' => null,
			],
		];

		$result = $this->sanitize( $input );

		$this->assertFalse( $result['adminbar']['remove_wp_logo'] );
		$this->assertFalse( $result['adminbar']['remove_site_menu'] );
		$this->assertFalse( $result['adminbar']['remove_search'] );
		$this->assertFalse( $result['adminbar']['remove_new_content'] );
	}

	/** @test */
	public function it_handles_missing_boolean_fields_as_false(): void {
		$input = [
			'_current_tab' => 'adminbar',
			'adminbar'     => [],
		];

		$result = $this->sanitize( $input );

		$this->assertFalse( $result['adminbar']['remove_wp_logo'] );
		$this->assertFalse( $result['adminbar']['remove_site_menu'] );
	}

	// =========================================================================
	// Select/Radio field validation tests
	// =========================================================================

	/** @test */
	public function it_validates_core_updates_option(): void {
		$valid_values = [ 'default', 'disable_all', 'security_only', 'minor_only', 'all_updates' ];

		foreach ( $valid_values as $value ) {
			$input = [
				'_current_tab' => 'updates',
				'updates'      => [ 'core_updates' => $value ],
			];
			$result = $this->sanitize( $input );
			$this->assertSame( $value, $result['updates']['core_updates'] );
		}
	}

	/** @test */
	public function it_rejects_invalid_core_updates_value(): void {
		$input = [
			'_current_tab' => 'updates',
			'updates'      => [ 'core_updates' => 'invalid_value' ],
		];

		$result = $this->sanitize( $input );

		$this->assertSame( 'default', $result['updates']['core_updates'] );
	}

	/** @test */
	public function it_validates_clean_filenames_types(): void {
		// Valid: 'all'
		$input = [
			'_current_tab' => 'media',
			'media'        => [ 'clean_filenames_types' => 'all' ],
		];
		$result = $this->sanitize( $input );
		$this->assertSame( 'all', $result['media']['clean_filenames_types'] );

		// Valid: 'images'
		$input['media']['clean_filenames_types'] = 'images';
		$result = $this->sanitize( $input );
		$this->assertSame( 'images', $result['media']['clean_filenames_types'] );

		// Invalid: falls back to 'all'
		$input['media']['clean_filenames_types'] = 'invalid';
		$result = $this->sanitize( $input );
		$this->assertSame( 'all', $result['media']['clean_filenames_types'] );
	}

	/** @test */
	public function it_validates_menu_role_options(): void {
		$valid_roles = [ 'all', 'non_admin', 'non_editor' ];

		foreach ( $valid_roles as $role ) {
			$input = [
				'_current_tab' => 'menus',
				'menus'        => [ 'remove_posts_for' => $role ],
			];
			$result = $this->sanitize( $input );
			$this->assertSame( $role, $result['menus']['remove_posts_for'] );
		}
	}

	/** @test */
	public function it_rejects_invalid_menu_role_value(): void {
		$input = [
			'_current_tab' => 'menus',
			'menus'        => [ 'remove_posts_for' => 'hacker_role' ],
		];

		$result = $this->sanitize( $input );

		$this->assertSame( 'non_admin', $result['menus']['remove_posts_for'] );
	}

	// =========================================================================
	// Text field sanitization tests
	// =========================================================================

	/** @test */
	public function it_sanitizes_custom_footer_text_with_kses(): void {
		$input = [
			'_current_tab' => 'footer',
			'footer'       => [
				'custom_footer_text' => '<p>Valid <strong>HTML</strong></p><script>alert("xss")</script>',
			],
		];

		$result = $this->sanitize( $input );

		// Script should be removed
		$this->assertStringNotContainsString( '<script>', $result['footer']['custom_footer_text'] );
		// Safe HTML preserved
		$this->assertStringContainsString( '<p>', $result['footer']['custom_footer_text'] );
	}

	/** @test */
	public function it_sanitizes_custom_version_text_as_plain_text(): void {
		$input = [
			'_current_tab' => 'footer',
			'footer'       => [
				'custom_version_text' => '<script>alert("xss")</script>Version 2.0',
			],
		];

		$result = $this->sanitize( $input );

		// HTML should be stripped
		$this->assertStringNotContainsString( '<script>', $result['footer']['custom_version_text'] );
		$this->assertStringContainsString( 'Version 2.0', $result['footer']['custom_version_text'] );
	}

	/** @test */
	public function it_handles_empty_text_fields(): void {
		$input = [
			'_current_tab' => 'footer',
			'footer'       => [
				'custom_footer_text'  => '',
				'custom_version_text' => '',
			],
		];

		$result = $this->sanitize( $input );

		$this->assertSame( '', $result['footer']['custom_footer_text'] );
		$this->assertSame( '', $result['footer']['custom_version_text'] );
	}

	/** @test */
	public function it_handles_missing_text_fields(): void {
		$input = [
			'_current_tab' => 'footer',
			'footer'       => [],
		];

		$result = $this->sanitize( $input );

		$this->assertSame( '', $result['footer']['custom_footer_text'] );
		$this->assertSame( '', $result['footer']['custom_version_text'] );
	}

	// =========================================================================
	// Integer field tests
	// =========================================================================

	/** @test */
	public function it_sanitizes_custom_login_logo_as_absolute_integer(): void {
		$input = [
			'_current_tab' => 'frontend',
			'frontend'     => [ 'custom_login_logo' => '123' ],
		];

		$result = $this->sanitize( $input );

		$this->assertSame( 123, $result['frontend']['custom_login_logo'] );
	}

	/** @test */
	public function it_handles_negative_logo_id(): void {
		$input = [
			'_current_tab' => 'frontend',
			'frontend'     => [ 'custom_login_logo' => '-456' ],
		];

		$result = $this->sanitize( $input );

		$this->assertSame( 456, $result['frontend']['custom_login_logo'] );
	}

	/** @test */
	public function it_handles_empty_logo_id(): void {
		$input = [
			'_current_tab' => 'frontend',
			'frontend'     => [ 'custom_login_logo' => '' ],
		];

		$result = $this->sanitize( $input );

		$this->assertSame( 0, $result['frontend']['custom_login_logo'] );
	}

	/** @test */
	public function it_handles_non_numeric_logo_id(): void {
		$input = [
			'_current_tab' => 'frontend',
			'frontend'     => [ 'custom_login_logo' => 'not-a-number' ],
		];

		$result = $this->sanitize( $input );

		$this->assertSame( 0, $result['frontend']['custom_login_logo'] );
	}

	// =========================================================================
	// Tab-specific tests
	// =========================================================================

	/** @test */
	public function it_sanitizes_all_adminbar_options(): void {
		$input = [
			'_current_tab' => 'adminbar',
			'adminbar'     => [
				'remove_wp_logo'        => '1',
				'remove_site_menu'      => '1',
				'remove_new_content'    => '1',
				'remove_search'         => '1',
				'remove_howdy_frontend' => '1',
			],
		];

		$result = $this->sanitize( $input );

		$this->assertCount( 5, $result['adminbar'] );
		$this->assertTrue( $result['adminbar']['remove_wp_logo'] );
		$this->assertTrue( $result['adminbar']['remove_site_menu'] );
		$this->assertTrue( $result['adminbar']['remove_new_content'] );
		$this->assertTrue( $result['adminbar']['remove_search'] );
		$this->assertTrue( $result['adminbar']['remove_howdy_frontend'] );
	}

	/** @test */
	public function it_sanitizes_all_menu_items(): void {
		$input = [
			'_current_tab' => 'menus',
			'menus'        => [
				'remove_posts'    => '1',
				'remove_posts_for' => 'all',
				'remove_media'    => '1',
				'remove_media_for' => 'non_editor',
			],
		];

		$result = $this->sanitize( $input );

		$expected_keys = [
			'remove_posts', 'remove_posts_for',
			'remove_media', 'remove_media_for',
			'remove_pages', 'remove_pages_for',
			'remove_appearance', 'remove_appearance_for',
			'remove_plugins', 'remove_plugins_for',
			'remove_users', 'remove_users_for',
			'remove_tools', 'remove_tools_for',
			'remove_settings', 'remove_settings_for',
		];

		foreach ( $expected_keys as $key ) {
			$this->assertArrayHasKey( $key, $result['menus'] );
		}

		$this->assertTrue( $result['menus']['remove_posts'] );
		$this->assertSame( 'all', $result['menus']['remove_posts_for'] );
		$this->assertTrue( $result['menus']['remove_media'] );
		$this->assertSame( 'non_editor', $result['menus']['remove_media_for'] );
		// Unset ones should be false with default role
		$this->assertFalse( $result['menus']['remove_pages'] );
		$this->assertSame( 'non_admin', $result['menus']['remove_pages_for'] );
	}

	/** @test */
	public function it_sanitizes_all_plugin_options(): void {
		$input = [
			'_current_tab' => 'plugins',
			'plugins'      => [
				'hide_pixelyoursite_notices' => '1',
				'hide_yoast_notices'         => '1',
			],
		];

		$result = $this->sanitize( $input );

		$this->assertTrue( $result['plugins']['hide_pixelyoursite_notices'] );
		$this->assertFalse( $result['plugins']['hide_elementor_notices'] );
		$this->assertTrue( $result['plugins']['hide_yoast_notices'] );
		$this->assertFalse( $result['plugins']['hide_complianz_comments'] );
		$this->assertFalse( $result['plugins']['hide_gtm4wp_comments'] );
		$this->assertFalse( $result['plugins']['hide_woocommerce_clutter'] );
	}

	// =========================================================================
	// Edge cases and security tests
	// =========================================================================

	/** @test */
	public function it_handles_non_array_tab_data(): void {
		$input = [
			'_current_tab' => 'adminbar',
			'adminbar'     => 'not-an-array',
		];

		$result = $this->sanitize( $input );

		// Should use empty array as fallback
		$this->assertFalse( $result['adminbar']['remove_wp_logo'] );
	}

	/** @test */
	public function it_ignores_extra_unknown_fields(): void {
		$input = [
			'_current_tab' => 'adminbar',
			'adminbar'     => [
				'remove_wp_logo' => '1',
				'malicious_field' => 'hacked',
				'another_unknown' => true,
			],
		];

		$result = $this->sanitize( $input );

		$this->assertArrayNotHasKey( 'malicious_field', $result['adminbar'] );
		$this->assertArrayNotHasKey( 'another_unknown', $result['adminbar'] );
		$this->assertCount( 5, $result['adminbar'] );
	}

	/** @test */
	public function it_handles_missing_tab_data_array(): void {
		$input = [
			'_current_tab' => 'comments',
			// 'comments' key missing entirely
		];

		$result = $this->sanitize( $input );

		$this->assertFalse( $result['comments']['disable_comments'] );
	}
}
