<?php

namespace App;

/**
 * Home page product grid: filters, brand/flat metadata, book exclusion.
 */

function pbc_is_home_product_grid(): bool
{
    return ! empty($GLOBALS['pbc_rendering_home_product_grid']);
}

/**
 * Product brand taxonomy: ACF "brand" (legacy) preferred over WooCommerce product_brand.
 *
 * @return string|null Taxonomy slug, or null when no brand taxonomy is registered.
 */
function pbc_product_brand_taxonomy(): ?string
{
    $taxonomy = null;

    if (taxonomy_exists('brand')) {
        $taxonomy = 'brand';
    } elseif (taxonomy_exists('product_brand')) {
        $taxonomy = 'product_brand';
    }

    return apply_filters('pbc_product_brand_taxonomy', $taxonomy);
}

/**
 * @return array<int|string> Term slugs, names, or IDs (fallback if auto-resolve finds nothing).
 */
function pbc_true_north_brand_identifiers(): array
{
    return apply_filters('pbc_true_north_brand_identifiers', [
        'true-north-cider',
        'True North Cider',
    ]);
}

/**
 * @return array<int|string> Term slugs, names, or IDs (fallback if auto-resolve finds nothing).
 */
function pbc_prairie_bears_brand_identifiers(): array
{
    return apply_filters('pbc_prairie_bears_brand_identifiers', [
        'prairie-bears-cider',
        'prairie-bears',
        'Prairie Bears Cider',
    ]);
}

/**
 * Resolve brand term IDs from the ACF `brand` taxonomy by name/slug patterns.
 *
 * @param  array<int, string>  $slug_contains  Substrings to match in term slug.
 * @param  array<int, string>  $name_contains  Substrings to match in term name (case-insensitive).
 * @return array<int>
 */
function pbc_resolve_brand_term_ids(array $slug_contains, array $name_contains): array
{
    $taxonomy = pbc_product_brand_taxonomy();

    if (! $taxonomy) {
        return [];
    }

    $terms = get_terms([
        'taxonomy'   => $taxonomy,
        'hide_empty' => false,
    ]);

    if (is_wp_error($terms) || empty($terms)) {
        return [];
    }

    $ids = [];

    foreach ($terms as $term) {
        $slug = strtolower($term->slug);
        $name = strtolower($term->name);

        foreach ($slug_contains as $needle) {
            $needle = strtolower($needle);

            if ($needle !== '' && str_contains($slug, $needle)) {
                $ids[] = (int) $term->term_id;
                continue 2;
            }
        }

        foreach ($name_contains as $needle) {
            $needle = strtolower($needle);

            if ($needle !== '' && str_contains($name, $needle)) {
                $ids[] = (int) $term->term_id;
                break;
            }
        }
    }

    return array_values(array_unique($ids));
}

function pbc_product_has_brand_term(int $product_id, array $identifiers): bool
{
    $taxonomy = pbc_product_brand_taxonomy();

    if (! $taxonomy) {
        return false;
    }

    foreach ($identifiers as $identifier) {
        if (is_numeric($identifier)) {
            if (has_term((int) $identifier, $taxonomy, $product_id)) {
                return true;
            }

            continue;
        }

        $identifier = (string) $identifier;
        $slug       = sanitize_title($identifier);

        if ($slug !== '' && has_term($slug, $taxonomy, $product_id)) {
            return true;
        }

        $term = get_term_by('name', $identifier, $taxonomy);

        if ($term && ! is_wp_error($term) && has_term((int) $term->term_id, $taxonomy, $product_id)) {
            return true;
        }
    }

    $product_terms = wp_get_post_terms($product_id, $taxonomy, ['fields' => 'ids']);

    if (is_wp_error($product_terms) || empty($product_terms)) {
        return false;
    }

    foreach ($identifiers as $identifier) {
        if (! is_numeric($identifier)) {
            continue;
        }

        if (in_array((int) $identifier, $product_terms, true)) {
            return true;
        }
    }

    return false;
}

function pbc_product_has_resolved_brand(int $product_id, string $brand_key): bool
{
    $map = [
        'tnc' => [
            'slug_contains' => apply_filters('pbc_true_north_brand_slug_contains', ['true-north']),
            'name_contains' => apply_filters('pbc_true_north_brand_name_contains', ['true north']),
            'identifiers'   => pbc_true_north_brand_identifiers(),
        ],
        'pbc' => [
            'slug_contains' => apply_filters('pbc_prairie_bears_brand_slug_contains', ['prairie-bears']),
            'name_contains' => apply_filters('pbc_prairie_bears_brand_name_contains', ['prairie bears']),
            'identifiers'   => pbc_prairie_bears_brand_identifiers(),
        ],
    ];

    if (! isset($map[$brand_key])) {
        return false;
    }

    $config = $map[$brand_key];
    $ids    = pbc_resolve_brand_term_ids($config['slug_contains'], $config['name_contains']);

    if (! empty($ids)) {
        return pbc_product_has_brand_term($product_id, $ids);
    }

    return pbc_product_has_brand_term($product_id, $config['identifiers']);
}

function pbc_product_is_true_north_cider(int $product_id): bool
{
    if (pbc_product_brand_taxonomy()) {
        return pbc_product_has_resolved_brand($product_id, 'tnc');
    }

    $slug = (string) apply_filters('pbc_true_north_cider_category_slug', 'true-north-cider');

    return has_term($slug, 'product_cat', $product_id);
}

function pbc_product_is_prairie_bears_cider(int $product_id): bool
{
    if (pbc_is_cocktail_book_product($product_id)) {
        return false;
    }

    if (pbc_product_brand_taxonomy()) {
        return pbc_product_has_resolved_brand($product_id, 'pbc');
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
