<?php

namespace App;

/**
 * Regional delivery events (multi-city truck runs) — ACF options, checkout route, shipping.
 */

const PBC_ORDER_ROUTE_SLUG_META  = '_pbc_delivery_route_slug';
const PBC_ORDER_ROUTE_LABEL_META = '_pbc_delivery_route_label';
const PBC_ORDER_EVENT_LABEL_META = '_pbc_regional_event_label';

function pbc_regional_event_is_active() {
    if ( ! function_exists( 'get_field' ) ) {
        return false;
    }

    if ( ! get_field( 'regional_event_enabled', 'option' ) ) {
        return false;
    }

    $now   = (int) current_time( 'timestamp' );
    $start = get_field( 'regional_event_start', 'option' );
    $end   = get_field( 'regional_event_end', 'option' );

    if ( $start ) {
        $start_ts = strtotime( $start . ' 00:00:00' );
        if ( $start_ts && $now < $start_ts ) {
            return false;
        }
    }

    if ( $end ) {
        $end_ts = strtotime( $end . ' 23:59:59' );
        if ( $end_ts && $now > $end_ts ) {
            return false;
        }
    }

    return true;
}

function pbc_regional_routes_enabled() {
    return function_exists( 'get_field' ) && (bool) get_field( 'enable_regional_routes', 'option' );
}

/**
 * Event is on, routes are enabled, and the cart contains cider.
 */
function pbc_regional_routes_apply_to_cart() {
    return pbc_regional_event_is_active()
        && pbc_regional_routes_enabled()
        && pbc_cart_contains_cider();
}

/**
 * @return array<int, array{slug: string, label: string, sort: int}>
 */
function pbc_get_regional_routes() {
    if ( ! function_exists( 'get_field' ) ) {
        return [];
    }

    $rows = get_field( 'regional_routes', 'option' );

    if ( ! is_array( $rows ) ) {
        return [];
    }

    $routes = [];

    foreach ( $rows as $row ) {
        if ( empty( $row['active'] ) ) {
            continue;
        }

        $slug = isset( $row['route_slug'] ) ? sanitize_title( $row['route_slug'] ) : '';

        if ( $slug === '' ) {
            continue;
        }

        $routes[] = [
            'slug'  => $slug,
            'label' => isset( $row['customer_label'] ) ? (string) $row['customer_label'] : $slug,
            'sort'  => isset( $row['admin_sort_order'] ) ? (int) $row['admin_sort_order'] : 0,
        ];
    }

    usort(
        $routes,
        function ( $a, $b ) {
            if ( $a['sort'] === $b['sort'] ) {
                return strcmp( $a['label'], $b['label'] );
            }

            return $a['sort'] <=> $b['sort'];
        }
    );

    return $routes;
}

function pbc_get_regional_route_by_slug( $slug ) {
    $slug = sanitize_title( $slug );

    foreach ( pbc_get_regional_routes() as $route ) {
        if ( $route['slug'] === $slug ) {
            return $route;
        }
    }

    return null;
}

function pbc_regional_event_label() {
    if ( ! function_exists( 'get_field' ) ) {
        return '';
    }

    $label = get_field( 'regional_event_admin_label', 'option' );

    return is_string( $label ) ? trim( $label ) : '';
}

function pbc_checkout_route_field_required() {
    return pbc_regional_routes_apply_to_cart() && count( pbc_get_regional_routes() ) > 0;
}

/**
 * Checkout field config for the delivery route (rendered in a dedicated callout).
 *
 * @return array<string, mixed>
 */
function pbc_get_delivery_route_checkout_field() {
    $options = [ '' => __( 'Select your delivery route', 'sage' ) ];

    foreach ( pbc_get_regional_routes() as $route ) {
        $options[ $route['slug'] ] = $route['label'];
    }

    $field = [
        'type'        => 'select',
        'label'       => __( 'Delivery route', 'sage' ),
        'required'    => true,
        'class'       => [ 'form-row-wide', 'pbc-checkout-route-field' ],
        'input_class' => [ 'pbc-checkout-route__select' ],
        'options'     => $options,
        'priority'    => 1,
    ];

    $selected = pbc_get_selected_delivery_route_slug();

    if ( $selected && isset( $options[ $selected ] ) ) {
        $field['default'] = $selected;
    }

    return $field;
}

/**
 * Cart shipping destination line when regional routes replace zone/calculator shipping.
 *
 * @return string Empty when default WooCommerce destination should be used.
 */
function pbc_get_regional_shipping_destination_message() {
    if ( ! pbc_regional_routes_apply_to_cart() ) {
        return '';
    }

    $route = pbc_get_selected_delivery_route_slug();
    $data  = $route ? pbc_get_regional_route_by_slug( $route ) : null;

    if ( $data ) {
        return sprintf(
            /* translators: %s: delivery route label */
            __( 'Regional delivery route: <strong>%s</strong>. Enter your full street address at checkout.', 'sage' ),
            esc_html( $data['label'] )
        );
    }

    return __(
        'Choose your delivery route at checkout. Your delivery stop is based on that route—not a previous address you looked up in the cart.',
        'sage'
    );
}

function pbc_render_checkout_delivery_route_section() {
    if ( ! pbc_checkout_route_field_required() || ! function_exists( 'WC' ) || ! WC()->checkout() ) {
        return;
    }

    $checkout = WC()->checkout();
    $field    = pbc_get_delivery_route_checkout_field();
    $intro    = function_exists( 'get_field' ) ? get_field( 'regional_event_checkout_message', 'option' ) : '';

    if ( ! is_string( $intro ) || trim( $intro ) === '' ) {
        $intro = __(
            'Choose when and where we deliver your cider on this regional run. Shipping is free. We still need your full address below for order records.',
            'sage'
        );
    }

    $GLOBALS['pbc_rendering_delivery_route'] = true;
    ?>
    <section class="pbc-checkout-route" aria-labelledby="pbc-checkout-route-title">
        <p class="pbc-checkout-route__eyebrow"><?php esc_html_e( 'Regional delivery', 'sage' ); ?></p>
        <h3 id="pbc-checkout-route-title" class="pbc-checkout-route__title">
            <?php esc_html_e( 'Choose your delivery route', 'sage' ); ?>
        </h3>
        <p class="pbc-checkout-route__intro"><?php echo esc_html( trim( $intro ) ); ?></p>
        <?php
        woocommerce_form_field(
            'pbc_delivery_route',
            $field,
            $checkout->get_value( 'pbc_delivery_route' )
        );
        ?>
    </section>
    <?php
    $GLOBALS['pbc_rendering_delivery_route'] = false;
}

function pbc_get_selected_delivery_route_slug() {
    if ( function_exists( 'WC' ) && WC()->session ) {
        $session_slug = WC()->session->get( 'pbc_delivery_route_slug' );

        if ( is_string( $session_slug ) && $session_slug !== '' ) {
            return sanitize_title( $session_slug );
        }
    }

    if ( isset( $_POST['pbc_delivery_route'] ) ) {
        return sanitize_title( wp_unslash( $_POST['pbc_delivery_route'] ) );
    }

    return '';
}

/**
 * ACF field group — Prairie Bears Settings options page.
 */
add_action(
    'acf/init',
    function () {
        if ( ! function_exists( 'acf_add_local_field_group' ) ) {
            return;
        }

        acf_add_local_field_group(
            [
                'key'                   => 'group_pbc_regional_delivery',
                'title'                 => 'Regional delivery event',
                'fields'                => [
                    [
                        'key'   => 'field_pbc_regional_event_tab',
                        'label' => 'Regional delivery',
                        'type'  => 'tab',
                    ],
                    [
                        'key'           => 'field_pbc_regional_event_enabled',
                        'label'         => 'Event enabled',
                        'name'          => 'regional_event_enabled',
                        'type'          => 'true_false',
                        'default_value' => 0,
                        'ui'            => 1,
                    ],
                    [
                        'key'            => 'field_pbc_regional_event_start',
                        'label'          => 'Start date',
                        'name'           => 'regional_event_start',
                        'type'           => 'date_picker',
                        'display_format' => 'Y-m-d',
                        'return_format'  => 'Y-m-d',
                    ],
                    [
                        'key'            => 'field_pbc_regional_event_end',
                        'label'          => 'End date',
                        'name'           => 'regional_event_end',
                        'type'           => 'date_picker',
                        'display_format' => 'Y-m-d',
                        'return_format'  => 'Y-m-d',
                    ],
                    [
                        'key'   => 'field_pbc_regional_event_admin_label',
                        'label' => 'Admin / order label (optional)',
                        'name'  => 'regional_event_admin_label',
                        'type'  => 'text',
                        'instructions' => 'Shown on orders for staff (e.g. "June 2026 truck run").',
                    ],
                    [
                        'key'           => 'field_pbc_enable_regional_routes',
                        'label'         => 'Enable delivery route selector',
                        'name'          => 'enable_regional_routes',
                        'type'          => 'true_false',
                        'default_value' => 0,
                        'ui'            => 1,
                        'instructions'  => 'When on and the cart has cider, checkout requires a route and regional shipping is free.',
                    ],
                    [
                        'key'           => 'field_pbc_enable_can_bulk_discount',
                        'label'         => 'Apply 24+ can bulk discount',
                        'name'          => 'enable_can_bulk_discount',
                        'type'          => 'true_false',
                        'default_value' => 0,
                        'ui'            => 1,
                        'instructions'  => 'When the regional event is active, counts singles + 4-packs + flats as total cans and applies the discount below.',
                    ],
                    [
                        'key'           => 'field_pbc_can_bulk_discount_minimum',
                        'label'         => 'Minimum cans',
                        'name'          => 'can_bulk_discount_minimum',
                        'type'          => 'number',
                        'default_value' => 24,
                        'min'           => 1,
                    ],
                    [
                        'key'           => 'field_pbc_can_bulk_discount_percent',
                        'label'         => 'Discount percent',
                        'name'          => 'can_bulk_discount_percent',
                        'type'          => 'number',
                        'default_value' => 20,
                        'min'           => 1,
                        'max'           => 100,
                        'append'        => '%',
                    ],
                    [
                        'key'           => 'field_pbc_enable_can_discount_banner',
                        'label'         => 'Show can discount message on cart',
                        'name'          => 'enable_can_discount_banner',
                        'type'          => 'true_false',
                        'default_value' => 0,
                        'ui'            => 1,
                        'instructions'  => 'Progress message (e.g. 18/24 cans). Leave message blank for default copy.',
                    ],
                    [
                        'key'          => 'field_pbc_can_discount_message',
                        'label'        => 'Can discount cart message',
                        'name'         => 'can_discount_cart_message',
                        'type'         => 'textarea',
                        'rows'         => 2,
                        'instructions' => 'Optional. Use %1$d for cans in cart and %2$d for minimum (e.g. “%1$d of %2$d cans — 20%% off at %2$d”).',
                    ],
                    [
                        'key'   => 'field_pbc_regional_event_cart_message',
                        'label' => 'Cart message (cider / regional)',
                        'name'  => 'regional_event_cart_message',
                        'type'  => 'textarea',
                        'rows'  => 3,
                    ],
                    [
                        'key'   => 'field_pbc_regional_event_checkout_message',
                        'label' => 'Checkout message',
                        'name'  => 'regional_event_checkout_message',
                        'type'  => 'textarea',
                        'rows'  => 3,
                    ],
                    [
                        'key'          => 'field_pbc_regional_routes',
                        'label'        => 'Delivery routes',
                        'name'         => 'regional_routes',
                        'type'         => 'repeater',
                        'layout'       => 'table',
                        'button_label' => 'Add route',
                        'sub_fields'   => [
                            [
                                'key'   => 'field_pbc_route_slug',
                                'label' => 'Slug',
                                'name'  => 'route_slug',
                                'type'  => 'text',
                                'instructions' => 'Stable ID (e.g. calgary, edmonton-north).',
                            ],
                            [
                                'key'   => 'field_pbc_route_customer_label',
                                'label' => 'Customer label',
                                'name'  => 'customer_label',
                                'type'  => 'text',
                            ],
                            [
                                'key'           => 'field_pbc_route_admin_sort',
                                'label'         => 'Sort order',
                                'name'          => 'admin_sort_order',
                                'type'          => 'number',
                                'default_value' => 0,
                            ],
                            [
                                'key'           => 'field_pbc_route_active',
                                'label'         => 'Active',
                                'name'          => 'active',
                                'type'          => 'true_false',
                                'default_value' => 1,
                                'ui'            => 1,
                            ],
                        ],
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
                'menu_order'            => 5,
                'position'              => 'normal',
                'style'                 => 'default',
                'label_placement'       => 'top',
                'instruction_placement' => 'label',
                'active'                => true,
            ]
        );
    },
    15
);

add_filter(
    'pbc_require_edmonton_zone_for_cider',
    function ( $required ) {
        if ( pbc_regional_routes_apply_to_cart() ) {
            return false;
        }

        return $required;
    }
);

add_filter(
    'pbc_show_cider_shipping_address_notice',
    function ( $show ) {
        if ( pbc_regional_routes_apply_to_cart() ) {
            return false;
        }

        return $show;
    }
);

add_filter(
    'pbc_cart_shipping_notice_message',
    function ( $message ) {
        if ( ! pbc_regional_routes_apply_to_cart() || ! pbc_cart_contains_cocktail_book() ) {
            return $message;
        }

        $custom = function_exists( 'get_field' ) ? get_field( 'regional_event_cart_message', 'option' ) : '';

        if ( is_string( $custom ) && trim( $custom ) !== '' ) {
            return trim( $custom );
        }

        return __(
            'This order ships on our regional delivery run. Choose your delivery route at checkout. The Cocktail Recipe Book ships with your cider at no extra shipping cost when your order meets the minimum.',
            'sage'
        );
    }
);

add_action(
    'woocommerce_before_cart',
    function () {
        if ( ! pbc_regional_routes_apply_to_cart() || pbc_cart_contains_cocktail_book() ) {
            return;
        }

        $custom = function_exists( 'get_field' ) ? get_field( 'regional_event_cart_message', 'option' ) : '';

        if ( ! is_string( $custom ) || trim( $custom ) === '' ) {
            $custom = __(
                'Cider in your cart is for our regional delivery run. Choose your delivery route at checkout — shipping is free for this event.',
                'sage'
            );
        }

        echo '<div class="shipping-notice" role="status">' . esc_html( trim( $custom ) ) . '</div>';
    },
    11
);

add_action(
    'woocommerce_before_cart',
    function () {
        if ( ! function_exists( 'get_field' ) || ! get_field( 'enable_can_discount_banner', 'option' ) ) {
            return;
        }

        if ( ! pbc_can_bulk_discount_is_active() && ! pbc_cart_total_can_quantity() ) {
            return;
        }

        $minimum = pbc_can_bulk_discount_minimum();
        $current = pbc_cart_total_can_quantity();
        $percent = (int) pbc_can_bulk_discount_percent();
        $custom  = get_field( 'can_discount_cart_message', 'option' );

        if ( is_string( $custom ) && trim( $custom ) !== '' ) {
            $message = sprintf( trim( $custom ), $current, $minimum );
        } elseif ( pbc_cart_qualifies_for_can_bulk_discount() ) {
            $message = sprintf(
                __( '%1$d cans in your cart — %2$d%% off applied.', 'sage' ),
                $current,
                $percent
            );
        } else {
            $message = sprintf(
                __( '%1$d of %2$d cans — add more for %3$d%% off all tagged cans in your cart.', 'sage' ),
                $current,
                $minimum,
                $percent
            );
        }

        echo '<div class="shipping-notice shipping-notice--info pbc-can-discount-notice" role="status" data-pbc-can-count="' . esc_attr( (string) $current ) . '" data-pbc-can-minimum="' . esc_attr( (string) $minimum ) . '">' . esc_html( $message ) . '</div>';
    },
    13
);

add_filter(
    'woocommerce_shipping_show_shipping_calculator',
    function ( $show ) {
        if ( pbc_regional_routes_apply_to_cart() ) {
            return false;
        }

        return $show;
    },
    10,
    1
);

add_filter(
    'woocommerce_package_rates',
    function ( $rates, $package ) {
        if ( ! pbc_regional_routes_apply_to_cart() ) {
            return $rates;
        }

        $slug  = pbc_get_selected_delivery_route_slug();
        $route = $slug ? pbc_get_regional_route_by_slug( $slug ) : null;
        $label = $route ? $route['label'] : __( 'Regional delivery', 'sage' );

        $rate = new \WC_Shipping_Rate(
            'pbc_regional_delivery',
            sprintf(
                /* translators: %s: delivery route label */
                __( 'Free delivery — %s', 'sage' ),
                $label
            ),
            0,
            [],
            'pbc_regional_delivery'
        );

        return [ 'pbc_regional_delivery' => $rate ];
    },
    20,
    2
);

add_filter(
    'woocommerce_checkout_fields',
    function ( $fields ) {
        if ( ! pbc_checkout_route_field_required() ) {
            return $fields;
        }

        // Registered for validation; rendered in pbc_render_checkout_delivery_route_section().
        $fields['billing']['pbc_delivery_route'] = pbc_get_delivery_route_checkout_field();

        return $fields;
    }
);

add_filter(
    'woocommerce_form_field',
    function ( $field, $key, $args, $value ) {
        if ( 'pbc_delivery_route' === $key && empty( $GLOBALS['pbc_rendering_delivery_route'] ) ) {
            return '';
        }

        return $field;
    },
    10,
    4
);

add_action( 'woocommerce_checkout_before_customer_details', 'App\pbc_render_checkout_delivery_route_section', 5 );

add_action(
    'woocommerce_checkout_update_order_review',
    function ( $posted_data ) {
        if ( ! function_exists( 'WC' ) || ! WC()->session ) {
            return;
        }

        parse_str( $posted_data, $data );

        if ( ! empty( $data['pbc_delivery_route'] ) ) {
            WC()->session->set( 'pbc_delivery_route_slug', sanitize_title( $data['pbc_delivery_route'] ) );
        }
    }
);

add_action(
    'woocommerce_checkout_process',
    function () {
        if ( ! pbc_checkout_route_field_required() ) {
            return;
        }

        $slug  = isset( $_POST['pbc_delivery_route'] ) ? sanitize_title( wp_unslash( $_POST['pbc_delivery_route'] ) ) : '';
        $route = pbc_get_regional_route_by_slug( $slug );

        if ( ! $route ) {
            wc_add_notice( __( 'Please select a valid delivery route for this order.', 'sage' ), 'error' );
        }
    }
);

add_action(
    'woocommerce_checkout_update_order_meta',
    function ( $order_id ) {
        if ( ! pbc_checkout_route_field_required() && empty( $_POST['pbc_delivery_route'] ) ) {
            return;
        }

        $slug  = isset( $_POST['pbc_delivery_route'] ) ? sanitize_title( wp_unslash( $_POST['pbc_delivery_route'] ) ) : '';
        $route = pbc_get_regional_route_by_slug( $slug );

        if ( ! $route ) {
            return;
        }

        update_post_meta( $order_id, PBC_ORDER_ROUTE_SLUG_META, $route['slug'] );
        update_post_meta( $order_id, PBC_ORDER_ROUTE_LABEL_META, $route['label'] );

        $event_label = pbc_regional_event_label();

        if ( $event_label !== '' ) {
            update_post_meta( $order_id, PBC_ORDER_EVENT_LABEL_META, $event_label );
        }

        if ( function_exists( 'WC' ) && WC()->session ) {
            WC()->session->set( 'pbc_delivery_route_slug', $route['slug'] );
        }
    }
);

add_action(
    'woocommerce_admin_order_data_after_shipping_address',
    function ( $order ) {
        $label = $order->get_meta( PBC_ORDER_ROUTE_LABEL_META );
        $slug  = $order->get_meta( PBC_ORDER_ROUTE_SLUG_META );
        $event = $order->get_meta( PBC_ORDER_EVENT_LABEL_META );

        if ( ! $label && ! $slug && ! $event ) {
            return;
        }

        echo '<p class="form-field form-field-wide"><strong>' . esc_html__( 'Regional delivery', 'sage' ) . '</strong><br>';

        if ( $event ) {
            echo esc_html( $event ) . '<br>';
        }

        if ( $label ) {
            echo esc_html( $label );

            if ( $slug ) {
                echo ' <code>(' . esc_html( $slug ) . ')</code>';
            }
        }

        echo '</p>';
    }
);

add_filter(
    'manage_edit-shop_order_columns',
    function ( $columns ) {
        $new = [];

        foreach ( $columns as $key => $label ) {
            $new[ $key ] = $label;

            if ( $key === 'order_status' ) {
                $new['pbc_delivery_route'] = __( 'Route', 'sage' );
            }
        }

        return $new;
    }
);

add_action(
    'manage_shop_order_posts_custom_column',
    function ( $column, $post_id ) {
        if ( $column !== 'pbc_delivery_route' ) {
            return;
        }

        $label = get_post_meta( $post_id, PBC_ORDER_ROUTE_LABEL_META, true );

        echo $label ? esc_html( $label ) : '—';
    },
    10,
    2
);

add_filter(
    'woocommerce_shop_order_list_table_columns',
    function ( $columns ) {
        $columns['pbc_delivery_route'] = __( 'Route', 'sage' );

        return $columns;
    }
);

add_action(
    'woocommerce_shop_order_list_table_custom_column',
    function ( $column, $order ) {
        if ( $column !== 'pbc_delivery_route' ) {
            return;
        }

        $label = $order->get_meta( PBC_ORDER_ROUTE_LABEL_META );

        echo $label ? esc_html( $label ) : '—';
    },
    10,
    2
);

add_filter(
    'woocommerce_shop_order_list_table_sortable_columns',
    function ( $columns ) {
        $columns['pbc_delivery_route'] = 'pbc_delivery_route';

        return $columns;
    }
);

add_filter(
    'request',
    function ( $vars ) {
        if ( isset( $vars['orderby'] ) && $vars['orderby'] === 'pbc_delivery_route' ) {
            $vars['meta_key'] = PBC_ORDER_ROUTE_LABEL_META;
            $vars['orderby']  = 'meta_value';
        }

        return $vars;
    }
);

$render_route_order_filter = function () {
    $routes = pbc_get_regional_routes();

    if ( empty( $routes ) ) {
        return;
    }

    $current = isset( $_GET['pbc_delivery_route'] ) ? sanitize_title( wp_unslash( $_GET['pbc_delivery_route'] ) ) : '';

    echo '<select name="pbc_delivery_route" class="postform">';
    echo '<option value="">' . esc_html__( 'All delivery routes', 'sage' ) . '</option>';

    foreach ( $routes as $route ) {
        printf(
            '<option value="%s"%s>%s</option>',
            esc_attr( $route['slug'] ),
            selected( $current, $route['slug'], false ),
            esc_html( $route['label'] )
        );
    }

    echo '</select>';
};

add_filter(
    'restrict_manage_posts',
    function ( $post_type ) use ( $render_route_order_filter ) {
        if ( $post_type !== 'shop_order' ) {
            return;
        }

        $render_route_order_filter();
    }
);

add_action(
    'woocommerce_order_list_table_restrict_manage_orders',
    function () use ( $render_route_order_filter ) {
        $render_route_order_filter();
    }
);

add_filter(
    'pre_get_posts',
    function ( $query ) {
        if ( ! is_admin() || ! $query->is_main_query() ) {
            return;
        }

        $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

        if ( ! $screen || ! in_array( $screen->id, [ 'edit-shop_order', 'woocommerce_page_wc-orders' ], true ) ) {
            return;
        }

        if ( empty( $_GET['pbc_delivery_route'] ) ) {
            return;
        }

        $slug = sanitize_title( wp_unslash( $_GET['pbc_delivery_route'] ) );

        $query->set(
            'meta_query',
            [
                [
                    'key'   => PBC_ORDER_ROUTE_SLUG_META,
                    'value' => $slug,
                ],
            ]
        );
    }
);
