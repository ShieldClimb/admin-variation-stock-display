<?php

/**
 * Plugin Name: ShieldClimb – Admin Variation Stock Display for WooCommerce
 * Plugin URI: https://shieldclimb.com/free-woocommerce-plugins/admin-variation-stock-display/
 * Description: Admin Variation Stock Display lets you track variation stock easily in your admin panel. Get a clear overview of product variations in WooCommerce.
 * Version: 1.0.0
 * Requires Plugins: woocommerce
 * Requires at least: 5.8
 * Tested up to: 6.7
 * WC requires at least: 5.8
 * WC tested up to: 9.7.1
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
    $columns['variation_stock'] = __( 'Stock Quantity', 'textdomain' );
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
                    $stock_quantities[] = ( $stock_quantity !== null ) ? $stock_quantity : __( 'N/A', 'textdomain' );
                }
            }
            echo implode( ', ', $stock_quantities );
        } else {
            echo '-';
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

// Define custom sorting method for the stock quantity column
function shieldclimb_admin_variation_stock_display_column_orderby( $vars ) {
    if ( isset( $vars['orderby'] ) && 'stock' === $vars['orderby'] ) {
        $vars = array_merge( $vars, array(
            'meta_key' => '_stock',
            'orderby' => 'meta_value_num'
        ) );
    }
    return $vars;
}
add_filter( 'request', 'shieldclimb_admin_variation_stock_display_column_orderby' );

?>