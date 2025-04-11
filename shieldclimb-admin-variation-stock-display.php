<?php

/**
 * Plugin Name: ShieldClimb – Admin Variation Stock Display for WooCommerce
 * Plugin URI: https://shieldclimb.com/free-woocommerce-plugins/admin-variation-stock-display/
 * Description: Admin Variation Stock Display lets you track variation stock easily in your admin panel. Get a clear overview of product variations in WooCommerce.
 * Version: 1.0.2
 * Requires Plugins: woocommerce
 * Requires at least: 5.8
 * Tested up to: 6.8
 * WC requires at least: 5.8
 * WC tested up to: 9.8.1
 * Requires PHP: 7.2
 * Author: shieldclimb.com
 * Author URI: https://shieldclimb.com/about-us/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Add new column to product variations table
function shieldclimb_admin_variation_stock_display_column( $columns ) {
    $columns['variation_stock'] = esc_html__( 'Stock Quantity', 'shieldclimb-admin-variation-stock-display' );
    return $columns;
}
add_filter( 'manage_edit-product_columns', 'shieldclimb_admin_variation_stock_display_column', 20 );

// Populate stock quantity data for variation products
function shieldclimb_admin_variation_stock_display_column_content( $column, $post_id ) {
    if ( 'variation_stock' === $column ) {
        $product = wc_get_product( $post_id );
        if ( $product && $product->is_type( 'variable' ) ) {
            $variations = $product->get_available_variations();
            $stock_quantities = array();
            foreach ( $variations as $variation ) {
                $variation_obj = wc_get_product( $variation['variation_id'] );
                if ( $variation_obj ) {
                    $stock_quantity = $variation_obj->get_stock_quantity();
                    $stock_quantities[] = ( $stock_quantity !== null ) ? esc_html( $stock_quantity ) : esc_html__( 'N/A', 'shieldclimb-admin-variation-stock-display' );
                }
            }
            echo esc_html( implode( ', ', $stock_quantities ) );
        } else {
            echo esc_html( '-' );
        }
    }
}
add_action( 'manage_product_posts_custom_column', 'shieldclimb_admin_variation_stock_display_column_content', 20, 2 );

// Make the stock quantity column sortable
function shieldclimb_admin_variation_stock_display_column_sortable( $columns ) {
    $columns['variation_stock'] = 'stock';
    return $columns;
}
add_filter( 'manage_edit-product_sortable_columns', 'shieldclimb_admin_variation_stock_display_column_sortable' );

// Optimized sorting to avoid slow query warnings with nonce verification
function shieldclimb_admin_variation_stock_display_column_orderby( $query ) {
    global $pagenow;

    if (is_admin() && 'edit.php' === $pagenow && isset($_GET['orderby']) && 'stock' === $_GET['orderby']) {
        // Ensure nonce is properly sanitized before verification
        $nonce = isset($_GET['_wpnonce']) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';

        // Verify nonce before processing request
        if (!wp_verify_nonce($nonce, 'shieldclimb_stock_order')) {
            wp_die( esc_html__( 'Security check failed', 'shieldclimb-admin-variation-stock-display' ) );
        }

        $query->set('orderby', 'meta_value_num');
        $query->set('meta_key', '_stock');
        $query->set('meta_type', 'NUMERIC'); // Ensures numeric sorting
    }
}
add_action('pre_get_posts', 'shieldclimb_admin_variation_stock_display_column_orderby');

// Add nonce field to product admin sorting form
function shieldclimb_admin_variation_stock_add_nonce() {
    if (is_admin()) {
        echo '<input type="hidden" name="_wpnonce" value="' . esc_attr(wp_create_nonce('shieldclimb_stock_order')) . '">';
    }
}
add_action('manage_product_posts_custom_column', 'shieldclimb_admin_variation_stock_add_nonce');

?>