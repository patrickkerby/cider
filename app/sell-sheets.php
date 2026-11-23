<?php

namespace App;

/**
 * On-demand brand sell sheet PDFs (Prairie Bears / True North).
 */

function pbc_sell_sheet_capability(): string
{
    return (string) apply_filters('pbc_sell_sheet_capability', 'manage_woocommerce');
}

function pbc_sell_sheet_font_dir(): string
{
    return get_theme_file_path().'/resources/sell-sheets/fonts';
}

function pbc_sell_sheet_file_uri(string $path): string
{
    $path = wp_normalize_path($path);

    if ($path === '' || ! file_exists($path)) {
        return '';
    }

    return 'file://'.$path;
}

/**
 * @param mixed $field ACF image array, ID, or empty.
 */
function pbc_sell_sheet_image_uri($field, int $fallback_attachment_id = 0): string
{
    $attachment_id = 0;

    if (is_array($field) && ! empty($field['ID'])) {
        $attachment_id = (int) $field['ID'];
    } elseif (is_numeric($field) && (int) $field > 0) {
        $attachment_id = (int) $field;
    }

    if ($attachment_id) {
        $path = get_attached_file($attachment_id);
        $uri  = is_string($path) ? pbc_sell_sheet_file_uri($path) : '';

        if ($uri !== '') {
            return $uri;
        }
    }

    if ($fallback_attachment_id > 0) {
        $path = get_attached_file($fallback_attachment_id);
        $uri  = is_string($path) ? pbc_sell_sheet_file_uri($path) : '';

        if ($uri !== '') {
            return $uri;
        }
    }

    return '';
}

function pbc_sell_sheet_sanitize_hex(string $color, string $fallback): string
{
    $color = trim($color);

    if (preg_match('/^#?([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $color, $matches)) {
        $hex = $matches[1];

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        return '#'.strtolower($hex);
    }

    return $fallback;
}

function pbc_sell_sheet_format_price($value): string
{
    $value = trim((string) $value);

    if ($value === '') {
        return '';
    }

    if (preg_match('/^\$/', $value)) {
        return $value;
    }

    $numeric = preg_replace('/[^0-9.\-]/', '', $value);

    if ($numeric !== '' && is_numeric($numeric)) {
        return '$'.number_format((float) $numeric, 2);
    }

    return $value;
}

function pbc_sell_sheet_format_abv($value): string
{
    $value = trim((string) $value);

    if ($value === '') {
        return '';
    }

    if (stripos($value, 'alc') !== false) {
        return $value;
    }

    if (substr($value, -1) !== '%') {
        $value .= '%';
    }

    return $value.' alc/vol.';
}

function pbc_sell_sheet_format_volume($value): string
{
    $value = trim((string) $value);

    if ($value === '') {
        return '';
    }

    if (preg_match('/[a-zA-Z]/', $value)) {
        return $value;
    }

    return $value.' mL';
}

function pbc_sell_sheet_default_accent(string $brand): string
{
    return $brand === 'tnc' ? '#3d3d3d' : '#a89934';
}

/**
 * @return array{pbc: string, tnc: string}
 */
function pbc_sell_sheet_brands(): array
{
    return [
        'pbc' => __('Prairie Bears Cider', 'sage'),
        'tnc' => __('True North Cider', 'sage'),
    ];
}

function pbc_sell_sheet_is_brand(string $brand): bool
{
    return isset(pbc_sell_sheet_brands()[$brand]);
}

function pbc_sell_sheet_products_per_page(string $brand): int
{
    return $brand === 'tnc' ? 6 : 4;
}

/**
 * @return array<string, mixed>
 */
function pbc_sell_sheet_brand_settings(string $brand): array
{
    $get = static function ($name, $default = '') {
        if (! function_exists('get_field')) {
            return $default;
        }

        $value = get_field($name, 'option');

        if ($value === null || $value === false || $value === '') {
            return $default;
        }

        return $value;
    };

    $defaults = [
        'pbc' => [
            'contact' => 'Brett Mack or Kelli Burdek',
            'email'   => 'hello@prairiebearscider.ca',
            'footer'  => '*Please inquire for keg availability and pricing',
        ],
        'tnc' => [
            'contact' => 'Brett Mack or Kelli Burdek',
            'email'   => 'contact@truenorthcider.com',
            'footer'  => '*Inquire for alternative keg sizes',
        ],
    ];

    $prefix = $brand === 'tnc' ? 'sell_sheet_tnc_' : 'sell_sheet_pbc_';
    $base   = $defaults[$brand] ?? $defaults['pbc'];

    return [
        'brand'    => $brand,
        'label'    => pbc_sell_sheet_brands()[$brand] ?? $brand,
        'season'   => (string) $get('sell_sheet_season', 'SPRING/SUMMER 2026'),
        'logo_uri' => pbc_sell_sheet_image_uri($get($prefix.'logo')),
        'contact'  => (string) $get($prefix.'contact', $base['contact']),
        'email'    => (string) $get($prefix.'email', $base['email']),
        'footer'   => (string) $get($prefix.'footer', $base['footer']),
        'fonts'    => pbc_sell_sheet_font_uris(),
    ];
}

/**
 * @return array<string, string>
 */
function pbc_sell_sheet_font_uris(): array
{
    $dir = pbc_sell_sheet_font_dir();

    return [
        'pitch_medium' => pbc_sell_sheet_file_uri($dir.'/Pitch-Medium.ttf'),
        'pitch_bold'   => pbc_sell_sheet_file_uri($dir.'/Pitch-Bold.ttf'),
        'wanderlust'   => pbc_sell_sheet_file_uri($dir.'/Wanderlust.ttf'),
    ];
}

function pbc_sell_sheet_title_looks_like_tnc(int $product_id): bool
{
    $title = strtolower(wp_strip_all_tags(get_the_title($product_id)));

    return (bool) preg_match('/\btnc\b|true\s*north/', $title);
}

function pbc_sell_sheet_product_belongs_to_brand(int $product_id, string $brand): bool
{
    if (pbc_is_cocktail_book_product($product_id)) {
        return false;
    }

    $helper_tnc = pbc_product_is_true_north_cider($product_id);
    $helper_pbc = pbc_product_is_prairie_bears_cider($product_id);

    if ($helper_tnc || $helper_pbc) {
        return $brand === 'tnc' ? $helper_tnc : $helper_pbc;
    }

    $is_tnc = pbc_sell_sheet_title_looks_like_tnc($product_id)
        || has_term('true-north-cider', 'product_cat', $product_id);

    if ($brand === 'tnc') {
        return $is_tnc;
    }

    return ! $is_tnc && has_term('cider', 'product_cat', $product_id);
}

/**
 * @return int[]
 */
function pbc_sell_sheet_product_ids_for_brand(string $brand): array
{
    $ids = get_posts([
        'post_type'      => 'product',
        'post_status'    => ['publish', 'private'],
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'orderby'        => 'title',
        'order'          => 'ASC',
    ]);

    $ids = array_map('intval', (array) $ids);

    return array_values(array_filter($ids, static function ($id) use ($brand) {
        return pbc_sell_sheet_product_belongs_to_brand($id, $brand);
    }));
}

function pbc_sell_sheet_plain_text($value): string
{
    $value = wp_strip_all_tags((string) $value);
    $value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');

    return trim(preg_replace('/\s+/', ' ', $value));
}

/**
 * @return array<string, mixed>
 */
function pbc_sell_sheet_product_payload(int $product_id, string $brand): array
{
    $product = function_exists('wc_get_product') ? wc_get_product($product_id) : null;
    $title   = get_the_title($product_id);
    $get     = static function ($name) use ($product_id) {
        return function_exists('get_field') ? get_field($name, $product_id) : null;
    };

    $display = trim((string) $get('sell_sheet_display_name'));
    $name    = $display !== '' ? $display : html_entity_decode(wp_strip_all_tags($title), ENT_QUOTES, 'UTF-8');

    if ($display === '' && $brand === 'tnc') {
        $name = trim(preg_replace('/^\s*TNC\s*[\p{Pd}:]+\s*/u', '', $name));
    }

    $description = trim((string) $get('sell_sheet_description'));

    if ($description === '') {
        $post = get_post($product_id);
        $description = $post ? $post->post_excerpt : '';
    }

    $status = (string) $get('sell_sheet_status');

    if (! in_array($status, ['available', 'coming_soon', 'out_of_stock', 'auto'], true)) {
        $status = 'available';
    }

    $woo_oos = $product instanceof \WC_Product && pbc_product_loop_is_out_of_stock($product);

    if ($status === 'auto') {
        $status = $woo_oos ? 'out_of_stock' : 'available';
    }

    $prices_raw = $get('sell_sheet_prices');
    $prices     = [];

    if (is_array($prices_raw)) {
        foreach ($prices_raw as $row) {
            $label = isset($row['label']) ? trim((string) $row['label']) : '';
            $price = isset($row['price']) ? pbc_sell_sheet_format_price($row['price']) : '';

            if ($label === '' && $price === '') {
                continue;
            }

            $prices[] = [
                'label' => $label,
                'price' => $price,
            ];
        }
    }

    $image_field = $get('sell_sheet_image');
    $thumb_id    = (int) get_post_thumbnail_id($product_id);
    $image_uri   = pbc_sell_sheet_image_uri($image_field, $thumb_id);
    $has_print   = is_array($image_field) && ! empty($image_field['ID']);

    $warnings = [];

    if (! $has_print) {
        $warnings[] = __('No print PNG', 'sage');
    }

    if ($status === 'available' && $prices === []) {
        $warnings[] = __('No wholesale prices', 'sage');
    }

    $thumb = $thumb_id ? wp_get_attachment_image_url($thumb_id, 'thumbnail') : '';

    if (! $thumb && is_array($image_field) && ! empty($image_field['sizes']['thumbnail'])) {
        $thumb = $image_field['sizes']['thumbnail'];
    }

    return [
        'id'          => $product_id,
        'name'        => $name,
        'name_html'   => nl2br(esc_html($name), false),
        'subtitle'    => trim((string) $get('sell_sheet_subtitle')),
        'description' => pbc_sell_sheet_plain_text($description),
        'accent'      => pbc_sell_sheet_sanitize_hex((string) $get('sell_sheet_accent_color'), pbc_sell_sheet_default_accent($brand)),
        'abv'         => pbc_sell_sheet_format_abv($get('alcohol_content')),
        'volume'      => pbc_sell_sheet_format_volume($get('container_size')),
        'prices'      => $prices,
        'image_uri'   => $image_uri,
        'status'      => $status,
        'thumbnail'   => $thumb,
        'warnings'    => $warnings,
        'edit_link'   => get_edit_post_link($product_id, 'raw'),
        'title'       => $title,
    ];
}

/**
 * @param int[] $product_ids
 * @return array<int, array<string, mixed>>
 */
function pbc_sell_sheet_payloads(array $product_ids, string $brand): array
{
    $rows = [];

    foreach ($product_ids as $id) {
        $id = (int) $id;

        if ($id <= 0 || ! pbc_sell_sheet_product_belongs_to_brand($id, $brand)) {
            continue;
        }

        $rows[] = pbc_sell_sheet_product_payload($id, $brand);
    }

    return $rows;
}

/**
 * @param array<int, array<string, mixed>> $products
 * @return array<int, array{available: array, coming: array, oos: array}>
 */
function pbc_sell_sheet_paginate(array $products, string $brand): array
{
    $available = [];
    $coming    = [];
    $oos       = [];

    foreach ($products as $product) {
        if ($product['status'] === 'out_of_stock') {
            $oos[] = $product;
        } elseif ($product['status'] === 'coming_soon') {
            $coming[] = $product;
        } else {
            $available[] = $product;
        }
    }

    $main     = array_merge($available, $coming);
    $per_page = pbc_sell_sheet_products_per_page($brand);
    $chunks   = $main === [] ? [[]] : array_chunk($main, $per_page);
    $pages    = [];

    foreach ($chunks as $index => $chunk) {
        $is_last = $index === (count($chunks) - 1);
        $pages[] = [
            'available' => $chunk,
            'oos'       => $is_last ? $oos : [],
        ];
    }

    if ($main === [] && $oos !== []) {
        $pages = [
            [
                'available' => [],
                'oos'       => $oos,
            ],
        ];
    }

    return $pages;
}

function pbc_sell_sheet_filename(string $brand, string $season): string
{
    $stem = $brand === 'tnc' ? 'TNC-SellSheet' : 'PrairieBearsCider-SellSheet';
    $tag  = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '', $season) ?: 'SellSheet');

    return $stem.'-'.$tag.'.pdf';
}

function pbc_sell_sheet_register_acf(): void
{
    if (! function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group(
        [
            'key'                   => 'group_pbc_sell_sheet_product',
            'title'                 => 'Sell sheet',
            'fields'                => [
                [
                    'key'           => 'field_pbc_sell_sheet_image',
                    'label'         => 'Print can PNG',
                    'name'          => 'sell_sheet_image',
                    'type'          => 'image',
                    'return_format' => 'array',
                    'preview_size'  => 'medium',
                    'library'       => 'all',
                    'mime_types'    => 'png,jpg,jpeg',
                    'instructions'  => 'High-resolution transparent PNG of the can (print quality, ~300dpi). Used only on sell sheets.',
                ],
                [
                    'key'          => 'field_pbc_sell_sheet_display_name',
                    'label'        => 'Display name',
                    'name'         => 'sell_sheet_display_name',
                    'type'         => 'textarea',
                    'rows'         => 2,
                    'instructions' => 'Optional. Use a line break for stacked names (e.g. Alberta Prairie Berry). Falls back to the product title.',
                ],
                [
                    'key'   => 'field_pbc_sell_sheet_subtitle',
                    'label' => 'Subtitle',
                    'name'  => 'sell_sheet_subtitle',
                    'type'  => 'text',
                    'instructions' => 'Optional line under the name (e.g. Raspberry + Haskap).',
                ],
                [
                    'key'          => 'field_pbc_sell_sheet_description',
                    'label'        => 'Sell sheet description',
                    'name'         => 'sell_sheet_description',
                    'type'         => 'textarea',
                    'rows'         => 4,
                    'instructions' => 'Optional override of the product short description.',
                ],
                [
                    'key'           => 'field_pbc_sell_sheet_accent_color',
                    'label'         => 'Name colour',
                    'name'          => 'sell_sheet_accent_color',
                    'type'          => 'color_picker',
                    'instructions'  => 'Product title (and Prairie Bears ABV badge) colour. Match the can.',
                    'enable_opacity' => 0,
                ],
                [
                    'key'           => 'field_pbc_sell_sheet_status',
                    'label'         => 'Sell sheet status',
                    'name'          => 'sell_sheet_status',
                    'type'          => 'select',
                    'choices'       => [
                        'available'    => 'Available',
                        'auto'         => 'Auto (follow store stock)',
                        'coming_soon'  => 'Coming soon (full card)',
                        'out_of_stock' => 'Out of stock (footer strip)',
                    ],
                    'default_value' => 'available',
                    'allow_null'    => 0,
                    'ui'            => 1,
                ],
                [
                    'key'          => 'field_pbc_sell_sheet_prices',
                    'label'        => 'Wholesale prices',
                    'name'         => 'sell_sheet_prices',
                    'type'         => 'repeater',
                    'layout'       => 'table',
                    'button_label' => 'Add price',
                    'instructions' => 'Licensee / wholesale lines for this sheet (not website retail). Examples: Singles, 12 Packs, Flats (24), 30L Keg.',
                    'sub_fields'   => [
                        [
                            'key'   => 'field_pbc_sell_sheet_price_label',
                            'label' => 'Label',
                            'name'  => 'label',
                            'type'  => 'text',
                        ],
                        [
                            'key'   => 'field_pbc_sell_sheet_price_value',
                            'label' => 'Price',
                            'name'  => 'price',
                            'type'  => 'text',
                        ],
                    ],
                ],
            ],
            'location'              => [
                [
                    [
                        'param'    => 'post_type',
                        'operator' => '==',
                        'value'    => 'product',
                    ],
                ],
            ],
            'menu_order'            => 8,
            'position'              => 'normal',
            'style'                 => 'default',
            'label_placement'       => 'top',
            'instruction_placement' => 'label',
            'active'                => true,
        ]
    );

    acf_add_local_field_group(
        [
            'key'                   => 'group_pbc_sell_sheet_settings',
            'title'                 => 'Sell sheets',
            'fields'                => [
                [
                    'key'   => 'field_pbc_sell_sheet_tab',
                    'label' => 'Sell sheets',
                    'type'  => 'tab',
                ],
                [
                    'key'           => 'field_pbc_sell_sheet_season',
                    'label'         => 'Season label',
                    'name'          => 'sell_sheet_season',
                    'type'          => 'text',
                    'default_value' => 'SPRING/SUMMER 2026',
                    'instructions'  => 'Shown in the header, e.g. SPRING/SUMMER 2026. Can be overridden when generating a sheet.',
                ],
                [
                    'key'   => 'field_pbc_sell_sheet_pbc_message',
                    'label' => 'Prairie Bears',
                    'name'  => 'sell_sheet_pbc_heading',
                    'type'  => 'message',
                    'message' => 'Logo, contacts, and footnote for Prairie Bears sheets.',
                ],
                [
                    'key'           => 'field_pbc_sell_sheet_pbc_logo',
                    'label'         => 'Prairie Bears logo',
                    'name'          => 'sell_sheet_pbc_logo',
                    'type'          => 'image',
                    'return_format' => 'array',
                    'preview_size'  => 'medium',
                    'mime_types'    => 'png,jpg,jpeg',
                ],
                [
                    'key'           => 'field_pbc_sell_sheet_pbc_contact',
                    'label'         => 'Prairie Bears contacts',
                    'name'          => 'sell_sheet_pbc_contact',
                    'type'          => 'text',
                    'default_value' => 'Brett Mack or Kelli Burdek',
                ],
                [
                    'key'           => 'field_pbc_sell_sheet_pbc_email',
                    'label'         => 'Prairie Bears email',
                    'name'          => 'sell_sheet_pbc_email',
                    'type'          => 'email',
                    'default_value' => 'hello@prairiebearscider.ca',
                ],
                [
                    'key'           => 'field_pbc_sell_sheet_pbc_footer',
                    'label'         => 'Prairie Bears footnote',
                    'name'          => 'sell_sheet_pbc_footer',
                    'type'          => 'text',
                    'default_value' => '*Please inquire for keg availability and pricing',
                ],
                [
                    'key'   => 'field_pbc_sell_sheet_tnc_message',
                    'label' => 'True North Cider',
                    'name'  => 'sell_sheet_tnc_heading',
                    'type'  => 'message',
                    'message' => 'Logo, contacts, and footnote for True North sheets.',
                ],
                [
                    'key'           => 'field_pbc_sell_sheet_tnc_logo',
                    'label'         => 'True North logo',
                    'name'          => 'sell_sheet_tnc_logo',
                    'type'          => 'image',
                    'return_format' => 'array',
                    'preview_size'  => 'medium',
                    'mime_types'    => 'png,jpg,jpeg',
                ],
                [
                    'key'           => 'field_pbc_sell_sheet_tnc_contact',
                    'label'         => 'True North contacts',
                    'name'          => 'sell_sheet_tnc_contact',
                    'type'          => 'text',
                    'default_value' => 'Brett Mack or Kelli Burdek',
                ],
                [
                    'key'           => 'field_pbc_sell_sheet_tnc_email',
                    'label'         => 'True North email',
                    'name'          => 'sell_sheet_tnc_email',
                    'type'          => 'email',
                    'default_value' => 'contact@truenorthcider.com',
                ],
                [
                    'key'           => 'field_pbc_sell_sheet_tnc_footer',
                    'label'         => 'True North footnote',
                    'name'          => 'sell_sheet_tnc_footer',
                    'type'          => 'text',
                    'default_value' => '*Inquire for alternative keg sizes',
                ],
            ],
            'location'              => [
                [
                    [
                        'param'    => 'options_page',
                        'operator' => '==',
                        'value'    => 'theme-general-settings',
                    ],
                ],
            ],
            'menu_order'            => 8,
            'position'              => 'normal',
            'style'                 => 'default',
            'label_placement'       => 'top',
            'instruction_placement' => 'label',
            'active'                => true,
        ]
    );
}

add_action('acf/init', __NAMESPACE__.'\\pbc_sell_sheet_register_acf', 20);

function pbc_sell_sheet_admin_menu(): void
{
    add_submenu_page(
        'edit.php?post_type=product',
        __('Sell Sheets', 'sage'),
        __('Sell Sheets', 'sage'),
        pbc_sell_sheet_capability(),
        'pbc-sell-sheets',
        __NAMESPACE__.'\\pbc_sell_sheet_admin_page'
    );
}

add_action('admin_menu', __NAMESPACE__.'\\pbc_sell_sheet_admin_menu');

function pbc_sell_sheet_admin_assets(string $hook): void
{
    if ($hook !== 'product_page_pbc-sell-sheets') {
        return;
    }

    $base = get_theme_file_uri().'/resources/sell-sheets';

    wp_enqueue_style('pbc-sell-sheets-admin', $base.'/admin.css', [], '1.0.0');
    wp_enqueue_script('jquery-ui-sortable');
    wp_enqueue_script('pbc-sell-sheets-admin', $base.'/admin.js', ['jquery', 'jquery-ui-sortable'], '1.0.0', true);
}

add_action('admin_enqueue_scripts', __NAMESPACE__.'\\pbc_sell_sheet_admin_assets');

function pbc_sell_sheet_admin_page(): void
{
    if (! current_user_can(pbc_sell_sheet_capability())) {
        wp_die(esc_html__('You do not have permission to generate sell sheets.', 'sage'));
    }

    $brand    = isset($_GET['brand']) ? sanitize_key(wp_unslash($_GET['brand'])) : 'pbc';
    $brand    = pbc_sell_sheet_is_brand($brand) ? $brand : 'pbc';
    $settings = pbc_sell_sheet_brand_settings($brand);
    $ids      = pbc_sell_sheet_product_ids_for_brand($brand);
    $products = pbc_sell_sheet_payloads($ids, $brand);
    $dompdf   = class_exists('\\Dompdf\\Dompdf');

    echo template('sell-sheets.admin', [
        'brand'    => $brand,
        'brands'   => pbc_sell_sheet_brands(),
        'settings' => $settings,
        'products' => $products,
        'dompdf'   => $dompdf,
        'action'   => admin_url('admin-post.php'),
    ]);
}

function pbc_sell_sheet_handle_generate(): void
{
    if (! current_user_can(pbc_sell_sheet_capability())) {
        wp_die(esc_html__('You do not have permission to generate sell sheets.', 'sage'));
    }

    check_admin_referer('pbc_generate_sell_sheet');

    $brand  = isset($_POST['brand']) ? sanitize_key(wp_unslash($_POST['brand'])) : 'pbc';
    $brand  = pbc_sell_sheet_is_brand($brand) ? $brand : 'pbc';
    $season = isset($_POST['season']) ? sanitize_text_field(wp_unslash($_POST['season'])) : '';
    $ids    = isset($_POST['product_ids']) ? array_map('intval', (array) $_POST['product_ids']) : [];
    $ids    = array_values(array_filter($ids));

    if ($ids === []) {
        wp_safe_redirect(
            add_query_arg(
                [
                    'page'    => 'pbc-sell-sheets',
                    'brand'   => $brand,
                    'message' => 'empty',
                ],
                admin_url('edit.php?post_type=product')
            )
        );
        exit;
    }

    $settings = pbc_sell_sheet_brand_settings($brand);

    if ($season !== '') {
        $settings['season'] = $season;
    }

    pbc_sell_sheet_stream_pdf($brand, $ids, $settings);
}

add_action('admin_post_pbc_generate_sell_sheet', __NAMESPACE__.'\\pbc_sell_sheet_handle_generate');

/**
 * @param int[]                $product_ids
 * @param array<string, mixed> $settings
 */
function pbc_sell_sheet_stream_pdf(string $brand, array $product_ids, array $settings): void
{
    if (! class_exists('\\Dompdf\\Dompdf')) {
        wp_die(esc_html__('PDF library is missing. Run composer install in the cider theme.', 'sage'));
    }

    @ini_set('memory_limit', '256M');
    @set_time_limit(120);

    $products = pbc_sell_sheet_payloads($product_ids, $brand);

    if ($products === []) {
        wp_die(esc_html__('No matching products for this brand.', 'sage'));
    }

    $html = pbc_sell_sheet_render_html($brand, $products, $settings);
    $pdf  = pbc_sell_sheet_dompdf_from_html($html);

    $filename = pbc_sell_sheet_filename($brand, (string) $settings['season']);

    $pdf->stream($filename, ['Attachment' => true]);
    exit;
}

/**
 * @param array<int, array<string, mixed>> $products
 * @param array<string, mixed>             $settings
 */
function pbc_sell_sheet_render_html(string $brand, array $products, array $settings): string
{
    $view = $brand === 'tnc' ? 'sell-sheets.tnc' : 'sell-sheets.pbc';

    return template($view, [
        'brand'    => $brand,
        'settings' => $settings,
        'pages'    => pbc_sell_sheet_paginate($products, $brand),
        'fonts'    => $settings['fonts'],
    ]);
}

function pbc_sell_sheet_font_cache_dir(): string
{
    $upload = wp_upload_dir();
    $dir    = trailingslashit($upload['basedir']).'sell-sheet-fonts';

    wp_mkdir_p($dir);

    return $dir;
}

/**
 * @param array<string, string> $style
 */
function pbc_sell_sheet_register_dompdf_font(\Dompdf\FontMetrics $metrics, array $style, string $path): void
{
    if ($path === '' || ! file_exists($path)) {
        return;
    }

    $metrics->registerFont($style, pbc_sell_sheet_file_uri($path));
}

function pbc_sell_sheet_dompdf_from_html(string $html): \Dompdf\Dompdf
{
    $font_dir = pbc_sell_sheet_font_cache_dir();
    $chroot   = array_values(array_unique(array_filter([
        get_theme_file_path(),
        pbc_sell_sheet_font_dir(),
        WP_CONTENT_DIR,
        isset(wp_upload_dir()['basedir']) ? wp_upload_dir()['basedir'] : '',
        $font_dir,
        sys_get_temp_dir(),
    ])));

    $options = new \Dompdf\Options();
    $options->set('isRemoteEnabled', true);
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isPhpEnabled', false);
    $options->set('defaultFont', 'Pitch');
    $options->set('dpi', 96);
    $options->set('fontDir', $font_dir);
    $options->set('fontCache', $font_dir);
    $options->setChroot($chroot);

    $dompdf = new \Dompdf\Dompdf($options);
    $dompdf->setPaper('letter', 'portrait');

    $metrics = $dompdf->getFontMetrics();
    $dir     = pbc_sell_sheet_font_dir();

    pbc_sell_sheet_register_dompdf_font($metrics, ['family' => 'Pitch', 'style' => 'normal', 'weight' => 'normal'], $dir.'/Pitch-Medium.ttf');
    pbc_sell_sheet_register_dompdf_font($metrics, ['family' => 'Pitch', 'style' => 'normal', 'weight' => '500'], $dir.'/Pitch-Medium.ttf');
    pbc_sell_sheet_register_dompdf_font($metrics, ['family' => 'Pitch', 'style' => 'normal', 'weight' => 'bold'], $dir.'/Pitch-Bold.ttf');
    pbc_sell_sheet_register_dompdf_font($metrics, ['family' => 'Pitch', 'style' => 'normal', 'weight' => '700'], $dir.'/Pitch-Bold.ttf');
    pbc_sell_sheet_register_dompdf_font($metrics, ['family' => 'Wanderlust', 'style' => 'normal', 'weight' => 'normal'], $dir.'/Wanderlust.ttf');
    pbc_sell_sheet_register_dompdf_font($metrics, ['family' => 'Wanderlust', 'style' => 'normal', 'weight' => 'bold'], $dir.'/Wanderlust.ttf');
    pbc_sell_sheet_register_dompdf_font($metrics, ['family' => 'Wanderlust', 'style' => 'normal', 'weight' => '700'], $dir.'/Wanderlust.ttf');

    $dompdf->loadHtml($html);
    $dompdf->render();

    return $dompdf;
}
