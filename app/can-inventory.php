<?php

namespace App;

/**
 * Shared pack-pool inventory for variable cans and bottles (single / 4-pack / flat).
 *
 * Stock is stored as physical units (singles) on the parent product. Variations
 * consume units per pack via existing pack-size helpers in can-bulk-discount.php.
 */

const PBC_CAN_POOL_MANAGE_META = '_pbc_manage_can_pool_stock';
const PBC_CAN_POOL_QTY_META    = '_pbc_can_stock_cans';
const PBC_ORDER_POOL_REDUCED   = '_pbc_can_pool_stock_reduced';
const PBC_ORDER_POOL_RESTORED  = '_pbc_can_pool_stock_restored';
const PBC_ITEM_REDUCED_CANS    = '_pbc_reduced_can_stock';

/**
 * Parent product ID for pool logic (parent for variations, self otherwise).
 */
function pbc_can_pool_parent_id(\WC_Product $product): int
{
    if ($product->is_type('variation')) {
        return (int) $product->get_parent_id();
    }

    return (int) $product->get_id();
}

/**
 * @return string[]
 */
function pbc_bottle_product_tag_slugs(): array
{
    return array_values(array_unique(apply_filters('pbc_bottle_product_tag_slugs', ['bottles', 'bottle'])));
}

function pbc_product_has_bottle_tag(int $product_id): bool
{
    $product_id = (int) $product_id;

    if ($product_id <= 0) {
        return false;
    }

    foreach (pbc_bottle_product_tag_slugs() as $tag_slug) {
        if (has_term($tag_slug, 'product_tag', $product_id)) {
            return true;
        }
    }

    $parent_id = wp_get_post_parent_id($product_id);

    if ($parent_id) {
        foreach (pbc_bottle_product_tag_slugs() as $tag_slug) {
            if (has_term($tag_slug, 'product_tag', $parent_id)) {
                return true;
            }
        }
    }

    return false;
}

/**
 * Variable product with single / 4-pack / flat style variations.
 */
function pbc_variable_product_has_pack_sizes(\WC_Product $product): bool
{
    if (! $product->is_type('variable')) {
        return false;
    }

    foreach ($product->get_children() as $variation_id) {
        $variation = wc_get_product($variation_id);

        if (! $variation) {
            continue;
        }

        if (pbc_get_cans_per_unit_for_product($variation) >= 1) {
            return true;
        }
    }

    return false;
}

/**
 * Cans, bottles, or other variable cider with pack-size variations.
 */
function pbc_pack_pool_parent_is_eligible(int $parent_id): bool
{
    if ($parent_id <= 0 || pbc_is_cocktail_book_product($parent_id)) {
        return false;
    }

    $parent = wc_get_product($parent_id);

    if (! $parent || ! $parent->is_type('variable')) {
        return false;
    }

    if (pbc_product_has_can_tag($parent_id) || pbc_product_has_bottle_tag($parent_id)) {
        return true;
    }

    if (has_term('cider', 'product_cat', $parent_id) && pbc_variable_product_has_pack_sizes($parent)) {
        return true;
    }

    return (bool) apply_filters('pbc_pack_pool_parent_is_eligible', false, $parent_id, $parent);
}

/**
 * Customer-facing unit label for stock messages (cans vs bottles).
 */
function pbc_pack_pool_unit_label(int $parent_id, int $count = 1): string
{
    if (pbc_product_has_bottle_tag($parent_id)) {
        return _n('bottle', 'bottles', $count, 'sage');
    }

    return _n('can', 'cans', $count, 'sage');
}

/**
 * Pool stock applies to eligible variable parents when pool management is enabled.
 */
function pbc_can_pool_applies_to_product(\WC_Product $product): bool
{
    $parent_id = pbc_can_pool_parent_id($product);

    if ($parent_id <= 0 || ! pbc_can_pool_is_enabled($parent_id)) {
        return false;
    }

    return pbc_pack_pool_parent_is_eligible($parent_id);
}

function pbc_can_pool_is_enabled(int $parent_id): bool
{
    if ($parent_id <= 0) {
        return false;
    }

    return get_post_meta($parent_id, PBC_CAN_POOL_MANAGE_META, true) === 'yes';
}

function pbc_can_pool_get_stock(int $parent_id): int
{
    if (! pbc_can_pool_is_enabled($parent_id)) {
        return 0;
    }

    return max(0, (int) get_post_meta($parent_id, PBC_CAN_POOL_QTY_META, true));
}

function pbc_can_pool_set_stock(int $parent_id, int $quantity): void
{
    update_post_meta($parent_id, PBC_CAN_POOL_QTY_META, max(0, $quantity));
}

/**
 * Cans required for one unit of this product (variation pack size).
 */
function pbc_can_pool_cans_per_unit(\WC_Product $product): int
{
    return max(1, (int) pbc_get_cans_per_unit_for_product($product));
}

/**
 * Max purchasable units of this variation from the current pool.
 */
function pbc_can_pool_max_purchase_quantity(\WC_Product $product): int
{
    if (! pbc_can_pool_applies_to_product($product)) {
        return 0;
    }

    $parent_id = pbc_can_pool_parent_id($product);
    $stock     = pbc_can_pool_get_stock($parent_id);
    $per_unit  = pbc_can_pool_cans_per_unit($product);

    if ($per_unit < 1) {
        return 0;
    }

    return (int) floor($stock / $per_unit);
}

function pbc_can_pool_variation_in_stock(\WC_Product $product): bool
{
    return pbc_can_pool_max_purchase_quantity($product) > 0;
}

/**
 * Loop / archive: out of stock when no pack size can be fulfilled from the pool.
 */
function pbc_product_loop_is_out_of_stock(\WC_Product $product): bool
{
    if (pbc_can_pool_applies_to_product($product)) {
        $parent_id = pbc_can_pool_parent_id($product);
        $parent    = wc_get_product($parent_id);

        if ($parent && $parent->is_type('variable')) {
            foreach ($parent->get_children() as $variation_id) {
                $variation = wc_get_product($variation_id);

                if ($variation && pbc_can_pool_variation_in_stock($variation)) {
                    return false;
                }
            }

            return true;
        }

        return ! pbc_can_pool_variation_in_stock($product);
    }

    if ($product->is_type('variable')) {
        foreach ($product->get_children() as $variation_id) {
            $variation = wc_get_product($variation_id);

            if ($variation && ! $variation->is_in_stock()) {
                return true;
            }
        }

        return false;
    }

    return ! $product->is_in_stock();
}

/**
 * @param \WC_Order_Item_Product $item
 */
function pbc_get_order_item_can_quantity($item): int
{
    $product = $item->get_product();

    if (! $product) {
        return 0;
    }

    $qty = (float) $item->get_quantity();

    return (int) round($qty * pbc_can_pool_cans_per_unit($product));
}

/**
 * Cans already in cart for a parent (excluding a cart key when updating a line).
 */
function pbc_cart_cans_for_parent(int $parent_id, string $exclude_cart_key = ''): int
{
    if (! function_exists('WC') || ! WC()->cart) {
        return 0;
    }

    $total = 0;

    foreach (WC()->cart->get_cart() as $cart_key => $cart_item) {
        if ($exclude_cart_key && $cart_key === $exclude_cart_key) {
            continue;
        }

        if (empty($cart_item['data']) || ! $cart_item['data'] instanceof \WC_Product) {
            continue;
        }

        $line_parent = pbc_can_pool_parent_id($cart_item['data']);

        if ($line_parent === $parent_id) {
            $total += pbc_get_cart_line_can_quantity($cart_item);
        }
    }

    return $total;
}

function pbc_validate_can_pool_cart_quantity(\WC_Product $product, int $quantity, string $exclude_cart_key = ''): bool
{
    if (! pbc_can_pool_applies_to_product($product)) {
        return true;
    }

    $parent_id  = pbc_can_pool_parent_id($product);
    $available  = pbc_can_pool_get_stock($parent_id);
    $needed     = $quantity * pbc_can_pool_cans_per_unit($product);
    $in_cart    = pbc_cart_cans_for_parent($parent_id, $exclude_cart_key);
    $remaining  = $available - $in_cart;

    if ($needed <= $remaining) {
        return true;
    }

    $max_units = (int) floor(max(0, $remaining) / pbc_can_pool_cans_per_unit($product));

    wc_add_notice(
        sprintf(
            /* translators: 1: product name, 2: max units */
            __('Only %2$s available for %1$s based on current inventory.', 'sage'),
            $product->get_name(),
            $max_units > 0 ? (string) $max_units : __('0', 'sage')
        ),
        'error'
    );

    return false;
}

/**
 * Admin: can pool fields on variable products.
 */
function pbc_can_pool_admin_product_fields(): void
{
    global $product_object;

    if (! $product_object instanceof \WC_Product || ! $product_object->is_type('variable')) {
        return;
    }

    if (! pbc_pack_pool_parent_is_eligible($product_object->get_id())) {
        return;
    }

    echo '<div class="options_group pbc-can-pool-stock show_if_variable">';

    woocommerce_wp_checkbox([
        'id'            => PBC_CAN_POOL_MANAGE_META,
        'label'         => __('Manage pack pool stock', 'sage'),
        'description'   => __('Track one shared count in singles for all pack sizes (single, 4-pack, flat). Leave per-variation stock off.', 'sage'),
        'value'         => pbc_can_pool_is_enabled($product_object->get_id()) ? 'yes' : 'no',
        'wrapper_class' => 'show_if_variable',
    ]);

    woocommerce_wp_text_input([
        'id'                => PBC_CAN_POOL_QTY_META,
        'label'             => __('Stock (singles)', 'sage'),
        'description'       => __('Total singles available for this product (e.g. 1200 cans or 1200 bottles — not per pack).', 'sage'),
        'type'              => 'number',
        'value'             => pbc_can_pool_get_stock($product_object->get_id()),
        'custom_attributes' => [
            'step' => '1',
            'min'  => '0',
        ],
        'wrapper_class'     => 'show_if_variable',
    ]);

    echo '</div>';
}

function pbc_can_pool_save_admin_product(\WC_Product $product): void
{
    if (! $product->is_type('variable')) {
        return;
    }

    $product_id = $product->get_id();
    $manage     = isset($_POST[PBC_CAN_POOL_MANAGE_META]) ? 'yes' : 'no';

    update_post_meta($product_id, PBC_CAN_POOL_MANAGE_META, $manage);

    if (isset($_POST[PBC_CAN_POOL_QTY_META])) {
        pbc_can_pool_set_stock($product_id, (int) wc_clean(wp_unslash($_POST[PBC_CAN_POOL_QTY_META])));
    }

    if ($manage === 'yes') {
        foreach ($product->get_children() as $variation_id) {
            $variation = wc_get_product($variation_id);

            if (! $variation) {
                continue;
            }

            $variation->set_manage_stock(false);
            $variation->set_stock_status('instock');
            $variation->save();
        }
    }
}

function pbc_can_pool_reduce_order_stock(\WC_Order $order): void
{
    if ($order->get_meta(PBC_ORDER_POOL_REDUCED) === 'yes') {
        return;
    }

    $by_parent = [];
    $notes     = [];

    foreach ($order->get_items() as $item) {
        if (! $item instanceof \WC_Order_Item_Product) {
            continue;
        }

        $product = $item->get_product();

        if (! $product || ! pbc_can_pool_applies_to_product($product)) {
            continue;
        }

        $parent_id = pbc_can_pool_parent_id($product);
        $cans      = pbc_get_order_item_can_quantity($item);

        if ($cans <= 0) {
            continue;
        }

        $by_parent[$parent_id] = ($by_parent[$parent_id] ?? 0) + $cans;
        $item->add_meta_data(PBC_ITEM_REDUCED_CANS, $cans, true);
        $item->save();
    }

    if (empty($by_parent)) {
        return;
    }

    foreach ($by_parent as $parent_id => $cans) {
        $before = pbc_can_pool_get_stock((int) $parent_id);
        $after  = max(0, $before - $cans);
        pbc_can_pool_set_stock((int) $parent_id, $after);
        $notes[] = sprintf(
            '%s (%d→%d singles)',
            get_the_title((int) $parent_id),
            $before,
            $after
        );
    }

    $order->update_meta_data(PBC_ORDER_POOL_REDUCED, 'yes');
    $order->update_meta_data('_pbc_can_pool_reductions', $by_parent);
    $order->save();
    $order->add_order_note(__('Pack pool stock reduced:', 'sage') . ' ' . implode('; ', $notes));
}

function pbc_can_pool_restore_order_stock(\WC_Order $order): void
{
    if ($order->get_meta(PBC_ORDER_POOL_REDUCED) !== 'yes' || $order->get_meta(PBC_ORDER_POOL_RESTORED) === 'yes') {
        return;
    }

    $notes = [];

    foreach ($order->get_items() as $item) {
        if (! $item instanceof \WC_Order_Item_Product) {
            continue;
        }

        $cans = (int) $item->get_meta(PBC_ITEM_REDUCED_CANS);

        if ($cans <= 0) {
            continue;
        }

        $product = $item->get_product();

        if (! $product) {
            continue;
        }

        $parent_id = pbc_can_pool_parent_id($product);

        if (! pbc_can_pool_is_enabled($parent_id)) {
            continue;
        }

        $before = pbc_can_pool_get_stock($parent_id);
        $after  = $before + $cans;
        pbc_can_pool_set_stock($parent_id, $after);
        $notes[] = sprintf('%s (%d→%d singles)', get_the_title($parent_id), $before, $after);

        $item->delete_meta_data(PBC_ITEM_REDUCED_CANS);
        $item->save();
    }

    if (empty($notes)) {
        return;
    }

    $order->update_meta_data(PBC_ORDER_POOL_RESTORED, 'yes');
    $order->save();
    $order->add_order_note(__('Pack pool stock restored:', 'sage') . ' ' . implode('; ', $notes));
}

add_action('woocommerce_product_options_inventory_product_data', __NAMESPACE__ . '\\pbc_can_pool_admin_product_fields', 25);
add_action('woocommerce_admin_process_product_object', __NAMESPACE__ . '\\pbc_can_pool_save_admin_product', 20, 1);

add_filter('woocommerce_variation_manage_stock', function ($manage, $product) {
    if ($product instanceof \WC_Product_Variation && pbc_can_pool_applies_to_product($product)) {
        return false;
    }

    return $manage;
}, 10, 2);

add_filter('woocommerce_product_is_in_stock', function ($in_stock, $product) {
    if ($product instanceof \WC_Product && pbc_can_pool_applies_to_product($product)) {
        return pbc_can_pool_variation_in_stock($product);
    }

    return $in_stock;
}, 10, 2);

add_filter('woocommerce_product_backorders_allowed', function ($allowed, $product_id, $product) {
    if ($product instanceof \WC_Product && pbc_can_pool_applies_to_product($product)) {
        return false;
    }

    return $allowed;
}, 10, 3);

add_filter('woocommerce_available_variation', function ($data, $variable_product, $variation) {
    if (! $variation instanceof \WC_Product_Variation || ! pbc_can_pool_applies_to_product($variation)) {
        return $data;
    }

    $max_qty = pbc_can_pool_max_purchase_quantity($variation);
    $in_stock = $max_qty > 0;

    $data['max_qty']        = $in_stock ? $max_qty : 0;
    $data['is_in_stock']    = $in_stock;
    $data['is_purchasable'] = $in_stock;
    $data['backorders_allowed'] = false;

    if ($in_stock) {
        $parent_id = pbc_can_pool_parent_id($variation);
        $units     = pbc_can_pool_get_stock($parent_id);
        $unit_word = pbc_pack_pool_unit_label($parent_id, $units);
        $data['availability_html'] = '<p class="stock in-stock">' . esc_html(
            sprintf(
                /* translators: 1: quantity, 2: unit word (can/cans or bottle/bottles) */
                _n('%1$d %2$s available (shared across pack sizes)', '%1$d %2$s available (shared across pack sizes)', $units, 'sage'),
                $units,
                $unit_word
            )
        ) . '</p>';
    } else {
        $data['availability_html'] = '<p class="stock out-of-stock">' . esc_html__('Out of stock', 'woocommerce') . '</p>';
    }

    return $data;
}, 10, 3);

add_filter('woocommerce_quantity_input_max', function ($max, $product) {
    if ($product instanceof \WC_Product && pbc_can_pool_applies_to_product($product)) {
        $pool_max = pbc_can_pool_max_purchase_quantity($product);

        if ($max === '' || $max === -1) {
            return $pool_max > 0 ? $pool_max : 0;
        }

        return min((int) $max, $pool_max);
    }

    return $max;
}, 10, 2);

add_filter('woocommerce_add_to_cart_validation', function ($passed, $product_id, $quantity) {
    if (! $passed) {
        return false;
    }

    $product = wc_get_product($product_id);

    if ($product) {
        return pbc_validate_can_pool_cart_quantity($product, (int) $quantity);
    }

    return $passed;
}, 10, 3);

add_filter('woocommerce_update_cart_validation', function ($passed, $cart_key, $values, $quantity) {
    if (! $passed || empty($values['data']) || ! $values['data'] instanceof \WC_Product) {
        return $passed;
    }

    return pbc_validate_can_pool_cart_quantity($values['data'], (int) $quantity, (string) $cart_key);
}, 10, 4);

add_action('woocommerce_check_cart_items', function () {
    if (! function_exists('WC') || ! WC()->cart) {
        return;
    }

    $by_parent = [];

    foreach (WC()->cart->get_cart() as $cart_item) {
        if (empty($cart_item['data']) || ! $cart_item['data'] instanceof \WC_Product) {
            continue;
        }

        $product = $cart_item['data'];

        if (! pbc_can_pool_applies_to_product($product)) {
            continue;
        }

        $parent_id = pbc_can_pool_parent_id($product);
        $by_parent[$parent_id] = ($by_parent[$parent_id] ?? 0) + pbc_get_cart_line_can_quantity($cart_item);
    }

    foreach ($by_parent as $parent_id => $needed) {
        $available = pbc_can_pool_get_stock((int) $parent_id);

        if ($needed > $available) {
            wc_add_notice(
                sprintf(
                    /* translators: %s: product title */
                    __('Not enough inventory for %s. Please update your cart.', 'sage'),
                    get_the_title((int) $parent_id)
                ),
                'error'
            );
        }
    }
});

add_action('woocommerce_reduce_order_stock', __NAMESPACE__ . '\\pbc_can_pool_reduce_order_stock', 10, 1);
add_action('woocommerce_restore_order_stock', __NAMESPACE__ . '\\pbc_can_pool_restore_order_stock', 10, 1);

add_action('admin_notices', function () {
    if (! current_user_can('manage_woocommerce')) {
        return;
    }

    $screen = function_exists('get_current_screen') ? get_current_screen() : null;

    if (! $screen || ! in_array($screen->id, ['edit-product', 'product'], true)) {
        return;
    }

    if (get_option('woocommerce_manage_stock') !== 'yes') {
        echo '<div class="notice notice-warning"><p>';
        echo esc_html__('Enable WooCommerce → Settings → Products → Inventory → “Manage stock” for pack pool inventory to reduce on orders.', 'sage');
        echo '</p></div>';
    }
});
