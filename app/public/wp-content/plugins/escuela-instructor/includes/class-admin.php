<?php
/**
 * Admin panel for managing inscriptions
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Escuela_Instructor_Admin' ) ) {
    class Escuela_Instructor_Admin {
        public static function init() {
            add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
            add_action( 'admin_post_escuela_instructor_approve', array( __CLASS__, 'handle_approve' ) );
            add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
            add_action( 'admin_notices', array( __CLASS__, 'maybe_admin_notices' ) );
        }

        public static function register_menu() {
            add_menu_page(
                __( 'Inscripciones', 'escuela-instructor' ),
                'Inscripciones',
                Escuela_Instructor::CAPABILITY,
                'escuela-inscripciones',
                array( __CLASS__, 'render_admin_page' ),
                'dashicons-groups',
                26
            );
        }

        public static function enqueue_assets( $hook ) {
            $page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';

            if ( 'escuela-inscripciones' === $page ) {
                wp_register_style( 'escuela-instructor-admin', plugin_dir_url( dirname( __FILE__ ) ) . 'assets/admin.css', array(), '0.1.0' );
                wp_enqueue_style( 'escuela-instructor-admin' );
            }
        }

        public static function maybe_admin_notices() {
            if ( ! isset( $_GET['page'] ) || 'escuela-inscripciones' !== $_GET['page'] ) {
                return;
            }

            if ( isset( $_GET['escuela_notice'] ) && 'approved' === $_GET['escuela_notice'] ) {
                echo '<div class="notice notice-success is-dismissible"><p>Inscripción aprobada correctamente.</p></div>';
            }

            if ( isset( $_GET['escuela_error'] ) ) {
                $err = esc_html( sanitize_text_field( wp_unslash( $_GET['escuela_error'] ) ) );
                echo '<div class="notice notice-error is-dismissible"><p>Error: ' . $err . '</p></div>';
            }
        }

        public static function render_admin_page() {
            if ( ! current_user_can( Escuela_Instructor::CAPABILITY ) ) {
                wp_die( 'No tenés permisos para ver esta página.' );
            }

            $status = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';
            $paged  = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
            $per_page = 20;

            global $wpdb;
            $table = $wpdb->prefix . 'escuela_inscripciones';

            if ( $status ) {
                $total = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(1) FROM {$table} WHERE status = %s", $status ) );
                $rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE status = %s ORDER BY created_at DESC LIMIT %d OFFSET %d", $status, $per_page, ( $paged - 1 ) * $per_page ), ARRAY_A );
            } else {
                $total = (int) $wpdb->get_var( "SELECT COUNT(1) FROM {$table}" );
                $rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} ORDER BY created_at DESC LIMIT %d OFFSET %d", $per_page, ( $paged - 1 ) * $per_page ), ARRAY_A );
            }

            $total_pages = $total > 0 ? ceil( $total / $per_page ) : 1;

            $base_url = admin_url( 'admin.php?page=escuela-inscripciones' );
            if ( $status ) {
                $base_url = add_query_arg( 'status', $status, $base_url );
            }

            echo '<div class="wrap">';
            echo '<h1>Inscripciones</h1>';

            // Status filters
            echo '<p class="escuela-filters">';
            $all_classes = empty( $status ) ? 'current' : '';
            $pending_classes = 'pending' === $status ? 'current' : '';
            $active_classes = 'active' === $status ? 'current' : '';

            echo '<a href="' . esc_url( admin_url( 'admin.php?page=escuela-inscripciones' ) ) . '" class="button ' . esc_attr( $all_classes ) . '">Todos</a> ';
            echo '<a href="' . esc_url( add_query_arg( 'status', 'pending', admin_url( 'admin.php?page=escuela-inscripciones' ) ) ) . '" class="button ' . esc_attr( $pending_classes ) . '">Pendientes</a> ';
            echo '<a href="' . esc_url( add_query_arg( 'status', 'active', admin_url( 'admin.php?page=escuela-inscripciones' ) ) ) . '" class="button ' . esc_attr( $active_classes ) . '">Aprobadas</a> ';
            echo '</p>';

            echo '<table class="wp-list-table widefat fixed striped escuela-inscripciones-table">';
            echo '<thead><tr><th>Usuario</th><th>Curso</th><th>Estado</th><th>Fecha</th><th>Acciones</th></tr></thead>';
            echo '<tbody>';

            if ( empty( $rows ) ) {
                echo '<tr><td colspan="5">No hay inscripciones.</td></tr>';
            } else {
                foreach ( $rows as $row ) {
                    $user_id = intval( $row['user_id'] );
                    $course_id = intval( $row['course_id'] );
                    $user = get_userdata( $user_id );
                    $user_label = $user ? esc_html( $user->display_name ) . ' (' . esc_html( $user->user_login ) . ')' : 'Usuario #' . $user_id;
                    $user_link = admin_url( 'user-edit.php?user_id=' . $user_id );
                    $course_title = get_the_title( $course_id );
                    $course_link = get_edit_post_link( $course_id );

                    echo '<tr>';
                    echo '<td><a href="' . esc_url( $user_link ) . '">' . $user_label . '</a></td>';
                    echo '<td><a href="' . esc_url( $course_link ) . '">' . esc_html( $course_title ) . '</a></td>';
                    echo '<td><span class="escuela-badge escuela-badge-' . esc_attr( $row['status'] ) . '">' . esc_html( $row['status'] ) . '</span></td>';
                    echo '<td>' . esc_html( $row['created_at'] ) . '</td>';
                    echo '<td>';

                    if ( 'pending' === $row['status'] ) {
                        // Approve form
                        echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="display:inline">';
                        wp_nonce_field( 'escuela_approve_' . intval( $row['id'] ) );
                        echo '<input type="hidden" name="action" value="escuela_instructor_approve" />';
                        echo '<input type="hidden" name="inscripcion_id" value="' . intval( $row['id'] ) . '" />';
                        echo '<input type="hidden" name="redirect_to" value="' . esc_attr( $base_url ) . '" />';
                        echo '<input type="submit" class="button button-primary" value="Aprobar" />';
                        echo '</form>';
                    } else {
                        echo '&mdash;';
                    }

                    echo '</td>';
                    echo '</tr>';
                }
            }

            echo '</tbody>';
            echo '</table>';

            // Pagination
            if ( $total_pages > 1 ) {
                $page_links = paginate_links( array(
                    'base' => add_query_arg( 'paged', '%#%' ),
                    'format' => '',
                    'current' => $paged,
                    'total' => $total_pages,
                    'add_args' => array( 'status' => $status ),
                ) );

                if ( $page_links ) {
                    echo '<div class="tablenav"><div class="tablenav-pages" style="margin:1em 0">' . $page_links . '</div></div>';
                }
            }

            echo '</div>'; // .wrap
        }

        public static function handle_approve() {
            if ( ! isset( $_POST['inscripcion_id'] ) ) {
                wp_safe_redirect( admin_url( 'admin.php?page=escuela-inscripciones&escuela_error=missing_id' ) );
                exit;
            }

            $ins_id = intval( $_POST['inscripcion_id'] );

            // nonce check
            if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['_wpnonce'] ), 'escuela_approve_' . $ins_id ) ) {
                wp_safe_redirect( admin_url( 'admin.php?page=escuela-inscripciones&escuela_error=invalid_nonce' ) );
                exit;
            }

            if ( ! current_user_can( Escuela_Instructor::CAPABILITY ) ) {
                wp_safe_redirect( admin_url( 'admin.php?page=escuela-inscripciones&escuela_error=permission_denied' ) );
                exit;
            }

            $res = Escuela_Instructor_Service::aprobar_inscripcion( $ins_id, get_current_user_id() );

            $redirect_to = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : admin_url( 'admin.php?page=escuela-inscripciones' );

            if ( is_wp_error( $res ) ) {
                $code = $res->get_error_code();
                $redirect = add_query_arg( 'escuela_error', $code, $redirect_to );
                wp_safe_redirect( $redirect );
                exit;
            }

            $redirect = add_query_arg( 'escuela_notice', 'approved', $redirect_to );
            wp_safe_redirect( $redirect );
            exit;
        }
    }
}
