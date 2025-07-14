<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Renders the list table for wishlists on the admin screen.
 *
 * @class THWL_Table
 */
class THWL_Table extends WP_List_Table {

    public function __construct() {
        parent::__construct( [
            'singular' => __( 'Wishlist', 'th-wishlist' ),
            'plural'   => __( 'Wishlists', 'th-wishlist' ),
            'ajax'     => false,
        ] );
    }

    /**
     * Prepare the items for the table to process.
     */
    public function prepare_items() {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = [ $columns, $hidden, $sortable ];

        $this->process_bulk_action();

        $per_page = 20;
        $current_page = $this->get_pagenum();
        $total_items = THWL_Data::get_wishlist_count();

        $this->set_pagination_args( [
            'total_items' => $total_items,
            'per_page'    => $per_page,
        ] );

        // Verify nonce for sorting parameters
        if ( isset( $_GET['orderby'], $_GET['order'], $_GET['_wpnonce'] ) ) {
            if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'thw_wishlist_sort' ) ) {
                wp_die( esc_html__( 'Security check failed.', 'th-wishlist' ) );
            }
            $orderby = sanitize_key( wp_unslash( $_GET['orderby'] ) );
            $order = sanitize_key( wp_unslash( $_GET['order'] ) );
        } else {
            $orderby = 'created_at';
            $order = 'desc';
        }

        $this->items = THWL_Data::get_wishlists( [
            'per_page' => $per_page,
            'paged'    => $current_page,
            'orderby'  => $orderby,
            'order'    => $order,
        ] );
    }

    /**
     * Override the parent columns method. Defines the columns to show.
     */
    public function get_columns() {
        return [
            'cb'            => '<input type="checkbox" />',
            'wishlist_name' => __( 'Name', 'th-wishlist' ),
            'username'      => __( 'Username', 'th-wishlist' ),
            'privacy'       => __( 'Privacy', 'th-wishlist' ),
            'items'         => __( 'Items', 'th-wishlist' ),
            'created_at'    => __( 'Date', 'th-wishlist' ),
        ];
    }

    /**
     * Defines which columns are hidden
     */
    public function get_hidden_columns() {
        return [];
    }

    /**
     * Defines the columns that are sortable
     */
    public function get_sortable_columns() {
        return [
            'wishlist_name' => [ 'wishlist_name', false ],
            'username'      => [ 'username', false ],
            'created_at'    => [ 'created_at', true ],
        ];
    }

    /**
     * Render the checkbox column.
     */
    protected function column_cb( $item ) {
        return sprintf( '<input type="checkbox" name="wishlist[]" value="%d" />', $item['id'] );
    }
    
    /**
     * Render the "Name" column with actions.
     */
    protected function column_wishlist_name( $item ) {
        $actions = [];
        $wishlist_page_url = get_permalink( get_option( 'thwl_page_id' ) );

        // Always show the view link for the admin, as long as a wishlist page is set.
        if ( $wishlist_page_url ) {
            $actions['view'] = sprintf(
                '<a href="%s" target="_blank" aria-label="%s">View</a>',
                add_query_arg( 'wishlist_token', $item['wishlist_token'], $wishlist_page_url ),
                /* translators: %s : Wishlist Name */
                esc_attr( sprintf( __( 'View %s', 'th-wishlist' ), $item['wishlist_name'] ) )
            );
        }

        $delete_nonce = wp_create_nonce( 'thw_delete_wishlist' );
        $page = 'thwl-wishlists-tracking';
        $actions['delete'] = sprintf(
            '<a href="?page=%s&action=delete&wishlist=%d&_wpnonce=%s" onclick="return confirm(\'%s\')" aria-label="%s">Delete</a>',
            esc_attr( $page ),
            absint( $item['id'] ),
            esc_attr( $delete_nonce ),
            esc_js( __( 'Are you sure you want to permanently delete this wishlist?', 'th-wishlist' ) ),
            /* translators: %s : Wishlist Name */
            esc_attr( sprintf( __( 'Delete %s', 'th-wishlist' ), $item['wishlist_name'] ) )
        );
        
        return '<strong>' . esc_html( $item['wishlist_name'] ) . '</strong>' . $this->row_actions( $actions );
    }
    
    /**
     * Render the "Username" column.
     */
    protected function column_username( $item ) {
        if ( ! empty( $item['username'] ) ) {
            return esc_html( $item['username'] );
        }
        return '<em>' . __( 'Guest', 'th-wishlist' ) . '</em>';
    }

    /**
     * Render the "Items" column.
     */
    protected function column_items( $item ) {
        return absint( $item['item_count'] );
    }

    /**
     * Render the privacy column.
     */
    protected function column_privacy( $item ) {
        return ucfirst( esc_html( $item['privacy'] ) );
    }

    /**
     * Render other columns.
     */
    public function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'created_at':
                return date_i18n( get_option( 'date_format' ), strtotime( $item[$column_name] ) );
            default:
                return '---';
        }
    }
    
    /**
     * Defines the bulk actions.
     */
    public function get_bulk_actions() {
        return [
            'bulk-delete' => __( 'Delete', 'th-wishlist' ),
        ];
    }
    
    /**
     * Process bulk actions.
     */
    public function process_bulk_action() {
        // Single delete
        if ( 'delete' === $this->current_action() ) {
            if ( ! isset( $_GET['wishlist'], $_REQUEST['_wpnonce'] ) ) {
                wp_die( esc_html__( 'Invalid request.', 'th-wishlist' ) );
            }
            $nonce = sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) );
            if ( ! wp_verify_nonce( $nonce, 'thw_delete_wishlist' ) ) {
                wp_die( esc_html__( 'Security check failed.', 'th-wishlist' ) );
            }
           THWL_Data::delete_wishlist( absint( $_GET['wishlist'] ) );
            wp_safe_redirect( wp_get_referer() );
            exit;
        }

        // Bulk delete
        if ( ( isset( $_POST['action'] ) && 'bulk-delete' === $_POST['action'] ) || ( isset( $_POST['action2'] ) && 'bulk-delete' === $_POST['action2'] ) ) {
            if ( ! isset( $_POST['wishlist'], $_POST['_wpnonce'] ) ) {
                wp_die( esc_html__( 'Invalid bulk delete request.', 'th-wishlist' ) );
            }
            $nonce = sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) );
            if ( ! wp_verify_nonce( $nonce, 'thw_bulk_delete_wishlist' ) ) {
                wp_die( esc_html__( 'Security check failed.', 'th-wishlist' ) );
            }
            $delete_ids = array_map( 'absint', (array) wp_unslash( $_POST['wishlist'] ) );
            foreach ( $delete_ids as $id ) {
                THWL_Data::delete_wishlist( $id );
            }
            wp_safe_redirect( wp_get_referer() );
            exit;
        }
    }
}