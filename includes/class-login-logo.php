<?php
/**
 * Login logo customization functionality
 *
 * @package Admin_Clean_Up
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP_Clean_Up_Login_Logo class
 */
class WP_Clean_Up_Login_Logo {

	/**
	 * Constructor
	 */
	public function __construct() {
		$options  = WP_Clean_Up::get_options();
		$frontend = isset( $options['frontend'] ) ? $options['frontend'] : [];

		// Use site logo as login logo
		if ( ! empty( $frontend['use_site_logo_on_login'] ) ) {
			add_action( 'login_enqueue_scripts', [ $this, 'custom_login_logo' ] );
			add_filter( 'login_headerurl', [ $this, 'login_logo_url' ] );
			add_filter( 'login_headertext', [ $this, 'login_logo_text' ] );
		}
	}

	/**
	 * Get the custom logo URL and dimensions
	 *
	 * Checks for custom login logo first, then falls back to site logo from Customizer.
	 *
	 * @return array|false Logo data array or false if no logo set
	 */
	private function get_logo_data() {
		$options  = WP_Clean_Up::get_options();
		$frontend = isset( $options['frontend'] ) ? $options['frontend'] : [];

		// Check for custom login logo first
		$logo_id = ! empty( $frontend['custom_login_logo'] ) ? absint( $frontend['custom_login_logo'] ) : 0;

		// Fall back to site logo from Customizer
		if ( ! $logo_id ) {
			$logo_id = get_theme_mod( 'custom_logo' );
		}

		if ( ! $logo_id ) {
			return false;
		}

		$logo_image = wp_get_attachment_image_src( $logo_id, 'full' );

		if ( ! $logo_image ) {
			return false;
		}

		return [
			'url'    => $logo_image[0],
			'width'  => $logo_image[1],
			'height' => $logo_image[2],
		];
	}

	/**
	 * Output custom login logo CSS
	 */
	public function custom_login_logo() {
		$logo_data = $this->get_logo_data();

		if ( ! $logo_data ) {
			return;
		}

		// Calculate dimensions - max width 320px, maintain aspect ratio
		$max_width  = 320;
		$width      = $logo_data['width'];
		$height     = $logo_data['height'];

		if ( $width > $max_width ) {
			$ratio  = $max_width / $width;
			$width  = $max_width;
			$height = round( $height * $ratio );
		}

		?>
		<style type="text/css">
			#login h1 a,
			.login h1 a {
				background-image: url(<?php echo esc_url( $logo_data['url'] ); ?>);
				background-size: contain;
				background-repeat: no-repeat;
				background-position: center;
				width: <?php echo (int) $width; ?>px;
				height: <?php echo (int) $height; ?>px;
				max-width: 100%;
			}
		</style>
		<?php
	}

	/**
	 * Change login logo URL to site home
	 *
	 * @return string
	 */
	public function login_logo_url() {
		return home_url( '/' );
	}

	/**
	 * Change login logo text to site name
	 *
	 * @return string
	 */
	public function login_logo_text() {
		return get_bloginfo( 'name' );
	}
}
