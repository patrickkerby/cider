<?php

namespace App;

/**
 * Configurable flat-sale cart promo (regional event + ACF options).
 *
 * Example: 24+ physical cans in cart + at least N cans of featured product(s) → % off entire cart.
 * Replaces the legacy 24+ can line discount while this promo mode is active.
 */

const PBC_ORDER_FLAT_SALE_DISCOUNT_META = '_pbc_flat_sale_discount_amount';

/**
 * @return ''|'bulk_cans'|'flat_sale'
 */
function pbc_regional_promo_type(): string
{
    if (! function_exists('get_field') || ! pbc_regional_event_is_active()) {
        return '';
    }

    $type = get_field('regional_promo_type', 'option');

    if (! is_string($type) || $type === '') {
        // Legacy: bulk cans toggle only.
        return get_field('enable_can_bulk_discount', 'option') ? 'bulk_cans' : '';
    }

    if ($type === 'none' || $type === '') {
        return '';
    }

    if ($type === 'flat_sale' || $type === 'bulk_cans') {
        return $type;
    }

    return (string) apply_filters('pbc_regional_promo_type', '');
}

function pbc_flat_sale_promo_is_active(): bool
{
    return pbc_regional_promo_type() === 'flat_sale';
}

/**
 * @return int[]
 */
function pbc_flat_sale_required_product_ids(): array
{
    if (! function_exists('get_field')) {
        return [];
    }

    $raw = get_field('flat_sale_required_products', 'option');

    if (! is_array($raw)) {
        return [];
    }

    $ids = [];

    foreach ($raw as $row) {
        $id = is_object($row) && isset($row->ID) ? (int) $row->ID : (int) $row;

        if ($id > 0) {
            $ids[] = $id;
        }
    }

    $ids = array_values(array_unique($ids));

    return array_map('intval', apply_filters('pbc_flat_sale_required_product_ids', $ids));
}

function pbc_flat_sale_minimum_total_cans(): int
{
    $minimum = 24;

    if (function_exists('get_field')) {
        $option = get_field('flat_sale_minimum_total_cans', 'option');

        if (is_numeric($option) && (int) $option > 0) {
            $minimum = (int) $option;
        }
    }

    return (int) apply_filters('pbc_flat_sale_minimum_total_cans', $minimum);
}

function pbc_flat_sale_required_can_minimum(): int
{
    $minimum = 1;

    if (function_exists('get_field')) {
        $option = get_field('flat_sale_required_can_minimum', 'option');

        if (is_numeric($option) && (int) $option > 0) {
            $minimum = (int) $option;
        }
    }

    return (int) apply_filters('pbc_flat_sale_required_can_minimum', $minimum);
}

function pbc_flat_sale_cart_discount_percent(): float
{
    $percent = 10.0;

    if (function_exists('get_field')) {
        $option = get_field('flat_sale_cart_discount_percent', 'option');

        if (is_numeric($option) && (float) $option > 0) {
            $percent = (float) $option;
        }
    }

    return (float) apply_filters('pbc_flat_sale_cart_discount_percent', $percent);
}

function pbc_cart_line_matches_product_id(array $cart_item, int $parent_product_id): bool
{
    if (empty($cart_item['data']) || ! $cart_item['data'] instanceof \WC_Product) {
        return false;
    }

    $product = $cart_item['data'];
    $line_id = (int) $product->get_id();
    $parent  = (int) $product->get_parent_id();

    if ($line_id === $parent_product_id) {
        return true;
    }

    return $parent === $parent_product_id;
}

/**
 * Physical cans of featured product(s) anywhere in the cart (any pack size).
 */
function pbc_cart_physical_cans_for_required_products(): int
{
    $required_ids = pbc_flat_sale_required_product_ids();

    if (empty($required_ids) || ! function_exists('WC') || ! WC()->cart) {
        return 0;
    }

    $total = 0;

    foreach (WC()->cart->get_cart() as $cart_item) {
        foreach ($required_ids as $product_id) {
            if (! pbc_cart_line_matches_product_id($cart_item, $product_id)) {
                continue;
            }

            $total += pbc_get_cart_line_can_quantity($cart_item);
            break;
        }
    }

    return (int) apply_filters('pbc_cart_physical_cans_for_required_products', $total);
}

function pbc_cart_qualifies_for_flat_sale_promo(): bool
{
    if (! pbc_flat_sale_promo_is_active()) {
        return false;
    }

    if (pbc_cart_total_can_quantity() < pbc_flat_sale_minimum_total_cans()) {
        return false;
    }

    if (pbc_cart_physical_cans_for_required_products() < pbc_flat_sale_required_can_minimum()) {
        return false;
    }

    return (bool) apply_filters('pbc_cart_qualifies_for_flat_sale_promo', true);
}

function pbc_flat_sale_promo_label(): string
{
    $label = sprintf(
        /* translators: %s: discount percent */
        __('Flat sale (%s%% off)', 'sage'),
        rtrim(rtrim(number_format(pbc_flat_sale_cart_discount_percent(), 2, '.', ''), '0'), '.')
    );

    return (string) apply_filters('pbc_flat_sale_promo_label', $label);
}

/**
 * Entire cart: percent of line subtotal (excludes shipping/taxes; includes all product types).
 */
function pbc_flat_sale_promo_discount_amount(\WC_Cart $cart = null): float
{
    if (! $cart instanceof \WC_Cart) {
        $cart = function_exists('WC') && WC()->cart ? WC()->cart : null;
    }

    if (! $cart || ! pbc_cart_qualifies_for_flat_sale_promo()) {
        return 0.0;
    }

    $percent = pbc_flat_sale_cart_discount_percent();
    $base    = (float) $cart->get_cart_contents_total();

    if ($base <= 0) {
        return 0.0;
    }

    return (float) apply_filters(
        'pbc_flat_sale_promo_discount_amount',
        round($base * ($percent / 100), wc_get_price_decimals()),
        $cart,
        $percent
    );
}

add_filter('pbc_can_bulk_discount_is_active', function ($active) {
    if (pbc_flat_sale_promo_is_active()) {
        return false;
    }

    if (pbc_regional_promo_type() === 'bulk_cans') {
        return (bool) $active;
    }

    if (pbc_regional_promo_type() === '') {
        return false;
    }

    return (bool) $active;
});

add_filter('pbc_home_flat_sale_filter_is_available', function ($show) {
    if (! pbc_regional_event_is_active()) {
        return false;
    }

    $type = pbc_regional_promo_type();

    return $type === 'flat_sale' || $type === 'bulk_cans';
}, 20);

add_action(
    'woocommerce_cart_calculate_fees',
    function ($cart) {
        if (is_admin() && ! defined('DOING_AJAX')) {
            return;
        }

        if (! $cart instanceof \WC_Cart) {
            return;
        }

        $amount = pbc_flat_sale_promo_discount_amount($cart);

        if ($amount <= 0) {
            return;
        }

        $cart->add_fee(pbc_flat_sale_promo_label(), -1 * abs($amount), false);
    },
    20,
    1
);

add_action(
    'woocommerce_before_cart',
    function () {
        if (! pbc_flat_sale_promo_is_active() || ! function_exists('WC') || ! WC()->cart) {
            return;
        }

        if (pbc_cart_qualifies_for_flat_sale_promo()) {
            $message = sprintf(
                /* translators: 1: discount percent */
                __('%1$s%% flat sale discount applied to your order.', 'sage'),
                rtrim(rtrim(number_format(pbc_flat_sale_cart_discount_percent(), 2, '.', ''), '0'), '.')
            );
        } else {
            $need_total    = pbc_flat_sale_minimum_total_cans();
            $need_featured = pbc_flat_sale_required_can_minimum();
            $total_cans    = pbc_cart_total_can_quantity();
            $featured_cans = pbc_cart_physical_cans_for_required_products();

            if ($total_cans < $need_total) {
                $message = sprintf(
                    /* translators: 1: cans in cart, 2: cans required (a flat) */
                    __('Flat sale: reach %2$d cans total (%1$d in cart) — mix singles, 4-packs, or flats.', 'sage'),
                    $total_cans,
                    $need_total
                );
            } elseif ($featured_cans < $need_featured) {
                $message = sprintf(
                    /* translators: 1: featured cans in cart, 2: minimum featured cans */
                    __('Flat sale: include at least %2$d can(s) of the featured cider (%1$d in cart).', 'sage'),
                    $featured_cans,
                    $need_featured
                );
            } else {
                $message = '';
            }
        }

        $message = (string) apply_filters('pbc_flat_sale_cart_notice_message', $message);

        if ($message === '') {
            return;
        }

        echo '<div class="shipping-notice pbc-flat-sale-notice" role="status">' . esc_html($message) . '</div>';
    },
    12
);

add_action(
    'woocommerce_checkout_create_order',
    function ($order) {
        if (! $order instanceof \WC_Order) {
            return;
        }

        if (! pbc_cart_qualifies_for_flat_sale_promo()) {
            return;
        }

        $order->update_meta_data(PBC_ORDER_FLAT_SALE_DISCOUNT_META, pbc_flat_sale_promo_discount_amount());
        $order->update_meta_data('_pbc_flat_sale_promo_label', pbc_flat_sale_promo_label());
    }
);