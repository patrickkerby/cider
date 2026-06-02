{{--
  Cart Page — Prairie Bears markup with mobile-friendly row layout (style only).
  Business rules (shipping, cider notices, minimums) live in app/filters.php and page content.

  @see https://woocommerce.com/document/template-structure/
  @package WooCommerce/Templates
  @version 3.8.0
--}}

@php
  defined('ABSPATH') || exit;
@endphp

<div class="row justify-content-center">
  <div class="col-md-8">
    <h4 class="cart-items-heading">{{ __('Your Items:', 'sage') }}</h4>

    <form class="woocommerce-cart-form" action="{{ esc_url(wc_get_cart_url()) }}" method="post">
      {{-- Alerts inside the form so WooCommerce AJAX cart updates refresh them (can count, shipping notices). --}}
      @php do_action('woocommerce_before_cart'); @endphp
      @php do_action('woocommerce_before_cart_table'); @endphp

      <table class="shop_table cart woocommerce-cart-form__contents" cellspacing="0">
        <thead>
          <tr>
            <th class="product-remove"><span class="screen-reader-text">{{ __('Remove item', 'woocommerce') }}</span></th>
            <th class="product-name">{{ __('Product', 'woocommerce') }}</th>
            <th class="product-quantity">{{ __('Quantity', 'woocommerce') }}</th>
            <th class="product-subtotal">{{ __('Subtotal', 'woocommerce') }}</th>
          </tr>
        </thead>
        <tbody>
          @php do_action('woocommerce_before_cart_contents'); @endphp

          @foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item)
            @php
              $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
              $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);
            @endphp

            @if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_cart_item_visible', true, $cart_item, $cart_item_key))
              @php
                $product_permalink = apply_filters(
                  'woocommerce_cart_item_permalink',
                  $_product->is_visible() ? $_product->get_permalink($cart_item) : '',
                  $cart_item,
                  $cart_item_key
                );
              @endphp

              @php
                $thumbnail = apply_filters(
                  'woocommerce_cart_item_thumbnail',
                  $_product->get_image('woocommerce_thumbnail'),
                  $cart_item,
                  $cart_item_key
                );
              @endphp

              <tr class="cart-line woocommerce-cart-form__cart-item {{ esc_attr(apply_filters('woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key)) }}">
                <td colspan="4" class="cart-line__cell">
                  <div class="cart-line">
                    <div class="cart-line__thumbnail">
                      @if ($product_permalink)
                        <a href="{{ esc_url($product_permalink) }}">{!! $thumbnail !!}</a>
                      @else
                        {!! $thumbnail !!}
                      @endif
                    </div>

                    <div class="cart-line__main">
                      <div class="cart-line__name product-name">
                        @if (!$product_permalink)
                          {!! wp_kses_post(apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key)) !!}
                        @else
                          {!! wp_kses_post(apply_filters('woocommerce_cart_item_name', sprintf('<a href="%s">%s</a>', esc_url($product_permalink), $_product->get_name()), $cart_item, $cart_item_key)) !!}
                        @endif
                        @php do_action('woocommerce_after_cart_item_name', $cart_item, $cart_item_key); @endphp
                      </div>

                      @if (wc_get_formatted_cart_item_data($cart_item) || ($_product->backorders_require_notification() && $_product->is_on_backorder($cart_item['quantity'])))
                        <div class="cart-line__meta product-meta">
                          {!! wc_get_formatted_cart_item_data($cart_item) !!}
                          @if ($_product->backorders_require_notification() && $_product->is_on_backorder($cart_item['quantity']))
                            {!! wp_kses_post(apply_filters('woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__('Available on backorder', 'woocommerce') . '</p>', $product_id)) !!}
                          @endif
                        </div>
                      @endif

                      <div class="cart-line__quantity product-quantity">
                        @if ($_product->is_sold_individually())
                          {!! sprintf('1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key) !!}
                        @else
                          @php
                            $product_quantity = woocommerce_quantity_input([
                              'input_name'   => "cart[{$cart_item_key}][qty]",
                              'input_value'  => $cart_item['quantity'],
                              'max_value'    => $_product->get_max_purchase_quantity(),
                              'min_value'    => '0',
                              'product_name' => $_product->get_name(),
                            ], $_product, false);
                          @endphp
                          {!! apply_filters('woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item) !!}
                        @endif
                      </div>
                    </div>

                    <div class="cart-line__aside">
                      <div class="cart-line__remove product-remove">
                        {!! apply_filters(
                          'woocommerce_cart_item_remove_link',
                          sprintf(
                            '<a href="%s" class="remove" aria-label="%s" data-product_id="%s" data-product_sku="%s">&times;</a>',
                            esc_url(wc_get_cart_remove_url($cart_item_key)),
                            esc_attr__('Remove this item', 'woocommerce'),
                            esc_attr($product_id),
                            esc_attr($_product->get_sku())
                          ),
                          $cart_item_key
                        ) !!}
                      </div>
                      <div class="cart-line__subtotal product-subtotal">
                        {!! apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key) !!}
                      </div>
                    </div>
                  </div>
                </td>
              </tr>
            @endif
          @endforeach

          @php do_action('woocommerce_cart_contents'); @endphp

          <tr>
            <td colspan="6" class="actions">
              @if (wc_coupons_enabled())
                <div class="coupon">
                  <label for="coupon_code">{{ __('Coupon:', 'woocommerce') }}</label>
                  <input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="{{ esc_attr__('Coupon code', 'woocommerce') }}" />
                  <button type="submit" class="button" name="apply_coupon" value="{{ esc_attr__('Apply coupon', 'woocommerce') }}">{{ __('Apply coupon', 'woocommerce') }}</button>
                  @php do_action('woocommerce_cart_coupon'); @endphp
                </div>
              @endif

              <button type="submit" class="button" name="update_cart" value="{{ esc_attr__('Update cart', 'woocommerce') }}">{{ __('Update cart', 'woocommerce') }}</button>

              @php do_action('woocommerce_cart_actions'); @endphp
              @php wp_nonce_field('woocommerce-cart', 'woocommerce-cart-nonce'); @endphp
            </td>
          </tr>

          @php do_action('woocommerce_after_cart_contents'); @endphp
        </tbody>
      </table>

      @php do_action('woocommerce_after_cart_table'); @endphp
    </form>

    @php do_action('woocommerce_before_cart_collaterals'); @endphp

    <div class="cart-collaterals col-sm-12">
      @php
        /**
         * @hooked woocommerce_cross_sell_display
         * @hooked woocommerce_cart_totals - 10
         */
        do_action('woocommerce_cart_collaterals');
      @endphp
    </div>

    @php do_action('woocommerce_after_cart'); @endphp
  </div>
</div>
