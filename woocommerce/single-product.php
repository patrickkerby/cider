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
$blade_template = locate_template( 'resources/views/woocommerce/single-product.blade.php' );

if ( $blade_template ) {
    // Let Sage's template_include filter handle the rendering
    include( get_template_directory() . '/index.php' );
} else {
    // Fallback to WooCommerce default
    wc_get_template( 'single-product.php' );
}

