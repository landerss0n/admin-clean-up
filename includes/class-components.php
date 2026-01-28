<?php
/**
 * UI Component Render Library
 *
 * Provides reusable UI component render methods for Admin Clean Up premium settings interface.
 * All methods use BEM naming convention matching the CSS design system.
 *
 * @package Admin_Clean_Up
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Clean Up UI Components
 *
 * Static methods for rendering card, toggle, and setting group components.
 * All output uses BEM class names (acu-* namespace) and proper escaping.
 */
class WP_Clean_Up_Components {

	/**
	 * Render a card component
	 *
	 * @param array $args {
	 *     Component parameters.
	 *
	 *     @type string $id          HTML id attribute (optional).
	 *     @type string $title       Card header title (optional).
	 *     @type string $description Subtitle text below title (optional).
	 *     @type string $content     Pre-rendered HTML for card body (already escaped by caller).
	 *     @type string $modifier    BEM modifier suffix (e.g., 'highlighted' becomes 'acu-card--highlighted').
	 * }
	 * @return void Outputs HTML directly.
	 */
	public static function render_card( $args = [] ) {
		$defaults = [
			'id'          => '',
			'title'       => '',
			'description' => '',
			'content'     => '',
			'modifier'    => '',
		];
		$args = wp_parse_args( $args, $defaults );

		$classes = 'acu-card';
		if ( ! empty( $args['modifier'] ) ) {
			$classes .= ' acu-card--' . sanitize_html_class( $args['modifier'] );
		}

		?>
		<div class="<?php echo esc_attr( $classes ); ?>"<?php if ( ! empty( $args['id'] ) ) : ?> id="<?php echo esc_attr( $args['id'] ); ?>"<?php endif; ?>>
			<?php if ( ! empty( $args['title'] ) ) : ?>
				<div class="acu-card__header">
					<h3 class="acu-card__title"><?php echo esc_html( $args['title'] ); ?></h3>
					<?php if ( ! empty( $args['description'] ) ) : ?>
						<p class="acu-card__description"><?php echo esc_html( $args['description'] ); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<div class="acu-card__body">
				<?php echo $args['content']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Content escaped by caller ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render a toggle switch component
	 *
	 * CRITICAL: Hidden input appears BEFORE checkbox to ensure unchecked state submits '0'.
	 * When checked, checkbox value overwrites hidden value. When unchecked, only hidden value submits.
	 *
	 * @param array $args {
	 *     Component parameters.
	 *
	 *     @type string $name        Form field name attribute.
	 *     @type string $value       Checkbox value when checked (default '1').
	 *     @type bool   $checked     Initial checked state (default false).
	 *     @type string $label       Setting label text.
	 *     @type string $description Help text below label (optional).
	 *     @type string $id          HTML id (auto-generated from name if empty).
	 *     @type bool   $disabled    Disabled state (default false).
	 * }
	 * @return void Outputs HTML directly.
	 */
	public static function render_toggle( $args = [] ) {
		$defaults = [
			'name'        => '',
			'value'       => '1',
			'checked'     => false,
			'label'       => '',
			'description' => '',
			'id'          => '',
			'disabled'    => false,
		];
		$args = wp_parse_args( $args, $defaults );

		// Auto-generate ID from name if not provided
		if ( empty( $args['id'] ) ) {
			$id_base = str_replace( [ '[', ']' ], [ '-', '' ], $args['name'] );
			$args['id'] = 'toggle-' . sanitize_title( $id_base );
		}

		?>
		<div class="acu-setting">
			<input type="hidden" name="<?php echo esc_attr( $args['name'] ); ?>" value="0">
			<input type="checkbox"
			       id="<?php echo esc_attr( $args['id'] ); ?>"
			       name="<?php echo esc_attr( $args['name'] ); ?>"
			       value="<?php echo esc_attr( $args['value'] ); ?>"
			       class="acu-toggle__input"
			       <?php checked( $args['checked'] ); ?>
			       <?php disabled( $args['disabled'] ); ?>>
			<label for="<?php echo esc_attr( $args['id'] ); ?>" class="acu-toggle">
				<span class="acu-toggle__track"></span>
				<span class="acu-toggle__thumb"></span>
			</label>
			<div class="acu-setting__content">
				<label for="<?php echo esc_attr( $args['id'] ); ?>" class="acu-setting__label">
					<?php echo esc_html( $args['label'] ); ?>
				</label>
				<?php if ( ! empty( $args['description'] ) ) : ?>
					<p class="acu-setting__description"><?php echo wp_kses_post( $args['description'] ); ?></p>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render a setting group component
	 *
	 * Groups multiple setting toggles under an optional heading.
	 * Each setting in the array is passed to render_toggle().
	 *
	 * @param array $args {
	 *     Component parameters.
	 *
	 *     @type string $title    Group heading (optional, shown as uppercase label).
	 *     @type array  $settings Array of setting definition arrays (each passed to render_toggle).
	 * }
	 * @return void Outputs HTML directly.
	 */
	public static function render_setting_group( $args = [] ) {
		$defaults = [
			'title'    => '',
			'settings' => [],
		];
		$args = wp_parse_args( $args, $defaults );

		?>
		<div class="acu-setting-group">
			<?php if ( ! empty( $args['title'] ) ) : ?>
				<h4 class="acu-setting-group__title"><?php echo esc_html( $args['title'] ); ?></h4>
			<?php endif; ?>
			<div class="acu-setting-group__items">
				<?php foreach ( $args['settings'] as $setting ) : ?>
					<?php self::render_toggle( $setting ); ?>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render a radio group component
	 *
	 * For Updates core_updates and Media clean_filenames_types.
	 *
	 * @param array $args {
	 *     Component parameters.
	 *
	 *     @type string $name    Form field name attribute.
	 *     @type string $value   Currently selected value.
	 *     @type array  $options Array of radio options.
	 *                           Each option: ['value' => '', 'label' => '', 'description' => ''].
	 * }
	 * @return void Outputs HTML directly.
	 */
	public static function render_radio_group( $args = [] ) {
		$defaults = [
			'name'    => '',
			'value'   => '',
			'options' => [],
		];
		$args = wp_parse_args( $args, $defaults );

		?>
		<div class="acu-radio-group">
			<?php foreach ( $args['options'] as $option ) : ?>
				<?php
				$option_value = isset( $option['value'] ) ? $option['value'] : '';
				$option_label = isset( $option['label'] ) ? $option['label'] : '';
				$option_description = isset( $option['description'] ) ? $option['description'] : '';
				$radio_id = 'radio-' . sanitize_title( $args['name'] . '-' . $option_value );
				?>
				<div class="acu-radio">
					<input type="radio"
					       id="<?php echo esc_attr( $radio_id ); ?>"
					       name="<?php echo esc_attr( $args['name'] ); ?>"
					       value="<?php echo esc_attr( $option_value ); ?>"
					       class="acu-radio__input"
					       <?php checked( $args['value'], $option_value ); ?>>
					<label for="<?php echo esc_attr( $radio_id ); ?>" class="acu-radio__indicator"></label>
					<div class="acu-radio__content">
						<label for="<?php echo esc_attr( $radio_id ); ?>" class="acu-radio__label">
							<?php echo esc_html( $option_label ); ?>
						</label>
						<?php if ( ! empty( $option_description ) ) : ?>
							<p class="acu-radio__description"><?php echo esc_html( $option_description ); ?></p>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Render a text input component
	 *
	 * For Footer custom text fields.
	 *
	 * @param array $args {
	 *     Component parameters.
	 *
	 *     @type string $name        Form field name attribute.
	 *     @type string $value       Input value.
	 *     @type string $placeholder Placeholder text (optional).
	 *     @type string $label       Setting label text.
	 *     @type string $description Help text below input (optional).
	 *     @type string $id          HTML id (auto-generated from name if empty).
	 * }
	 * @return void Outputs HTML directly.
	 */
	public static function render_text_input( $args = [] ) {
		$defaults = [
			'name'        => '',
			'value'       => '',
			'placeholder' => '',
			'label'       => '',
			'description' => '',
			'id'          => '',
		];
		$args = wp_parse_args( $args, $defaults );

		// Auto-generate ID from name if not provided
		if ( empty( $args['id'] ) ) {
			$id_base = str_replace( [ '[', ']' ], [ '-', '' ], $args['name'] );
			$args['id'] = 'text-' . sanitize_title( $id_base );
		}

		?>
		<div class="acu-setting acu-setting--text">
			<div class="acu-setting__content">
				<label for="<?php echo esc_attr( $args['id'] ); ?>" class="acu-setting__label">
					<?php echo esc_html( $args['label'] ); ?>
				</label>
				<input type="text"
				       id="<?php echo esc_attr( $args['id'] ); ?>"
				       name="<?php echo esc_attr( $args['name'] ); ?>"
				       value="<?php echo esc_attr( $args['value'] ); ?>"
				       placeholder="<?php echo esc_attr( $args['placeholder'] ); ?>"
				       class="acu-text-input">
				<?php if ( ! empty( $args['description'] ) ) : ?>
					<p class="acu-setting__description"><?php echo esc_html( $args['description'] ); ?></p>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render a select dropdown component
	 *
	 * For Menus tab role dropdowns.
	 *
	 * @param array $args {
	 *     Component parameters.
	 *
	 *     @type string $name     Form field name attribute.
	 *     @type string $value    Currently selected value.
	 *     @type array  $options  Array of select options.
	 *                            Each option: ['value' => '', 'label' => ''].
	 *     @type string $id       HTML id (auto-generated from name if empty).
	 *     @type bool   $disabled Disabled state (default false).
	 * }
	 * @return void Outputs HTML directly.
	 */
	public static function render_select( $args = [] ) {
		$defaults = [
			'name'     => '',
			'value'    => '',
			'options'  => [],
			'id'       => '',
			'disabled' => false,
		];
		$args = wp_parse_args( $args, $defaults );

		// Auto-generate ID from name if not provided
		if ( empty( $args['id'] ) ) {
			$id_base = str_replace( [ '[', ']' ], [ '-', '' ], $args['name'] );
			$args['id'] = 'select-' . sanitize_title( $id_base );
		}

		?>
		<select id="<?php echo esc_attr( $args['id'] ); ?>"
		        name="<?php echo esc_attr( $args['name'] ); ?>"
		        class="acu-select"
		        <?php disabled( $args['disabled'] ); ?>>
			<?php foreach ( $args['options'] as $option ) : ?>
				<?php
				$option_value = isset( $option['value'] ) ? $option['value'] : '';
				$option_label = isset( $option['label'] ) ? $option['label'] : '';
				?>
				<option value="<?php echo esc_attr( $option_value ); ?>" <?php selected( $args['value'], $option_value ); ?>>
					<?php echo esc_html( $option_label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<?php
	}
}
