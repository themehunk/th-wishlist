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
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Direct query required for custom table.
            $wishlist = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}thw_wishlists WHERE user_id = %d AND is_default = 1", $user_id ) );

            // Check for a guest wishlist cookie and merge it
            if ( isset( $_COOKIE['thw_guest_uniqid'] ) ) {
                $guest_token = sanitize_text_field( wp_unslash( $_COOKIE['thw_guest_uniqid'] ) );
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Direct query required for custom table.
                $guest_wishlist = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}thw_wishlists WHERE session_id = %s", $guest_token ) );
                
                if ( $guest_wishlist ) {
                    if ( ! $wishlist ) {
                        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Direct query required for custom table.
                        $wpdb->update( "{$wpdb->prefix}thw_wishlists", [ 'user_id' => $user_id, 'session_id' => null ], [ 'id' => $guest_wishlist->id ] );
                        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Direct query required for custom table.
                        $wishlist = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}thw_wishlists WHERE id = %d", $guest_wishlist->id ) );
                    } else {
                        $guest_items = self::get_wishlist_items( $guest_wishlist->id );
                        foreach ( $guest_items as $item ) {
                            if ( ! self::is_product_in_wishlist( $wishlist->id, $item->product_id, $item->variation_id ) ) {
                                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Direct query required for custom table.
                                $wpdb->update( "{$wpdb->prefix}thw_wishlist_items", [ 'wishlist_id' => $wishlist->id ], [ 'id' => $item->id ] );
                            } else {
                                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Direct query required for custom table.
                                $wpdb->delete( "{$wpdb->prefix}thw_wishlist_items", [ 'id' => $item->id ] );
                            }
                        }
                        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Direct query required for custom table.
                        $wpdb->delete( "{$wpdb->prefix}thw_wishlists", [ 'id' => $guest_wishlist->id ] );
                        // Invalidate cache for user wishlist after merging guest wishlist.
                        wp_cache_delete( "thw_wishlist_user_{$user_id}", 'th_wishlist' );
                    }
                }
                setcookie( 'thw_guest_uniqid', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN );
            }
            
            if ( ! $wishlist ) {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Direct query required for custom table.
                $wpdb->insert( "{$wpdb->prefix}thw_wishlists", [ 'user_id' => $user_id, 'wishlist_name' => __( 'My Wishlist', 'th-wishlist' ), 'wishlist_token' => wp_generate_password( 32, false ), 'is_default' => 1, 'privacy' => 'public' ] );
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Direct query required for custom table.
                $wishlist = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}thw_wishlists WHERE id = %d", $wpdb->insert_id ) );
                // Cache the new wishlist.
                wp_cache_set( "thw_wishlist_user_{$user_id}", $wishlist, 'th_wishlist', HOUR_IN_SECONDS );
            }

        } else {
            // --- Handle Guest User ---
            $guest_uniqid = isset( $_COOKIE['thw_guest_uniqid'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['thw_guest_uniqid'] ) ) : null;
            $wishlist = null;

            if ( ! empty( $guest_uniqid ) ) {
                $wishlist = self::get_wishlist_by_token( $guest_uniqid );
            }

            if ( ! $wishlist ) {
                $new_uniqid = uniqid( 'guest_', true );
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Direct query required for custom table.
                $wpdb->insert( "{$wpdb->prefix}thw_wishlists", [ 'session_id' => $new_uniqid, 'wishlist_name' => __( 'My Wishlist', 'th-wishlist' ), 'wishlist_token' => wp_generate_password( 32, false ), 'is_default' => 1, 'privacy' => 'public' ] );
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Direct query required for custom table.
                $wishlist = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}thw_wishlists WHERE id = %d", $wpdb->insert_id ) );
                // Cache the new guest wishlist.
                wp_cache_set( "thw_wishlist_guest_{$new_uniqid}", $wishlist, 'th_wishlist', 30 * DAY_IN_SECONDS );
                
                setcookie( 'thw_guest_uniqid', $new_uniqid, time() + ( 86400 * 365 ), COOKIEPATH, COOKIE_DOMAIN );
            }
        }
        
        return $wishlist;
    }

    public static function get_wishlists( $args = [] ) {
        global $wpdb;
        $defaults = [ 'per_page' => 20, 'paged' => 1, 'orderby' => 'id', 'order' => 'DESC' ];
        $args = wp_parse_args( $args, $defaults );
        $offset = ( $args['paged'] - 1 ) * $args['per_page'];

        // Generate cache key based on arguments.
        $cache_key = 'thw_wishlists_' . md5( serialize( $args ) );
        $wishlists = wp_cache_get( $cache_key, 'th_wishlist' );

        if ( false === $wishlists ) {
            $allowed_orderby = [ 'id', 'wishlist_name', 'user_id', 'created_at' ];
            $orderby = in_array( $args['orderby'], $allowed_orderby, true ) ? $args['orderby'] : 'id';
            $order = strtoupper( $args['order'] ) === 'ASC' ? 'ASC' : 'DESC';

            $sql = "SELECT w.*, u.user_login as username, COUNT(i.id) as item_count
                    FROM {$wpdb->prefix}thw_wishlists w
                    LEFT JOIN {$wpdb->users} u ON w.user_id = u.ID
                    LEFT JOIN {$wpdb->prefix}thw_wishlist_items i ON w.id = i.wishlist_id
                    GROUP BY w.id
                    ORDER BY %s %s
                    LIMIT %d OFFSET %d";

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Direct query required for custom table.
            $wishlists = $wpdb->get_results( $wpdb->prepare( $sql, $orderby, $order, $args['per_page'], $offset ), ARRAY_A );
            wp_cache_set( $cache_key, $wishlists, 'th_wishlist', HOUR_IN_SECONDS );
        }

        return $wishlists;
    }

    public static function get_wishlist_count() {
        global $wpdb;
        $cache_key = 'thw_wishlist_count';
        $count = wp_cache_get( $cache_key, 'th_wishlist' );

        if ( false === $count ) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Direct query required for custom table; caching implemented.
            $count = (int) $wpdb->get_var( "SELECT COUNT(id) FROM {$wpdb->prefix}thw_wishlists" );
            wp_cache_set( $cache_key, $count, 'th_wishlist', HOUR_IN_SECONDS );
        }

        return $count;
    }

    public static function delete_wishlist( $wishlist_id ) {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Direct query required for custom table.
        $wpdb->delete( "{$wpdb->prefix}thw_wishlists", [ 'id' => $wishlist_id ] );
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Direct query required for custom table.
        $wpdb->delete( "{$wpdb->prefix}thw_wishlist_items", [ 'wishlist_id' => $wishlist_id ] );
        // Invalidate caches.
        wp_cache_delete( "thw_wishlist_{$wishlist_id}", 'th_wishlist' );
        wp_cache_delete( 'thw_wishlist_count', 'th_wishlist' );
    }
    
    public static function get_wishlist_by_token( $token ) {
        global $wpdb;
        $cache_key = 'thw_wishlist_token_' . md5( $token );
        $wishlist = wp_cache_get( $cache_key, 'th_wishlist' );

        if ( false === $wishlist ) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Direct query required for custom table.
            $wishlist = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}thw_wishlists WHERE wishlist_token = %s", $token ) );
            wp_cache_set( $cache_key, $wishlist, 'th_wishlist', HOUR_IN_SECONDS );
        }
        return $wishlist;
    }
    
    public static function get_wishlist_items( $wishlist_id ) {
        global $wpdb;
        if ( ! $wishlist_id ) {
            return [];
        }
        $cache_key = 'thw_wishlist_items_' . $wishlist_id;
        $items = wp_cache_get( $cache_key, 'th_wishlist' );

        if ( false === $items ) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Direct query required for custom table.
            $items = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}thw_wishlist_items WHERE wishlist_id = %d", $wishlist_id ) );
            wp_cache_set( $cache_key, $items, 'th_wishlist', HOUR_IN_SECONDS );
        }
        return $items;
    }
    
    public static function get_item( $item_id ) {
        global $wpdb;
        $cache_key = 'thw_wishlist_item_' . $item_id;
        $item = wp_cache_get( $cache_key, 'th_wishlist' );

        if ( false === $item ) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Direct query required for custom table.
            $item = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}thw_wishlist_items WHERE id = %d", $item_id ) );
            wp_cache_set( $cache_key, $item, 'th_wishlist', HOUR_IN_SECONDS );
        }
        return $item;
    }
    
    public static function add_item( $wishlist_id, $product_id, $variation_id = 0 ) {
        global $wpdb;
        if ( self::is_product_in_wishlist( $wishlist_id, $product_id, $variation_id ) ) {
            return false;
        }
        //_.

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Direct query required for custom table.
        $wpdb->insert( "{$wpdb->prefix}thw_wishlist_items", [ 'wishlist_id' => $wishlist_id, 'product_id' => $product_id, 'variation_id' => $variation_id, 'quantity' => 1 ] );
        $insert_id = $wpdb->insert_id;
        // Invalidate cache for wishlist items.
        wp_cache_delete( "thw_wishlist_items_{$wishlist_id}", 'th_wishlist' );
        return $insert_id;
    }
    
    public static function remove_item( $item_id ) {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Direct query required for custom table.
        $wpdb->delete( "{$wpdb->prefix}thw_wishlist_items", [ 'id' => $item_id ] );
        // Invalidate cache for the item and related wishlist.
        $item = self::get_item( $item_id );
        if ( $item ) {
            wp_cache_delete( "thw_wishlist_items_{$item->wishlist_id}", 'th_wishlist' );
        }
        wp_cache_delete( "thw_wishlist_item_{$item_id}", 'th_wishlist' );
    }
    
    public static function update_item_quantity( $item_id, $quantity ) {
        global $wpdb;
        if ( $quantity < 1 ) {
            $quantity = 1;
        }
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Direct query required for custom table.
        $wpdb->update( "{$wpdb->prefix}thw_wishlist_items", [ 'quantity' => $quantity ], [ 'id' => $item_id ] );
        // Invalidate cache for the item and related wishlist.
        $item = self::get_item( $item_id );
        if ( $item ) {
            wp_cache_delete( "thw_wishlist_items_{$item->wishlist_id}", 'th_wishlist' );
        }
        wp_cache_delete( "thw_wishlist_item_{$item_id}", 'th_wishlist' );
    }
    
    public static function is_product_in_wishlist( $wishlist_id, $product_id, $variation_id = 0 ) {
        global $wpdb;
        $cache_key = 'thw_product_in_wishlist_' . $wishlist_id . '_' . $product_id . '_' . $variation_id;
        $exists = wp_cache_get( $cache_key, 'th_wishlist' );

        if ( false === $exists ) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Direct query required for custom table.
            $exists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}thw_wishlist_items WHERE wishlist_id = %d AND product_id = %d AND variation_id = %d", $wishlist_id, $product_id, $variation_id ) ) > 0;
            wp_cache_set( $cache_key, $exists, 'th_wishlist', HOUR_IN_SECONDS );
        }
        return $exists;
    }
}