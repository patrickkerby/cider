<?php

namespace App;

/**
 * Home page product grid: filters, brand/flat metadata, book exclusion.
 */

function pbc_is_home_product_grid(): bool
{
    return ! empty($GLOBALS['pbc_rendering_home_product_grid']);
}

function pbc_true_north_cider_category_slug(): string
{
    return (string) apply_filters('pbc_true_north_cider_category_slug', 'true-north-cider');
}

function pbc_product_is_true_north_cider(int $product_id): bool
{
    return has_term(pbc_true_north_cider_category_slug(), 'product_cat', $product_id);
}

function pbc_product_is_prairie_bears_cider(int $product_id): bool
{
    if (pbc_is_cocktail_book_product($product_id)) {
        return false;
    }

    if (pbc_product_is_true_north_cider($product_id)) {
        return false;
    }

    return has_term('cider', 'product_cat', $product_id);
}

/**
 * True when the product has a flat / 24-pack variation (flat sale option).
 */
function pbc_product_has_flat_sale_option(int $product_id): bool
{
    $product = wc_get_product($product_id);

    if (! $product instanceof \WC_Product) {
        return false;
    }

    if ($product->is_type('variable')) {
        foreach ($product->get_variation_attributes() as $options) {
            foreach ((array) $options as $option) {
                if (pbc_parse_pack_size_to_cans((string) $option) === 24) {
                    return true;
                }
            }
        }

        foreach ($product->get_children() as $variation_id) {
            $variation = wc_get_product($variation_id);

            if (! $variation) {
                continue;
            }

            if (pbc_parse_pack_size_from_label($variation->get_name()) === 24) {
                return true;
            }

            foreach ($variation->get_attributes() as $value) {
                if (pbc_parse_pack_size_to_cans((string) $value) === 24) {
                    return true;
                }
            }
        }
    }

    return pbc_parse_pack_size_from_label($product->get_name()) === 24;
}

function pbc_home_product_grid_post_classes(array $classes, \WC_Product $product): array
{
    if (! pbc_is_home_product_grid()) {
        return $classes;
    }

    $product_id = $product->get_id();

    if (pbc_product_is_true_north_cider($product_id)) {
        $classes[] = 'pbc-brand-tnc';
    } elseif (pbc_product_is_prairie_bears_cider($product_id)) {
        $classes[] = 'pbc-brand-pbc';
    }

    if (pbc_product_has_flat_sale_option($product_id)) {
        $classes[] = 'pbc-has-flat-sale';
    }

    return $classes;
}

add_filter('woocommerce_post_class', __NAMESPACE__ . '\\pbc_home_product_grid_post_classes', 15, 2);

add_filter('woocommerce_shortcode_products_query', function ($query_args, $attributes, $type) {
    if (! pbc_is_home_product_grid()) {
        return $query_args;
    }

    $exclude = [(int) pbc_cocktail_book_product_id()];
    $existing = isset($query_args['post__not_in']) ? (array) $query_args['post__not_in'] : [];

    $query_args['post__not_in'] = array_values(array_unique(array_merge($existing, $exclude)));

    return $query_args;
}, 10, 3);

function pbc_render_home_product_filters(): void
{
    if (! empty($GLOBALS['pbc_home_product_filters_rendered'])) {
        return;
    }

    $GLOBALS['pbc_home_product_filters_rendered'] = true;

    ?>
    <nav class="pbc-product-filters" aria-label="<?php esc_attr_e('Filter products', 'sage'); ?>">
        <div class="pbc-product-filters__panel">
            <p class="pbc-product-filters__title"><?php esc_html_e('Filter products', 'sage'); ?></p>

            <div class="pbc-product-filters__row">
                <span class="pbc-product-filters__label" id="pbc-filter-brand-label"><?php esc_html_e('Brand', 'sage'); ?></span>
                <div class="pbc-product-filters__controls" role="group" aria-labelledby="pbc-filter-brand-label">
                    <button type="button" class="pbc-product-filters__btn is-active" data-filter-brand="all" aria-pressed="true">
                        <?php esc_html_e('All', 'sage'); ?>
                    </button>
                    <button type="button" class="pbc-product-filters__btn" data-filter-brand="pbc" aria-pressed="false">
                        <?php esc_html_e('Prairie Bears', 'sage'); ?>
                    </button>
                    <button type="button" class="pbc-product-filters__btn" data-filter-brand="tnc" aria-pressed="false">
                        <?php esc_html_e('TNC', 'sage'); ?>
                    </button>
                </div>
            </div>

            <div class="pbc-product-filters__row">
                <span class="pbc-product-filters__label" id="pbc-filter-options-label"><?php esc_html_e('Options', 'sage'); ?></span>
                <div class="pbc-product-filters__controls" role="group" aria-labelledby="pbc-filter-options-label">
                    <button type="button" class="pbc-product-filters__btn" data-filter-flat-only aria-pressed="false">
                        <?php esc_html_e('Flat sales only', 'sage'); ?>
                    </button>
                    <button type="button" class="pbc-product-filters__btn is-active" data-filter-show-oos aria-pressed="true">
                        <?php esc_html_e('Show out of stock', 'sage'); ?>
                    </button>
                </div>
            </div>
        </div>
    </nav>
    <p class="pbc-product-filters__empty" hidden>
        <?php esc_html_e('No products match these filters. Try changing your selection.', 'sage'); ?>
    </p>
    <?php
}

/**
 * WC [products] shortcode only fires woocommerce_before_shop_loop when paginate="true".
 * Filters are rendered in the Blade partial; this hook is a fallback for the shortcode path.
 */
add_action('woocommerce_shortcode_before_products_loop', function () {
    if (! pbc_is_home_product_grid()) {
        return;
    }

    if (! empty($GLOBALS['pbc_home_product_filters_rendered'])) {
        return;
    }

    pbc_render_home_product_filters();
}, 5);
