<?php
/**
 * Payment meta box for LearnDash courses
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Escuela_Instructor_Payment_Meta' ) ) {
    class Escuela_Instructor_Payment_Meta {
        const NONCE_ACTION = 'escuela_payment_meta';
        const NONCE_NAME   = 'escuela_payment_meta_nonce';

        public static function init() {
            add_action( 'add_meta_boxes', array( __CLASS__, 'register_meta_box' ) );
            add_action( 'save_post', array( __CLASS__, 'save_meta' ), 10, 2 );
        }

        public static function register_meta_box() {
            add_meta_box(
                'escuela-payment-instructions',
                __( 'Instrucciones de pago', 'escuela-instructor' ),
                array( __CLASS__, 'render_meta_box' ),
                'sfwd-courses',
                'side',
                'default'
            );
        }

        public static function render_meta_box( $post ) {
            // Security nonce
            wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );

            $cbu_key     = '_escuela_payment_cbu';
            $alias_key   = '_escuela_payment_alias';
            $banco_key   = '_escuela_payment_banco';
            $titular_key = '_escuela_payment_titular';

            // Fallback to older non-namespaced meta for backwards compatibility
            $cbu     = get_post_meta( $post->ID, $cbu_key, true );
            if ( empty( $cbu ) ) {
                $cbu = get_post_meta( $post->ID, 'cbu', true );
            }

            $alias = get_post_meta( $post->ID, $alias_key, true );
            if ( empty( $alias ) ) {
                $alias = get_post_meta( $post->ID, 'alias', true );
            }

            $banco = get_post_meta( $post->ID, $banco_key, true );
            if ( empty( $banco ) ) {
                $banco = get_post_meta( $post->ID, 'banco', true );
            }

            $titular = get_post_meta( $post->ID, $titular_key, true );
            if ( empty( $titular ) ) {
                $titular = get_post_meta( $post->ID, 'titular', true );
            }

            $can_edit_post = current_user_can( 'edit_post', $post->ID );
            $can_manage    = class_exists( 'Escuela_Instructor' ) ? user_can( get_current_user_id(), Escuela_Instructor::CAPABILITY ) : false;

            // If the user cannot edit the post or lacks the plugin capability, show read-only
            $editable = ( $can_edit_post && $can_manage );

            ?>
            <p>
                <label for="escuela_payment_cbu"><strong><?php esc_html_e( 'CBU', 'escuela-instructor' ); ?></strong></label>
                <?php if ( $editable ) : ?>
                    <input type="text" id="escuela_payment_cbu" name="escuela_payment_cbu" value="<?php echo esc_attr( $cbu ); ?>" class="widefat" />
                <?php else : ?>
                    <div><?php echo esc_html( $cbu ); ?></div>
                <?php endif; ?>
            </p>

            <p>
                <label for="escuela_payment_alias"><strong><?php esc_html_e( 'Alias', 'escuela-instructor' ); ?></strong></label>
                <?php if ( $editable ) : ?>
                    <input type="text" id="escuela_payment_alias" name="escuela_payment_alias" value="<?php echo esc_attr( $alias ); ?>" class="widefat" />
                <?php else : ?>
                    <div><?php echo esc_html( $alias ); ?></div>
                <?php endif; ?>
            </p>

            <p>
                <label for="escuela_payment_banco"><strong><?php esc_html_e( 'Banco', 'escuela-instructor' ); ?></strong></label>
                <?php if ( $editable ) : ?>
                    <input type="text" id="escuela_payment_banco" name="escuela_payment_banco" value="<?php echo esc_attr( $banco ); ?>" class="widefat" />
                <?php else : ?>
                    <div><?php echo esc_html( $banco ); ?></div>
                <?php endif; ?>
            </p>

            <p>
                <label for="escuela_payment_titular"><strong><?php esc_html_e( 'Titular', 'escuela-instructor' ); ?></strong></label>
                <?php if ( $editable ) : ?>
                    <input type="text" id="escuela_payment_titular" name="escuela_payment_titular" value="<?php echo esc_attr( $titular ); ?>" class="widefat" />
                <?php else : ?>
                    <div><?php echo esc_html( $titular ); ?></div>
                <?php endif; ?>
            </p>
            <?php
        }

        public static function save_meta( $post_id, $post ) {
            // Only run on sfwd-courses post type
            if ( 'sfwd-courses' !== $post->post_type ) {
                return;
            }

            // Autosave / revision check
            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
                return;
            }

            // Verify nonce
            if ( empty( $_POST[ self::NONCE_NAME ] ) || ! wp_verify_nonce( wp_unslash( $_POST[ self::NONCE_NAME ] ), self::NONCE_ACTION ) ) {
                return;
            }

            // Capability checks
            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return;
            }

            if ( ! ( class_exists( 'Escuela_Instructor' ) && user_can( get_current_user_id(), Escuela_Instructor::CAPABILITY ) ) ) {
                return;
            }

            $fields = array(
                'escuela_payment_cbu'     => '_escuela_payment_cbu',
                'escuela_payment_alias'   => '_escuela_payment_alias',
                'escuela_payment_banco'   => '_escuela_payment_banco',
                'escuela_payment_titular' => '_escuela_payment_titular',
            );

            foreach ( $fields as $input_name => $meta_key ) {
                if ( isset( $_POST[ $input_name ] ) ) {
                    $value = sanitize_text_field( wp_unslash( $_POST[ $input_name ] ) );
                    update_post_meta( $post_id, $meta_key, $value );
                }
            }
        }
    }
}
