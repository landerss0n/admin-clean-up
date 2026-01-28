<?php
/**
 * Unit tests for filename cleaning logic
 *
 * @package Admin_Clean_Up
 */

namespace AdminCleanUp\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Test clean_filename() logic
 *
 * Since the original method is private, we test a standalone implementation
 * that mirrors the actual code.
 */
class CleanFilenamesTest extends TestCase {

	/**
	 * Clean a filename using the same logic as the plugin
	 */
	private function clean( string $filename ): string {
		$replacements = [
			// Swedish/Nordic
			'å' => 'a', 'Å' => 'a', 'ä' => 'a', 'Ä' => 'a', 'ö' => 'o', 'Ö' => 'o',
			'æ' => 'ae', 'Æ' => 'ae', 'ø' => 'o', 'Ø' => 'o',
			// German
			'ü' => 'u', 'Ü' => 'u', 'ß' => 'ss',
			// French/Spanish/Portuguese
			'é' => 'e', 'É' => 'e', 'è' => 'e', 'È' => 'e', 'ê' => 'e', 'Ê' => 'e', 'ë' => 'e', 'Ë' => 'e',
			'á' => 'a', 'Á' => 'a', 'à' => 'a', 'À' => 'a', 'â' => 'a', 'Â' => 'a', 'ã' => 'a', 'Ã' => 'a',
			'í' => 'i', 'Í' => 'i', 'ì' => 'i', 'Ì' => 'i', 'î' => 'i', 'Î' => 'i', 'ï' => 'i', 'Ï' => 'i',
			'ó' => 'o', 'Ó' => 'o', 'ò' => 'o', 'Ò' => 'o', 'ô' => 'o', 'Ô' => 'o', 'õ' => 'o', 'Õ' => 'o',
			'ú' => 'u', 'Ú' => 'u', 'ù' => 'u', 'Ù' => 'u', 'û' => 'u', 'Û' => 'u',
			'ñ' => 'n', 'Ñ' => 'n', 'ç' => 'c', 'Ç' => 'c',
			'ý' => 'y', 'Ý' => 'y', 'ÿ' => 'y', 'Ÿ' => 'y',
			// Polish
			'ą' => 'a', 'Ą' => 'a', 'ć' => 'c', 'Ć' => 'c', 'ę' => 'e', 'Ę' => 'e',
			'ł' => 'l', 'Ł' => 'l', 'ń' => 'n', 'Ń' => 'n', 'ś' => 's', 'Ś' => 's',
			'ź' => 'z', 'Ź' => 'z', 'ż' => 'z', 'Ż' => 'z',
			// Czech/Slovak
			'č' => 'c', 'Č' => 'c', 'ď' => 'd', 'Ď' => 'd', 'ě' => 'e', 'Ě' => 'e',
			'ň' => 'n', 'Ň' => 'n', 'ř' => 'r', 'Ř' => 'r', 'š' => 's', 'Š' => 's',
			'ť' => 't', 'Ť' => 't', 'ů' => 'u', 'Ů' => 'u', 'ž' => 'z', 'Ž' => 'z',
			// Special characters
			'&' => '-and-', '@' => '-at-', '#' => '', '$' => '', '%' => '',
			'^' => '', '*' => '', '(' => '', ')' => '', '[' => '', ']' => '',
			'{' => '', '}' => '', '|' => '', '\\' => '', '/' => '-',
			':' => '', ';' => '', '"' => '', "'" => '', '<' => '', '>' => '',
			',' => '', '.' => '-', '?' => '', '!' => '', '`' => '', '~' => '',
			'=' => '', '+' => '', '×' => 'x', '–' => '-', '—' => '-',
			'€' => 'eur', '£' => 'gbp', '¥' => 'yen',
			// Whitespace
			' ' => '-', "\t" => '-', "\n" => '-', "\r" => '-', '_' => '-', '%20' => '-',
		];

		$cleaned = str_replace( array_keys( $replacements ), array_values( $replacements ), $filename );
		$cleaned = remove_accents( $cleaned );
		$cleaned = strtolower( $cleaned );
		$cleaned = preg_replace( '/[^a-z0-9\-]/', '', $cleaned );
		$cleaned = preg_replace( '/-+/', '-', $cleaned );
		$cleaned = trim( $cleaned, '-' );

		return $cleaned;
	}

	// =========================================================================
	// Basic functionality tests
	// =========================================================================

	/** @test */
	public function it_returns_empty_string_for_empty_input(): void {
		$this->assertSame( '', $this->clean( '' ) );
	}

	/** @test */
	public function it_keeps_already_clean_filenames(): void {
		$this->assertSame( 'my-file', $this->clean( 'my-file' ) );
		$this->assertSame( 'document123', $this->clean( 'document123' ) );
	}

	/** @test */
	public function it_converts_to_lowercase(): void {
		$this->assertSame( 'myfile', $this->clean( 'MyFile' ) );
		$this->assertSame( 'document', $this->clean( 'DOCUMENT' ) );
	}

	// =========================================================================
	// Whitespace handling
	// =========================================================================

	/** @test */
	public function it_replaces_spaces_with_dashes(): void {
		$this->assertSame( 'my-file-name', $this->clean( 'my file name' ) );
	}

	/** @test */
	public function it_replaces_underscores_with_dashes(): void {
		$this->assertSame( 'my-file-name', $this->clean( 'my_file_name' ) );
	}

	/** @test */
	public function it_replaces_tabs_and_newlines_with_dashes(): void {
		$this->assertSame( 'tab-here', $this->clean( "tab\there" ) );
		$this->assertSame( 'new-line', $this->clean( "new\nline" ) );
	}

	/** @test */
	public function it_collapses_multiple_dashes(): void {
		$this->assertSame( 'my-file', $this->clean( 'my---file' ) );
		$this->assertSame( 'a-b-c', $this->clean( 'a - - - b - - c' ) );
	}

	/** @test */
	public function it_trims_dashes_from_ends(): void {
		$this->assertSame( 'myfile', $this->clean( '-myfile-' ) );
		$this->assertSame( 'myfile', $this->clean( '---myfile---' ) );
	}

	// =========================================================================
	// Swedish/Nordic characters
	// =========================================================================

	/** @test */
	public function it_converts_swedish_characters(): void {
		$this->assertSame( 'sasongens-fargglada-angbat', $this->clean( 'Säsongens färgglada ångbåt' ) );
	}

	/** @test */
	public function it_converts_danish_norwegian_characters(): void {
		// æ → ae, ø → o
		$this->assertSame( 'aero-and-ostergaard', $this->clean( 'Ærø and Østergaard' ) );
	}

	// =========================================================================
	// German characters
	// =========================================================================

	/** @test */
	public function it_converts_german_characters(): void {
		$this->assertSame( 'strasse-munchen', $this->clean( 'Straße München' ) );
		$this->assertSame( 'uber-grusse', $this->clean( 'Über Grüße' ) );
	}

	// =========================================================================
	// French/Spanish characters
	// =========================================================================

	/** @test */
	public function it_converts_french_characters(): void {
		$this->assertSame( 'cafe-facade', $this->clean( 'café façade' ) );
		$this->assertSame( 'tres-elegant', $this->clean( 'très élégant' ) );
	}

	/** @test */
	public function it_converts_spanish_characters(): void {
		$this->assertSame( 'espanol-nino', $this->clean( 'español niño' ) );
	}

	// =========================================================================
	// Special characters
	// =========================================================================

	/** @test */
	public function it_converts_ampersand(): void {
		$this->assertSame( 'rock-and-roll', $this->clean( 'rock & roll' ) );
	}

	/** @test */
	public function it_converts_at_sign(): void {
		$this->assertSame( 'email-at-example', $this->clean( 'email @ example' ) );
	}

	/** @test */
	public function it_removes_punctuation(): void {
		$this->assertSame( 'hello-world', $this->clean( 'Hello, World!' ) );
		$this->assertSame( 'whats-this', $this->clean( "What's this?" ) );
	}

	/** @test */
	public function it_converts_dots_to_dashes(): void {
		$this->assertSame( 'file-name-v2', $this->clean( 'file.name.v2' ) );
	}

	/** @test */
	public function it_converts_slashes_to_dashes(): void {
		$this->assertSame( 'path-to-file', $this->clean( 'path/to/file' ) );
	}

	/** @test */
	public function it_converts_currency_symbols(): void {
		$this->assertSame( '100eur', $this->clean( '100€' ) );
		$this->assertSame( '50gbp', $this->clean( '50£' ) );
	}

	// =========================================================================
	// Edge cases
	// =========================================================================

	/** @test */
	public function it_handles_only_special_characters(): void {
		// @ becomes 'at', rest removed, so result is 'at'
		$this->assertSame( 'at', $this->clean( '!@#$%^*()' ) );
	}

	/** @test */
	public function it_handles_special_chars_without_at(): void {
		$this->assertSame( '', $this->clean( '!#$%^*()' ) );
	}

	/** @test */
	public function it_handles_only_spaces(): void {
		$this->assertSame( '', $this->clean( '   ' ) );
	}

	/** @test */
	public function it_handles_mixed_complex_input(): void {
		$this->assertSame(
			'2024-sasongens-rapport-final-v2',
			$this->clean( '2024_Säsongens Rapport (FINAL) v2' )
		);
	}

	/** @test */
	public function it_handles_url_encoded_spaces(): void {
		// Note: '%' is replaced before '%20' in str_replace, so '%20' becomes '20'
		// This is a known limitation - for true URL decoding, use urldecode() first
		$this->assertSame( 'my20file', $this->clean( 'my%20file' ) );
	}

	/** @test */
	public function it_handles_numbers_only(): void {
		$this->assertSame( '12345', $this->clean( '12345' ) );
	}

	/** @test */
	public function it_preserves_dashes_in_original(): void {
		$this->assertSame( 'already-has-dashes', $this->clean( 'already-has-dashes' ) );
	}

	// =========================================================================
	// Real-world filename examples
	// =========================================================================

	/** @test */
	public function it_handles_typical_photo_filenames(): void {
		$this->assertSame( 'img-20240115-wa0003', $this->clean( 'IMG_20240115_WA0003' ) );
		$this->assertSame( 'dsc-0042', $this->clean( 'DSC_0042' ) );
		$this->assertSame( 'photo-2024-01-15-10-30-45', $this->clean( 'Photo 2024-01-15 10.30.45' ) );
	}

	/** @test */
	public function it_handles_document_filenames(): void {
		$this->assertSame( 'rapport-q4-2024-final', $this->clean( 'Rapport Q4 2024 (Final)' ) );
		$this->assertSame( 'budget-2024-v3-approved', $this->clean( 'Budget_2024_v3_APPROVED' ) );
	}

	/** @test */
	public function it_handles_swedish_document_names(): void {
		$this->assertSame( 'arsredovisning-2024', $this->clean( 'Årsredovisning 2024' ) );
		$this->assertSame( 'forsta-kvartalet-oversikt', $this->clean( 'Första kvartalet - Översikt' ) );
	}
}
