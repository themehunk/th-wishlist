<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handles data operations for the TH Wishlist.
 *
 * @class THWL_Data
 */
class THWL_Data {

    /**
     * Cache wishlist per request so we don't create/select it multiple times.
     *
     * @var object|null
     */
    private static $current_wishlist = null;

    /**
     * Get or create a wishlist for the current user.
     * Handles both logged-in users and guests using long-lived cookies.
     */
    public static function get_or_create_wishlist() {
        global $wpdb;

        // 🔁 If already resolved during this request, reuse it.
        if ( null !== self::$current_wishlist ) {
            return self::$current_wishlist;
        }

        if ( is_user_logged_in() ) {

            // --- Handle Logged-in User ---
            $user_id  = get_current_user_id();

            $wishlist = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}thwl_wishlists 
                     WHERE user_id = %d AND is_default = 1",
                    $user_id
                )
            );

            // 🔄 Merge guest wishlist (if any) into logged-in wishlist
            if ( isset( $_COOKIE['thwl_guest_uniqid'] ) ) {
                $guest_token    = sanitize_text_field( wp_unslash($_COOKIE['thwl_guest_uniqid']) );
                $guest_wishlist = $wpdb->get_row(
                    $wpdb->prepare(
                        "SELECT * FROM {$wpdb->prefix}thwl_wishlists WHERE session_id = %s",
                        $guest_token
                    )
                );

                if ( $guest_wishlist ) {

                    if ( ! $wishlist ) {
                        // No existing user wishlist → convert guest → user wishlist
                        $wpdb->update(
                            "{$wpdb->prefix}thwl_wishlists",
                            array(
                                'user_id'    => $user_id,
                                'session_id' => null,
                            ),
                            array( 'id' => $guest_wishlist->id ),
                            array( '%d', '%s' ),
                            array( '%d' )
                        );

                        $wishlist = $wpdb->get_row(
                            $wpdb->prepare(
                                "SELECT * FROM {$wpdb->prefix}thwl_wishlists WHERE id = %d",
                                $guest_wishlist->id
                            )
                        );

                    } else {
                        // Merge guest items into existing user wishlist
                        $guest_items = self::get_wishlist_items( $guest_wishlist->id );

                        foreach ( $guest_items as $item ) {
                            if ( ! self::is_product_in_wishlist( $wishlist->id, $item->product_id, $item->variation_id ) ) {
                                $wpdb->update(
                                    "{$wpdb->prefix}thwl_wishlist_items",
                                    array( 'wishlist_id' => $wishlist->id ),
                                    array( 'id' => $item->id ),
                                    array( '%d' ),
                                    array( '%d' )
                                );
                            } else {
                                $wpdb->delete(
                                    "{$wpdb->prefix}thwl_wishlist_items",
                                    array( 'id' => $item->id ),
                                    array( '%d' )
                                );
                            }
                        }

                        // Delete old guest wishlist row
                        $wpdb->delete(
                            "{$wpdb->prefix}thwl_wishlists",
                            array( 'id' => $guest_wishlist->id ),
                            array( '%d' )
                        );
                    }
                }

                // Clear guest cookie on login merge
                setcookie( 'thwl_guest_uniqid', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN );
                unset( $_COOKIE['thwl_guest_uniqid'] );
            }

            if ( ! $wishlist ) {
                // Create new default wishlist for user
                $wpdb->insert(
                    "{$wpdb->prefix}thwl_wishlists",
                    array(
                        'user_id'       => $user_id,
                        'wishlist_name' => __( 'My Wishlist', 'th-wishlist' ),
                        'wishlist_token'=> wp_generate_password( 32, false ),
                        'is_default'    => 1,
                        'privacy'       => 'public',
                    ),
                    array( '%d', '%s', '%s', '%d', '%s' )
                );

                $wishlist = $wpdb->get_row(
                    $wpdb->prepare(
                        "SELECT * FROM {$wpdb->prefix}thwl_wishlists WHERE id = %d",
                        $wpdb->insert_id
                    )
                );
            }

        } else {

            // --- Handle Guest User ---
            $guest_uniqid = isset( $_COOKIE['thwl_guest_uniqid'] )
                ? sanitize_text_field( wp_unslash($_COOKIE['thwl_guest_uniqid']) )
                : '';

            // ✅ If cookie not present, create it ONCE and also put it into $_COOKIE
            if ( empty( $guest_uniqid ) ) {
                $guest_uniqid = uniqid( 'guest_', true );

                // Long-lived cookie – 1 year
                setcookie(
                    'thwl_guest_uniqid',
                    $guest_uniqid,
                    time() + YEAR_IN_SECONDS,
                    COOKIEPATH,
                    COOKIE_DOMAIN
                );

                // Make it immediately available in this request
                $_COOKIE['thwl_guest_uniqid'] = $guest_uniqid;
            }

            // Try to fetch with that guest session id
            $wishlist = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}thwl_wishlists WHERE session_id = %s AND is_default = 1",
                    $guest_uniqid
                )
            );

            if ( ! $wishlist ) {
                // Create new guest default wishlist as 'public'
                $wpdb->insert(
                    "{$wpdb->prefix}thwl_wishlists",
                    array(
                        'session_id'    => $guest_uniqid,
                        'wishlist_name' => __( 'My Wishlist', 'th-wishlist' ),
                        'wishlist_token'=> wp_generate_password( 32, false ),
                        'is_default'    => 1,
                        'privacy'       => 'public',
                    ),
                    array( '%s', '%s', '%s', '%d', '%s' )
                );

                $wishlist = $wpdb->get_row(
                    $wpdb->prepare(
                        "SELECT * FROM {$wpdb->prefix}thwl_wishlists WHERE id = %d",
                        $wpdb->insert_id
                    )
                );
            }
        }

        // Cache for this request
        self::$current_wishlist = $wishlist;

        return $wishlist;
    }

    public static function get_wishlists( $args = [] ) {
    global $wpdb;

    $defaults = [
        'per_page' => 20,
        'paged'    => 1,
        'orderby'  => 'id',
        'order'    => 'DESC',
    ];
    $args = wp_parse_args( $args, $defaults );

    // Whitelisted ordering values
    $allowed_orderby = [
        'id'         => 'w.id',
        'user_login' => 'u.user_login',
        'item_count' => 'item_count',
    ];
    $allowed_order = [ 'ASC', 'DESC' ];

    $orderby = isset( $allowed_orderby[ $args['orderby'] ] ) ? $allowed_orderby[ $args['orderby'] ] : 'w.id';
    $order   = in_array( strtoupper( $args['order'] ), $allowed_order, true ) ? strtoupper( $args['order'] ) : 'DESC';

    $offset = ( $args['paged'] - 1 ) * absint( $args['per_page'] );

    $query = "
        SELECT w.*, u.user_login AS username, COUNT(i.id) AS item_count
        FROM {$wpdb->prefix}thwl_wishlists w
        LEFT JOIN {$wpdb->users} u ON w.user_id = u.ID
        LEFT JOIN {$wpdb->prefix}thwl_wishlist_items i ON w.id = i.wishlist_id
        GROUP BY w.id
        ORDER BY {$orderby} {$order}
        LIMIT %d OFFSET %d
    ";

    $prepared = $wpdb->prepare( 
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB
        $query, absint($args['per_page']), absint($offset) );

    /**
     * PHPCS: WordPress.DB.PreparedSQL.NotPrepared and PluginCheck.Security.DirectDB ignored
     * because `$orderby` and `$order` are fully sanitized via whitelist checks above.
     */
    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB
    return $wpdb->get_results( $prepared, ARRAY_A );
}


    public static function get_wishlist_count() {
        global $wpdb;
        return (int) $wpdb->get_var( "SELECT COUNT(id) FROM {$wpdb->prefix}thwl_wishlists" );
    }

    public static function delete_wishlist( $wishlist_id ) {
        global $wpdb;
        $wpdb->delete(
            "{$wpdb->prefix}thwl_wishlists",
            array( 'id' => $wishlist_id ),
            array( '%d' )
        );
        $wpdb->delete(
            "{$wpdb->prefix}thwl_wishlist_items",
            array( 'wishlist_id' => $wishlist_id ),
            array( '%d' )
        );
    }

    public static function get_wishlist_by_id( $wishlist_id ) {
        global $wpdb;

        if ( ! $wishlist_id ) {
            return null;
        }

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}thwl_wishlists WHERE id = %d",
                $wishlist_id
            )
        );
    }

    public static function get_wishlist_by_token( $token ) {
        global $wpdb;

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}thwl_wishlists WHERE wishlist_token = %s",
                $token
            )
        );
    }

    public static function get_wishlist_items( $wishlist_id ) {
        global $wpdb;

        if ( ! $wishlist_id ) {
            return [];
        }

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}thwl_wishlist_items WHERE wishlist_id = %d",
                $wishlist_id
            )
        );
    }

    public static function get_item( $item_id ) {
        global $wpdb;

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}thwl_wishlist_items WHERE id = %d",
                $item_id
            )
        );
    }

    public static function add_item( $wishlist_id, $product_id, $variation_id = 0 ) {
        global $wpdb;

        if ( self::is_product_in_wishlist( $wishlist_id, $product_id, $variation_id ) ) {
            return false;
        }

        $wpdb->insert(
            "{$wpdb->prefix}thwl_wishlist_items",
            array(
                'wishlist_id' => $wishlist_id,
                'product_id'  => $product_id,
                'variation_id'=> $variation_id,
                'quantity'    => 1,
            ),
            array( '%d', '%d', '%d', '%d' )
        );

        return $wpdb->insert_id;
    }

    public static function remove_item( $item_id ) {
        global $wpdb;

        return $wpdb->delete(
            "{$wpdb->prefix}thwl_wishlist_items",
            array( 'id' => $item_id ),
            array( '%d' )
        );
    }

    public static function update_item_quantity( $item_id, $quantity ) {
        global $wpdb;

        if ( $quantity < 1 ) {
            $quantity = 1;
        }

        return $wpdb->update(
            "{$wpdb->prefix}thwl_wishlist_items",
            array( 'quantity' => $quantity ),
            array( 'id' => $item_id ),
            array( '%d' ),
            array( '%d' )
        );
    }

    public static function is_product_in_wishlist( $wishlist_id, $product_id, $variation_id = 0 ) {
        global $wpdb;

        return $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) 
                 FROM {$wpdb->prefix}thwl_wishlist_items 
                 WHERE wishlist_id = %d AND product_id = %d AND variation_id = %d",
                $wishlist_id,
                $product_id,
                $variation_id
            )
        ) > 0;
    }
}