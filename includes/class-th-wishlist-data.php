<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handles data operations for the TH Wishlist.
 *
 * @class TH_Wishlist_Data
 */
class TH_Wishlist_Data {

    /**
     * Get or create a wishlist for the current user.
     * Handles both logged-in users and guests using long-lived cookies.
     */
    public static function get_or_create_wishlist() {
        global $wpdb;

        if ( is_user_logged_in() ) {
            // --- Handle Logged-in User ---
            $user_id = get_current_user_id();
            $wishlist = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}thw_wishlists WHERE user_id = %d AND is_default = 1", $user_id ) );

            // Check for a guest wishlist cookie and merge it
            if ( isset($_COOKIE['thw_guest_uniqid']) ) {
                $guest_token = sanitize_text_field($_COOKIE['thw_guest_uniqid']);
                $guest_wishlist = $wpdb->get_row( $wpdb->prepare("SELECT * FROM {$wpdb->prefix}thw_wishlists WHERE session_id = %s", $guest_token) );
                
                if($guest_wishlist) {
                     if(!$wishlist) {
                        $wpdb->update("{$wpdb->prefix}thw_wishlists", ['user_id' => $user_id, 'session_id' => null], ['id' => $guest_wishlist->id]);
                        $wishlist = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}thw_wishlists WHERE id = %d", $guest_wishlist->id ) );
                    } else {
                        $guest_items = self::get_wishlist_items($guest_wishlist->id);
                        foreach($guest_items as $item) {
                             if(!self::is_product_in_wishlist($wishlist->id, $item->product_id, $item->variation_id)) {
                                $wpdb->update("{$wpdb->prefix}thw_wishlist_items", ['wishlist_id' => $wishlist->id], ['id' => $item->id]);
                            } else {
                                $wpdb->delete("{$wpdb->prefix}thw_wishlist_items", ['id' => $item->id]);
                            }
                        }
                        $wpdb->delete("{$wpdb->prefix}thw_wishlists", ['id' => $guest_wishlist->id]);
                    }
                }
                setcookie('thw_guest_uniqid', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN);
            }
            
            if ( ! $wishlist ) {
                // Create new wishlists as 'public' so they are shareable by default.
                $wpdb->insert("{$wpdb->prefix}thw_wishlists", [ 'user_id' => $user_id, 'wishlist_name' => __( 'My Wishlist', 'th-wishlist' ), 'wishlist_token' => wp_generate_password( 32, false ), 'is_default' => 1, 'privacy' => 'public' ]);
                $wishlist = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}thw_wishlists WHERE id = %d", $wpdb->insert_id ) );
            }

        } else {
            // --- Handle Guest User ---
            $guest_uniqid = isset( $_COOKIE['thw_guest_uniqid'] ) ? sanitize_text_field( $_COOKIE['thw_guest_uniqid'] ) : null;
            $wishlist = null;

            if ( ! empty( $guest_uniqid ) ) {
                $wishlist = $wpdb->get_row( $wpdb->prepare("SELECT * FROM {$wpdb->prefix}thw_wishlists WHERE session_id = %s", $guest_uniqid) );
            }

            if ( ! $wishlist ) {
                $new_uniqid = uniqid( 'guest_', true );
                // Create new guest wishlists as 'public' so they are shareable by default.
                $wpdb->insert("{$wpdb->prefix}thw_wishlists", [ 'session_id' => $new_uniqid, 'wishlist_name' => __( 'My Wishlist', 'th-wishlist' ), 'wishlist_token' => wp_generate_password( 32, false ), 'is_default' => 1, 'privacy' => 'public' ]);
                $wishlist = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}thw_wishlists WHERE id = %d", $wpdb->insert_id ) );
                
                setcookie('thw_guest_uniqid', $new_uniqid, time() + (86400 * 365), COOKIEPATH, COOKIE_DOMAIN);
            }
        }
        
        return $wishlist;
    }

    public static function get_wishlists( $args = [] ) {
        global $wpdb;
        $defaults = [ 'per_page' => 20, 'paged' => 1, 'orderby' => 'id', 'order' => 'DESC' ];
        $args = wp_parse_args( $args, $defaults );
        $offset = ( $args['paged'] - 1 ) * $args['per_page'];
        
        $sql = "SELECT w.*, u.user_login as username, COUNT(i.id) as item_count
                FROM {$wpdb->prefix}thw_wishlists w
                LEFT JOIN {$wpdb->users} u ON w.user_id = u.ID
                LEFT JOIN {$wpdb->prefix}thw_wishlist_items i ON w.id = i.wishlist_id
                GROUP BY w.id
                ORDER BY {$args['orderby']} {$args['order']}
                LIMIT %d OFFSET %d";

        return $wpdb->get_results( $wpdb->prepare( $sql, $args['per_page'], $offset ), ARRAY_A );
    }

    public static function get_wishlist_count() {
        global $wpdb;
        return (int) $wpdb->get_var( "SELECT COUNT(id) FROM {$wpdb->prefix}thw_wishlists" );
    }

    public static function delete_wishlist( $wishlist_id ) {
        global $wpdb;
        $wpdb->delete( "{$wpdb->prefix}thw_wishlists", array( 'id' => $wishlist_id ) );
        $wpdb->delete( "{$wpdb->prefix}thw_wishlist_items", array( 'wishlist_id' => $wishlist_id ) );
    }
    
    public static function get_wishlist_by_token( $token ) { global $wpdb; return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}thw_wishlists WHERE wishlist_token = %s", $token ) ); }
    public static function get_wishlist_items( $wishlist_id ) { global $wpdb; if(!$wishlist_id) return []; return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}thw_wishlist_items WHERE wishlist_id = %d", $wishlist_id ) ); }
    public static function get_item( $item_id ) { global $wpdb; return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}thw_wishlist_items WHERE id = %d", $item_id ) ); }
    public static function add_item( $wishlist_id, $product_id, $variation_id = 0 ) { global $wpdb; if ( self::is_product_in_wishlist( $wishlist_id, $product_id, $variation_id ) ) { return false; } $wpdb->insert( "{$wpdb->prefix}thw_wishlist_items", [ 'wishlist_id' => $wishlist_id, 'product_id' => $product_id, 'variation_id' => $variation_id, 'quantity' => 1 ] ); return $wpdb->insert_id; }
    public static function remove_item( $item_id ) { global $wpdb; $wpdb->delete( "{$wpdb->prefix}thw_wishlist_items", array( 'id' => $item_id ) ); }
    public static function update_item_quantity( $item_id, $quantity ) { global $wpdb; if($quantity < 1) $quantity = 1; $wpdb->update( "{$wpdb->prefix}thw_wishlist_items", [ 'quantity' => $quantity ], [ 'id' => $item_id ] ); }
    public static function is_product_in_wishlist( $wishlist_id, $product_id, $variation_id = 0 ) { global $wpdb; return $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}thw_wishlist_items WHERE wishlist_id = %d AND product_id = %d AND variation_id = %d", $wishlist_id, $product_id, $variation_id ) ) > 0; }
}
