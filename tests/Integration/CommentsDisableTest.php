<?php
/**
 * Integration tests for comments disable feature
 *
 * Tests that all comment-related functionality is properly disabled.
 *
 * @package Admin_Clean_Up
 */

namespace AdminCleanUp\Tests\Integration;

use WP_UnitTestCase;

/**
 * Test comments disable integration
 */
class CommentsDisableTest extends WP_UnitTestCase {

	/**
	 * Test that comments class exists
	 */
	public function test_comments_class_exists(): void {
		$this->assertTrue( class_exists( 'WP_Clean_Up_Comments' ) );
	}

	/**
	 * Test that hooks are registered when comments disabled
	 */
	public function test_hooks_registered_when_disabled(): void {
		// Enable comment disabling
		update_option( \WP_Clean_Up::OPTION_KEY, [
			'comments' => [
				'disable_comments' => true,
			],
		] );

		// Re-instantiate
		new \WP_Clean_Up_Comments();

		// Check that comments_open filter is registered
		$this->assertTrue(
			has_filter( 'comments_open' ) !== false,
			'comments_open filter should be registered'
		);
	}

	/**
	 * Test that comments_open returns false when disabled
	 */
	public function test_comments_open_returns_false(): void {
		update_option( \WP_Clean_Up::OPTION_KEY, [
			'comments' => [
				'disable_comments' => true,
			],
		] );

		new \WP_Clean_Up_Comments();

		// Create a test post
		$post_id = self::factory()->post->create();

		// Comments should be closed
		$this->assertFalse( comments_open( $post_id ) );
	}

	/**
	 * Test that pings_open returns false when disabled
	 */
	public function test_pings_open_returns_false(): void {
		update_option( \WP_Clean_Up::OPTION_KEY, [
			'comments' => [
				'disable_comments' => true,
			],
		] );

		new \WP_Clean_Up_Comments();

		$post_id = self::factory()->post->create();

		$this->assertFalse( pings_open( $post_id ) );
	}

	/**
	 * Test that existing comments are hidden
	 */
	public function test_existing_comments_hidden(): void {
		// Create a post with comments BEFORE disabling
		$post_id = self::factory()->post->create();
		self::factory()->comment->create( [ 'comment_post_ID' => $post_id ] );
		self::factory()->comment->create( [ 'comment_post_ID' => $post_id ] );

		// Now disable comments
		update_option( \WP_Clean_Up::OPTION_KEY, [
			'comments' => [
				'disable_comments' => true,
			],
		] );

		new \WP_Clean_Up_Comments();

		// The comments filter should return empty array
		$comments = apply_filters( 'comments_array', [ 'fake_comment' ], $post_id );

		$this->assertEmpty( $comments );
	}

	/**
	 * Test that comment count returns zero
	 */
	public function test_comment_count_returns_zero(): void {
		// Create post with comments
		$post_id = self::factory()->post->create();
		self::factory()->comment->create_many( 5, [ 'comment_post_ID' => $post_id ] );

		// Disable comments
		update_option( \WP_Clean_Up::OPTION_KEY, [
			'comments' => [
				'disable_comments' => true,
			],
		] );

		new \WP_Clean_Up_Comments();

		// Filtered count should be 0
		$count = apply_filters( 'get_comments_number', 5, $post_id );

		$this->assertSame( 0, $count );
	}

	/**
	 * Test that hooks are NOT registered when comments enabled
	 */
	public function test_hooks_not_registered_when_enabled(): void {
		// Ensure comments are enabled (default)
		update_option( \WP_Clean_Up::OPTION_KEY, [
			'comments' => [
				'disable_comments' => false,
			],
		] );

		// Remove any existing filters from previous tests
		remove_all_filters( 'comments_open' );

		new \WP_Clean_Up_Comments();

		// Our specific filter should not be added
		global $wp_filter;
		$has_our_filter = false;

		if ( isset( $wp_filter['comments_open'] ) ) {
			foreach ( $wp_filter['comments_open']->callbacks as $callbacks ) {
				foreach ( $callbacks as $callback ) {
					if ( is_array( $callback['function'] ) &&
					     $callback['function'][0] instanceof \WP_Clean_Up_Comments &&
					     $callback['function'][1] === 'disable_comments_status' ) {
						$has_our_filter = true;
						break 2;
					}
				}
			}
		}

		$this->assertFalse( $has_our_filter );
	}

	/**
	 * Test that post type support for comments is removed
	 */
	public function test_post_type_support_removed(): void {
		// Ensure post type has comments support initially
		$this->assertTrue(
			post_type_supports( 'post', 'comments' ),
			'Post type should support comments initially'
		);

		update_option( \WP_Clean_Up::OPTION_KEY, [
			'comments' => [
				'disable_comments' => true,
			],
		] );

		$instance = new \WP_Clean_Up_Comments();

		// Call the method directly instead of triggering init
		// to avoid plugin update checker conflicts
		$instance->disable_comments_post_types_support();

		// Comments support should now be removed
		$this->assertFalse(
			post_type_supports( 'post', 'comments' ),
			'Comments support should be removed from post type'
		);

		// Restore for other tests
		add_post_type_support( 'post', 'comments' );
	}

	/**
	 * Clean up
	 */
	public function tearDown(): void {
		delete_option( \WP_Clean_Up::OPTION_KEY );
		parent::tearDown();
	}
}
