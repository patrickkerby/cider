<?php

namespace App;

/**
 * Add <body> classes
 */
add_filter('body_class', function (array $classes) {
    /** Add page slug if it doesn't exist */
    if (is_single() || is_page() && !is_front_page()) {
        if (!in_array(basename(get_permalink()), $classes)) {
            $classes[] = basename(get_permalink());
        }
    }

    /** Add class if sidebar is active */
    if (display_sidebar()) {
        $classes[] = 'sidebar-primary';
    }

    /** Clean up class names for custom templates */
    $classes = array_map(function ($class) {
        return preg_replace(['/-blade(-php)?$/', '/^page-template-views/'], '', $class);
    }, $classes);

    return array_filter($classes);
});

/**
 * Add "… Continued" to the excerpt
 */
add_filter('excerpt_more', function () {
    return ' &hellip; <a href="' . get_permalink() . '">' . __('Continued', 'sage') . '</a>';
});

/**
 * Template Hierarchy should search for .blade.php files
 */
collect([
    'index', '404', 'archive', 'author', 'category', 'tag', 'taxonomy', 'date', 'home',
    'frontpage', 'page', 'paged', 'search', 'single', 'singular', 'attachment', 'embed'
])->map(function ($type) {
    add_filter("{$type}_template_hierarchy", __NAMESPACE__.'\\filter_templates');
});

/**
 * Render page using Blade
 * Priority 999 ensures this runs AFTER WooCommerce's template_loader (which runs at 10)
 */
add_filter('template_include', function ($template) {
    collect(['get_header', 'wp_head'])->each(function ($tag) {
        ob_start();
        do_action($tag);
        $output = ob_get_clean();
        remove_all_actions($tag);
        add_action($tag, function () use ($output) {
            echo $output;
        });
    });
    $data = collect(get_body_class())->reduce(function ($data, $class) use ($template) {
        return apply_filters("sage/template/{$class}/data", $data, $template);
    }, []);
    if ($template) {
        echo template($template, $data);
        return get_stylesheet_directory().'/index.php';
    }
    return $template;
}, PHP_INT_MAX);

/**
 * Render comments.blade.php
 */
add_filter('comments_template', function ($comments_template) {
    $comments_template = str_replace(
        [get_stylesheet_directory(), get_template_directory()],
        '',
        $comments_template
    );

    $data = collect(get_body_class())->reduce(function ($data, $class) use ($comments_template) {
        return apply_filters("sage/template/{$class}/data", $data, $comments_template);
    }, []);

    $theme_template = locate_template(["views/{$comments_template}", $comments_template]);

    if ($theme_template) {
        echo template($theme_template, $data);
        return get_stylesheet_directory().'/index.php';
    }

    return $comments_template;
}, 100);

add_action('acf/init', 'App\my_acf_op_init');
function my_acf_op_init() {

    // Check function exists.
    if( function_exists('acf_add_options_page') ) {

        // Register options page.
        $option_page = acf_add_options_page(array(
            'page_title'    => __('Prairie Bears General Settings'),
            'menu_title'    => __('Prairie Bears Settings'),
            'menu_slug'     => 'theme-general-settings',
            'capability'    => 'edit_posts',
            'redirect'      => false
        ));
    }
}

/**
 * WooCommerce template location for Sage/Blade templates
 * This tells WooCommerce to look for templates in resources/views/woocommerce/
 */
add_filter('woocommerce_locate_template', function ($template, $template_name, $template_path) {
    $blade_template = str_replace('.php', '.blade.php', $template_name);
    
    // Check for Blade template in Sage's resources/views directory
    $possible_locations = [
        get_stylesheet_directory() . "/resources/views/woocommerce/{$blade_template}",
        get_stylesheet_directory() . "/resources/views/{$blade_template}",
    ];
    
    foreach ($possible_locations as $file) {
        if (file_exists($file)) {
            return $file;
        }
    }
    
    return $template;
}, 10, 3);

/**
 * Let WooCommerce know we have theme support and will handle templates
 * Don't return empty array - that causes fallback to default WP theme
 */
// REMOVED - This was causing the issue!


remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );

remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0 );
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );    // Strip out the default linking so we can control the quickview
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );     // Strip out the default linking so we can control the quickview
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );           // No prices in thumbnail view plz
// remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );           // We need to add our own button in for the quick view

// Setup for Product Modal Quickview
// remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );              // Get rid of sku and categories on product modal
/**
 * Remove related products output
 */
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );

add_filter( 'woocommerce_product_add_to_cart_text', 'App\woocommerce_add_to_cart_button_text_archives' );  
function woocommerce_add_to_cart_button_text_archives() {
    return __( 'View Product', 'woocommerce' );
}

add_action( 'woocommerce_after_shop_loop_item_title', 'App\pbc_shop_product_short_description', 35, 2 );
 
function pbc_shop_product_short_description() {
     the_excerpt();
}

// remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );


function add_custom_text_after_product_title(){
    $get_alc = get_field('alcohol_content');
    $get_vol = get_field('container_size');
    // $get_size = get_field('container_count');
    $alc = "";
    $vol = "";
    // $size = "";

    if ($get_alc) {
        $alc = ' / '.$get_alc;
    }

    // if ($get_size) {
    //     $size = $get_size.' / ';
    // }

    if ($get_vol) {
        $vol = $get_vol;
    }

    echo '<div class="cider_meta">'.$vol.$alc.'</div>';
}
add_action( 'woocommerce_single_product_summary', 'App\add_custom_text_after_product_title', 5);

// settings for product modal photo gallery. show bullets rather than thumbs. show prev and next arrows
add_filter( 'woocommerce_single_product_carousel_options', function( $options ) {
    $options['directionNav'] = true;
    $options['controlNav'] = true;
	return $options;
} );

// Modal button for Cocktail Book Product
add_action('woocommerce_after_add_to_cart_button', function () {
    global $product;
    $product_id = method_exists($product, 'get_id') ? $product->get_id() : $product->id;
    if ($product_id == 598 || $product_id == 679) {
        echo '<div class="modal-trigger-container">
                <a class="modal-trigger" href="#excerpt-modal" data-modal="excerpt-modal">Read an excerpt</a>
                <a target="_blank" class="insta" href="https://www.instagram.com/cider.cocktails/">cider.cocktails</a>
            </div>';
    }
} );

add_action( 'woocommerce_cart_totals_before_order_total', function () {
    echo '<div class="shipping-notice">Canada-wide shipping is only available for the Cocktail Recipe Book. <br><br>All cider products are only available for local Edmonton-region delivery.</div>';
} );

/**
 * Display notice on cart page if cider is in cart
 */
add_action( 'woocommerce_before_cart', function () {
    // Check if cart contains cider
    $has_cider = false;
    foreach ( WC()->cart->get_cart() as $cart_item ) {
        if ( has_term( 'cider', 'product_cat', $cart_item['product_id'] ) ) {
            $has_cider = true;
            break;
        }
    }
    
    if ( $has_cider ) {
        $postcode = WC()->customer->get_shipping_postcode();
        
        // If no shipping postcode provided yet, show info notice
        if ( empty( $postcode ) ) {
            wc_print_notice( __( 'Please calculate shipping below to verify that cider delivery is available to your location. Cider is only available for delivery within the Edmonton Region.' ), 'notice' );
        }
    }
} );

/**
 * Switch to free local delivery in Edmonton Region when cart total is $50+
 * Changes flat rate shipping cost from $9 to $0
 */
add_filter('woocommerce_package_rates', 'App\adjust_edmonton_shipping_by_cart_total', 10, 2);
function adjust_edmonton_shipping_by_cart_total($rates, $package) {
    // Get cart subtotal
    $cart_subtotal = WC()->cart->subtotal;
    
    // Check if cart is $50 or more
    if ($cart_subtotal >= 50) {
        // Get the shipping zone for this package
        $zone = \WC_Shipping_Zones::get_zone_matching_package($package);
        $zone_name = $zone->get_zone_name();
        
        // If this is the Edmonton Region zone, make shipping free
        if (!empty($zone_name) && stripos($zone_name, 'Edmonton') !== false) {
            foreach ($rates as $rate_key => $rate) {
                // Set flat_rate cost to 0 for free local delivery
                if ('flat_rate' === $rate->method_id) {
                    $rates[$rate_key]->cost = 0;
                    $rates[$rate_key]->label = 'Free Local Delivery';
                    
                    // Also zero out any taxes
                    if (!empty($rates[$rate_key]->taxes)) {
                        foreach ($rates[$rate_key]->taxes as $key => $tax) {
                            $rates[$rate_key]->taxes[$key] = 0;
                        }
                    }
                }
            }
        }
    }
    
    return $rates;
}

add_action( 'woocommerce_check_cart_items', function () {
    // Set minimum cart total amount
    $minimum_amount = 50;
    
    // Check if cart contains any cider products
    $has_cider = false;
    foreach ( WC()->cart->get_cart() as $cart_item ) {
        $product_id = $cart_item['product_id'];
        // Check if product has the "cider" category
        if ( has_term( 'cider', 'product_cat', $product_id ) ) {
            $has_cider = true;
            break;
        }
    }

    // If cart contains cider, check delivery location and minimum
    if ( $has_cider ) {
        // Get customer's shipping location
        $postcode = WC()->customer->get_shipping_postcode();
        $city = WC()->customer->get_shipping_city();
        $state = WC()->customer->get_shipping_state();
        $country = WC()->customer->get_shipping_country();
        
        // Check if shipping location is provided
        if ( ! empty( $postcode ) || ! empty( $city ) ) {
            // Get matching shipping zone for customer's location
            $package = array(
                'destination' => array(
                    'country'   => $country,
                    'state'     => $state,
                    'postcode'  => $postcode,
                    'city'      => $city,
                )
            );
            
            // Find the matching zone for this location
            $zone = \WC_Shipping_Zones::get_zone_matching_package( $package );
            $zone_name = $zone->get_zone_name();
            
            // Check if the matched zone is the Edmonton Region
            // If zone name is empty, it means it matched the default zone (not Edmonton)
            if ( empty( $zone_name ) || stripos( $zone_name, 'Edmonton' ) === false ) {
                wc_add_notice( __( "Cider products are only available for delivery within the Edmonton Region. Please remove cider from your cart or update your shipping address." ), 'error' );
            }
        }
        
        // Check minimum cart total
        $cart_subtotal = WC()->cart->subtotal;
        if ( $cart_subtotal < $minimum_amount ) {
            wc_add_notice( '' . sprintf( __( "A minimum total purchase amount of %s is required to checkout." ), wc_price( $minimum_amount ) ) . '', 'error' );
        }
    }
} );

