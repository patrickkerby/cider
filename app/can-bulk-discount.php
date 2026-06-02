<?php

namespace App;

/**
 * Bulk can discount: count physical cans across pack variations (single / 4-pack / flat).
 *
 * ADP cart conditions use line quantity, not cans-per-unit — handle the discount in the theme.
 * Disable any overlapping ADP rule for tagged cans to avoid double discounts.
 */

function pbc_can_product_tag() {
    return apply_filters( 'pbc_can_product_tag', 'cans' );
}

/**
 * @return string[]
 */
function pbc_can_product_tag_slugs() {
    $tags = [ pbc_can_product_tag(), 'can' ];

    return array_values( array_unique( apply_filters( 'pbc_can_product_tag_slugs', $tags ) ) );
}

function pbc_product_has_can_tag( $product_id ) {
    $product_id = (int) $product_id;

    if ( $product_id <= 0 ) {
        return false;
    }

    foreach ( pbc_can_product_tag_slugs() as $tag_slug ) {
        if ( has_term( $tag_slug, 'product_tag', $product_id ) ) {
            return true;
        }
    }

    $parent_id = wp_get_post_parent_id( $product_id );

    if ( ! $parent_id ) {
        return false;
    }

    foreach ( pbc_can_product_tag_slugs() as $tag_slug ) {
        if ( has_term( $tag_slug, 'product_tag', $parent_id ) ) {
            return true;
        }
    }

    return false;
}

/**
 * @return array<string, int> Slug => cans per unit (filterable).
 */
function pbc_pack_size_can_map() {
    return apply_filters(
        'pbc_pack_size_can_map',
        [
            'single'     => 1,
            '1'          => 1,
            'one'        => 1,
            '4-pack'     => 4,
            '4 pack'     => 4,
            '4pack'      => 4,
            'four-pack'  => 4,
            'four pack'  => 4,
            '4'          => 4,
            'flat'       => 24,
            '24-pack'    => 24,
            '24 pack'    => 24,
            '24pack'     => 24,
            '24'         => 24,
            'case'       => 24,
        ]
    );
}

/**
 * @param string $value Raw attribute option slug or label.
 */
function pbc_parse_pack_size_to_cans( $value ) {
    if ( $value === '' || $value === null ) {
        return null;
    }

    $normalized = strtolower( trim( html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8' ) ) );
    $normalized = preg_replace( '/\s+/', ' ', $normalized );
    $map        = pbc_pack_size_can_map();

    if ( isset( $map[ $normalized ] ) ) {
        return (int) $map[ $normalized ];
    }

    if ( preg_match( '/\bflat\b|\b24\s*-?\s*pack\b|\bcase\b/', $normalized ) ) {
        return 24;
    }

    if ( preg_match( '/\b4\s*-?\s*pack\b|\bfour\s*-?\s*pack\b/', $normalized ) ) {
        return 4;
    }

    if ( preg_match( '/\bsingle\b|\bone\b/', $normalized ) ) {
        return 1;
    }

    if ( preg_match( '/\b(\d+)\s*-?\s*pack\b/', $normalized, $matches ) ) {
        return max( 1, (int) $matches[1] );
    }

    return null;
}

/**
 * Parse pack size from a cart/catalog title like "Original Apple - 4 pack".
 */
function pbc_parse_pack_size_from_label( $label ) {
    $label = trim( (string) $label );

    if ( $label === '' ) {
        return null;
    }

    $cans = pbc_parse_pack_size_to_cans( $label );
    if ( $cans !== null ) {
        return $cans;
    }

    if ( preg_match( '/\s[-–—]\s*(.+)$/u', $label, $matches ) ) {
        return pbc_parse_pack_size_to_cans( trim( $matches[1] ) );
    }

    return null;
}

/**
 * @param \WC_Product $product Variation or simple product.
 */
function pbc_get_cans_per_unit_for_product( $product ) {
    if ( ! $product instanceof \WC_Product ) {
        return (int) apply_filters( 'pbc_cans_per_unit', 1, null );
    }

    if ( function_exists( 'get_field' ) ) {
        $acf_cans = get_field( 'cans_per_unit', $product->get_id() );
        if ( is_numeric( $acf_cans ) && (int) $acf_cans > 0 ) {
            return (int) apply_filters( 'pbc_cans_per_unit', (int) $acf_cans, $product );
        }
    }

    if ( $product->is_type( 'variation' ) ) {
        foreach ( $product->get_attributes() as $value ) {
            $cans = pbc_parse_pack_size_to_cans( $value );
            if ( $cans !== null ) {
                return (int) apply_filters( 'pbc_cans_per_unit', $cans, $product );
            }
        }
    }

    $cans = pbc_parse_pack_size_from_label( $product->get_name() );
    if ( $cans !== null ) {
        return (int) apply_filters( 'pbc_cans_per_unit', $cans, $product );
    }

    $slug = $product->get_slug();
    $cans = pbc_parse_pack_size_to_cans( $slug );
    if ( $cans !== null ) {
        return (int) apply_filters( 'pbc_cans_per_unit', $cans, $product );
    }

    return (int) apply_filters( 'pbc_cans_per_unit', 1, $product );
}

/**
 * @param array $cart_item WooCommerce cart line.
 */
function pbc_cart_line_is_can_product( $cart_item ) {
    if ( empty( $cart_item['data'] ) || ! $cart_item['data'] instanceof \WC_Product ) {
        return false;
    }

    $product = $cart_item['data'];
    $check_id = $product->get_parent_id() ? $product->get_parent_id() : $product->get_id();

    return pbc_product_has_can_tag( $check_id );
}

/**
 * @param array $cart_item WooCommerce cart line.
 */
function pbc_get_cart_line_can_quantity( $cart_item ) {
    $line_qty = isset( $cart_item['quantity'] ) ? (int) $cart_item['quantity'] : 0;

    if ( $line_qty <= 0 || empty( $cart_item['data'] ) || ! $cart_item['data'] instanceof \WC_Product ) {
        return 0;
    }

    if ( ! empty( $cart_item['variation'] ) && is_array( $cart_item['variation'] ) ) {
        foreach ( $cart_item['variation'] as $value ) {
            $cans = pbc_parse_pack_size_to_cans( $value );
            if ( $cans !== null ) {
                return (int) apply_filters(
                    'pbc_cart_line_can_quantity',
                    $line_qty * $cans,
                    $cart_item
                );
            }
        }
    }

    $cans = pbc_parse_pack_size_from_label( $cart_item['data']->get_name() );
    if ( $cans !== null ) {
        return (int) apply_filters(
            'pbc_cart_line_can_quantity',
            $line_qty * $cans,
            $cart_item
        );
    }

    $per_unit = pbc_get_cans_per_unit_for_product( $cart_item['data'] );

    return (int) apply_filters(
        'pbc_cart_line_can_quantity',
        $line_qty * $per_unit,
        $cart_item
    );
}

function pbc_cart_total_can_quantity() {
    if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
        return 0;
    }

    $total = 0;

    foreach ( WC()->cart->get_cart() as $cart_item ) {
        if ( ! pbc_cart_line_is_can_product( $cart_item ) ) {
            continue;
        }

        $total += pbc_get_cart_line_can_quantity( $cart_item );
    }

    return (int) apply_filters( 'pbc_cart_total_can_quantity', $total );
}

function pbc_can_bulk_discount_minimum() {
    $minimum = 24;

    if ( function_exists( 'get_field' ) ) {
        $option = get_field( 'can_bulk_discount_minimum', 'option' );
        if ( is_numeric( $option ) && (int) $option > 0 ) {
            $minimum = (int) $option;
        }
    }

    return (int) apply_filters( 'pbc_can_bulk_discount_minimum', $minimum );
}

function pbc_can_bulk_discount_percent() {
    $percent = 20;

    if ( function_exists( 'get_field' ) ) {
        $option = get_field( 'can_bulk_discount_percent', 'option' );
        if ( is_numeric( $option ) && (float) $option > 0 ) {
            $percent = (float) $option;
        }
    }

    return (float) apply_filters( 'pbc_can_bulk_discount_percent', $percent );
}

function pbc_can_bulk_discount_is_active() {
    $default = false;

    if ( function_exists( 'get_field' ) && pbc_regional_event_is_active() ) {
        $default = (bool) get_field( 'enable_can_bulk_discount', 'option' );
    }

    return (bool) apply_filters( 'pbc_can_bulk_discount_is_active', $default );
}

function pbc_cart_qualifies_for_can_bulk_discount() {
    return pbc_can_bulk_discount_is_active()
        && pbc_cart_total_can_quantity() >= pbc_can_bulk_discount_minimum();
}

/**
 * @return array<string, array{cart_item: array, product: \WC_Product, base_price: float}>
 */
function pbc_get_can_bulk_discount_cart_lines() {
    if ( ! function_exists( 'WC' ) || ! WC()->cart || ! pbc_cart_qualifies_for_can_bulk_discount() ) {
        return [];
    }

    $lines = [];

    foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
        if ( ! pbc_cart_line_is_can_product( $cart_item ) ) {
            continue;
        }

        $product = $cart_item['data'];

        if ( ! $product instanceof \WC_Product ) {
            continue;
        }

        $base_price = (float) $product->get_regular_price( 'edit' );

        if ( $base_price <= 0 ) {
            $base_price = (float) $product->get_price( 'edit' );
        }

        $base_price = (float) apply_filters(
            'pbc_can_bulk_discount_base_price',
            $base_price,
            $cart_item,
            $product
        );

        if ( $base_price <= 0 ) {
            continue;
        }

        $lines[ $cart_item_key ] = [
            'cart_item'  => $cart_item,
            'product'    => $product,
            'base_price' => $base_price,
        ];
    }

    return $lines;
}

add_action(
    'woocommerce_before_calculate_totals',
    function ( $cart ) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            return;
        }

        if ( ! $cart instanceof \WC_Cart ) {
            return;
        }

        $lines = pbc_get_can_bulk_discount_cart_lines();

        if ( empty( $lines ) ) {
            return;
        }

        $percent   = pbc_can_bulk_discount_percent();
        $multiplier = 1 - ( $percent / 100 );

        foreach ( $lines as $cart_item_key => $line ) {
            $product    = $line['product'];
            $base_price = $line['base_price'];
            $new_price  = round( $base_price * $multiplier, wc_get_price_decimals() );

            $product->set_price( $new_price );

            if ( isset( $cart->cart_contents[ $cart_item_key ] ) ) {
                $cart->cart_contents[ $cart_item_key ]['pbc_can_bulk_discount'] = [
                    'base'    => $base_price,
                    'percent' => $percent,
                ];
            }
        }
    },
    999999,
    1
);

add_filter(
    'woocommerce_cart_item_price',
    function ( $price_html, $cart_item, $cart_item_key ) {
        if ( empty( $cart_item['pbc_can_bulk_discount'] ) ) {
            return $price_html;
        }

        $base    = (float) $cart_item['pbc_can_bulk_discount']['base'];
        $current = isset( $cart_item['data'] ) ? (float) $cart_item['data']->get_price() : 0;

        if ( $base <= $current ) {
            return $price_html;
        }

        return sprintf(
            '%s <ins>%s</ins>',
            wc_price( $base ),
            wc_price( $current )
        );
    },
    20,
    3
);

add_action(
    'woocommerce_checkout_create_order_line_item',
    function ( $item, $cart_item_key, $values ) {
        if ( empty( $values['pbc_can_bulk_discount'] ) ) {
            return;
        }

        $item->add_meta_data(
            __( 'Can bulk discount', 'sage' ),
            sprintf(
                '%s%% off (%d+ cans)',
                $values['pbc_can_bulk_discount']['percent'],
                pbc_can_bulk_discount_minimum()
            ),
            true
        );
    },
    10,
    3
);
