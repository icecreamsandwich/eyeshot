<?php
/**
 * This template can be overridden by copying it to yourtheme/flexible-checkout-fields/fields/inspirecheckbox.php
 *
 * @var string  $key   Field ID.
 * @var mixed[] $args  Custom attributes for field.
 * @var mixed   $value Field value.
 * @var string[] $custom_attributes .
 *
 * @package Flexible Checkout Fields PRO
 */

$default_value  = '1';
$settings_value = $args['placeholder'] !== '' ? $args['placeholder'] : $default_value;
?>
<p class="form-row <?php echo esc_attr( $args['class'] ); ?>"
	id="<?php echo esc_attr( $key ); ?>_field"
	data-priority="<?php echo esc_attr( $args['priority'] ); ?>"
	data-fcf-field="<?php echo esc_attr( $key ); ?>">
	<label for="<?php echo esc_attr( $key ); ?>">
		<input
			type="checkbox"
			class="input-checkbox"
			name="<?php echo esc_attr( $key ); ?>"
			id="<?php echo esc_attr( $key ); ?>"
			value="<?php echo esc_attr( $settings_value ); ?>"
			<?php echo (string) $value === $settings_value ? 'checked' : ''; ?>
			data-fcf-field-input="<?php echo esc_attr( $key ); ?>"
			<?php foreach ( $custom_attributes as $attr_key => $attr_value ) : ?>
				<?php echo esc_attr( $attr_key ); ?>="<?php echo esc_attr( $attr_value ); ?>"
			<?php endforeach; ?> />
		<?php echo wp_kses_post( $args['label'] ); ?>
		<?php if ( $args['required'] ) : ?>
			<abbr class="required"
				title="<?php echo esc_attr( __( 'Required Field', 'flexible-checkout-fields' ) ); ?>">*</abbr>
		<?php else : ?>
			<span class="optional">(<?php echo esc_html__( 'optional', 'woocommerce' ); ?>)</span>
		<?php endif; ?>
	</label>
</p>
