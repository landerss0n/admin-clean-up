<?php
/**
 * Unit tests for parse_args_recursive() logic
 *
 * @package Admin_Clean_Up
 */

namespace AdminCleanUp\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Test parse_args_recursive() logic
 *
 * Since the original method is private, we test a standalone implementation
 * that mirrors the actual code.
 */
class ParseArgsRecursiveTest extends TestCase {

	/**
	 * Parse args recursively using the same logic as the plugin
	 */
	private function parse( array $args, array $defaults ): array {
		$args     = (array) $args;
		$defaults = (array) $defaults;
		$result   = $defaults;

		foreach ( $args as $key => $value ) {
			if ( is_array( $value ) && isset( $result[ $key ] ) && is_array( $result[ $key ] ) ) {
				$result[ $key ] = $this->parse( $value, $result[ $key ] );
			} else {
				$result[ $key ] = $value;
			}
		}

		return $result;
	}

	// =========================================================================
	// Basic functionality
	// =========================================================================

	/** @test */
	public function it_returns_defaults_when_args_empty(): void {
		$defaults = [
			'option1' => true,
			'option2' => 'value',
		];

		$result = $this->parse( [], $defaults );

		$this->assertSame( $defaults, $result );
	}

	/** @test */
	public function it_returns_args_when_defaults_empty(): void {
		$args = [
			'option1' => true,
			'option2' => 'value',
		];

		$result = $this->parse( $args, [] );

		$this->assertSame( $args, $result );
	}

	/** @test */
	public function it_overrides_defaults_with_args(): void {
		$defaults = [
			'option1' => false,
			'option2' => 'default',
		];
		$args = [
			'option1' => true,
			'option2' => 'custom',
		];

		$result = $this->parse( $args, $defaults );

		$this->assertTrue( $result['option1'] );
		$this->assertSame( 'custom', $result['option2'] );
	}

	/** @test */
	public function it_preserves_defaults_for_missing_args(): void {
		$defaults = [
			'option1' => true,
			'option2' => 'default',
			'option3' => 42,
		];
		$args = [
			'option2' => 'custom',
		];

		$result = $this->parse( $args, $defaults );

		$this->assertTrue( $result['option1'] );
		$this->assertSame( 'custom', $result['option2'] );
		$this->assertSame( 42, $result['option3'] );
	}

	/** @test */
	public function it_adds_new_keys_from_args(): void {
		$defaults = [
			'option1' => true,
		];
		$args = [
			'option1' => false,
			'option2' => 'new',
		];

		$result = $this->parse( $args, $defaults );

		$this->assertFalse( $result['option1'] );
		$this->assertSame( 'new', $result['option2'] );
	}

	// =========================================================================
	// Nested array handling
	// =========================================================================

	/** @test */
	public function it_merges_nested_arrays(): void {
		$defaults = [
			'adminbar' => [
				'remove_logo' => false,
				'remove_menu' => false,
			],
		];
		$args = [
			'adminbar' => [
				'remove_logo' => true,
			],
		];

		$result = $this->parse( $args, $defaults );

		$this->assertTrue( $result['adminbar']['remove_logo'] );
		$this->assertFalse( $result['adminbar']['remove_menu'] );
	}

	/** @test */
	public function it_handles_deeply_nested_arrays(): void {
		$defaults = [
			'level1' => [
				'level2' => [
					'level3' => [
						'option1' => 'default',
						'option2' => 'default',
					],
				],
			],
		];
		$args = [
			'level1' => [
				'level2' => [
					'level3' => [
						'option1' => 'custom',
					],
				],
			],
		];

		$result = $this->parse( $args, $defaults );

		$this->assertSame( 'custom', $result['level1']['level2']['level3']['option1'] );
		$this->assertSame( 'default', $result['level1']['level2']['level3']['option2'] );
	}

	/** @test */
	public function it_replaces_entire_array_when_arg_is_not_array(): void {
		$defaults = [
			'option' => [
				'nested' => true,
			],
		];
		$args = [
			'option' => 'string_value',
		];

		$result = $this->parse( $args, $defaults );

		$this->assertSame( 'string_value', $result['option'] );
	}

	/** @test */
	public function it_handles_array_replacing_scalar_default(): void {
		$defaults = [
			'option' => 'string_default',
		];
		$args = [
			'option' => [ 'array', 'value' ],
		];

		$result = $this->parse( $args, $defaults );

		$this->assertSame( [ 'array', 'value' ], $result['option'] );
	}

	// =========================================================================
	// Plugin-specific scenarios
	// =========================================================================

	/** @test */
	public function it_handles_plugin_options_structure(): void {
		$defaults = [
			'adminbar' => [
				'remove_wp_logo'     => false,
				'remove_site_menu'   => false,
				'remove_new_content' => false,
			],
			'comments' => [
				'disable_comments' => false,
			],
			'dashboard' => [
				'remove_welcome_panel' => false,
				'remove_at_a_glance'   => false,
			],
		];

		$saved_options = [
			'adminbar' => [
				'remove_wp_logo' => true,
			],
			'comments' => [
				'disable_comments' => true,
			],
		];

		$result = $this->parse( $saved_options, $defaults );

		// Saved values should be preserved
		$this->assertTrue( $result['adminbar']['remove_wp_logo'] );
		$this->assertTrue( $result['comments']['disable_comments'] );

		// Defaults should fill in missing values
		$this->assertFalse( $result['adminbar']['remove_site_menu'] );
		$this->assertFalse( $result['adminbar']['remove_new_content'] );
		$this->assertFalse( $result['dashboard']['remove_welcome_panel'] );
		$this->assertFalse( $result['dashboard']['remove_at_a_glance'] );
	}

	/** @test */
	public function it_handles_new_defaults_added_to_existing_options(): void {
		// Simulate a plugin update adding new options
		$new_defaults = [
			'adminbar' => [
				'remove_wp_logo'   => false,
				'remove_site_menu' => false,
				'new_option'       => false, // New in update
			],
			'new_section' => [  // Entire new section
				'option1' => true,
				'option2' => 'default',
			],
		];

		// Old saved options (before update)
		$old_saved = [
			'adminbar' => [
				'remove_wp_logo' => true,
			],
		];

		$result = $this->parse( $old_saved, $new_defaults );

		// Old saved value preserved
		$this->assertTrue( $result['adminbar']['remove_wp_logo'] );

		// New option gets default
		$this->assertFalse( $result['adminbar']['new_option'] );

		// New section gets defaults
		$this->assertTrue( $result['new_section']['option1'] );
		$this->assertSame( 'default', $result['new_section']['option2'] );
	}

	// =========================================================================
	// Edge cases
	// =========================================================================

	/** @test */
	public function it_handles_null_values(): void {
		$defaults = [
			'option1' => 'default',
			'option2' => 'default',
		];
		$args = [
			'option1' => null,
		];

		$result = $this->parse( $args, $defaults );

		$this->assertNull( $result['option1'] );
		$this->assertSame( 'default', $result['option2'] );
	}

	/** @test */
	public function it_handles_boolean_false_values(): void {
		$defaults = [
			'option1' => true,
		];
		$args = [
			'option1' => false,
		];

		$result = $this->parse( $args, $defaults );

		$this->assertFalse( $result['option1'] );
	}

	/** @test */
	public function it_handles_zero_values(): void {
		$defaults = [
			'option1' => 10,
		];
		$args = [
			'option1' => 0,
		];

		$result = $this->parse( $args, $defaults );

		$this->assertSame( 0, $result['option1'] );
	}

	/** @test */
	public function it_handles_empty_string_values(): void {
		$defaults = [
			'option1' => 'default',
		];
		$args = [
			'option1' => '',
		];

		$result = $this->parse( $args, $defaults );

		$this->assertSame( '', $result['option1'] );
	}

	/** @test */
	public function it_handles_numeric_keys(): void {
		$defaults = [
			0 => 'first',
			1 => 'second',
		];
		$args = [
			0 => 'custom_first',
		];

		$result = $this->parse( $args, $defaults );

		$this->assertSame( 'custom_first', $result[0] );
		$this->assertSame( 'second', $result[1] );
	}
}
