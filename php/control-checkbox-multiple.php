<?php /**
 * Multiple checkbox customize control class.
 *
 * @since  1.0.0
 * @access public
 */
class lqx_Customize_Control_Checkbox_Multiple extends WP_Customize_Control {

    /**
     * The type of customize control being rendered.
     *
     * @since  1.0.0
     * @access public
     * @var    string
     */
    public $type = 'checkbox-multiple';

    /**
     * Enqueue scripts/styles.
     *
     * @since  1.0.0
     * @access public
     * @return void
     */
    public function enqueue() {
        wp_enqueue_script( 'lqx-customize-controls', ( get_template_directory_uri() ) . '/js/customize-controls.js', array( 'jquery' ) );
    }

    /**
     * Displays the control content.
     *
     * @since  1.0.0
     * @access public
     * @return void
     */
    public function render_content() {
        if ( empty( $this->choices ) )
            return; ?>

        <?php if ( !empty( $this->label ) ) : ?>
            <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
        <?php endif; ?>

        <?php if ( !empty( $this->description ) ) : ?>
            <span class="description customize-control-description"><?php echo $this->description; ?></span>
        <?php endif; ?>

        <?php $multi_values = !is_array( $this->value() ) ? explode( ',', $this->value() ) : $this->value(); ?>

        <ul>
        	<?php $count = 0;?>
            <?php foreach ( $this->choices as $value => $label ) : ?>

                <li>
                    <label>
                        <input type="checkbox" value="<?php echo esc_attr( $value ); ?>" id ="fluid_screen<?php echo($count); ?>" name="fluid_screen[]" <?php if (!empty($multi_values[0])): checked( in_array( $value, $multi_values ) ); endif; ?> /> 
                        <?php echo esc_html( $label ); ?>
                    </label>
                </li>
			<?php $count ++; ?>
            <?php endforeach; ?>
        </ul>

        <input type="hidden" <?php $this->link(); ?> value= "<?php echo sanitize_text_field( $this->value() );?>" />
    <?php }
}