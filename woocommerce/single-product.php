<?php
/**
 * The Template for displaying all single products
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product.php.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Load the Blade template through Sage's template system
$blade_template = get_stylesheet_directory() . '/resources/views/woocommerce/single-product.blade.php';

if (file_exists($blade_template) && function_exists('\App\template')) {
    echo \App\template('woocommerce.single-product');
} else {
    // Fallback to default WooCommerce template if Blade template doesn't exist
    wc_get_template_part('content', 'single-product');
}
